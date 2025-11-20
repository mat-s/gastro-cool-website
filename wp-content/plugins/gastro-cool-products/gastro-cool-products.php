<?php

/**
 * Plugin Name: Gastro Cool Products
 * Description: Registers the Product custom post type for Gastro-Cool.
 * Version: 0.1.0
 * Author: Gastro-Cool
 * Text Domain: gastro-cool-products
 * Domain Path: /languages
 */

if (! defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

define('GCP_VERSION', '0.1.0');
define('GCP_PLUGIN_FILE', __FILE__);
define('GCP_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Load translations early
add_action('init', function () {
  load_plugin_textdomain('gastro-cool-products', false, dirname(plugin_basename(__FILE__)) . '/languages');
}, 0);

/**
 * Register the core Product CPT.
 */
function gcp_register_post_type()
{
  $labels = [
    'name'                  => __('Products', 'gastro-cool-products'),
    'singular_name'         => __('Product', 'gastro-cool-products'),
    'menu_name'             => __('Products', 'gastro-cool-products'),
    'name_admin_bar'        => __('Product', 'gastro-cool-products'),
    'add_new'               => __('Add New', 'gastro-cool-products'),
    'add_new_item'          => __('Add New Product', 'gastro-cool-products'),
    'new_item'              => __('New Product', 'gastro-cool-products'),
    'edit_item'             => __('Edit Product', 'gastro-cool-products'),
    'view_item'             => __('View Product', 'gastro-cool-products'),
    'all_items'             => __('All Products', 'gastro-cool-products'),
    'search_items'          => __('Search Products', 'gastro-cool-products'),
    'not_found'             => __('No products found.', 'gastro-cool-products'),
    'not_found_in_trash'    => __('No products found in Trash.', 'gastro-cool-products'),
  ];

  $args = [
    'labels'             => $labels,
    'public'             => true,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'show_in_rest'       => true,
    'menu_icon'          => 'dashicons-cart',
    'supports'           => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
    'has_archive'        => true,
    'rewrite'            => ['slug' => 'products', 'with_front' => false],
    'publicly_queryable' => true,
    'capability_type'    => 'post',
    'map_meta_cap'       => true,
  ];

  register_post_type('product', $args);
}
add_action('init', 'gcp_register_post_type', 5);

/**
 * Register custom taxonomies for Products.
 */
function gcp_register_taxonomies()
{
  // Hierarchical product categories (from feed path starting with Products > ...)
  register_taxonomy('product_category', ['product'], [
    'labels' => [
      'name' => __('Product Categories', 'gastro-cool-products'),
      'singular_name' => __('Product Category', 'gastro-cool-products'),
    ],
    'hierarchical' => true,
    'show_ui' => true,
    'show_in_rest' => true,
    'public' => true,
    'rewrite' => ['slug' => 'product-category', 'with_front' => false],
  ]);

  // Hierarchical industries (from feed path starting with Industry > ...)
  register_taxonomy('industry', ['product'], [
    'labels' => [
      'name' => __('Industries', 'gastro-cool-products'),
      'singular_name' => __('Industry', 'gastro-cool-products'),
    ],
    'hierarchical' => true,
    'show_ui' => true,
    'show_in_rest' => true,
    'public' => true,
    'rewrite' => ['slug' => 'industry', 'with_front' => false],
  ]);

  // Flat brand taxonomy
  register_taxonomy('brand', ['product'], [
    'labels' => [
      'name' => __('Brands', 'gastro-cool-products'),
      'singular_name' => __('Brand', 'gastro-cool-products'),
    ],
    'hierarchical' => false,
    'show_ui' => true,
    'show_in_rest' => true,
    'public' => true,
    'rewrite' => ['slug' => 'brand', 'with_front' => false],
  ]);

  // Flat product group taxonomy (from g:cust_product_group)
  register_taxonomy('product_group', ['product'], [
    'labels' => [
      'name' => __('Product Groups', 'gastro-cool-products'),
      'singular_name' => __('Product Group', 'gastro-cool-products'),
    ],
    'hierarchical' => false,
    'show_ui' => true,
    'show_in_rest' => true,
    'public' => true,
    'rewrite' => ['slug' => 'product-group', 'with_front' => false],
  ]);

  // Flat color taxonomy (from g:color)
  register_taxonomy('color', ['product'], [
    'labels' => [
      'name' => __('Colors', 'gastro-cool-products'),
      'singular_name' => __('Color', 'gastro-cool-products'),
    ],
    'hierarchical' => false,
    'show_ui' => true,
    'show_in_rest' => true,
    'public' => true,
    'rewrite' => ['slug' => 'product-color', 'with_front' => false],
  ]);

  // Hierarchical certification taxonomy (Authority > Name). Code stored as term meta later.
  register_taxonomy('certification', ['product'], [
    'labels' => [
      'name' => __('Certifications', 'gastro-cool-products'),
      'singular_name' => __('Certification', 'gastro-cool-products'),
    ],
    'hierarchical' => true,
    'show_ui' => true,
    'show_in_rest' => true,
    'public' => true,
    'rewrite' => ['slug' => 'certification', 'with_front' => false],
  ]);
}
add_action('init', 'gcp_register_taxonomies', 6);

// Activation/Deactivation: multisite-safe
function gcp_activate_per_site()
{
  gcp_register_post_type();
  flush_rewrite_rules();
}

function gcp_activate($network_wide)
{
  if (is_multisite() && $network_wide) {
    $site_ids = function_exists('get_sites') ? get_sites(array('fields' => 'ids')) : array();
    if (empty($site_ids)) {
      global $wpdb;
      $site_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
    }
    foreach ($site_ids as $site_id) {
      switch_to_blog((int) $site_id);
      gcp_activate_per_site();
      restore_current_blog();
    }
  } else {
    gcp_activate_per_site();
  }
}
register_activation_hook(__FILE__, 'gcp_activate');

function gcp_deactivate($network_wide)
{
  if (is_multisite() && $network_wide) {
    $site_ids = function_exists('get_sites') ? get_sites(array('fields' => 'ids')) : array();
    if (empty($site_ids)) {
      global $wpdb;
      $site_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
    }
    foreach ($site_ids as $site_id) {
      switch_to_blog((int) $site_id);
      flush_rewrite_rules();
      restore_current_blog();
    }
  } else {
    flush_rewrite_rules();
  }
}
register_deactivation_hook(__FILE__, 'gcp_deactivate');

// Load ACF field groups (if ACF is active)
add_action('plugins_loaded', function () {
  if (function_exists('acf_add_local_field_group')) {
    require_once GCP_PLUGIN_DIR . 'includes/acf-fields.php';
  }
  // Importer is independent of ACF presence (falls back to post meta)
  require_once GCP_PLUGIN_DIR . 'includes/importer.php';
  if (is_admin()) {
    require_once GCP_PLUGIN_DIR . 'includes/admin-import.php';
    require_once GCP_PLUGIN_DIR . 'includes/admin-columns.php';
  }
  // Elementor Skin: load after Elementor Pro initializes to avoid early autoload issues
  add_action('elementor_pro/init', function() {
    require_once GCP_PLUGIN_DIR . 'elementor-skins/products-grid.php';
  });
  // Elementor widget: Inquiry button
  add_action('elementor/widgets/register', function($widgets_manager) {
    if (! class_exists('\Elementor\Widget_Base')) {
      return;
    }
    require_once GCP_PLUGIN_DIR . 'elementor-widgets/inquiry-button.php';
    $widgets_manager->register( new \GCP\Elementor\Widgets\Inquiry_Button_Widget() );
  });
  // Enqueue skin styles and inquiry script on frontend
  add_action('wp_enqueue_scripts', function(){
    wp_enqueue_style(
      'gcp-products-grid',
      plugins_url('assets/css/products-grid.css', __FILE__),
      [],
      GCP_VERSION
    );

    wp_enqueue_script(
      'gcp-inquiry',
      plugins_url('assets/js/inquiry.js', __FILE__),
      [],
      GCP_VERSION,
      true
    );

    wp_enqueue_script(
      'gcp-inquiry-overlay',
      plugins_url('assets/js/inquiry-overlay.js', __FILE__),
      ['gcp-inquiry'],
      GCP_VERSION,
      true
    );

    wp_enqueue_script(
      'gcp-inquiry-badge',
      plugins_url('assets/js/inquiry-badge.js', __FILE__),
      ['gcp-inquiry'],
      GCP_VERSION,
      true
    );
  });

  // Render inquiry overlay markup in footer
  add_action('wp_footer', function () {
    ?>
    <div class="gc-inquiry-overlay" aria-hidden="true">
      <aside class="gc-inquiry-overlay__panel" role="dialog" aria-modal="true" aria-labelledby="gc-inquiry-overlay-title">
        <header class="gc-inquiry-overlay__header">
          <div id="gc-inquiry-overlay-title"><?php echo esc_html__('Ihre Anfrage', 'gastro-cool-products'); ?></div>
          <button type="button" class="gc-inquiry-overlay__close" aria-label="<?php echo esc_attr__('Overlay schließen', 'gastro-cool-products'); ?>">×</button>
        </header>
        <div class="gc-inquiry-overlay__body">
          <div class="gc-inquiry-overlay__empty"><?php echo esc_html__('Ihre Merkliste ist derzeit leer.', 'gastro-cool-products'); ?></div>
          <ul class="gc-inquiry-overlay__list"></ul>
        </div>
        <footer class="gc-inquiry-overlay__footer">
          <button type="button" class="gc-inquiry-overlay__clear"><?php echo esc_html__('Liste leeren', 'gastro-cool-products'); ?></button>
          <a class="gc-inquiry-overlay__submit" href="<?php echo esc_url('/anfrage'); ?>"><?php echo esc_html__('Zur Anfrage fortfahren', 'gastro-cool-products'); ?></a>
        </footer>
      </aside>
    </div>
    <?php
  });
});

// Allow XML uploads for admins (needed for the Odoo XML import)
add_filter('upload_mimes', function ($mimes) {
  if (current_user_can('manage_options')) {
    $mimes['xml']  = 'text/xml';
    $mimes['rss']  = 'application/rss+xml';
    $mimes['atom'] = 'application/atom+xml';
  }
  return $mimes;
});
