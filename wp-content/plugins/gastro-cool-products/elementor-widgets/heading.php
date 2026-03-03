<?php

namespace GCP\Elementor\Widgets;

if (! defined('ABSPATH')) {
  exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Heading_Widget extends Widget_Base
{
  public function get_name() {
    return 'gcp_heading';
  }

  public function get_title() {
    return __('Überschrift', 'gastro-cool-products');
  }

  public function get_icon() {
    return 'eicon-heading';
  }

  public function get_categories() {
    return ['gastro-cool'];
  }

  public function get_style_depends() {
    return ['gcp-heading-widget'];
  }

  protected function register_controls() {

    $this->start_controls_section(
      'section_heading',
      [
        'label' => __('Überschrift', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
      'tag',
      [
        'label'   => __('HTML-Tag', 'gastro-cool-products'),
        'type'    => Controls_Manager::SELECT,
        'default' => 'h1',
        'options' => [
          'h1' => 'H1',
          'h2' => 'H2',
          'h3' => 'H3',
          'h4' => 'H4',
          'h5' => 'H5',
          'h6' => 'H6',
        ],
      ]
    );

    $this->add_control(
      'prefix',
      [
        'label'       => __('Präfix', 'gastro-cool-products'),
        'description' => __('Statischer Text vor dem dynamischen Wert', 'gastro-cool-products'),
        'type'        => Controls_Manager::TEXT,
        'default'     => '',
        'label_block' => true,
        'separator'   => 'before',
      ]
    );

    $this->add_control(
      'value',
      [
        'label'       => __('Wert', 'gastro-cool-products'),
        'description' => __('Dynamisch befüllbar – z.B. per ACF-Tag', 'gastro-cool-products'),
        'type'        => Controls_Manager::TEXT,
        'default'     => '',
        'placeholder' => __('z.B. Modellname', 'gastro-cool-products'),
        'dynamic'     => ['active' => true],
        'label_block' => true,
      ]
    );

    $this->add_control(
      'suffix',
      [
        'label'       => __('Suffix', 'gastro-cool-products'),
        'description' => __('Statischer Text nach dem dynamischen Wert', 'gastro-cool-products'),
        'type'        => Controls_Manager::TEXT,
        'default'     => '',
        'label_block' => true,
      ]
    );

    $this->end_controls_section();
  }

  protected function render() {
    $settings = $this->get_settings_for_display();

    $prefix = trim($settings['prefix'] ?? '');
    $value  = trim($settings['value']  ?? '');
    $suffix = trim($settings['suffix'] ?? '');
    $tag    = $settings['tag'] ?? 'h1';

    $allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
    if (! in_array($tag, $allowed_tags, true)) {
      $tag = 'h1';
    }

    if ($prefix === '' && $value === '' && $suffix === '') {
      return;
    }

    echo '<' . $tag . ' class="gc-heading">';

    if ($prefix !== '') {
      echo '<span class="gc-heading__prefix">' . esc_html($prefix) . '</span> ';
    }

    if ($value !== '') {
      echo '<span class="gc-heading__value">' . esc_html($value) . '</span>';
    }

    if ($suffix !== '') {
      echo ' <span class="gc-heading__suffix">' . esc_html($suffix) . '</span>';
    }

    echo '</' . $tag . '>';
  }
}
