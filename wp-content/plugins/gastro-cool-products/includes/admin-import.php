<?php
if (! defined('ABSPATH')) { exit; }

// Admin page: Gastro-Cool → Odoo Import
add_action('admin_menu', function () {
  $parent_slug = 'edit.php?post_type=product';
  add_submenu_page(
    $parent_slug,
    __('Odoo Import', 'gastro-cool-products'),
    __('Odoo Import', 'gastro-cool-products'),
    'manage_options',
    'gcp-odoo-import',
    'gcp_render_odoo_import_page'
  );
});

function gcp_render_odoo_import_page()
{
  if (! current_user_can('manage_options')) { wp_die(__('Insufficient permissions')); }

  $result = null; $error = null;

  if (! empty($_POST['gcp_import_submit'])) {
    check_admin_referer('gcp_odoo_import');

    $download_images = ! empty($_POST['gcp_download_images']) ? true : false;

    if (! isset($_FILES['gcp_xml_file'])) {
      $error = __('No file uploaded.', 'gastro-cool-products');
    } else {
      $file = $_FILES['gcp_xml_file'];
      if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = sprintf(__('Upload error: %s', 'gastro-cool-products'), (int)$file['error']);
      } else {
        // Only allow XML mime types
        $overrides = [
          'test_form' => false,
          'mimes' => [
            // Allow common XML variants
            'xml'  => 'text/xml',
            'rss'  => 'application/rss+xml',
            'atom' => 'application/atom+xml',
          ],
        ];
        $uploaded = wp_handle_upload($file, $overrides);
        if (isset($uploaded['error'])) {
          $error = $uploaded['error'];
        } else {
          $xml_path = $uploaded['file'];
          if (! function_exists('gcp_import_odoo')) {
            require_once plugin_dir_path(__FILE__) . 'importer.php';
          }
          $res = gcp_import_odoo($xml_path, $download_images);
          if (is_wp_error($res)) {
            $error = $res->get_error_message();
          } else {
            $result = $res;
          }
        }
      }
    }
  }

  echo '<div class="wrap">';
  echo '<h1>' . esc_html__('Odoo XML Import', 'gastro-cool-products') . '</h1>';

  if ($error) {
    echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
  }
  if ($result) {
    echo '<div class="notice notice-success"><p>' . esc_html(sprintf(__('Import finished. Total: %d, Matched: %d, Created: %d, Updated: %d, Skipped: %d', 'gastro-cool-products'),
      $result['total'], $result['matched'], $result['created'], $result['updated'], $result['skipped'])) . '</p></div>';
  }

  ?>
  <style>
    .gcp-dropzone { border: 2px dashed #97a; padding: 24px; text-align: center; background: #fafafa; cursor: pointer; }
    .gcp-dropzone.dragover { background: #eef6ff; border-color: #2271b1; }
    .gcp-hidden-file { display: none; }
  </style>
  <form method="post" enctype="multipart/form-data">
    <?php wp_nonce_field('gcp_odoo_import'); ?>
    <input id="gcp_xml_file" class="gcp-hidden-file" type="file" name="gcp_xml_file" accept=".xml,application/xml,text/xml,application/rss+xml" />
    <div id="gcp_dropzone" class="gcp-dropzone" onclick="document.getElementById('gcp_xml_file').click()">
      <strong><?php echo esc_html__('Drag & Drop your Odoo XML here', 'gastro-cool-products'); ?></strong><br />
      <em><?php echo esc_html__('...or click to choose a file', 'gastro-cool-products'); ?></em>
      <div id="gcp_filename" style="margin-top:8px;color:#333;"></div>
    </div>
    <p>
      <label><input type="checkbox" name="gcp_download_images" value="1" /> <?php echo esc_html__('Download images and set featured image', 'gastro-cool-products'); ?></label>
    </p>
    <p>
      <button type="submit" class="button button-primary" name="gcp_import_submit" value="1"><?php echo esc_html__('Start Import', 'gastro-cool-products'); ?></button>
    </p>
  </form>
  <script>
    (function(){
      const dz = document.getElementById('gcp_dropzone');
      const fi = document.getElementById('gcp_xml_file');
      const fn = document.getElementById('gcp_filename');
      function showName(file){ fn.textContent = file ? file.name : ''; }
      fi.addEventListener('change', function(){ showName(this.files[0]); });
      dz.addEventListener('dragover', function(e){ e.preventDefault(); dz.classList.add('dragover'); });
      dz.addEventListener('dragleave', function(e){ dz.classList.remove('dragover'); });
      dz.addEventListener('drop', function(e){ e.preventDefault(); dz.classList.remove('dragover'); if (e.dataTransfer.files.length){ fi.files = e.dataTransfer.files; showName(fi.files[0]); }});
    })();
  </script>
  <?php
  echo '</div>';
}
