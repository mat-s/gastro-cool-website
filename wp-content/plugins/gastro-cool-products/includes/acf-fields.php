<?php
// Register ACF local field groups for Products and Certification taxonomy.

if (! defined('ABSPATH')) { exit; }

add_action('acf/init', function () {
  if (! function_exists('acf_add_local_field_group')) { return; }

  // Product Data (Feed)
  acf_add_local_field_group([
    'key' => 'group_gc_product_data',
    'title' => 'Product Data (Feed)',
    'fields' => [
      [ 'key' => 'tab_general', 'label' => 'Allgemein', 'type' => 'tab', 'placement' => 'top' ],
      [ 'key' => 'field_external_id','label' => 'External ID','name' => 'external_id','type' => 'text','instructions' => 'Unique ID from feed (g:id).','required' => 1,'readonly' => 1, 'wrapper' => ['width' => '25'] ],
      [ 'key' => 'field_gtin','label' => 'GTIN','name' => 'gtin','type' => 'text', 'instructions' => 'Global Trade Item Number from feed.', 'wrapper' => ['width' => '25'] ],
      [ 'key' => 'field_product_model_name','label' => 'Product Model Name','name' => 'product_model_name','type' => 'text','instructions' => 'From g:product_model_name (e.g., GCAP50).', 'wrapper' => ['width' => '25'] ],
      [ 'key' => 'field_product_3digit_mastercode','label' => 'Product 3-digit Mastercode','name' => 'product_3digit_mastercode','type' => 'text', 'instructions' => 'Three-digit internal mastercode from feed.', 'wrapper' => ['width' => '25'] ],
      [ 'key' => 'field_source_link','label' => 'Source Link (feed link)','name' => 'source_link','type' => 'url', 'instructions' => 'Original product link from feed.', 'wrapper' => ['width' => '50'] ],
      [ 'key' => 'field_categories_raw','label' => 'Categories (raw from feed)','name' => 'categories_raw','type' => 'text','instructions' => 'Original g:categories string (for reference).', 'wrapper' => ['width' => '50'] ],
      [ 'key' => 'field_featured_image_source_url','label' => 'Featured Image Source URL','name' => 'featured_image_source_url','type' => 'url','instructions' => 'Optional: original source URL for featured image.', 'wrapper' => ['width' => '100'] ],
      [ 'key' => 'field_additional_image_links','label' => 'Additional Image Links','name' => 'additional_image_links','type' => 'repeater','layout' => 'table','button_label' => 'Add Image Link','instructions' => 'Additional product images from feed.', 'sub_fields' => [ [ 'key' => 'field_additional_image_link_url','label' => 'URL','name' => 'url','type' => 'url', 'instructions' => 'Image URL from feed.' ] ] ],
      // not specified in list: keep cm fields here
      [ 'key' => 'field_length_cm','label' => 'Length (cm)','name' => 'length_cm','type' => 'number','step' => 0.1, 'instructions' => 'Parsed product length in centimeters.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_width_cm','label' => 'Width (cm)','name' => 'width_cm','type' => 'number','step' => 0.1, 'instructions' => 'Parsed product width in centimeters.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_height_cm','label' => 'Height (cm)','name' => 'height_cm','type' => 'number','step' => 0.1, 'instructions' => 'Parsed product height in centimeters.', 'wrapper' => ['width' => '33.3'] ],

      [ 'key' => 'tab_availability_price', 'label' => 'Verfügbarkeit & Preis', 'type' => 'tab' ],
      [ 'key' => 'group_stock', 'label' => 'Lager', 'name' => 'lager', 'type' => 'group', 'layout' => 'block', 'sub_fields' => [
        [ 'key' => 'field_stock','label' => 'Stock','name' => 'stock','type' => 'number','min' => 0,'step' => 1, 'instructions' => 'Current stock quantity from feed.', 'wrapper' => ['width' => '33.3'] ],
        [ 'key' => 'field_availability','label' => 'Availability','name' => 'availability','type' => 'select','choices' => [ 'in_stock' => 'in_stock','out_of_stock' => 'out_of_stock','preorder' => 'preorder','backorder' => 'backorder' ],'allow_null' => 1,'ui' => 1,'return_format' => 'value', 'instructions' => 'Availability status from feed.', 'wrapper' => ['width' => '33.3'] ],
        [ 'key' => 'field_refill_time','label' => 'Refill Time','name' => 'refill_time','type' => 'number','min' => 0,'step' => 1, 'instructions' => 'Refill time from feed (days).', 'wrapper' => ['width' => '33.3'] ],
      ]],
      [ 'key' => 'group_price', 'label' => 'Preis', 'name' => 'preis', 'type' => 'group', 'layout' => 'block', 'sub_fields' => [
        [ 'key' => 'field_price_amount','label' => 'Price Amount','name' => 'price_amount','type' => 'number','min' => 0,'step' => 0.01, 'instructions' => 'Numeric price parsed from g:price.', 'wrapper' => ['width' => '33.3'] ],
        [ 'key' => 'field_price_currency','label' => 'Price Currency','name' => 'price_currency','type' => 'text', 'instructions' => 'Currency code parsed from g:price.', 'wrapper' => ['width' => '33.3'] ],
        [ 'key' => 'field_price_raw','label' => 'Price Raw','name' => 'price_raw','type' => 'text','instructions' => 'Optional: original price string from feed.', 'wrapper' => ['width' => '33.3'] ],
      ]],

      [ 'key' => 'tab_shipping', 'label' => 'Versand', 'type' => 'tab' ],
      [ 'key' => 'field_shipping_weight_kg','label' => 'Shipping Weight (kg)','name' => 'shipping_weight_kg','type' => 'number','step' => 0.01, 'instructions' => 'Shipping weight in kilograms.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_pallet_size','label' => 'Pallet Size','name' => 'pallet_size','type' => 'text', 'instructions' => 'Pallet size description from feed.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_warehouse','label' => 'Warehouse','name' => 'warehouse','type' => 'text', 'instructions' => 'Warehouse location from feed.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_shipping_price_1','label' => 'Shipping Price 1','name' => 'shipping_price_1','type' => 'number','step' => 0.01, 'instructions' => 'Shipping price tier 1.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_shipping_price_2','label' => 'Shipping Price 2','name' => 'shipping_price_2','type' => 'number','step' => 0.01, 'instructions' => 'Shipping price tier 2.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_shipping_price_3','label' => 'Shipping Price 3','name' => 'shipping_price_3','type' => 'number','step' => 0.01, 'instructions' => 'Shipping price tier 3.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_shipping_price_4','label' => 'Shipping Price 4','name' => 'shipping_price_4','type' => 'number','step' => 0.01, 'instructions' => 'Shipping price tier 4.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_shipping_price_5','label' => 'Shipping Price 5','name' => 'shipping_price_5','type' => 'number','step' => 0.01, 'instructions' => 'Shipping price tier 5.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_shipping_price_6','label' => 'Shipping Price 6','name' => 'shipping_price_6','type' => 'number','step' => 0.01, 'instructions' => 'Shipping price tier 6.', 'wrapper' => ['width' => '33.3'] ],

      [ 'key' => 'tab_google', 'label' => 'Google Shopping', 'type' => 'tab' ],
      [ 'key' => 'field_google_active','label' => 'Google Shopping Active','name' => 'google_active','type' => 'true_false','ui' => 1, 'instructions' => 'Enable if item should be active in Google Shopping.', 'wrapper' => ['width' => '25'] ],
      [ 'key' => 'field_google_cat','label' => 'Google Product Category ID','name' => 'google_cat','type' => 'text', 'instructions' => 'Google product category ID from feed.', 'wrapper' => ['width' => '25'] ],
      [ 'key' => 'field_google_available_date','label' => 'Google Available Date','name' => 'google_available_date','type' => 'date_time_picker','display_format' => 'Y-m-d H:i:s','return_format' => 'Y-m-d H:i:s','first_day' => 1, 'instructions' => 'Availability date used for Google Shopping.', 'wrapper' => ['width' => '25'] ],
      [ 'key' => 'field_google_condition','label' => 'Google Shopping Condition','name' => 'google_condition','type' => 'text', 'instructions' => 'Item condition, e.g. new.', 'wrapper' => ['width' => '25'] ],

      [ 'key' => 'tab_documents', 'label' => 'Dokumente', 'type' => 'tab' ],
      [ 'key' => 'field_manual_document_url','label' => 'Manual Document URL','name' => 'manual_document_url','type' => 'url', 'instructions' => 'Link to the product manual (PDF).', 'wrapper' => ['width' => '50'] ],
      [ 'key' => 'field_datasheet_document_url','label' => 'Datasheet Document URL','name' => 'datasheet_document_url','type' => 'url', 'instructions' => 'Link to the product datasheet (PDF).', 'wrapper' => ['width' => '50'] ],
      [ 'key' => 'field_cad_files','label' => 'CAD Files','name' => 'cad_files','type' => 'repeater','layout' => 'table','button_label' => 'Add CAD File','instructions' => 'CAD download files from feed.', 'sub_fields' => [
        [ 'key' => 'field_cad_file_title','label' => 'Title','name' => 'title','type' => 'text' ],
        [ 'key' => 'field_cad_file_url','label' => 'URL','name' => 'url','type' => 'url' ],
        [ 'key' => 'field_cad_file_description','label' => 'Description','name' => 'description','type' => 'text' ],
        [ 'key' => 'field_cad_file_format','label' => 'Format','name' => 'format','type' => 'text' ],
      ] ],

      [ 'key' => 'tab_energy', 'label' => 'Energie & Compliance', 'type' => 'tab' ],
      [ 'key' => 'field_energy_label','label' => 'Energy Label','name' => 'energy_label','type' => 'text', 'instructions' => 'Energy efficiency label (A–G).', 'wrapper' => ['width' => '20'] ],
      [ 'key' => 'field_energy_consumption_24h_raw','label' => 'Energy Consumption (per 24h, raw)','name' => 'energy_consumption_24h_raw','type' => 'text','instructions' => 'Original string e.g. "0,44 kWh / 24h".', 'wrapper' => ['width' => '30'] ],
      [ 'key' => 'field_energy_consumption_per_year','label' => 'Energy Consumption (per year, kWh)','name' => 'energy_consumption_per_year','type' => 'number','step' => 0.01, 'wrapper' => ['width' => '20'] ],
      [ 'key' => 'field_eei_raw','label' => 'EEI (raw)','name' => 'eei_raw','type' => 'text','instructions' => 'Original EEI string, may contain commas (e.g., "43,4").', 'wrapper' => ['width' => '30'] ],
      [ 'key' => 'field_cust_gc_eprel','label' => 'EPREL Code','name' => 'cust_gc_eprel','type' => 'text', 'instructions' => 'EPREL registry code from feed.', 'wrapper' => ['width' => '20'] ],
      [ 'key' => 'field_energy_label_tooltip','label' => 'Energy Label Tooltip URL','name' => 'energy_label_tooltip','type' => 'url', 'instructions' => 'Link to energy label image.', 'wrapper' => ['width' => '40'] ],
      [ 'key' => 'field_energy_button_html','label' => 'Energy Button (HTML)','name' => 'energy_button_html','type' => 'textarea','new_lines' => 'br', 'instructions' => 'HTML snippet to display energy label button.', 'wrapper' => ['width' => '40'] ],

      [ 'key' => 'tab_tech', 'label' => 'Technik', 'type' => 'tab' ],
      [ 'key' => 'field_doors','label' => 'Doors','name' => 'doors','type' => 'text', 'instructions' => 'Door configuration description.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_door_type','label' => 'Door Type','name' => 'door_type','type' => 'text', 'instructions' => 'Door type (e.g., double glass).', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_reversible_door','label' => 'Reversible Door (raw)','name' => 'reversible_door','type' => 'text', 'instructions' => 'Reversible door information (raw text).', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_interior_lighting','label' => 'Interior Lighting','name' => 'interior_lighting','type' => 'text', 'instructions' => 'Interior lighting details.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_coolant','label' => 'Coolant','name' => 'coolant','type' => 'text', 'instructions' => 'Refrigerant type (e.g., R600a).', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_functions','label' => 'Functions','name' => 'functions','type' => 'text', 'instructions' => 'Functions such as Cooling/Freezing.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_climate_class','label' => 'Climate Class','name' => 'climate_class','type' => 'text', 'instructions' => 'Climate class (e.g., N/ST).', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_controller','label' => 'Controller','name' => 'controller','type' => 'text', 'instructions' => 'Controller type (manual/digital).', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_shelves_raw','label' => 'Shelves (raw)','name' => 'shelves_raw','type' => 'text','instructions' => 'Shelves text from feed (e.g., "8 pcs").', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_material_inside','label' => 'Material (inside)','name' => 'material_inside','type' => 'text', 'instructions' => 'Interior material (e.g., ABS).', 'wrapper' => ['width' => '50'] ],
      [ 'key' => 'field_electrical_connection','label' => 'Electrical Connection','name' => 'electrical_connection','type' => 'text', 'instructions' => 'Electrical connection details.', 'wrapper' => ['width' => '50'] ],

      [ 'key' => 'tab_temperature', 'label' => 'Temperatur', 'type' => 'tab' ],
      [ 'key' => 'field_temperature_range_raw','label' => 'Temperature Range (raw)','name' => 'temperature_range_raw','type' => 'text','instructions' => 'Original string, e.g., "0 °C / 10 °C".', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_temp_from_c','label' => 'Temp From (°C)','name' => 'temp_from_c','type' => 'number','step' => 0.1, 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_temp_till_c','label' => 'Temp Till (°C)','name' => 'temp_till_c','type' => 'number','step' => 0.1, 'wrapper' => ['width' => '33.3'] ],

      [ 'key' => 'tab_dimensions', 'label' => 'Abmessungen & Volumen', 'type' => 'tab' ],
      [ 'key' => 'field_width_mm','label' => 'Width (mm)','name' => 'width_mm','type' => 'number','step' => 1, 'instructions' => 'Exterior width in millimeters.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_height_mm','label' => 'Height (mm)','name' => 'height_mm','type' => 'number','step' => 1, 'instructions' => 'Exterior height in millimeters.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_depth_mm','label' => 'Depth (mm)','name' => 'depth_mm','type' => 'number','step' => 1, 'instructions' => 'Exterior depth in millimeters.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_hxwxd_inside_raw','label' => 'HxWxD Inside (raw)','name' => 'hxwxd_inside_raw','type' => 'text', 'instructions' => 'Interior HxWxD text (raw).', 'wrapper' => ['width' => '30'] ],
      [ 'key' => 'field_hxwxd_outside_raw','label' => 'HxWxD Outside (raw)','name' => 'hxwxd_outside_raw','type' => 'text', 'instructions' => 'Exterior HxWxD text (raw).', 'wrapper' => ['width' => '30'] ],
      [ 'key' => 'field_volume_l','label' => 'Volume (l)','name' => 'volume_l','type' => 'number','step' => 0.1, 'instructions' => 'Volume in liters (numeric).', 'wrapper' => ['width' => '20'] ],
      [ 'key' => 'field_volume_raw','label' => 'Volume (raw)','name' => 'volume_raw','type' => 'text', 'instructions' => 'Volume text as provided in feed.', 'wrapper' => ['width' => '20'] ],

      [ 'key' => 'tab_weight_noise', 'label' => 'Gewicht & Lautstärke', 'type' => 'tab' ],
      [ 'key' => 'field_net_weight_kg','label' => 'Net Weight (kg)','name' => 'net_weight_kg','type' => 'number','step' => 0.01, 'instructions' => 'Net product weight in kilograms.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_weight_raw','label' => 'Weight (raw)','name' => 'weight_raw','type' => 'text', 'instructions' => 'Weight text as provided in feed.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_noise_volume_db','label' => 'Noise Volume (dB)','name' => 'noise_volume_db','type' => 'number','step' => 0.1, 'instructions' => 'Noise level in dB.', 'wrapper' => ['width' => '33.3'] ],

      [ 'key' => 'tab_flags', 'label' => 'Funktionen / Flags', 'type' => 'tab' ],
      [ 'key' => 'field_convection_cooling','label' => 'Convection Cooling','name' => 'convection_cooling','type' => 'text', 'instructions' => 'Raw value from feed (e.g., Ja, Nein, Umluftkühlung, Ja (2 Ventilatoren)).', 'wrapper' => ['width' => '25'] ],
      [ 'key' => 'field_buildin','label' => 'Built-in','name' => 'buildin','type' => 'true_false','ui' => 1, 'instructions' => 'Enable if product is built-in capable.', 'wrapper' => ['width' => '25'] ],
      [ 'key' => 'field_cust_gc_bevcooler','label' => 'Beverage Cooler Flag','name' => 'cust_gc_bevcooler','type' => 'true_false','ui' => 1, 'instructions' => 'Enable if item is a beverage cooler.', 'wrapper' => ['width' => '25'] ],
      [ 'key' => 'field_cust_commercialuse','label' => 'Commercial Use Flag','name' => 'cust_commercialuse','type' => 'true_false','ui' => 1, 'instructions' => 'Enable if for commercial use only.', 'wrapper' => ['width' => '25'] ],
      [ 'key' => 'field_cust_gc_freezer','label' => 'Freezer Flag','name' => 'cust_gc_freezer','type' => 'true_false','ui' => 1, 'instructions' => 'Enable if item is a freezer.', 'wrapper' => ['width' => '25'] ],
      [ 'key' => 'field_cust_gc_householdapp','label' => 'Household Appliance','name' => 'cust_gc_householdapp','type' => 'true_false','ui' => 1, 'instructions' => 'Enable if household appliance.', 'wrapper' => ['width' => '25'] ],
      [ 'key' => 'field_cust_gc_product_capacity','label' => 'Product Capacity Flag','name' => 'cust_gc_product_capacity','type' => 'true_false','ui' => 1, 'instructions' => 'Enable if capacity data present.', 'wrapper' => ['width' => '25'] ],
      [ 'key' => 'field_cust_gc_product_capacity_cans','label' => 'Product Capacity (Cans) Flag','name' => 'cust_gc_product_capacity_cans','type' => 'true_false','ui' => 1, 'instructions' => 'Enable if can capacities present.', 'wrapper' => ['width' => '25'] ],
      [ 'key' => 'field_cust_gc_product_capacity_bottles','label' => 'Product Capacity (Bottles) Flag','name' => 'cust_gc_product_capacity_bottles','type' => 'true_false','ui' => 1, 'instructions' => 'Enable if bottle capacities present.', 'wrapper' => ['width' => '25'] ],

      [ 'key' => 'tab_capacity', 'label' => 'Kapazität', 'type' => 'tab' ],
      [ 'key' => 'field_capacity_cans','label' => 'Capacities (Cans)','name' => 'capacity_cans','type' => 'repeater','layout' => 'table','button_label' => 'Add Cans Capacity','instructions' => 'List all can capacities from feed.', 'sub_fields' => [ [ 'key' => 'field_capacity_cans_text','label' => 'Text','name' => 'text','type' => 'text', 'instructions' => 'Capacity text (e.g., 48 Cans 0,25 l).' ] ] ],
      [ 'key' => 'field_capacity_bottles','label' => 'Capacities (Bottles)','name' => 'capacity_bottles','type' => 'repeater','layout' => 'table','button_label' => 'Add Bottles Capacity','instructions' => 'List all bottle capacities from feed.', 'sub_fields' => [ [ 'key' => 'field_capacity_bottles_text','label' => 'Text','name' => 'text','type' => 'text', 'instructions' => 'Capacity text (e.g., 156 Bottles 0,5 l).' ] ] ],

      [ 'key' => 'tab_storage', 'label' => 'Lager / Container', 'type' => 'tab' ],
      [ 'key' => 'field_gc_shipment_storage_40fthqcon','label' => 'Shipment Storage (40ft HQ CON)','name' => 'gc_shipment_storage_40fthqcon','type' => 'number','step' => 1, 'instructions' => 'Units per 40ft HQ container.', 'wrapper' => ['width' => '33.3'] ],

      [ 'key' => 'tab_seo', 'label' => 'SEO', 'type' => 'tab' ],
      [ 'key' => 'field_dreisc_seo_sitemap_priority','label' => 'SEO Sitemap Priority','name' => 'dreisc_seo_sitemap_priority','type' => 'text', 'instructions' => 'Optional sitemap priority value.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_dreisc_seo_canonical_link_type','label' => 'SEO Canonical Link Type (raw)','name' => 'dreisc_seo_canonical_link_type','type' => 'textarea', 'instructions' => 'Raw JSON for canonical link type.', 'wrapper' => ['width' => '33.3'] ],
      [ 'key' => 'field_dreisc_seo_canonical_link_reference','label' => 'SEO Canonical Link Reference (raw)','name' => 'dreisc_seo_canonical_link_reference','type' => 'textarea', 'instructions' => 'Raw JSON for canonical link reference.', 'wrapper' => ['width' => '33.3'] ],
    ],
    'location' => [[[
      'param' => 'post_type',
      'operator' => '==',
      'value' => 'product',
    ]]],
    'position' => 'normal',
    'active' => true,
  ]);

  // Certification Term (taxonomy term meta)
  acf_add_local_field_group([
    'key' => 'group_gc_certification_term',
    'title' => 'Certification Term',
    'fields' => [
      [
        'key' => 'field_cert_code',
        'label' => 'Certification Code',
        'name' => 'cert_code',
        'type' => 'text',
        'instructions' => 'Stores the certification code (from g:certification_code).',
      ],
    ],
    'location' => [[[
      'param' => 'taxonomy',
      'operator' => '==',
      'value' => 'certification',
    ]]],
    'position' => 'normal',
    'active' => true,
  ]);
});
