<?php

namespace GCP\Elementor\Widgets;

if (! defined('ABSPATH')) {
  exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Icon_List_Widget extends Widget_Base
{
  public function get_name() {
    return 'gcp_icon_list';
  }

  public function get_title() {
    return __('Icon-Liste (ACF)', 'gastro-cool-products');
  }

  public function get_icon() {
    return 'eicon-icon-box';
  }

  public function get_categories() {
    return ['gastro-cool'];
  }

  public function get_style_depends() {
    return ['gcp-icon-list-widget'];
  }

  /**
   * Collect all repeater/flexible_content ACF fields for the product post type.
   *
   * @return array<string, string>  field_name => display label
   */
  private function get_acf_array_field_options(): array {
    $options = ['' => __('— Feld wählen —', 'gastro-cool-products')];

    if (! function_exists('acf_get_field_groups') || ! function_exists('acf_get_fields')) {
      return $options;
    }

    $groups = acf_get_field_groups(['post_type' => 'product']);
    foreach ($groups as $group) {
      $fields = acf_get_fields($group['key']);
      if (! $fields) {
        continue;
      }
      foreach ($fields as $field) {
        if (in_array($field['type'], ['repeater', 'flexible_content'], true)) {
          $options[$field['name']] = $field['label'] . ' (' . $field['name'] . ')';
        }
      }
    }

    return $options;
  }

  protected function register_controls() {

    // ── Content ──────────────────────────────────────────────────────────
    $this->start_controls_section(
      'section_items',
      [
        'label' => __('Abschnitte', 'gastro-cool-products'),
      ]
    );

    $repeater = new Repeater();

    $repeater->add_control(
      'heading',
      [
        'label'       => __('Überschrift', 'gastro-cool-products'),
        'type'        => Controls_Manager::TEXT,
        'default'     => '',
        'placeholder' => __('z.B. Dosenkapazität', 'gastro-cool-products'),
        'dynamic'     => ['active' => true],
        'label_block' => true,
      ]
    );

    $repeater->add_control(
      'heading_tag',
      [
        'label'   => __('Überschriften-Tag', 'gastro-cool-products'),
        'type'    => Controls_Manager::SELECT,
        'default' => 'h3',
        'options' => [
          'h2'   => 'H2',
          'h3'   => 'H3',
          'h4'   => 'H4',
          'h5'   => 'H5',
          'h6'   => 'H6',
          'p'    => 'p',
          'span' => 'span',
          'div'  => 'div',
        ],
      ]
    );

    $repeater->add_control(
      'icon',
      [
        'label'     => __('Icon (optional)', 'gastro-cool-products'),
        'type'      => Controls_Manager::ICONS,
        'default'   => ['value' => '', 'library' => ''],
        'separator' => 'before',
      ]
    );

    $repeater->add_control(
      'acf_field',
      [
        'label'     => __('ACF Array-Feld', 'gastro-cool-products'),
        'type'      => Controls_Manager::SELECT,
        'default'   => '',
        'options'   => $this->get_acf_array_field_options(),
        'separator' => 'before',
      ]
    );

    $repeater->add_control(
      'sub_field_key',
      [
        'label'       => __('Sub-Feld Schlüssel', 'gastro-cool-products'),
        'type'        => Controls_Manager::TEXT,
        'default'     => 'text',
        'placeholder' => __('z.B. text, url, title', 'gastro-cool-products'),
        'description' => __('Schlüssel des Sub-Feldes, dessen Wert angezeigt wird.', 'gastro-cool-products'),
        'label_block' => true,
      ]
    );

    $this->add_control(
      'sections',
      [
        'label'       => __('Abschnitte', 'gastro-cool-products'),
        'type'        => Controls_Manager::REPEATER,
        'fields'      => $repeater->get_controls(),
        'default'     => [
          [
            'heading'       => __('Dosenkapazität', 'gastro-cool-products'),
            'heading_tag'   => 'h3',
            'acf_field'     => 'capacity_cans',
            'sub_field_key' => 'text',
          ],
        ],
        'title_field' => '{{{ heading }}}',
      ]
    );

    $this->end_controls_section();
  }

  protected function render() {
    $settings = $this->get_settings_for_display();
    $sections = isset($settings['sections']) && is_array($settings['sections']) ? $settings['sections'] : [];

    if (empty($sections)) {
      return;
    }

    $allowed_tags = ['h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'div'];

    echo '<div class="gc-icon-list">';

    foreach ($sections as $section) {
      $heading       = isset($section['heading'])       ? trim($section['heading'])           : '';
      $heading_tag   = isset($section['heading_tag'])   ? $section['heading_tag']             : 'h3';
      $icon          = isset($section['icon'])          ? $section['icon']                    : [];
      $acf_field     = isset($section['acf_field'])     ? sanitize_key($section['acf_field']) : '';
      $sub_field_key = isset($section['sub_field_key']) ? trim($section['sub_field_key'])     : 'text';

      if (! in_array($heading_tag, $allowed_tags, true)) {
        $heading_tag = 'h3';
      }

      // Fetch ACF repeater data
      $items = [];
      if ($acf_field && function_exists('get_field')) {
        $raw = get_field($acf_field);
        if (is_array($raw)) {
          $items = $raw;
        }
      }

      if ($heading === '' && empty($items)) {
        continue;
      }

      echo '<div class="gc-icon-list__section elementor-repeater-item-' . esc_attr($section['_id']) . '">';

      // Heading (plain text, no icon)
      if ($heading !== '') {
        echo '<' . esc_attr($heading_tag) . ' class="gc-icon-list__heading">'
          . esc_html($heading)
          . '</' . esc_attr($heading_tag) . '>';
      }

      // List: icon appears on every item
      if (! empty($items)) {
        $has_icon = ! empty($icon['value']);

        echo '<ul class="gc-icon-list__items">';

        foreach ($items as $row) {
          if (! is_array($row)) {
            $display = (string) $row;
          } elseif ($sub_field_key !== '' && isset($row[$sub_field_key])) {
            $display = (string) $row[$sub_field_key];
          } else {
            $display = '';
            foreach ($row as $val) {
              if ($val !== '' && $val !== null) {
                $display = (string) $val;
                break;
              }
            }
          }

          if ($display === '') {
            continue;
          }

          echo '<li class="gc-icon-list__item">';

          if ($has_icon) {
            echo '<span class="gc-icon-list__item-icon" aria-hidden="true">';
            \Elementor\Icons_Manager::render_icon($icon, ['aria-hidden' => 'true']);
            echo '</span>';
          }

          echo '<span class="gc-icon-list__item-text">' . esc_html($display) . '</span>';
          echo '</li>';
        }

        echo '</ul>';
      }

      echo '</div>';
    }

    echo '</div>';
  }
}
