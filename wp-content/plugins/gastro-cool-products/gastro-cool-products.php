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
