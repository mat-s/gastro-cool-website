<?php

namespace GCP\Elementor\Widgets;

if (! defined('ABSPATH')) {
  exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Taxonomy_Labels_Widget extends Widget_Base
{
  public function get_name() {
    return 'gcp_taxonomy_labels';
  }

  public function get_title() {
    return __('Taxonomie-Labels', 'gastro-cool-products');
  }

  public function get_icon() {
    return 'eicon-price-list';
  }

  public function get_categories() {
    return ['gastro-cool'];
  }

  public function get_style_depends() {
    return ['gcp-plugin'];
  }

  private function get_taxonomy_options() {
    $taxonomies = get_taxonomies(['public' => true], 'objects');
    $options    = [];
    foreach ($taxonomies as $taxonomy) {
      $options[$taxonomy->name] = $taxonomy->label ?: $taxonomy->name;
    }
    return $options;
  }

  protected function register_controls() {

    // ── Content ──────────────────────────────────────────────────────────
    $this->start_controls_section(
      'section_content',
      [
        'label' => __('Einstellungen', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
      'taxonomy',
      [
        'label'   => __('Taxonomie', 'gastro-cool-products'),
        'type'    => Controls_Manager::SELECT,
        'default' => 'product_category',
        'options' => $this->get_taxonomy_options(),
      ]
    );

    $this->add_control(
      'variant',
      [
        'label'     => __('Stil', 'gastro-cool-products'),
        'type'      => Controls_Manager::SELECT,
        'default'   => 'default',
        'options'   => [
          'default' => __('Standard (hell)', 'gastro-cool-products'),
          'solid'   => __('Solid (dunkelblau)', 'gastro-cool-products'),
        ],
        'separator' => 'before',
      ]
    );

    $this->add_control(
      'show_links',
      [
        'label'        => __('Links zu Archivseiten', 'gastro-cool-products'),
        'type'         => Controls_Manager::SWITCHER,
        'label_on'     => __('Ja', 'gastro-cool-products'),
        'label_off'    => __('Nein', 'gastro-cool-products'),
        'return_value' => 'yes',
        'default'      => 'yes',
        'separator'    => 'before',
      ]
    );

    $this->end_controls_section();
  }

  protected function render() {
    $settings   = $this->get_settings_for_display();
    $taxonomy   = ! empty($settings['taxonomy']) ? $settings['taxonomy'] : 'product_category';
    $show_links = isset($settings['show_links']) && $settings['show_links'] === 'yes';
    $variant    = ! empty($settings['variant']) ? $settings['variant'] : 'default';

    // Begriffe des aktuellen Beitrags laden; im Editor Fallback auf alle Begriffe
    $post_id = get_the_ID();
    if ($post_id && has_term('', $taxonomy, $post_id)) {
      $terms = get_the_terms($post_id, $taxonomy);
    } else {
      $terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
        'number'     => 10,
      ]);
    }

    if (empty($terms) || is_wp_error($terms)) {
      if (isset(\Elementor\Plugin::$instance->editor) && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
        echo '<p style="color:#999;font-size:12px;margin:0">';
        echo esc_html__('Keine Begriffe gefunden für Taxonomie:', 'gastro-cool-products');
        echo ' <em>' . esc_html($taxonomy) . '</em>';
        echo '</p>';
      }
      return;
    }

    $wrapper_class = 'gc-tax-labels' . ($variant === 'solid' ? ' gc-tax-labels--solid' : '');
    echo '<div class="' . esc_attr($wrapper_class) . '">';

    foreach ($terms as $index => $term) {
      $color_index = ($index % 4) + 1;
      $classes     = 'gc-tax-label gc-tax-label--color-' . $color_index;

      if ($show_links) {
        $url = get_term_link($term);
        if (! is_wp_error($url)) {
          echo '<a class="' . esc_attr($classes) . '" href="' . esc_url($url) . '">' . esc_html($term->name) . '</a>';
          continue;
        }
      }

      echo '<span class="' . esc_attr($classes) . '">' . esc_html($term->name) . '</span>';
    }

    echo '</div>';
  }
}
