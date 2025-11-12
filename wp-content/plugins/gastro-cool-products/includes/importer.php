<?php
if (! defined('ABSPATH')) { exit; }

// Model list as provided
const GCP_MODEL_LIST = [
  'GCAP50','GCAP100','GCDC25','GCDC50','GCDC80','GCPT40','GCPT75','GCPT75-LED','GCPT85',
  'GCDC130','GCDC400','GCDC400 ECO','GCDC280','GCDC600','GCGD155','GCUC100','GCUC200',
  'GCUC300','GCGD15','GDGD60','GCGD135','GCGD175','GCKW25','GCKW50','GCKW65','GCKW90',
  'GCGD360','GCBIB110','GCBIB20','GCBIB30','GCBK160','GCPF40','GCPF80','GCFC100',
  'GCFC300','GCGW25','GCDF72','GCGF165','GCGD480','GCGD1050','GCGD1600','GCGD2200',
  'VICT50','VIRC330'
];

function gcp_norm($s){
  $s = strtoupper((string)$s);
  return preg_replace('/[^A-Z0-9]+/','',$s);
}

function gcp_build_patterns($models){
  $patterns = [];
  foreach ($models as $m){
    $base = gcp_norm($m);
    if (! $base) continue;
    $patterns[$base] = true;
    if (str_starts_with($base,'GC')){ $patterns[substr($base,2)] = true; }
    if (str_starts_with($base,'GCDC400')){
      $patterns['GCDC400ECO']=true; $patterns['GCDC400ECOSTAR']=true; $patterns['GCDC400ECOSTARPLUS']=true; $patterns['CDC400']=true; $patterns['CDC400ECO']=true;
    }
    if (str_starts_with($base,'GDGD')){ $patterns['GCGD'.substr($base,4)] = true; }
    if (str_starts_with($base,'GCKW')){ $patterns['GCGW'.substr($base,4)] = true; $patterns['KW'.substr($base,4)] = true; }
    if (str_starts_with($base,'GCGW')){ $patterns['GCKW'.substr($base,4)] = true; }
    if (str_starts_with($base,'GCAP')){ $patterns['AP'.substr($base,4)] = true; }
    if (str_ends_with($base,'LED')){ $patterns[substr($base,0,-3)] = true; }
  }
  return array_keys($patterns);
}

function gcp_item_text_norm(SimpleXMLElement $item){
  $parts = [];
  $walker = function($node) use (&$walker,&$parts){
    $text = trim((string)$node);
    if ($text !== '') $parts[] = $text;
    foreach ($node->children() as $child){ $walker($child); }
  };
  $walker($item);
  return gcp_norm(implode(' ', $parts));
}

function gcp_item_matches(SimpleXMLElement $item, $patterns){
  $hay = gcp_item_text_norm($item);
  foreach ($patterns as $p){ if ($p && str_contains($hay, $p)) return true; }
  return false;
}

function gcp_parse_price($raw){
  $raw = trim((string)$raw);
  if ($raw === '') return [null,null,$raw];
  if (preg_match('/([0-9.,]+)\s*([A-Z]{3})/',$raw,$m)){
    $amount = str_replace([','], ['.'], $m[1]);
    return [floatval($amount), $m[2], $raw];
  }
  return [null,null,$raw];
}

function gcp_to_float($s){
  $s = trim((string)$s);
  if ($s==='') return null;
  $s = str_replace([','], ['.'], $s);
  if (preg_match('/([0-9.]+)/',$s,$m)) return floatval($m[1]);
  return null;
}

function gcp_true_false($s){ return (trim((string)$s) === '1') ? 1 : 0; }

function gcp_get_first($item, $name, $ns=null){
  if ($ns){ $nodes = $item->children($ns)->{$name}; }
  else { $nodes = $item->{$name}; }
  if (isset($nodes[0])) return (string)$nodes[0];
  return '';
}

function gcp_get_all($item, $name, $ns=null){
  if ($ns){ $nodes = $item->children($ns)->{$name}; }
  else { $nodes = $item->{$name}; }
  $out = [];
  foreach ($nodes as $n){ $out[] = (string)$n; }
  return $out;
}

function gcp_find_product_by_external_id($external_id){
  $q = new WP_Query([
    'post_type' => 'product',
    'post_status' => 'any',
    'meta_key' => 'external_id',
    'meta_value' => $external_id,
    'fields' => 'ids',
    'posts_per_page' => 1,
  ]);
  return ($q->have_posts()) ? (int)$q->posts[0] : 0;
}

function gcp_upsert_term_path($taxonomy, $path){
  $parts = array_map('trim', explode('>', $path));
  $parent = 0; $last_id = 0;
  foreach ($parts as $part){
    if ($part==='') continue;
    $term = term_exists($part, $taxonomy, $parent);
    if (! $term){
      $created = wp_insert_term($part, $taxonomy, ['parent' => $parent]);
      if (is_wp_error($created)) break;
      $last_id = (int)$created['term_id'];
    } else {
      $last_id = (int)(is_array($term) ? $term['term_id'] : $term);
    }
    $parent = $last_id;
  }
  return $last_id;
}

function gcp_assign_terms_from_categories($post_id, $categories_raw){
  $paths = array_map('trim', explode('|', str_replace(["\n","\r"],'',$categories_raw)));
  $prod_terms = []; $ind_terms = [];
  foreach ($paths as $p){
    if ($p==='') continue;
    if (str_starts_with($p,'Products')){
      $tid = gcp_upsert_term_path('product_category', $p);
      if ($tid) $prod_terms[] = $tid;
    } elseif (str_starts_with($p,'Industry')){
      $tid = gcp_upsert_term_path('industry', $p);
      if ($tid) $ind_terms[] = $tid;
    }
  }
  if ($prod_terms) wp_set_object_terms($post_id, $prod_terms, 'product_category');
  if ($ind_terms) wp_set_object_terms($post_id, $ind_terms, 'industry');
}

function gcp_assign_simple_tax($post_id, $taxonomy, $value){
  $value = trim((string)$value);
  if ($value==='') return;
  $term = term_exists($value, $taxonomy);
  if (! $term){ $term = wp_insert_term($value, $taxonomy); if (is_wp_error($term)) return; }
  $tid = (int)(is_array($term)?$term['term_id']:$term);
  wp_set_object_terms($post_id, [$tid], $taxonomy, true);
}

function gcp_assign_certification($post_id, $authority, $name, $code){
  $path = trim($authority).' > '.trim($name);
  $tid = gcp_upsert_term_path('certification', $path);
  if ($tid){
    wp_set_object_terms($post_id, [$tid], 'certification', true);
    if ($code){ update_term_meta($tid, 'cert_code', $code); }
  }
}

function gcp_update_field_safe($name, $value, $post_id){
  // Use field keys for grouped fields so storage remains correct after grouping
  $key_map = [
    'stock' => 'field_stock',
    'availability' => 'field_availability',
    'refill_time' => 'field_refill_time',
    'price_amount' => 'field_price_amount',
    'price_currency' => 'field_price_currency',
    'price_raw' => 'field_price_raw',
  ];
  if (function_exists('update_field')){
    if (isset($key_map[$name])) {
      update_field($key_map[$name], $value, $post_id);
    } else {
      update_field($name, $value, $post_id);
    }
  }
  else { update_post_meta($post_id, $name, $value); }
}

function gcp_media_sideload_featured($url, $post_id){
  if (! function_exists('media_sideload_image')) require_once ABSPATH.'wp-admin/includes/media.php';
  if (! function_exists('download_url')) require_once ABSPATH.'wp-admin/includes/file.php';
  if (! function_exists('wp_read_image_metadata')) require_once ABSPATH.'wp-admin/includes/image.php';
  $att_id = media_sideload_image($url, $post_id, null, 'id');
  if (! is_wp_error($att_id)) set_post_thumbnail($post_id, (int)$att_id);
}

// Process a single <item> element (SimpleXMLElement) and update counters
function gcp_import_process_item(SimpleXMLElement $item, $download_images, &$count_created, &$count_updated, &$skipped){
  $g = 'http://base.google.com/ns/1.0';

  $external_id = gcp_get_first($item, 'id', $g);
  if ($external_id==='') { $skipped++; return; }

  $post_id = gcp_find_product_by_external_id($external_id);
  $title = (string)$item->title;
  $desc  = html_entity_decode((string)$item->description);

  if ($post_id){
    wp_update_post(['ID'=>$post_id,'post_title'=>$title,'post_content'=>$desc,'post_status'=>'publish']);
    $count_updated++;
  } else {
    $post_id = wp_insert_post(['post_type'=>'product','post_title'=>$title,'post_content'=>$desc,'post_status'=>'publish']);
    if (is_wp_error($post_id) || ! $post_id){ $skipped++; return; }
    $count_created++;
  }

  // ACF/meta mapping
  gcp_update_field_safe('external_id', $external_id, $post_id);
  gcp_update_field_safe('gtin', gcp_get_first($item,'gtin',$g), $post_id);
  gcp_update_field_safe('stock', intval(gcp_get_first($item,'stock',$g)), $post_id);
  gcp_update_field_safe('availability', gcp_get_first($item,'availability',$g), $post_id);
  gcp_update_field_safe('refill_time', intval(gcp_get_first($item,'refillTime',$g)), $post_id);

  [$price_amount, $price_cur, $price_raw] = gcp_parse_price(gcp_get_first($item,'price',$g));
  gcp_update_field_safe('price_raw', $price_raw, $post_id);
  if ($price_amount!==null) gcp_update_field_safe('price_amount', $price_amount, $post_id);
  if ($price_cur) gcp_update_field_safe('price_currency', $price_cur, $post_id);

  gcp_update_field_safe('shipping_weight_kg', gcp_to_float(gcp_get_first($item,'shipping_weight',$g)), $post_id);
  gcp_update_field_safe('length_cm', gcp_to_float(gcp_get_first($item,'product_length',$g)), $post_id);
  gcp_update_field_safe('width_cm',  gcp_to_float(gcp_get_first($item,'product_width',$g)),  $post_id);
  gcp_update_field_safe('height_cm', gcp_to_float(gcp_get_first($item,'product_height',$g)), $post_id);
  gcp_update_field_safe('pallet_size', gcp_get_first($item,'product_pallet_size',$g), $post_id);
  gcp_update_field_safe('warehouse', gcp_get_first($item,'cust_products_warehouse',$g), $post_id);

  gcp_update_field_safe('google_active', gcp_true_false(gcp_get_first($item,'custom_google_shopping_active',$g)), $post_id);
  gcp_update_field_safe('google_cat', gcp_get_first($item,'custom_google_shopping_product_cat',$g), $post_id);
  gcp_update_field_safe('google_available_date', str_replace(['T','+00:00'],[' ', ''], gcp_get_first($item,'custom_google_shopping_availabledate',$g)), $post_id);
  for ($i=1;$i<=6;$i++){
    $tag = 'custom_google_shopping_versandpreis'.$i;
    $fld = 'shipping_price_'.$i;
    $val = gcp_to_float(gcp_get_first($item,$tag,$g));
    if ($val!==null) gcp_update_field_safe($fld, $val, $post_id);
  }

  // Images
  $img = gcp_get_first($item,'image_link',$g);
  if ($img){ gcp_update_field_safe('featured_image_source_url', esc_url_raw($img), $post_id); if ($download_images) gcp_media_sideload_featured($img, $post_id); }
  $add_imgs = array_merge(gcp_get_all($item,'additional_image_link'), gcp_get_all($item,'additional_image_link',$g));
  if ($add_imgs){
    $rows = [];
    foreach ($add_imgs as $u){ $u = trim((string)$u); if ($u!=='') $rows[] = ['url'=>esc_url_raw($u)]; }
    if ($rows) gcp_update_field_safe('additional_image_links', $rows, $post_id);
  }

  // Links & categories (raw)
  gcp_update_field_safe('source_link', (string)$item->link, $post_id);
  gcp_update_field_safe('categories_raw', gcp_get_first($item,'categories',$g) ?: (string)$item->categories, $post_id);

  // Model/master
  gcp_update_field_safe('product_model_name', gcp_get_first($item,'product_model_name',$g), $post_id);
  gcp_update_field_safe('product_3digit_mastercode', gcp_get_first($item,'product_3digit_mastercode',$g), $post_id);

  // Documents
  gcp_update_field_safe('manual_document_url', gcp_get_first($item,'manualDocument',$g) ?: (string)$item->manualDocument, $post_id);
  gcp_update_field_safe('datasheet_document_url', gcp_get_first($item,'datasheetDocument',$g) ?: (string)$item->datasheetDocument, $post_id);

  // Energy
  gcp_update_field_safe('energy_label', gcp_get_first($item,'energylabel',$g), $post_id);
  gcp_update_field_safe('energy_consumption_24h_raw', gcp_get_first($item,'energyconsumption',$g), $post_id);
  $per_year = gcp_to_float(gcp_get_first($item,'energyconsumptionperyear',$g));
  if ($per_year!==null) gcp_update_field_safe('energy_consumption_per_year', $per_year, $post_id);
  gcp_update_field_safe('eei_raw', gcp_get_first($item,'eei',$g), $post_id);
  gcp_update_field_safe('cust_gc_eprel', gcp_get_first($item,'cust_gc_eprel',$g), $post_id);
  gcp_update_field_safe('energy_label_tooltip', gcp_get_first($item,'cust_energy_labeltooltip',$g), $post_id);
  gcp_update_field_safe('energy_button_html', gcp_get_first($item,'cust_energy_button',$g), $post_id);

  // Technical
  gcp_update_field_safe('doors', gcp_get_first($item,'doors',$g), $post_id);
  gcp_update_field_safe('door_type', gcp_get_first($item,'doortype',$g), $post_id);
  gcp_update_field_safe('reversible_door', gcp_get_first($item,'reversibledoor',$g), $post_id);
  gcp_update_field_safe('interior_lighting', gcp_get_first($item,'interiorlighting',$g), $post_id);
  gcp_update_field_safe('coolant', gcp_get_first($item,'coolant',$g), $post_id);
  gcp_update_field_safe('functions', gcp_get_first($item,'functions',$g), $post_id);
  gcp_update_field_safe('climate_class', gcp_get_first($item,'climateclass',$g), $post_id);
  gcp_update_field_safe('controller', gcp_get_first($item,'controller',$g), $post_id);
  gcp_update_field_safe('shelves_raw', gcp_get_first($item,'shelves',$g), $post_id);
  gcp_update_field_safe('material_inside', gcp_get_first($item,'materialinside',$g), $post_id);
  gcp_update_field_safe('electrical_connection', gcp_get_first($item,'electricalconnection',$g), $post_id);

  // Temps
  gcp_update_field_safe('temperature_range_raw', gcp_get_first($item,'temperaturerange',$g), $post_id);
  $tf = gcp_to_float(gcp_get_first($item,'tempfrom',$g)); if ($tf!==null) gcp_update_field_safe('temp_from_c', $tf, $post_id);
  $tt = gcp_to_float(gcp_get_first($item,'temptill',$g)); if ($tt!==null) gcp_update_field_safe('temp_till_c', $tt, $post_id);

  // Dimensions & volume
  $wmm = gcp_to_float(gcp_get_first($item,'width',$g)); if ($wmm!==null) gcp_update_field_safe('width_mm', $wmm, $post_id);
  $hmm = gcp_to_float(gcp_get_first($item,'height',$g)); if ($hmm!==null) gcp_update_field_safe('height_mm', $hmm, $post_id);
  $dmm = gcp_to_float(gcp_get_first($item,'depth',$g)); if ($dmm!==null) gcp_update_field_safe('depth_mm', $dmm, $post_id);
  gcp_update_field_safe('hxwxd_inside_raw', gcp_get_first($item,'hxwxdinside',$g), $post_id);
  gcp_update_field_safe('hxwxd_outside_raw', gcp_get_first($item,'hxwxdoutside',$g), $post_id);
  $vol_l = gcp_to_float(gcp_get_first($item,'volume',$g)); if ($vol_l!==null) gcp_update_field_safe('volume_l', $vol_l, $post_id);
  gcp_update_field_safe('volume_raw', gcp_get_first($item,'volume',$g), $post_id);

  // Weight & noise
  $nw = gcp_to_float(gcp_get_first($item,'weight',$g)); if ($nw!==null) gcp_update_field_safe('net_weight_kg', $nw, $post_id);
  gcp_update_field_safe('weight_raw', gcp_get_first($item,'weight',$g), $post_id);
  $db = gcp_to_float(gcp_get_first($item,'noisevolume',$g)); if ($db!==null) gcp_update_field_safe('noise_volume_db', $db, $post_id);

  // Flags
  gcp_update_field_safe('convection_cooling', gcp_true_false(gcp_get_first($item,'convectioncooling',$g) === 'Yes' ? '1' : gcp_get_first($item,'convectioncooling',$g)), $post_id);
  gcp_update_field_safe('buildin', gcp_true_false(gcp_get_first($item,'buildin',$g) === 'Yes' ? '1' : gcp_get_first($item,'buildin',$g)), $post_id);
  gcp_update_field_safe('cust_gc_bevcooler', gcp_true_false(gcp_get_first($item,'cust_gc_bevcooler',$g)), $post_id);
  gcp_update_field_safe('cust_commercialuse', gcp_true_false(gcp_get_first($item,'cust_commercialuse',$g)), $post_id);
  gcp_update_field_safe('cust_gc_freezer', gcp_true_false(gcp_get_first($item,'cust_gc_freezer',$g)), $post_id);
  gcp_update_field_safe('cust_gc_householdapp', gcp_true_false(gcp_get_first($item,'cust_gc_householdapp',$g)), $post_id);
  gcp_update_field_safe('cust_gc_product_capacity', gcp_true_false(gcp_get_first($item,'cust_gc_product_capacity',$g)), $post_id);
  gcp_update_field_safe('cust_gc_product_capacity_cans', gcp_true_false(gcp_get_first($item,'cust_gc_product_capacity_cans',$g)), $post_id);
  gcp_update_field_safe('cust_gc_product_capacity_bottles', gcp_true_false(gcp_get_first($item,'cust_gc_product_capacity_bottles',$g)), $post_id);

  // Capacities
  $cap_can_nodes = $item->children($g)->capacitycans;
  if (isset($cap_can_nodes[0])){
    $rows = [];
    foreach ($cap_can_nodes as $n){ $t = trim((string)$n); if ($t!=='') $rows[] = ['text'=>$t]; }
    if ($rows) gcp_update_field_safe('capacity_cans', $rows, $post_id);
  }
  $cap_bottle_nodes = $item->children($g)->capacitybottles;
  if (isset($cap_bottle_nodes[0])){
    $rows = [];
    foreach ($cap_bottle_nodes as $n){ $t = trim((string)$n); if ($t!=='') $rows[] = ['text'=>$t]; }
    if ($rows) gcp_update_field_safe('capacity_bottles', $rows, $post_id);
  }

  // Shipping / storage
  $hqc = gcp_to_float(gcp_get_first($item,'gc_shipment_storage_40fthqcon',$g));
  if ($hqc!==null) gcp_update_field_safe('gc_shipment_storage_40fthqcon', $hqc, $post_id);
  gcp_update_field_safe('dreisc_seo_sitemap_priority', gcp_get_first($item,'dreisc_seo_sitemap_priority',$g), $post_id);
  gcp_update_field_safe('dreisc_seo_canonical_link_type', gcp_get_first($item,'dreisc_seo_canonical_link_type',$g), $post_id);
  gcp_update_field_safe('dreisc_seo_canonical_link_reference', gcp_get_first($item,'dreisc_seo_canonical_link_reference',$g), $post_id);

  // Taxonomies
  $cat_raw = gcp_get_first($item,'categories',$g) ?: (string)$item->categories;
  if ($cat_raw) gcp_assign_terms_from_categories($post_id, $cat_raw);
  gcp_assign_simple_tax($post_id, 'product_group', gcp_get_first($item,'cust_product_group',$g));
  $color = gcp_get_first($item,'color',$g) ?: (string)$item->color;
  if ($color) gcp_assign_simple_tax($post_id, 'color', $color);
  $cert = $item->children($g)->certification;
  if (isset($cert[0])){
    $auth = (string)$cert->children($g)->certification_authority;
    $name = (string)$cert->children($g)->certification_name;
    $code = (string)$cert->children($g)->certification_code;
    if ($auth || $name) gcp_assign_certification($post_id, $auth, $name, $code);
  }
}

function gcp_import_odoo($file_path, $download_images = false){
  $file = $file_path ?: ABSPATH.'odoo.xml';
  if (! file_exists($file)) return new WP_Error('gcp_import','File not found: '.$file);
  @set_time_limit(0);
  @ignore_user_abort(true);
  if (function_exists('wp_raise_memory_limit')) { wp_raise_memory_limit('admin'); }

  $patterns = gcp_build_patterns(GCP_MODEL_LIST);
  $count_total = 0; $count_matched = 0; $count_created = 0; $count_updated = 0; $skipped = 0;

  // Stream parse with XMLReader to avoid memory/time issues
  $reader = new XMLReader();
  if (! $reader->open($file)) return new WP_Error('gcp_import','Unable to open XML');

  while ($reader->read()){
    if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'item'){
      $count_total++;
      $nodeXml = $reader->readOuterXML();
      if ($nodeXml === '') { $skipped++; continue; }
      $item = simplexml_load_string($nodeXml);
      if (! $item) { $skipped++; continue; }
      if (! gcp_item_matches($item, $patterns)) { $skipped++; continue; }
      $count_matched++;
      gcp_import_process_item($item, $download_images, $count_created, $count_updated, $skipped);
    }
  }
  $reader->close();

  return [
    'total' => $count_total,
    'matched' => $count_matched,
    'created' => $count_created,
    'updated' => $count_updated,
    'skipped' => $skipped,
  ];
}

// Expose as WP-CLI command: wp gcp import-odoo --file=path --download-images=1
if (defined('WP_CLI') && WP_CLI) {
  WP_CLI::add_command('gcp import-odoo', function($args, $assoc_args){
    $file = isset($assoc_args['file']) ? $assoc_args['file'] : (ABSPATH.'odoo.xml');
    $download = isset($assoc_args['download-images']) && (int)$assoc_args['download-images']===1;
    $res = gcp_import_odoo($file, $download);
    if (is_wp_error($res)) { WP_CLI::error($res->get_error_message()); return; }
    WP_CLI::success('Imported: '.json_encode($res));
  });
}
