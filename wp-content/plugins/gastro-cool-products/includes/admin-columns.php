<?php
if (! defined('ABSPATH')) { exit; }

// Add Product Model column to Products list table
add_filter('manage_edit-product_columns', function ($columns) {
  $new = [];
  foreach ($columns as $key => $label) {
    $new[$key] = $label;
    if ($key === 'title') {
      $new['gcp_product_model_name'] = __('Model', 'gastro-cool-products');
    }
  }
  if (! isset($new['gcp_product_model_name'])) {
    $new['gcp_product_model_name'] = __('Model', 'gastro-cool-products');
  }
  return $new;
});

add_action('manage_product_posts_custom_column', function ($column, $post_id) {
  if ($column === 'gcp_product_model_name') {
    $val = get_post_meta($post_id, 'product_model_name', true);
    if (! $val) { $val = '—'; }
    echo esc_html($val);
  }
}, 10, 2);

// Make Model column sortable
add_filter('manage_edit-product_sortable_columns', function ($columns) {
  $columns['gcp_product_model_name'] = 'gcp_product_model_name';
  return $columns;
});

add_action('pre_get_posts', function ($query) {
  if (! is_admin() || ! $query->is_main_query()) return;
  if ($query->get('post_type') !== 'product') return;

  // Sorting by model
  if ($query->get('orderby') === 'gcp_product_model_name') {
    $query->set('meta_key', 'product_model_name');
    $query->set('orderby', 'meta_value');
  }

  // Filtering by model exact match
  if (isset($_GET['gcp_filter_model']) && $_GET['gcp_filter_model'] !== '') {
    $model = sanitize_text_field(wp_unslash($_GET['gcp_filter_model']));
    $meta_query = (array) $query->get('meta_query');
    $meta_query[] = [
      'key' => 'product_model_name',
      'value' => $model,
      'compare' => '=',
    ];
    $query->set('meta_query', $meta_query);
  }
});

// Add a filter dropdown for Model in the list table
add_action('restrict_manage_posts', function ($post_type) {
  if ($post_type !== 'product') return;
  if (! current_user_can('edit_others_posts')) return;

  global $wpdb;
  $cache_key = 'gcp_product_model_list_v1';
  $models = get_transient($cache_key);
  if ($models === false) {
    // Get distinct model values for published and draft products
    $models = $wpdb->get_col($wpdb->prepare(
      "SELECT DISTINCT pm.meta_value
       FROM {$wpdb->postmeta} pm
       INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
       WHERE pm.meta_key = %s AND p.post_type = %s AND p.post_status IN ('publish','draft','pending','private')
       AND pm.meta_value <> ''
       ORDER BY pm.meta_value ASC
       LIMIT 1000",
      'product_model_name', 'product'
    ));
    if (! is_array($models)) { $models = []; }
    set_transient($cache_key, $models, 10 * MINUTE_IN_SECONDS);
  }

  $current = isset($_GET['gcp_filter_model']) ? sanitize_text_field(wp_unslash($_GET['gcp_filter_model'])) : '';
  echo '<label for="gcp_filter_model" class="screen-reader-text">' . esc_html__('Filter by Model', 'gastro-cool-products') . '</label>';
  echo '<select name="gcp_filter_model" id="gcp_filter_model">';
  echo '<option value="">' . esc_html__('All Models', 'gastro-cool-products') . '</option>';
  foreach ($models as $m) {
    printf('<option value="%s" %s>%s</option>', esc_attr($m), selected($current, $m, false), esc_html($m));
  }
  echo '</select>';
});

