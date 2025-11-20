<?php
namespace GCP\Elementor\Skins;

if (! defined('ABSPATH')) { exit; }

// Ensure Elementor Pro Posts skin base exists
if (! class_exists('ElementorPro\\Modules\\Posts\\Skins\\Skin_Base')) {
  return;
}

use ElementorPro\Modules\Posts\Skins\Skin_Base;

class Products_Grid_Skin extends Skin_Base
{
  public function get_id() {
    return 'gcp_products_grid';
  }

  public function get_title() {
    return __('Products Grid (Gastro‑Cool)', 'gastro-cool-products');
  }

  protected function render_post() {
    $post_id = get_the_ID();
    $permalink = get_permalink();
    $title = get_the_title();
    $excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( get_the_content() ), 22, '…' );

    // Featured image (fallback to placeholder)
    $thumb_html = '';
    if (has_post_thumbnail()) {
      $thumb_html = get_the_post_thumbnail($post_id, 'large', ['class' => 'gcp-card__img']);
    } else {
      $src = esc_url( get_field('featured_image_source_url', $post_id) ?: '' );
      if ($src) {
        $thumb_html = '<img class="gcp-card__img" src="' . $src . '" alt="' . esc_attr($title) . '" />';
      }
    }

    // Optional corner badge (Energy Button HTML from ACF)
    $corner_badge = '';
    $energy_html = function_exists('get_field') ? (string) get_field('energy_button_html', $post_id) : '';
    if ($energy_html !== '') {
      // Field already contains full HTML (e.g. <div class="energybutton"><img .../></div>)
      // Wrap in a minimal container for positioning in the card corner.
      $corner_badge = '<div class="gcp-card__corner-badge">' . wp_kses_post($energy_html) . '</div>';
    }

    // Badges repeater (ACF)
    $badges_html = '';
    if (function_exists('have_rows') && have_rows('badges', $post_id)) {
      $badges_html .= '<div class="gcp-card__badges">';
      while (have_rows('badges', $post_id)) { the_row();
        $label = get_sub_field('label');
        if (! $label) { $label = get_sub_field('text'); }
        if (! $label) { $label = get_sub_field('name'); }
        if ($label) {
          $badges_html .= '<span class="gcp-tag">' . esc_html($label) . '</span>';
        }
      }
      $badges_html .= '</div>';
    }

    // Buttons
    $details = '<a class="gcp-btn gcp-btn--primary" href="' . esc_url($permalink) . '">' . esc_html__('Details', 'gastro-cool-products') . '</a>';

    $image_url = '';
    if (has_post_thumbnail($post_id)) {
      $image_url = get_the_post_thumbnail_url($post_id, 'large') ?: '';
    } else {
      $image_url = function_exists('get_field') ? (string) get_field('featured_image_source_url', $post_id) : '';
    }

    $consult = sprintf(
      '<button type="button" class="gcp-btn gcp-btn--ghost gcp-consult-btn" data-gc-inquiry-button="1" data-product-id="%1$s" data-product-title="%2$s" data-product-image="%3$s" data-product-url="%4$s">%5$s</button>',
      esc_attr($post_id),
      esc_attr($title),
      esc_url($image_url),
      esc_url($permalink),
      esc_html__('Für Beratung vormerken', 'gastro-cool-products')
    );

    echo '<article class="gcp-card">';
    echo '  <div class="gcp-card__media">';
    echo '    <a href="' . esc_url($permalink) . '" class="gcp-card__media-link">' . $thumb_html . '</a>';
    echo      $corner_badge;
    echo '  </div>';
    echo '  <div class="gcp-card__body">';
    echo '    <h3 class="gcp-card__title"><a href="' . esc_url($permalink) . '">' . esc_html($title) . '</a></h3>';
    echo        $badges_html;
    echo '    <div class="gcp-card__excerpt">' . esc_html($excerpt) . '</div>';
    echo '    <div class="gcp-card__actions">' . $details . $consult . '</div>';
    echo '  </div>';
    echo '</article>';
  }
}

// Register skin with Elementor Pro Posts widget
add_action('elementor/widget/posts/skins_init', function($widget){
  if (! $widget || ! method_exists($widget, 'add_skin')) { return; }
  $widget->add_skin( new Products_Grid_Skin( $widget ) );
});
