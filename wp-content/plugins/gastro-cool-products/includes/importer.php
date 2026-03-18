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

// German category term (partial match) → English URL slug (for image path fallback)
const GCP_CATEGORY_DE_EN = [
  'Dosen Dispenser'           => 'can-dispenser-cooler',
  'Dosenkühlschrank'          => 'can-cooler',
  'Dosenspender'              => 'can-dispenser-cooler',
  'Werbekühlschrank'          => 'display-cooler',
  'Werbedisplaykühlschrank'   => 'display-cooler',
  'Werbegefrierschrank'       => 'display-freezer',
  'Werbegefriertruhe'         => 'display-freezer',
  'Glastürkühlschrank'        => 'glass-door-cooler',
  'Glastür-Kühlschrank'       => 'glass-door-cooler',
  'Glastür Kühlschrank'       => 'glass-door-cooler',
  'Glastür-Gefrierschrank'    => 'glass-door-freezer',
  'Glastürgefriergerät'       => 'glass-door-freezer',
  'Getränkekühlschrank'       => 'beverage-cooler',
  'Flaschenkühlschrank'       => 'bottle-cooler',
  'Weinkühlschrank'           => 'wine-cooler',
  'Weindisplaykühlschrank'    => 'wine-display-cooler',
  'Minikühlschrank'           => 'mini-cooler',
  'Mini-Kühlschrank'          => 'mini-cooler',
  'Thekenkühlschrank'         => 'countertop-cooler',
  'KühlWürfel'                => 'cube-cooler',
  'Kühlwürfel'                => 'cube-cooler',
  'Bag-in-Box'                => 'bib-cooler',
  'BIB-Kühlschrank'           => 'bib-cooler',
  'BIB Kühlschrank'           => 'bib-cooler',
  'Milchkühlschrank'          => 'milk-cooler',
  'Tiefkühltruhe'             => 'chest-freezer',
  'Kühltruhe'                 => 'chest-cooler',
  'Gefriertruhe'              => 'chest-freezer',
  'Runde Gefriertruhe'        => 'party-freezer',
  'Gefrierschrank'            => 'upright-freezer',
  'Party Freezer'             => 'party-freezer',
  'Retro-Kühlschrank'         => 'retro-cooler',
  'Retro Kühlschrank'         => 'retro-cooler',
  'Vintage'                   => 'vintage-cooler',
  'Bar-Kühlschrank'           => 'bar-cooler',
  'POS-Kühlschrank'           => 'pos-cooler',
  'Party Cooler'              => 'party-cooler',
  'Einbaukühlschrank'         => 'built-in-cooler',
];

// Model name prefix → English URL slug (ultimate fallback when category missing)
const GCP_MODEL_PREFIX_SLUG = [
  'GCAP'  => 'can-dispenser-cooler',
  'GCDC'  => 'display-cooler',
  'GCPT'  => 'party-cooler',
  'GCGD'  => 'glass-door-cooler',
  'GCUC'  => 'undercounter-cooler',
  'GCKW'  => 'cube-cooler',
  'GCGW'  => 'cube-cooler',
  'GCGF'  => 'glass-door-freezer',
  'GCFC'  => 'chest-freezer',
  'GCPF'  => 'party-freezer',
  'GCBIB' => 'bib-cooler',
  'GCBK'  => 'beverage-cooler',
  'GCDF'  => 'display-freezer',
  'VICT'  => 'vintage-cooler',
  'VIRC'  => 'vintage-cooler',
];

// Separator used to split <category> into individual taxonomy terms
const GCP_CATEGORY_SEPARATOR = '/';

// Known English detail keywords to extract from source URL filenames
const GCP_IMAGE_KNOWN_DETAILS = [
  'front','back','side','top','bottom','left','right',
  'inside','interior','detail','hero','open','closed',
  'empty','filled','overview','view',
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
    foreach ($node->getNamespaces(true) as $ns){
      foreach ($node->children($ns) as $child){ $walker($child); }
    }
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

function gcp_get_path(SimpleXMLElement $item, array $path, $ns=null){
  $cur = $item;
  $last = array_pop($path);
  foreach ($path as $p){
    if (! isset($cur->{$p}[0])) return '';
    $cur = $cur->{$p}[0];
  }
  if ($ns){
    $nodes = $cur->children($ns)->{$last};
    if (isset($nodes[0])) return (string)$nodes[0];
  }
  if (isset($cur->{$last}[0])) return (string)$cur->{$last}[0];
  return '';
}

function gcp_get_all_path(SimpleXMLElement $item, array $path, $ns=null){
  $cur = $item;
  $last = array_pop($path);
  foreach ($path as $p){
    if (! isset($cur->{$p}[0])) return [];
    $cur = $cur->{$p}[0];
  }
  $nodes = $ns ? $cur->children($ns)->{$last} : $cur->{$last};
  $out = [];
  foreach ($nodes as $n){ $out[] = (string)$n; }
  return $out;
}

function gcp_get_text_list_path(SimpleXMLElement $item, array $path){
  $vals = gcp_get_all_path($item, $path, null);
  $out = [];
  foreach ($vals as $v){
    $v = trim((string)$v);
    if ($v !== '') $out[] = $v;
  }
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
  // Avoid duplicate relationship inserts when the same term is mapped multiple times.
  if (has_term($tid, $taxonomy, $post_id)) return;
  wp_set_object_terms($post_id, [$tid], $taxonomy, true);
}

function gcp_assign_certification($post_id, $authority, $name, $code){
  $path = trim($authority).' > '.trim($name);
  $tid = gcp_upsert_term_path('certification', $path);
  if ($tid){
    if (! has_term($tid, 'certification', $post_id)) {
      wp_set_object_terms($post_id, [$tid], 'certification', true);
    }
    if ($code){ update_term_meta($tid, 'cert_code', $code); }
  }
}

function gcp_update_field_safe($name, $value, $post_id){
  if (function_exists('update_field')){
    update_field($name, $value, $post_id);
  } else {
    update_post_meta($post_id, $name, $value);
  }
}

function gcp_update_meta_raw($post_id, $name, $value){
  if ($value === null) return;
  if (is_string($value) && trim($value) === '') return;
  update_post_meta($post_id, $name, $value);
}

function gcp_find_attachment_by_source_url($url){
  $url = trim((string)$url);
  if ($url === '') return 0;
  $q = new WP_Query([
    'post_type' => 'attachment',
    'post_status' => 'inherit',
    'meta_key' => 'gcp_source_url',
    'meta_value' => $url,
    'fields' => 'ids',
    'posts_per_page' => 1,
  ]);
  if ($q->have_posts()) return (int)$q->posts[0];
  $maybe = attachment_url_to_postid($url);
  return $maybe ? (int)$maybe : 0;
}

function gcp_media_sideload_url($url, $post_id){
  $url = trim((string)$url);
  if ($url === '') return 0;
  $existing = gcp_find_attachment_by_source_url($url);
  if ($existing) return $existing;
  if (! function_exists('media_sideload_image')) require_once ABSPATH.'wp-admin/includes/media.php';
  if (! function_exists('download_url')) require_once ABSPATH.'wp-admin/includes/file.php';
  if (! function_exists('wp_read_image_metadata')) require_once ABSPATH.'wp-admin/includes/image.php';
  $att_id = media_sideload_image($url, $post_id, null, 'id');
  if (is_wp_error($att_id)) return 0;
  update_post_meta((int)$att_id, 'gcp_source_url', $url);
  return (int)$att_id;
}

function gcp_bool_from_text($s){
  $s = strtolower(trim((string)$s));
  if ($s === '') return null;
  if (in_array($s, ['1','true','yes','ja','y','j'])) return 1;
  if (in_array($s, ['0','false','no','nein','n'])) return 0;
  return 1;
}

function gcp_mm_to_cm($val){
  if ($val === null) return null;
  return ($val > 50) ? ($val / 10) : $val;
}

function gcp_build_download_repeater(SimpleXMLElement $item, string $group){
  if (! isset($item->downloads[0])) return [];
  $nodes = $item->downloads->{$group} ?? null;
  if (! isset($nodes[0])) return [];
  $rows = [];
  foreach ($nodes->children() as $n){
    $url = trim((string)$n->file_url);
    $file_type = trim((string)$n->file_type);
    if ($file_type === '') $file_type = trim((string)$n->format);
    $rows[] = [
      'title'    => trim((string)$n->title),
      'language' => strtolower(trim((string)$n->language)),
      'file_url' => ($url !== '') ? esc_url_raw($url) : '',
      'file_type' => $file_type,
      'file_size' => trim((string)$n->file_size),
      'version' => trim((string)$n->version),
      'last_updated' => trim((string)$n->last_updated),
      'file_note' => trim((string)$n->file_note),
    ];
  }
  return $rows;
}

// ── Image path & naming helpers ───────────────────────────────────────────────

function gcp_resolve_image_category_slug($product_group, $category, $model_name = ''){
  // 1. product_group → already English for most values, slugify first segment
  if ($product_group !== ''){
    $first = trim(strstr($product_group, '/', true) ?: $product_group);
    // Override the one German entry
    if (mb_strtolower($first) === 'gewerblicher getränkekühlschrank'){
      return 'commercial-beverage-cooler';
    }
    $slug = sanitize_title($first);
    if ($slug !== '') return $slug;
  }
  // 2. German category term → mapping table (check first segment)
  $cat_first = trim(strstr($category, '/', true) ?: $category);
  foreach (GCP_CATEGORY_DE_EN as $de => $en){
    if (mb_stripos($cat_first, $de) !== false) return $en;
  }
  // 3. Model name prefix → fixed slug
  if ($model_name !== ''){
    $m = strtoupper($model_name);
    foreach (GCP_MODEL_PREFIX_SLUG as $prefix => $slug){
      if (str_starts_with($m, $prefix)) return $slug;
    }
  }
  return 'products';
}

function gcp_model_to_slug($model_name){
  // "GCDC400 ECO STAR" → "gcdc400-eco-star"
  return sanitize_title($model_name) ?: 'product';
}

function gcp_image_detail_from_url($src_url, $position){
  // Try to detect a known English keyword at the end of the source filename
  $filename = strtolower(pathinfo(parse_url($src_url, PHP_URL_PATH), PATHINFO_FILENAME));
  foreach (GCP_IMAGE_KNOWN_DETAILS as $kw){
    if (str_ends_with($filename, '-'.$kw) || str_ends_with($filename, '_'.$kw) || $filename === $kw){
      return $kw;
    }
  }
  // Positional fallback
  return $position === 0 ? 'front' : 'detail-'.$position;
}

function gcp_build_product_image_basename($category_slug, $model_slug, $detail_slug){
  return 'gastro-cool-'.$category_slug.'-'.$model_slug.'-'.$detail_slug;
}

function gcp_product_image_alt($post_id){
  $model  = (string)get_post_meta($post_id, 'product_model_name', true);
  if ($model === '') $model = get_the_title($post_id);
  $groups = get_the_terms($post_id, 'product_group');
  $group  = ($groups && !is_wp_error($groups)) ? $groups[0]->name : '';
  $parts  = array_filter([$model, $group]);
  return implode(' – ', $parts);
}

function gcp_media_sideload_product_img($src_url, $post_id, $new_basename, $alt_text, $subdir){
  $src_url = trim((string)$src_url);
  if ($src_url === '') return 0;
  // Skip if already sideloaded from this source URL
  $existing = gcp_find_attachment_by_source_url($src_url);
  if ($existing) return $existing;

  if (!function_exists('media_sideload_image'))  require_once ABSPATH.'wp-admin/includes/media.php';
  if (!function_exists('download_url'))           require_once ABSPATH.'wp-admin/includes/file.php';
  if (!function_exists('wp_read_image_metadata')) require_once ABSPATH.'wp-admin/includes/image.php';

  $subdir  = '/'.trim($subdir, '/');
  $dir_fn  = function($dirs) use ($subdir){
    $dirs['subdir'] = $subdir;
    $dirs['path']   = $dirs['basedir'].$subdir;
    $dirs['url']    = $dirs['baseurl'].$subdir;
    return $dirs;
  };
  $name_fn = function($filename) use ($new_basename){
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return $new_basename.($ext ? '.'.$ext : '');
  };

  add_filter('upload_dir',        $dir_fn,  PHP_INT_MAX);
  add_filter('sanitize_file_name', $name_fn, PHP_INT_MAX);

  $att_id = media_sideload_image($src_url, $post_id, null, 'id');

  remove_filter('upload_dir',        $dir_fn,  PHP_INT_MAX);
  remove_filter('sanitize_file_name', $name_fn, PHP_INT_MAX);

  if (is_wp_error($att_id)) return 0;
  $att_id = (int)$att_id;
  update_post_meta($att_id, 'gcp_source_url', $src_url);
  if ($alt_text !== '') update_post_meta($att_id, '_wp_attachment_image_alt', $alt_text);
  return $att_id;
}

function gcp_extract_variants(SimpleXMLElement $item, int $post_id = 0){
  $out = [];
  $groups = $item->variants->variant_group ?? null;
  if (! isset($groups[0])) return $out;
  foreach ($groups as $g){
    $group = [
      'base_model'     => (string)$g->base_model,
      'can_size'       => (string)$g->can_size,
      'capacity'       => (string)$g->capacity,
      'volume'         => (string)$g->volume,
      'color_body'     => (string)$g->color_body,
      'color_canopy'   => (string)$g->color_canopy,
      'interior_color' => (string)$g->interior_color,
      'description'    => (string)$g->description,
      'options'        => [],
    ];
    if (isset($g->options->option[0])){
      foreach ($g->options->option as $opt){
        $img_url = trim((string)$opt->image_link);
        $img_id  = 0;
        if ($img_url !== '' && $post_id > 0) {
          $img_id = gcp_media_sideload_url($img_url, $post_id);
        }
        $group['options'][] = [
          'artno'           => (string)$opt->artno,
          'type'            => (string)$opt->type,
          'value'           => (string)$opt->value,
          'ean'             => (string)$opt->ean,
          'energylabel'        => (string)$opt->energylabel,
          'energy_consumption' => (string)$opt->energy_consumption,
          'link'            => esc_url_raw(trim((string)$opt->link)),
          'additional_info' => (string)$opt->additional_info,
          'image_link'      => $img_id > 0 ? $img_id : $img_url,
        ];
      }
    }
    $out[] = $group;
  }
  return $out;
}

function gcp_media_sideload_featured($url, $post_id){
  $att_id = gcp_media_sideload_url($url, $post_id);
  if ($att_id) set_post_thumbnail($post_id, (int)$att_id);
}

// Process a single <item> element (SimpleXMLElement) and update counters
function gcp_import_process_item(SimpleXMLElement $item, $download_images, &$count_created, &$count_updated, &$skipped){
  $g = 'http://base.google.com/ns/1.0';

  $external_id = gcp_get_first($item, 'id', $g);
  if ($external_id==='') $external_id = gcp_get_first($item, 'id');
  if ($external_id==='') $external_id = gcp_get_first($item, 'mastercode');
  if ($external_id==='') { $skipped++; return; }

  $post_id = gcp_find_product_by_external_id($external_id);
  $title = gcp_get_first($item, 'product_name');
  if ($title==='') $title = gcp_get_first($item, 'title_seo');
  if ($title==='') $title = gcp_get_first($item, 'title');
  // description_long kann verschachteltes HTML (<p>, <strong> …) enthalten.
  // (string)-Cast auf SimpleXMLElement liefert in dem Fall einen leeren String,
  // weil PHP nur direkte Textknoten liest. Stattdessen inneres HTML via DOM holen.
  $desc = '';
  if (isset($item->description_long[0])) {
    $dom  = dom_import_simplexml($item->description_long[0]);
    $desc = '';
    foreach ($dom->childNodes as $child) {
      $desc .= $dom->ownerDocument->saveHTML($child);
    }
    $desc = trim($desc);
  }
  if ($desc === '') $desc = gcp_get_first($item, 'intro');
  $desc = html_entity_decode($desc);
  $excerpt = gcp_get_first($item, 'short_description');
  if ($excerpt==='') $excerpt = gcp_get_first($item, 'intro');

  if ($post_id){
    $update = ['ID'=>$post_id,'post_title'=>$title,'post_content'=>$desc,'post_status'=>'publish'];
    if ($excerpt!=='') $update['post_excerpt'] = $excerpt;
    wp_update_post($update);
    $count_updated++;
  } else {
    $create = ['post_type'=>'product','post_title'=>$title,'post_content'=>$desc,'post_status'=>'publish'];
    if ($excerpt!=='') $create['post_excerpt'] = $excerpt;
    $post_id = wp_insert_post($create);
    if (is_wp_error($post_id) || ! $post_id){ $skipped++; return; }
    $count_created++;
  }

  // First variant – fallback for fields that moved into <variant> in the new XML structure
  // (ean, link, g:image_link, images, shipping, product_weight, energy properties)
  $variant0 = (isset($item->variants[0]) && isset($item->variants->variant[0]))
    ? $item->variants->variant[0] : null;

  // Helper: try properties_common (new XML) first, then properties (old XML)
  $gp = function(string $key) use ($item): string {
    $v = gcp_get_path($item, ['properties_common', $key]);
    if ($v === '') $v = gcp_get_path($item, ['properties', $key]);
    return $v;
  };

  // ACF/meta mapping
  gcp_update_field_safe('external_id', $external_id, $post_id);
  $gtin = gcp_get_first($item,'ean');
  if ($gtin==='') $gtin = gcp_get_first($item,'gtin',$g);
  if ($gtin==='' && $variant0) $gtin = gcp_get_first($variant0,'ean');
  if ($gtin!=='') gcp_update_field_safe('gtin', $gtin, $post_id);

  $product_model = gcp_get_path($item, ['product_info','model_name']);
  gcp_update_field_safe('product_model_name', $product_model, $post_id);
  gcp_update_field_safe('product_info_model_name', $product_model, $post_id);
  $mastercode3 = gcp_get_path($item, ['product_info','product_3digit_mastercode']);
  gcp_update_field_safe('product_3digit_mastercode', $mastercode3, $post_id);
  gcp_update_field_safe('product_info_product_3digit_mastercode', $mastercode3, $post_id);

  $source_link = gcp_get_first($item, 'link');
  if ($source_link==='' && $variant0) $source_link = gcp_get_first($variant0, 'link');
  if ($source_link!=='') gcp_update_field_safe('source_link', $source_link, $post_id);

  $category = trim(gcp_get_first($item, 'category'));
  $series   = trim(gcp_get_first($item, 'series'));
  if ($series !== '') gcp_update_field_safe('series', $series, $post_id);
  // Split <category> by separator into individual terms (separator may change later)
  if ($category !== '') {
    $cat_parts = array_filter(array_map('trim', explode(GCP_CATEGORY_SEPARATOR, $category)));
    foreach ($cat_parts as $cat_part) {
      gcp_assign_simple_tax($post_id, 'product_category', $cat_part);
    }
  }

  $product_group = gcp_get_path($item, ['product_info','product_group']);
  gcp_update_field_safe('product_info_product_group', $product_group, $post_id);
  if ($product_group!=='') gcp_assign_simple_tax($post_id, 'product_group', $product_group);

  $brand = gcp_get_first($item,'brand',$g);
  if ($brand==='') $brand = gcp_get_first($item,'brand');
  if ($brand!=='') gcp_assign_simple_tax($post_id, 'brand', $brand);

  // Shipping node: item-level first, fallback to first variant (new XML puts shipping inside variants)
  $sn = ($variant0 && isset($variant0->shipping[0])) ? $variant0 : $item;
  $ship_weight = gcp_get_path($item, ['shipping','shipping_weight'], $g);
  if ($ship_weight === '') $ship_weight = gcp_get_path($sn, ['shipping','shipping_weight'], $g);
  $ship_weight_val = gcp_to_float($ship_weight);
  if ($ship_weight_val!==null) gcp_update_field_safe('shipping_weight_kg', $ship_weight_val, $post_id);

  $ship_len_raw = gcp_get_path($item, ['shipping','product_length'], $g);
  if ($ship_len_raw === '') $ship_len_raw = gcp_get_path($sn, ['shipping','product_length'], $g);
  $ship_wid_raw = gcp_get_path($item, ['shipping','product_width'], $g);
  if ($ship_wid_raw === '') $ship_wid_raw = gcp_get_path($sn, ['shipping','product_width'], $g);
  $ship_hei_raw = gcp_get_path($item, ['shipping','product_height'], $g);
  if ($ship_hei_raw === '') $ship_hei_raw = gcp_get_path($sn, ['shipping','product_height'], $g);
  $ship_len = gcp_to_float($ship_len_raw);
  $ship_wid = gcp_to_float($ship_wid_raw);
  $ship_hei = gcp_to_float($ship_hei_raw);
  if ($ship_len!==null) gcp_update_field_safe('length_cm', $ship_len, $post_id);
  if ($ship_wid!==null) gcp_update_field_safe('width_cm', $ship_wid, $post_id);
  if ($ship_hei!==null) gcp_update_field_safe('height_cm', $ship_hei, $post_id);

  $pallet = gcp_get_path($item, ['shipping','product_pallet_size']);
  if ($pallet!=='') gcp_update_field_safe('pallet_size', $pallet, $post_id);
  $warehouse = gcp_get_path($item, ['shipping','products_warehouse']);
  if ($warehouse!=='') gcp_update_field_safe('warehouse', $warehouse, $post_id);

  $shipment_40hq = gcp_to_float(gcp_get_path($item, ['shipping','shipping_amount_40fthq']));
  if ($shipment_40hq!==null) gcp_update_field_safe('gc_shipment_storage_40fthqcon', $shipment_40hq, $post_id);
  $v = gcp_to_float(gcp_get_path($item, ['shipping','shipping_amount_80x60']));
  if ($v!==null) gcp_update_field_safe('shipping_amount_80x60', $v, $post_id);
  $v = gcp_to_float(gcp_get_path($item, ['shipping','shipping_amount_120x80']));
  if ($v!==null) gcp_update_field_safe('shipping_amount_120x80', $v, $post_id);
  $v = gcp_to_float(gcp_get_path($item, ['shipping','shipping_amount_145x100']));
  if ($v!==null) gcp_update_field_safe('shipping_amount_145x100', $v, $post_id);
  $v = gcp_to_float(gcp_get_path($item, ['shipping','shipping_amount_20ft']));
  if ($v!==null) gcp_update_field_safe('shipping_amount_20ft', $v, $post_id);
  $v = gcp_to_float(gcp_get_path($item, ['shipping','shipping_amount_40ft']));
  if ($v!==null) gcp_update_field_safe('shipping_amount_40ft', $v, $post_id);

  // No Google Shopping/price info in new XML structure

  // Images – resolve category/model slugs for structured naming
  $img_cat_slug   = gcp_resolve_image_category_slug($product_group, $category, $product_model);
  $img_model_slug = gcp_model_to_slug($product_model);
  $img_subdir     = 'produkte/'.$img_cat_slug;
  $img_alt        = gcp_product_image_alt($post_id);

  $img = gcp_get_first($item,'image_link',$g);
  if ($img==='') $img = gcp_get_first($item,'image_link');
  if ($img==='' && $variant0) {
    $img = gcp_get_first($variant0,'image_link',$g);
    if ($img==='') $img = gcp_get_first($variant0,'image_link');
  }
  if ($img){
    $basename = gcp_build_product_image_basename($img_cat_slug, $img_model_slug, 'front');
    $att_id   = gcp_media_sideload_product_img($img, $post_id, $basename, $img_alt, $img_subdir);
    if ($att_id){
      set_post_thumbnail($post_id, $att_id);
      gcp_update_field_safe('featured_image_source_url', $att_id, $post_id);
    }
  }
  $add_imgs = array_unique(array_filter(array_map('trim', array_merge(
    gcp_get_all($item,'additional_image_link'),
    gcp_get_all($item,'additional_image_link',$g),
    gcp_get_all_path($item, ['images','additional_image_link']),
    gcp_get_all_path($item, ['images','additional_image_link'], $g),
    $variant0 ? gcp_get_all($variant0,'additional_image_link') : [],
    $variant0 ? gcp_get_all($variant0,'additional_image_link',$g) : [],
    $variant0 ? gcp_get_all_path($variant0, ['images','additional_image_link']) : [],
    $variant0 ? gcp_get_all_path($variant0, ['images','additional_image_link'], $g) : []
  ))));
  if ($add_imgs){
    $ids = [];
    $pos = 1;
    foreach ($add_imgs as $u){
      $detail   = gcp_image_detail_from_url($u, $pos);
      $basename = gcp_build_product_image_basename($img_cat_slug, $img_model_slug, $detail);
      $att_id   = gcp_media_sideload_product_img($u, $post_id, $basename, $img_alt, $img_subdir);
      if ($att_id){
        $ids[] = $att_id;
      }
      $pos++;
    }
    if ($ids) gcp_update_field_safe('additional_image_links', $ids, $post_id);
  }

  // Text fields now stored via ACF
  $v = gcp_get_first($item, 'title_seo');
  if ($v !== '') gcp_update_field_safe('title_seo', $v, $post_id);
  $v = gcp_get_first($item, 'intro');
  if ($v !== '') gcp_update_field_safe('intro', $v, $post_id);
  $v = gcp_get_first($item, 'legal_notice');
  if ($v !== '') gcp_update_field_safe('legal_notice', $v, $post_id);

  // Article numbers as ACF repeater
  $artno_list_raw = gcp_get_text_list_path($item, ['artno_list', 'artno']);
  if ($artno_list_raw) {
    $artno_rows = array_map(fn($a) => ['artno' => $a], $artno_list_raw);
    gcp_update_field_safe('artno_list', $artno_rows, $post_id);
  }

  // Documents – full repeaters for all languages
  $datasheet_rows = gcp_build_download_repeater($item, 'datasheets');
  if ($datasheet_rows) gcp_update_field_safe('datasheets', $datasheet_rows, $post_id);
  $manual_rows = gcp_build_download_repeater($item, 'manuals');
  if ($manual_rows) gcp_update_field_safe('manuals', $manual_rows, $post_id);
  $cert_dl_rows = gcp_build_download_repeater($item, 'certificates_downloads');
  if ($cert_dl_rows) gcp_update_field_safe('certificates_downloads', $cert_dl_rows, $post_id);
  $install_rows = gcp_build_download_repeater($item, 'installation_guides');
  if ($install_rows) gcp_update_field_safe('installation_guides', $install_rows, $post_id);
  $other_doc_rows = gcp_build_download_repeater($item, 'other_documents');
  if ($other_doc_rows) gcp_update_field_safe('other_documents', $other_doc_rows, $post_id);
  $cad_rows = [];
  if (isset($item->downloads[0]) && isset($item->downloads->cad_files[0])){
    foreach ($item->downloads->cad_files->cad_file as $cad){
      $cad_url = trim((string)$cad->file_url);
      $cad_file_type = trim((string)$cad->file_type);
      if ($cad_file_type === '') $cad_file_type = trim((string)$cad->format);
      $cad_rows[] = [
        'title'       => trim((string)$cad->title),
        'url'         => ($cad_url !== '') ? esc_url_raw($cad_url) : '',
        'description' => trim((string)$cad->description),
        'format'      => trim((string)$cad->format),
        'file_type'   => $cad_file_type,
        'file_size'   => trim((string)$cad->file_size),
        'version'     => trim((string)$cad->version),
        'last_updated'=> trim((string)$cad->last_updated),
      ];
    }
  }
  if ($cad_rows) gcp_update_field_safe('cad_files', $cad_rows, $post_id);

  // Energy – in new XML these are direct children of <variant>, not inside <properties>
  $energy_label = $gp('properties_energylabel');
  if ($energy_label==='' && $variant0) $energy_label = gcp_get_first($variant0,'properties_energylabel');
  if ($energy_label!=='') gcp_update_field_safe('energy_label', $energy_label, $post_id);

  $energy_24h = $gp('properties_energy_consumption');
  if ($energy_24h==='' && $variant0) $energy_24h = gcp_get_first($variant0,'properties_energy_consumption');
  if ($energy_24h!=='') gcp_update_field_safe('energy_consumption_24h_raw', $energy_24h, $post_id);

  $per_year_raw = $gp('properties_energy_consumption_year');
  if ($per_year_raw==='' && $variant0) $per_year_raw = gcp_get_first($variant0,'properties_energy_consumption_year');
  $per_year = gcp_to_float($per_year_raw);
  if ($per_year!==null) gcp_update_field_safe('energy_consumption_per_year', $per_year, $post_id);

  $eei = $gp('properties_eei');
  if ($eei==='' && $variant0) $eei = gcp_get_first($variant0,'properties_eei');
  if ($eei!=='') gcp_update_field_safe('eei_raw', $eei, $post_id);
  $eprel = gcp_get_path($item, ['energy_label','eprel_code']);
  if ($eprel!=='') gcp_update_field_safe('cust_gc_eprel', $eprel, $post_id);
  $tooltip = gcp_get_path($item, ['energy_label','tooltip_url']);
  if ($tooltip!=='') gcp_update_field_safe('energy_label_tooltip', $tooltip, $post_id);
  $button_html = gcp_get_path($item, ['energy_label','button_html']);
  if ($button_html!=='') gcp_update_field_safe('energy_button_html', $button_html, $post_id);

  // Technical
  $doors = $gp('properties_doors');
  if ($doors!=='') gcp_update_field_safe('doors', $doors, $post_id);
  $door_type = $gp('properties_door_type');
  if ($door_type!=='') gcp_update_field_safe('door_type', $door_type, $post_id);
  $reversible = $gp('properties_reversible_door');
  if ($reversible!=='') gcp_update_field_safe('reversible_door', $reversible, $post_id);
  $interior = $gp('properties_interior_lighting');
  if ($interior!=='') gcp_update_field_safe('interior_lighting', $interior, $post_id);
  $coolant = $gp('properties_coolant');
  if ($coolant!=='') gcp_update_field_safe('coolant', $coolant, $post_id);
  $functions = $gp('properties_functions');
  if ($functions!=='') gcp_update_field_safe('functions', $functions, $post_id);
  $climate = $gp('properties_climate_class');
  if ($climate!=='') gcp_update_field_safe('climate_class', $climate, $post_id);
  $controller = $gp('properties_controller');
  if ($controller!=='') gcp_update_field_safe('controller', $controller, $post_id);
  $shelves = $gp('properties_shelves');
  if ($shelves!=='') gcp_update_field_safe('shelves_raw', $shelves, $post_id);
  $material_inside = $gp('properties_material_inside');
  if ($material_inside!=='') gcp_update_field_safe('material_inside', $material_inside, $post_id);
  $electrical = $gp('properties_electrical_connection');
  if ($electrical!=='') gcp_update_field_safe('electrical_connection', $electrical, $post_id);
  $durchmesser = $gp('properties_durchmesser');
  if ($durchmesser!=='') gcp_update_field_safe('properties_durchmesser', $durchmesser, $post_id);
  $certs_raw = $gp('properties_certificates');
  if ($certs_raw!=='') gcp_update_field_safe('properties_certificates', $certs_raw, $post_id);

  // Temps
  $temp_range = $gp('properties_temperature_range');
  if ($temp_range!=='') gcp_update_field_safe('temperature_range_raw', $temp_range, $post_id);
  $tf = gcp_to_float($gp('properties_temp_from'));
  if ($tf!==null) gcp_update_field_safe('temp_from_c', $tf, $post_id);
  $tt = gcp_to_float($gp('properties_temp_till'));
  if ($tt!==null) gcp_update_field_safe('temp_till_c', $tt, $post_id);

  // Dimensions & volume
  $wmm = gcp_to_float($gp('properties_width'));
  if ($wmm!==null) gcp_update_field_safe('width_mm', $wmm, $post_id);
  $hmm = gcp_to_float($gp('properties_height'));
  if ($hmm!==null) gcp_update_field_safe('height_mm', $hmm, $post_id);
  $dmm = gcp_to_float($gp('properties_depth'));
  if ($dmm!==null) gcp_update_field_safe('depth_mm', $dmm, $post_id);
  $inside = $gp('properties_insidemeasurements');
  if ($inside!=='') gcp_update_field_safe('hxwxd_inside_raw', $inside, $post_id);
  $outside = $gp('properties_outsidemeasurements');
  if ($outside!=='') gcp_update_field_safe('hxwxd_outside_raw', $outside, $post_id);
  $vol_raw = $gp('properties_volume');
  if ($vol_raw!=='') gcp_update_field_safe('volume_raw', $vol_raw, $post_id);
  $vol_l = gcp_to_float($vol_raw);
  if ($vol_l!==null) gcp_update_field_safe('volume_l', $vol_l, $post_id);

  // Weight & noise
  $nw_raw = gcp_get_first($item,'product_weight');
  if ($nw_raw==='' && $variant0) $nw_raw = gcp_get_first($variant0,'product_weight');
  $nw = gcp_to_float($nw_raw);
  if ($nw!==null) gcp_update_field_safe('net_weight_kg', $nw, $post_id);
  if ($nw_raw!=='') gcp_update_field_safe('weight_raw', $nw_raw, $post_id);
  $db = gcp_to_float(gcp_get_path($item, ['properties','properties_noise_volume']));
  if ($db!==null) gcp_update_field_safe('noise_volume_db', $db, $post_id);

  // Flags
  $conv = trim($gp('properties_convection_cooling'));
  if ($conv!=='') gcp_update_field_safe('convection_cooling', $conv, $post_id);
  $buildin = gcp_bool_from_text($gp('properties_buildin'));
  if ($buildin!==null) gcp_update_field_safe('buildin', $buildin, $post_id);

  $commercial = gcp_bool_from_text(gcp_get_path($item, ['usage_flags','commercial_use']));
  if ($commercial!==null) gcp_update_field_safe('cust_commercialuse', $commercial, $post_id);
  $household = gcp_bool_from_text(gcp_get_path($item, ['usage_flags','household_app']));
  if ($household!==null) gcp_update_field_safe('cust_gc_householdapp', $household, $post_id);
  $bevcooler = gcp_bool_from_text(gcp_get_path($item, ['usage_flags','beverage_cooler']));
  if ($bevcooler!==null) gcp_update_field_safe('cust_gc_bevcooler', $bevcooler, $post_id);

  // Capacities
  $has_cans = false;
  $has_bottles = false;
  $has_capacity = false;
  if (isset($item->capacities[0])){
    foreach ($item->capacities->children() as $child){
      $name = $child->getName();
      $val = trim((string)$child);
      if ($val==='') continue;
      gcp_update_field_safe($name, $val, $post_id);
      $has_capacity = true;
      if (str_starts_with($name, 'cans_')) $has_cans = true;
      if (str_starts_with($name, 'bottles_')) $has_bottles = true;
    }
  }
  if ($has_cans) gcp_update_field_safe('cust_gc_product_capacity_cans', 1, $post_id);
  if ($has_bottles) gcp_update_field_safe('cust_gc_product_capacity_bottles', 1, $post_id);
  if ($has_capacity) gcp_update_field_safe('cust_gc_product_capacity', 1, $post_id);

  // Taxonomies
  $color = $gp('properties_color');
  if ($color) gcp_assign_simple_tax($post_id, 'color', $color);

  $cert = $item->children($g)->certification;
  if (! isset($cert[0]) && isset($item->certification[0])) $cert = $item->certification;
  if (isset($cert[0])){
    $auth = gcp_get_first($cert[0], 'certification_authority');
    if ($auth==='') $auth = gcp_get_first($cert[0], 'certification_authority', $g);
    $name = gcp_get_first($cert[0], 'certification_name');
    if ($name==='') $name = gcp_get_first($cert[0], 'certification_name', $g);
    $code = gcp_get_first($cert[0], 'certification_code');
    if ($code==='') $code = gcp_get_first($cert[0], 'certification_code', $g);
    if ($auth || $name) gcp_assign_certification($post_id, $auth, $name, $code);
  }

  // Rich lists
  $use_cases_raw = gcp_get_text_list_path($item, ['use_cases', 'use_case']);
  if ($use_cases_raw) {
    $use_case_rows = array_map(fn($t) => ['text' => $t], $use_cases_raw);
    gcp_update_field_safe('use_cases', $use_case_rows, $post_id);
  }
  $benefits_raw = gcp_get_text_list_path($item, ['benefits', 'benefit']);
  if ($benefits_raw) {
    gcp_update_field_safe('benefits', array_map(fn($t) => ['text' => $t], $benefits_raw), $post_id);
  }
  $features_raw = gcp_get_text_list_path($item, ['features', 'feature']);
  if ($features_raw) {
    gcp_update_field_safe('features', array_map(fn($t) => ['text' => $t], $features_raw), $post_id);
  }

  // Variants as ACF repeater
  $variants = gcp_extract_variants($item, $post_id);
  if ($variants) gcp_update_field_safe('variants', $variants, $post_id);

  // Videos (supports multiple entries)
  $video_rows = [];
  $video_seen = [];
  $video_nodes = $item->xpath('.//video');
  if (is_array($video_nodes)) {
    foreach ($video_nodes as $video_node) {
      $youtube_id = trim((string)$video_node->youtube_id);
      if ($youtube_id === '') continue;
      if (isset($video_seen[$youtube_id])) continue;
      $video_seen[$youtube_id] = true;
      $video_rows[] = [
        'title' => '',
        'youtube_id' => $youtube_id,
        'video_file' => 0,
      ];
    }
  }
  if ($video_rows) {
    gcp_update_field_safe('videos', $video_rows, $post_id);
    // Keep legacy meta key for compatibility with older templates.
    gcp_update_meta_raw($post_id, 'youtube_id', $video_rows[0]['youtube_id']);
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

// ── Custom upload directory for product images (manual backend uploads) ───────
// Applies only when uploading a media file while editing a product post.
// Directory: /wp-content/uploads/produkte/[category-slug]/
add_filter('upload_dir', function($dirs){
  if (!is_admin()) return $dirs;
  $post_id = (int)($_REQUEST['post_id'] ?? 0);
  if (!$post_id || get_post_type($post_id) !== 'product') return $dirs;

  $groups   = get_the_terms($post_id, 'product_group');
  $pg       = ($groups && !is_wp_error($groups)) ? $groups[0]->name : '';
  $cats     = get_the_terms($post_id, 'product_category');
  $cat      = ($cats && !is_wp_error($cats)) ? $cats[0]->name : '';
  $model    = (string)get_post_meta($post_id, 'product_model_name', true);
  $cat_slug = gcp_resolve_image_category_slug($pg, $cat, $model);

  $subdir = '/produkte/'.$cat_slug;
  $dirs['subdir'] = $subdir;
  $dirs['path']   = $dirs['basedir'].$subdir;
  $dirs['url']    = $dirs['baseurl'].$subdir;
  return $dirs;
}, 20);

// ── Alt-text after ACF save (manual product edits in backend) ─────────────────
// Fires after ACF fields are committed to DB (priority 20).
// Sets the alt-text on the featured image if it is still empty.
add_action('acf/save_post', function($post_id){
  if (get_post_type($post_id) !== 'product') return;
  $alt     = gcp_product_image_alt($post_id);
  if ($alt === '') return;
  $thumb   = get_post_thumbnail_id($post_id);
  if ($thumb && get_post_meta($thumb, '_wp_attachment_image_alt', true) === ''){
    update_post_meta($thumb, '_wp_attachment_image_alt', $alt);
  }
}, 20);

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
