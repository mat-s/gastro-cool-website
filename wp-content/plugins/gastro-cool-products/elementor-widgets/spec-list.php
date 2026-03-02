<?php

namespace GCP\Elementor\Widgets;

if (! defined('ABSPATH')) {
  exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Spec_List_Widget extends Widget_Base
{
  public function get_name() {
    return 'gcp_spec_list';
  }

  public function get_title() {
    return __('Merkmal-Liste', 'gastro-cool-products');
  }

  public function get_icon() {
    return 'eicon-bullet-list';
  }

  public function get_categories() {
    return ['gastro-cool'];
  }

  public function get_style_depends() {
    return ['gcp-spec-list-widget'];
  }

  protected function register_controls() {

    // ── Content ──────────────────────────────────────────────────────────
    $this->start_controls_section(
      'section_items',
      [
        'label' => __('Merkmale', 'gastro-cool-products'),
      ]
    );

    $repeater = new Repeater();

    $repeater->add_control(
      'icon',
      [
        'label'   => __('Icon (optional)', 'gastro-cool-products'),
        'type'    => Controls_Manager::ICONS,
        'default' => ['value' => '', 'library' => ''],
      ]
    );

    $repeater->add_control(
      'label',
      [
        'label'       => __('Bezeichnung (optional)', 'gastro-cool-products'),
        'type'        => Controls_Manager::TEXT,
        'default'     => '',
        'placeholder' => __('z.B. Energieklasse', 'gastro-cool-products'),
        'dynamic'     => ['active' => true],
        'label_block' => true,
        'separator'   => 'before',
      ]
    );

    $repeater->add_control(
      'value',
      [
        'label'       => __('Wert', 'gastro-cool-products'),
        'type'        => Controls_Manager::TEXT,
        'default'     => '',
        'placeholder' => __('z.B. A+++', 'gastro-cool-products'),
        'dynamic'     => ['active' => true],
        'label_block' => true,
      ]
    );

    $repeater->add_control(
      'appendix',
      [
        'label'       => __('Appendix (optional)', 'gastro-cool-products'),
        'type'        => Controls_Manager::TEXT,
        'default'     => '',
        'placeholder' => __('z.B. kWh/24h', 'gastro-cool-products'),
        'dynamic'     => ['active' => true],
        'label_block' => true,
      ]
    );

    $this->add_control(
      'items',
      [
        'label'       => __('Merkmale', 'gastro-cool-products'),
        'type'        => Controls_Manager::REPEATER,
        'fields'      => $repeater->get_controls(),
        'default'     => [
          [
            'label' => __('Energieklasse', 'gastro-cool-products'),
            'value' => __('A+++', 'gastro-cool-products'),
          ],
          [
            'label' => __('Volumen', 'gastro-cool-products'),
            'value' => __('320 L', 'gastro-cool-products'),
          ],
          [
            'label' => __('Temperatur', 'gastro-cool-products'),
            'value' => __('0 – 10 °C', 'gastro-cool-products'),
          ],
        ],
        'title_field' => "{{{ label ? label + ': ' : '' }}}{{{ value }}}",
      ]
    );

    $this->end_controls_section();
  }

  protected function render() {
    $settings = $this->get_settings_for_display();
    $items    = isset($settings['items']) && is_array($settings['items']) ? $settings['items'] : [];

    if (empty($items)) {
      return;
    }

    echo '<div class="gc-spec-list">';

    foreach ($items as $item) {
      $label   = isset($item['label'])   ? trim($item['label'])   : '';
      $value   = isset($item['value'])   ? trim($item['value'])   : '';
      $appendix = isset($item['appendix']) ? trim($item['appendix']) : '';
      $icon    = isset($item['icon'])    ? $item['icon']          : [];

      if ($value === '' && $label === '') {
        continue;
      }

      echo '<div class="gc-spec-item elementor-repeater-item-' . esc_attr($item['_id']) . '">';

      if (! empty($icon['value'])) {
        echo '<span class="gc-spec-item__icon">';
        \Elementor\Icons_Manager::render_icon($icon, ['aria-hidden' => 'true']);
        echo '</span>';
      }

      echo '<div class="gc-spec-item__content">';

      if ($value !== '' || $appendix !== '') {
        echo '<span class="gc-spec-item__value">';
        echo esc_html($value);
        if ($appendix !== '') {
          echo '<span class="gc-spec-item__appendix">' . esc_html($appendix) . '</span>';
        }
        echo '</span>';
      }

      if ($label !== '') {
        echo '<span class="gc-spec-item__label">' . esc_html($label) . '</span>';
      }

      echo '</div>';

      echo '</div>';
    }

    echo '</div>';
  }
}
