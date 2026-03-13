<?php

namespace GCP\Elementor\Widgets;

if (! defined('ABSPATH')) {
  exit;
}

use Elementor\Widget_Base;

class Product_Gallery_Widget extends Widget_Base
{
  public function get_name() {
    return 'gcp_product_gallery';
  }

  public function get_title() {
    return __('Produkt-Galerie', 'gastro-cool-products');
  }

  public function get_icon() {
    return 'eicon-gallery-grid';
  }

  public function get_categories() {
    return ['gastro-cool'];
  }

  public function get_style_depends() {
    return ['gcp-plugin'];
  }

  public function get_script_depends() {
    return ['gcp-plugin'];
  }

  protected function register_controls() {
    // Keine Controls – alle Daten kommen automatisch aus dem Post
  }

  protected function render() {
    $post_id = get_the_ID();

    if (! $post_id) {
      if (isset(\Elementor\Plugin::$instance->editor) && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
        echo '<p style="color:#999;font-size:12px;margin:0">' . esc_html__('Produkt-Galerie: kein Beitrag im Kontext.', 'gastro-cool-products') . '</p>';
      }
      return;
    }

    // ── Bilder sammeln ────────────────────────────────────────────────────
    $images = [];

    // 1. Featured Image
    $featured_id = get_post_thumbnail_id($post_id);
    if ($featured_id) {
      $src = wp_get_attachment_image_url($featured_id, 'large');
      $alt = get_post_meta($featured_id, '_wp_attachment_image_alt', true) ?: get_the_title($post_id);
      if ($src) {
        $images[] = ['url' => $src, 'alt' => $alt];
      }
    }

    // Fallback: featured_image_source_url (ACF, externe URL aus dem Import)
    if (empty($images)) {
      $src = get_field('featured_image_source_url', $post_id);
      if ($src) {
        $images[] = ['url' => $src, 'alt' => get_the_title($post_id)];
      }
    }

    // 2. Weitere Bilder aus ACF gallery additional_image_links (return_format='id')
    $additional = get_field('additional_image_links', $post_id);
    if (is_array($additional)) {
      foreach ($additional as $row) {
        if (is_numeric($row)) {
          // ACF gallery mit return_format='id' liefert Integer-IDs
          $url = wp_get_attachment_image_url((int)$row, 'large');
          $alt = get_post_meta((int)$row, '_wp_attachment_image_alt', true) ?: '';
        } else {
          // Fallback: array-Format
          $url = isset($row['url']) ? trim($row['url']) : '';
          $alt = isset($row['alt']) ? $row['alt'] : '';
        }
        if ($url !== '') {
          $images[] = ['url' => $url, 'alt' => $alt];
        }
      }
    }

    if (empty($images)) {
      return;
    }

    $total      = count($images);
    $multi      = $total > 1;
    $widget_id  = $this->get_id();

    // ── Markup ────────────────────────────────────────────────────────────
    echo '<div class="gc-product-gallery" id="gc-gallery-' . esc_attr($widget_id) . '" data-gallery>';

    // Hauptbild
    echo '<div class="gc-product-gallery__main">';
    echo '<img class="gc-product-gallery__main-img"'
       . ' src="' . esc_url($images[0]['url']) . '"'
       . ' alt="' . esc_attr($images[0]['alt']) . '"'
       . ' loading="eager" />';
    echo '</div>';

    if ($multi) {
      // Thumbnails (Swiper)
      echo '<div class="gc-product-gallery__thumbs swiper">';
      echo '<div class="swiper-wrapper">';
      foreach ($images as $i => $img) {
        $active = $i === 0 ? ' gc-product-gallery__thumb--active' : '';
        echo '<div class="swiper-slide">'
           . '<button type="button"'
           . ' class="gc-product-gallery__thumb' . $active . '"'
           . ' data-src="' . esc_url($img['url']) . '"'
           . ' data-alt="' . esc_attr($img['alt']) . '"'
           . ' data-index="' . esc_attr($i) . '"'
           . ' aria-label="' . esc_attr(sprintf(__('Bild %d von %d', 'gastro-cool-products'), $i + 1, $total)) . '">'
           . '<img src="' . esc_url($img['url']) . '" alt="" loading="lazy" />'
           . '</button>'
           . '</div>';
      }
      echo '</div>';
      echo '<div class="swiper-button-prev"></div>';
      echo '<div class="swiper-button-next"></div>';
      echo '</div>';

      // Zähler
      echo '<p class="gc-product-gallery__counter">';
      echo '<span class="gc-product-gallery__counter-current">1</span>'
         . ' / '
         . '<span class="gc-product-gallery__counter-total">' . esc_html($total) . '</span>'
         . ' &mdash; '
         . esc_html__('Weitere Ansichten verfügbar', 'gastro-cool-products');
      echo '</p>';
    }

    echo '</div>';
  }
}
