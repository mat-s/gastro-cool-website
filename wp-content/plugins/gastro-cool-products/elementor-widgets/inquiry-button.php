<?php

namespace GCP\Elementor\Widgets;

if (! defined('ABSPATH')) {
  exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;

class Inquiry_Button_Widget extends Widget_Base
{
  public function get_name() {
    return 'gcp_inquiry_button';
  }

  public function get_title() {
    return __('Inquiry Button', 'gastro-cool-products');
  }

  public function get_icon() {
    return 'eicon-button';
  }

  public function get_categories() {
    return ['gastro-cool'];
  }

  protected function register_controls() {
    $this->start_controls_section(
      'section_content',
      [
        'label' => __('Button', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
				'button_icon',
				[
					'label' => __('Icon', 'gastro-cool-products'),
					'type' => Controls_Manager::ICONS,
					'fa4compatibility' => 'icon',
					'skin' => 'inline',
					'label_block' => false,
        'default' => [
          'value' => 'eicon-editor-list-ul',
          'library' => 'eicons',
        ],
				]
			);

    $this->add_responsive_control(
      'icon_size',
      [
        'label' => __('Icon Size', 'gastro-cool-products'),
        'type' => Controls_Manager::SLIDER,
        'size_units' => ['px', 'em', 'rem'],
        'range' => [
          'px' => ['min' => 8, 'max' => 48],
          'em' => ['min' => 0.5, 'max' => 3],
          'rem' => ['min' => 0.5, 'max' => 3],
        ],
        'default' => [
          'unit' => 'px',
          'size' => 16,
        ],
        'selectors' => [
          '{{WRAPPER}} .gc-inquiry-button__icon, {{WRAPPER}} .gc-inquiry-button__icon svg, {{WRAPPER}} .gc-inquiry-button__icon i' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; font-size: {{SIZE}}{{UNIT}};',
        ],
      ]
    );

    $this->add_control(
      'button_text',
      [
        'label' => __('Button text', 'gastro-cool-products'),
        'type' => Controls_Manager::TEXT,
        'default' => __('Anfrage', 'gastro-cool-products'),
        'placeholder' => __('Anfrage', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
      'show_counter',
      [
        'label' => __('Show counter', 'gastro-cool-products'),
        'type' => Controls_Manager::SWITCHER,
        'label_on' => __('Yes', 'gastro-cool-products'),
        'label_off' => __('No', 'gastro-cool-products'),
        'return_value' => 'yes',
        'default' => 'yes',
      ]
    );

    $this->end_controls_section();
  }

  protected function render() {
    $settings = $this->get_settings_for_display();

    $text = isset($settings['button_text']) ? $settings['button_text'] : '';
    $show_counter = isset($settings['show_counter']) && $settings['show_counter'] === 'yes';

    echo '<button type="button" class="gc-inquiry-button inquiry-toggle">';

    if (! empty($settings['button_icon']['value'])) {
      echo '<span class="gc-inquiry-button__icon-wrap">';
      Icons_Manager::render_icon(
        $settings['button_icon'],
        [
          'aria-hidden' => 'true',
          'class'       => 'gc-inquiry-button__icon',
        ],
        false
      );
      echo '</span>';
    }

    if ($text !== '') {
      echo '<span class="gc-inquiry-button__label">' . esc_html($text) . '</span>';
    }

    if ($show_counter) {
      echo '<span class="gc-inquiry-button__badge"><span class="gc-inquiry-badge" data-gc-inquiry-count>0</span></span>';
    }

    echo '</button>';
  }
}
