<?php
if (! defined('ABSPATH')) { exit; }

/**
 * Adds a "Produktbilder / Sonstige" filter to the media library.
 * – Grid view: via wp.media JS API (AttachmentFilters)
 * – List view: via restrict_manage_posts dropdown
 *
 * Product images are identified by _wp_attached_file starting with "produkte/".
 */

// ── 1. Enqueue JS for grid view ───────────────────────────────────────────────
add_action('admin_enqueue_scripts', function ($hook) {
  if ($hook !== 'upload.php' && $hook !== 'post.php' && $hook !== 'post-new.php') { return; }
  wp_enqueue_media(); // ensure wp.media is loaded
  wp_enqueue_script(
    'gcp-media-filter',
    plugin_dir_url(GCP_PLUGIN_FILE) . 'assets/js/admin-media-filter.js',
    ['media-views'],
    GCP_VERSION,
    true
  );
  wp_localize_script('gcp-media-filter', 'gcpMediaFilter', [
    'labels' => [
      'all'      => __('Alle Medien',      'gastro-cool-products'),
      'produkte' => __('Produktbilder',    'gastro-cool-products'),
      'other'    => __('Sonstige Medien',  'gastro-cool-products'),
    ],
  ]);
});

// ── 2. List view dropdown ─────────────────────────────────────────────────────
add_action('restrict_manage_posts', function ($post_type) {
  if ($post_type !== 'attachment') { return; }
  $current = isset($_GET['gcp_media_folder']) ? sanitize_key($_GET['gcp_media_folder']) : '';
  ?>
  <select name="gcp_media_folder" id="gcp-media-folder-filter">
    <option value=""><?php esc_html_e('Alle Medien', 'gastro-cool-products'); ?></option>
    <option value="produkte" <?php selected($current, 'produkte'); ?>><?php esc_html_e('Produktbilder', 'gastro-cool-products'); ?></option>
    <option value="other"    <?php selected($current, 'other'); ?>><?php esc_html_e('Sonstige Medien', 'gastro-cool-products'); ?></option>
  </select>
  <?php
});

// ── 3. Apply filter to queries ────────────────────────────────────────────────

// Grid view (AJAX)
add_filter('ajax_query_attachments_args', 'gcp_apply_folder_meta_query');

// List view
add_action('pre_get_posts', function (WP_Query $q) {
  if (! is_admin() || $q->get('post_type') !== 'attachment') { return; }
  $filtered = gcp_apply_folder_meta_query($q->query_vars);
  foreach ($filtered as $key => $val) {
    $q->set($key, $val);
  }
});

function gcp_apply_folder_meta_query(array $args): array {
  // Grid view passes the prop via $args['gcp_media_folder'];
  // list view passes it via $_GET['gcp_media_folder']
  $folder = '';
  if (! empty($args['gcp_media_folder'])) {
    $folder = sanitize_key($args['gcp_media_folder']);
  } elseif (isset($_GET['gcp_media_folder']) && $_GET['gcp_media_folder'] !== '') {
    $folder = sanitize_key($_GET['gcp_media_folder']);
  }

  if ($folder === '') { return $args; }

  $meta_query = isset($args['meta_query']) && is_array($args['meta_query']) ? $args['meta_query'] : [];

  if ($folder === 'produkte') {
    $meta_query[] = ['key' => '_wp_attached_file', 'value' => 'produkte/', 'compare' => 'LIKE'];
  } elseif ($folder === 'other') {
    $meta_query[] = ['key' => '_wp_attached_file', 'value' => 'produkte/', 'compare' => 'NOT LIKE'];
  }

  $args['meta_query'] = $meta_query;
  return $args;
}
