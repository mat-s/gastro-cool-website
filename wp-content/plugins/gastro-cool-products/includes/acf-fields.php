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
      [
        'key' => 'field_external_id',
        'label' => 'External ID',
        'name' => 'external_id',
        'type' => 'text',
        'instructions' => 'Unique ID from feed (g:id).',
        'required' => 1,
        'readonly' => 1,
      ],
      [
        'key' => 'field_gtin',
        'label' => 'GTIN',
        'name' => 'gtin',
        'type' => 'text',
      ],
      [
        'key' => 'field_stock',
        'label' => 'Stock',
        'name' => 'stock',
        'type' => 'number',
        'min' => 0,
        'step' => 1,
      ],
      [
        'key' => 'field_availability',
        'label' => 'Availability',
        'name' => 'availability',
        'type' => 'select',
        'choices' => [
          'in_stock' => 'in_stock',
          'out_of_stock' => 'out_of_stock',
          'preorder' => 'preorder',
          'backorder' => 'backorder',
        ],
        'allow_null' => 1,
        'ui' => 1,
        'return_format' => 'value',
      ],
      [
        'key' => 'field_refill_time',
        'label' => 'Refill Time',
        'name' => 'refill_time',
        'type' => 'number',
        'min' => 0,
        'step' => 1,
      ],
      [
        'key' => 'field_price_amount',
        'label' => 'Price Amount',
        'name' => 'price_amount',
        'type' => 'number',
        'min' => 0,
        'step' => 0.01,
      ],
      [
        'key' => 'field_price_currency',
        'label' => 'Price Currency',
        'name' => 'price_currency',
        'type' => 'text',
      ],
      [
        'key' => 'field_price_raw',
        'label' => 'Price Raw',
        'name' => 'price_raw',
        'type' => 'text',
        'instructions' => 'Optional: original price string from feed.',
      ],
      [
        'key' => 'field_shipping_weight_kg',
        'label' => 'Shipping Weight (kg)',
        'name' => 'shipping_weight_kg',
        'type' => 'number',
        'step' => 0.01,
      ],
      [
        'key' => 'field_length_cm',
        'label' => 'Length (cm)',
        'name' => 'length_cm',
        'type' => 'number',
        'step' => 0.1,
      ],
      [
        'key' => 'field_width_cm',
        'label' => 'Width (cm)',
        'name' => 'width_cm',
        'type' => 'number',
        'step' => 0.1,
      ],
      [
        'key' => 'field_height_cm',
        'label' => 'Height (cm)',
        'name' => 'height_cm',
        'type' => 'number',
        'step' => 0.1,
      ],
      [
        'key' => 'field_pallet_size',
        'label' => 'Pallet Size',
        'name' => 'pallet_size',
        'type' => 'text',
      ],
      [
        'key' => 'field_warehouse',
        'label' => 'Warehouse',
        'name' => 'warehouse',
        'type' => 'text',
      ],
      [
        'key' => 'field_google_active',
        'label' => 'Google Shopping Active',
        'name' => 'google_active',
        'type' => 'true_false',
        'ui' => 1,
      ],
      [
        'key' => 'field_google_cat',
        'label' => 'Google Product Category ID',
        'name' => 'google_cat',
        'type' => 'text',
      ],
      [
        'key' => 'field_google_available_date',
        'label' => 'Google Available Date',
        'name' => 'google_available_date',
        'type' => 'date_time_picker',
        'display_format' => 'Y-m-d H:i:s',
        'return_format' => 'Y-m-d H:i:s',
        'first_day' => 1,
      ],
      [
        'key' => 'field_shipping_price_1',
        'label' => 'Shipping Price 1',
        'name' => 'shipping_price_1',
        'type' => 'number',
        'step' => 0.01,
      ],
      [
        'key' => 'field_shipping_price_2',
        'label' => 'Shipping Price 2',
        'name' => 'shipping_price_2',
        'type' => 'number',
        'step' => 0.01,
      ],
      [
        'key' => 'field_shipping_price_3',
        'label' => 'Shipping Price 3',
        'name' => 'shipping_price_3',
        'type' => 'number',
        'step' => 0.01,
      ],
      [
        'key' => 'field_shipping_price_4',
        'label' => 'Shipping Price 4',
        'name' => 'shipping_price_4',
        'type' => 'number',
        'step' => 0.01,
      ],
      [
        'key' => 'field_shipping_price_5',
        'label' => 'Shipping Price 5',
        'name' => 'shipping_price_5',
        'type' => 'number',
        'step' => 0.01,
      ],
      [
        'key' => 'field_shipping_price_6',
        'label' => 'Shipping Price 6',
        'name' => 'shipping_price_6',
        'type' => 'number',
        'step' => 0.01,
      ],
      [
        'key' => 'field_featured_image_source_url',
        'label' => 'Featured Image Source URL',
        'name' => 'featured_image_source_url',
        'type' => 'url',
        'instructions' => 'Optional: original source URL for featured image.',
      ],
      [
        'key' => 'field_additional_image_links',
        'label' => 'Additional Image Links',
        'name' => 'additional_image_links',
        'type' => 'repeater',
        'layout' => 'table',
        'button_label' => 'Add Image Link',
        'sub_fields' => [
          [
            'key' => 'field_additional_image_link_url',
            'label' => 'URL',
            'name' => 'url',
            'type' => 'url',
          ],
        ],
      ],
      // Feed link and categorization (raw)
      [
        'key' => 'field_source_link',
        'label' => 'Source Link (feed link)',
        'name' => 'source_link',
        'type' => 'url',
      ],
      [
        'key' => 'field_categories_raw',
        'label' => 'Categories (raw from feed)',
        'name' => 'categories_raw',
        'type' => 'text',
        'instructions' => 'Original g:categories string (for reference).',
      ],

      // Model and master data
      [
        'key' => 'field_product_model_name',
        'label' => 'Product Model Name',
        'name' => 'product_model_name',
        'type' => 'text',
        'instructions' => 'From g:product_model_name (e.g., GCAP50).',
      ],
      [
        'key' => 'field_product_3digit_mastercode',
        'label' => 'Product 3-digit Mastercode',
        'name' => 'product_3digit_mastercode',
        'type' => 'text',
      ],

      // Documents
      [
        'key' => 'field_manual_document_url',
        'label' => 'Manual Document URL',
        'name' => 'manual_document_url',
        'type' => 'url',
      ],
      [
        'key' => 'field_datasheet_document_url',
        'label' => 'Datasheet Document URL',
        'name' => 'datasheet_document_url',
        'type' => 'url',
      ],

      // Energy and compliance
      [
        'key' => 'field_energy_label',
        'label' => 'Energy Label',
        'name' => 'energy_label',
        'type' => 'text',
      ],
      [
        'key' => 'field_energy_consumption_24h_raw',
        'label' => 'Energy Consumption (per 24h, raw)',
        'name' => 'energy_consumption_24h_raw',
        'type' => 'text',
        'instructions' => 'Original string e.g. "0,44 kWh / 24h".',
      ],
      [
        'key' => 'field_energy_consumption_per_year',
        'label' => 'Energy Consumption (per year, kWh)',
        'name' => 'energy_consumption_per_year',
        'type' => 'number',
        'step' => 0.01,
      ],
      [
        'key' => 'field_eei_raw',
        'label' => 'EEI (raw)',
        'name' => 'eei_raw',
        'type' => 'text',
        'instructions' => 'Original EEI string, may contain commas (e.g., "43,4").',
      ],
      [
        'key' => 'field_cust_gc_eprel',
        'label' => 'EPREL Code',
        'name' => 'cust_gc_eprel',
        'type' => 'text',
      ],
      [
        'key' => 'field_energy_label_tooltip',
        'label' => 'Energy Label Tooltip URL',
        'name' => 'energy_label_tooltip',
        'type' => 'url',
      ],
      [
        'key' => 'field_energy_button_html',
        'label' => 'Energy Button (HTML)',
        'name' => 'energy_button_html',
        'type' => 'textarea',
        'new_lines' => 'br',
      ],

      // Technical specs
      [
        'key' => 'field_doors',
        'label' => 'Doors',
        'name' => 'doors',
        'type' => 'text',
      ],
      [
        'key' => 'field_door_type',
        'label' => 'Door Type',
        'name' => 'door_type',
        'type' => 'text',
      ],
      [
        'key' => 'field_reversible_door',
        'label' => 'Reversible Door (raw)',
        'name' => 'reversible_door',
        'type' => 'text',
      ],
      [
        'key' => 'field_interior_lighting',
        'label' => 'Interior Lighting',
        'name' => 'interior_lighting',
        'type' => 'text',
      ],
      [
        'key' => 'field_coolant',
        'label' => 'Coolant',
        'name' => 'coolant',
        'type' => 'text',
      ],
      [
        'key' => 'field_functions',
        'label' => 'Functions',
        'name' => 'functions',
        'type' => 'text',
      ],
      [
        'key' => 'field_climate_class',
        'label' => 'Climate Class',
        'name' => 'climate_class',
        'type' => 'text',
      ],
      [
        'key' => 'field_controller',
        'label' => 'Controller',
        'name' => 'controller',
        'type' => 'text',
      ],
      [
        'key' => 'field_shelves_raw',
        'label' => 'Shelves (raw)',
        'name' => 'shelves_raw',
        'type' => 'text',
        'instructions' => 'Original string, e.g., "8 pcs".',
      ],
      [
        'key' => 'field_material_inside',
        'label' => 'Material (inside)',
        'name' => 'material_inside',
        'type' => 'text',
      ],
      [
        'key' => 'field_electrical_connection',
        'label' => 'Electrical Connection',
        'name' => 'electrical_connection',
        'type' => 'text',
      ],

      // Temperature
      [
        'key' => 'field_temperature_range_raw',
        'label' => 'Temperature Range (raw)',
        'name' => 'temperature_range_raw',
        'type' => 'text',
        'instructions' => 'Original string, e.g., "0 °C / 10 °C".',
      ],
      [
        'key' => 'field_temp_from_c',
        'label' => 'Temp From (°C)',
        'name' => 'temp_from_c',
        'type' => 'number',
        'step' => 0.1,
      ],
      [
        'key' => 'field_temp_till_c',
        'label' => 'Temp Till (°C)',
        'name' => 'temp_till_c',
        'type' => 'number',
        'step' => 0.1,
      ],

      // Dimensions and volume
      [
        'key' => 'field_width_mm',
        'label' => 'Width (mm)',
        'name' => 'width_mm',
        'type' => 'number',
        'step' => 1,
      ],
      [
        'key' => 'field_height_mm',
        'label' => 'Height (mm)',
        'name' => 'height_mm',
        'type' => 'number',
        'step' => 1,
      ],
      [
        'key' => 'field_depth_mm',
        'label' => 'Depth (mm)',
        'name' => 'depth_mm',
        'type' => 'number',
        'step' => 1,
      ],
      [
        'key' => 'field_hxwxd_inside_raw',
        'label' => 'HxWxD Inside (raw)',
        'name' => 'hxwxd_inside_raw',
        'type' => 'text',
      ],
      [
        'key' => 'field_hxwxd_outside_raw',
        'label' => 'HxWxD Outside (raw)',
        'name' => 'hxwxd_outside_raw',
        'type' => 'text',
      ],
      [
        'key' => 'field_volume_l',
        'label' => 'Volume (l)',
        'name' => 'volume_l',
        'type' => 'number',
        'step' => 0.1,
      ],
      [
        'key' => 'field_volume_raw',
        'label' => 'Volume (raw)',
        'name' => 'volume_raw',
        'type' => 'text',
      ],

      // Weight and noise
      [
        'key' => 'field_net_weight_kg',
        'label' => 'Net Weight (kg)',
        'name' => 'net_weight_kg',
        'type' => 'number',
        'step' => 0.01,
      ],
      [
        'key' => 'field_weight_raw',
        'label' => 'Weight (raw)',
        'name' => 'weight_raw',
        'type' => 'text',
      ],
      [
        'key' => 'field_noise_volume_db',
        'label' => 'Noise Volume (dB)',
        'name' => 'noise_volume_db',
        'type' => 'number',
        'step' => 0.1,
      ],

      // Booleans and flags
      [
        'key' => 'field_convection_cooling',
        'label' => 'Convection Cooling',
        'name' => 'convection_cooling',
        'type' => 'true_false',
        'ui' => 1,
      ],
      [
        'key' => 'field_buildin',
        'label' => 'Built-in',
        'name' => 'buildin',
        'type' => 'true_false',
        'ui' => 1,
      ],
      [
        'key' => 'field_cust_gc_bevcooler',
        'label' => 'Beverage Cooler Flag',
        'name' => 'cust_gc_bevcooler',
        'type' => 'true_false',
        'ui' => 1,
      ],
      [
        'key' => 'field_cust_commercialuse',
        'label' => 'Commercial Use Flag',
        'name' => 'cust_commercialuse',
        'type' => 'true_false',
        'ui' => 1,
      ],
      [
        'key' => 'field_cust_gc_freezer',
        'label' => 'Freezer Flag',
        'name' => 'cust_gc_freezer',
        'type' => 'true_false',
        'ui' => 1,
      ],
      [
        'key' => 'field_cust_gc_householdapp',
        'label' => 'Household Appliance',
        'name' => 'cust_gc_householdapp',
        'type' => 'true_false',
        'ui' => 1,
      ],
      [
        'key' => 'field_cust_gc_product_capacity',
        'label' => 'Product Capacity Flag',
        'name' => 'cust_gc_product_capacity',
        'type' => 'true_false',
        'ui' => 1,
      ],
      [
        'key' => 'field_cust_gc_product_capacity_cans',
        'label' => 'Product Capacity (Cans) Flag',
        'name' => 'cust_gc_product_capacity_cans',
        'type' => 'true_false',
        'ui' => 1,
      ],
      [
        'key' => 'field_cust_gc_product_capacity_bottles',
        'label' => 'Product Capacity (Bottles) Flag',
        'name' => 'cust_gc_product_capacity_bottles',
        'type' => 'true_false',
        'ui' => 1,
      ],

      // Capacities (repeaters to preserve all variants)
      [
        'key' => 'field_capacity_cans',
        'label' => 'Capacities (Cans)',
        'name' => 'capacity_cans',
        'type' => 'repeater',
        'layout' => 'table',
        'button_label' => 'Add Cans Capacity',
        'sub_fields' => [
          [
            'key' => 'field_capacity_cans_text',
            'label' => 'Text',
            'name' => 'text',
            'type' => 'text',
          ],
        ],
      ],
      [
        'key' => 'field_capacity_bottles',
        'label' => 'Capacities (Bottles)',
        'name' => 'capacity_bottles',
        'type' => 'repeater',
        'layout' => 'table',
        'button_label' => 'Add Bottles Capacity',
        'sub_fields' => [
          [
            'key' => 'field_capacity_bottles_text',
            'label' => 'Text',
            'name' => 'text',
            'type' => 'text',
          ],
        ],
      ],

      // Shipping/storage
      [
        'key' => 'field_gc_shipment_storage_40fthqcon',
        'label' => 'Shipment Storage (40ft HQ CON)',
        'name' => 'gc_shipment_storage_40fthqcon',
        'type' => 'number',
        'step' => 1,
      ],

      // SEO (raw JSON strings)
      [
        'key' => 'field_dreisc_seo_sitemap_priority',
        'label' => 'SEO Sitemap Priority',
        'name' => 'dreisc_seo_sitemap_priority',
        'type' => 'text',
      ],
      [
        'key' => 'field_dreisc_seo_canonical_link_type',
        'label' => 'SEO Canonical Link Type (raw)',
        'name' => 'dreisc_seo_canonical_link_type',
        'type' => 'textarea',
      ],
      [
        'key' => 'field_dreisc_seo_canonical_link_reference',
        'label' => 'SEO Canonical Link Reference (raw)',
        'name' => 'dreisc_seo_canonical_link_reference',
        'type' => 'textarea',
      ],

      // Google Shopping condition
      [
        'key' => 'field_google_condition',
        'label' => 'Google Shopping Condition',
        'name' => 'google_condition',
        'type' => 'text',
      ],
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
