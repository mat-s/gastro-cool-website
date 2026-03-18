<?php

namespace GCP\Elementor\Widgets;

if (! defined('ABSPATH')) {
  exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Label_List_Widget extends Widget_Base
{
  public function get_name() {
    return 'gcp_label_list';
  }

  public function get_title() {
    return __('Labels', 'gastro-cool-products');
  }

  public function get_icon() {
    return 'eicon-tags';
  }

  public function get_categories() {
    return ['gastro-cool'];
  }

  public function get_style_depends() {
    return ['gcp-plugin'];
  }

  protected function register_controls() {

    // ── Content ──────────────────────────────────────────────────────────
    $this->start_controls_section(
      'section_labels',
      [
        'label' => __('Labels', 'gastro-cool-products'),
      ]
    );

    $repeater = new Repeater();

    $repeater->add_control(
      'label',
      [
        'label'       => __('Beschriftung (optional)', 'gastro-cool-products'),
        'type'        => Controls_Manager::TEXT,
        'default'     => '',
        'placeholder' => __('z.B. Kategorie', 'gastro-cool-products'),
        'dynamic'     => ['active' => true],
        'label_block' => true,
      ]
    );

    $repeater->add_control(
      'value',
      [
        'label'       => __('Wert', 'gastro-cool-products'),
        'type'        => Controls_Manager::TEXT,
        'default'     => __('Label', 'gastro-cool-products'),
        'placeholder' => __('z.B. Displaykühlschränke', 'gastro-cool-products'),
        'dynamic'     => ['active' => true],
        'label_block' => true,
      ]
    );

    $repeater->add_control(
      'link',
      [
        'label'   => __('Link (optional)', 'gastro-cool-products'),
        'type'    => Controls_Manager::URL,
        'dynamic' => ['active' => true],
        'options' => ['url', 'is_external', 'nofollow'],
      ]
    );

    $repeater->add_control(
      'variant',
      [
        'label'   => __('Stil', 'gastro-cool-products'),
        'type'    => Controls_Manager::SELECT,
        'default' => 'default',
        'options' => [
          'default'   => __('Standard (grau)', 'gastro-cool-products'),
          'highlight' => __('Hervorgehoben (blau)', 'gastro-cool-products'),
        ],
      ]
    );

    $this->add_control(
      'labels',
      [
        'label'       => __('Labels', 'gastro-cool-products'),
        'type'        => Controls_Manager::REPEATER,
        'fields'      => $repeater->get_controls(),
        'default'     => [
          [
            'label'   => __('Kategorie', 'gastro-cool-products'),
            'value'   => __('Displaykühlschränke', 'gastro-cool-products'),
            'variant' => 'default',
          ],
          [
            'label'   => __('Serie', 'gastro-cool-products'),
            'value'   => __('ECO STAR+', 'gastro-cool-products'),
            'variant' => 'default',
          ],
        ],
        'title_field' => "{{{ label ? label + ': ' : '' }}}{{{ value }}}",
      ]
    );

    $this->end_controls_section();
  }

  protected function render() {
    $settings = $this->get_settings_for_display();
    $items    = isset($settings['labels']) && is_array($settings['labels']) ? $settings['labels'] : [];

    if (empty($items)) {
      return;
    }

    echo '<ul class="gc-labels">';

    foreach ($items as $item) {
      $label   = isset($item['label']) ? $item['label'] : '';
      $value   = isset($item['value']) ? $item['value'] : '';
      $variant = isset($item['variant']) ? $item['variant'] : 'default';
      $link    = isset($item['link']['url']) ? $item['link']['url'] : '';

      if ($value === '' && $label === '') {
        continue;
      }

      $classes = 'gc-label elementor-repeater-item-' . esc_attr($item['_id']);
      if ($variant === 'highlight') {
        $classes .= ' gc-label--highlight';
      }

      echo '<li class="gc-labels__item">';

      if ($link) {
        $target   = ! empty($item['link']['is_external']) ? ' target="_blank"' : '';
        $nofollow = ! empty($item['link']['nofollow']) ? ' rel="nofollow"' : '';
        echo '<a class="' . esc_attr($classes) . '" href="' . esc_url($link) . '"' . $target . $nofollow . '>';
      } else {
        echo '<span class="' . esc_attr($classes) . '">';
      }

      if ($label !== '') {
        echo '<span class="gc-label__prefix">' . esc_html($label) . ':&nbsp;</span>';
      }

      echo '<span class="gc-label__value">' . esc_html($value) . '</span>';

      echo $link ? '</a>' : '</span>';

      echo '</li>';
    }

    echo '</ul>';
  }
}
