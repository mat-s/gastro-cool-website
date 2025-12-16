<?php

namespace GCP\Elementor\Widgets;

if (! defined('ABSPATH')) {
  exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Inquiry_List_Widget extends Widget_Base
{
  public function get_name() {
    return 'gcp_inquiry_list';
  }

  public function get_title() {
    return __('Inquiry List', 'gastro-cool-products');
  }

  public function get_icon() {
    return 'eicon-product-info';
  }

  public function get_categories() {
    return ['gastro-cool'];
  }

  public function get_script_depends() {
    return ['gcp-inquiry-list-widget'];
  }

  public function get_style_depends() {
    return ['gcp-inquiry-list-widget'];
  }

  protected function register_controls() {
    $this->start_controls_section(
      'section_content',
      [
        'label' => __('Inhaltstexte', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
      'heading_text',
      [
        'label' => __('Titel', 'gastro-cool-products'),
        'type' => Controls_Manager::TEXT,
        'default' => __('Ihre Produktauswahl', 'gastro-cool-products'),
        'placeholder' => __('Ihre Produktauswahl', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
      'empty_text',
      [
        'label' => __('Leertext', 'gastro-cool-products'),
        'type' => Controls_Manager::TEXTAREA,
        'rows' => 3,
        'default' => __('Es wurden noch keine Produkte zur Anfrage hinzugefügt.', 'gastro-cool-products'),
        'placeholder' => __('Es wurden noch keine Produkte zur Anfrage hinzugefügt.', 'gastro-cool-products'),
      ]
    );

    $this->end_controls_section();
  }

  protected function render() {
    $settings = $this->get_settings_for_display();

    $heading = ! empty($settings['heading_text'])
      ? $settings['heading_text']
      : __('Ihre Produktauswahl', 'gastro-cool-products');

    $empty_text = ! empty($settings['empty_text'])
      ? $settings['empty_text']
      : __('Es wurden noch keine Produkte zur Anfrage hinzugefügt.', 'gastro-cool-products');

    $quantity_label = __('Menge', 'gastro-cool-products');
    $remove_label   = __('Entfernen', 'gastro-cool-products');
    ?>
    <div
      class="gc-inquiry-list gc-inquiry-list--empty"
      data-gc-inquiry-list
      data-heading-text="<?php echo esc_attr($heading); ?>"
      data-empty-text="<?php echo esc_attr($empty_text); ?>"
      data-quantity-label="<?php echo esc_attr($quantity_label); ?>"
      data-remove-label="<?php echo esc_attr($remove_label); ?>"
    >
      <div class="gc-inquiry-list__header">
        <h3 class="gc-inquiry-list__title" data-gc-inquiry-heading><?php echo esc_html($heading); ?></h3>
      </div>
      <div class="gc-inquiry-list__body">
        <div class="gc-inquiry-list__empty" data-gc-inquiry-empty><?php echo esc_html($empty_text); ?></div>
        <ul class="gc-inquiry-list__items" data-gc-inquiry-items></ul>
      </div>
    </div>
    <?php
  }
}
