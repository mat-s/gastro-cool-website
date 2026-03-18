<?php

namespace GCP\Elementor\Widgets;

if (! defined('ABSPATH')) {
  exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Utils;

class Capacity_Grid_Widget extends Widget_Base
{
  public function get_name() {
    return 'gcp_capacity_grid';
  }

  public function get_title() {
    return __('Kapazitäts-Grid', 'gastro-cool-products');
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

  // ── Helper: render controls for one item ──────────────────────────────
  private function add_item_controls( string $prefix, string $default_label ): void {
    $this->add_control(
      $prefix . '_image',
      [
        'label'   => __('Bild', 'gastro-cool-products'),
        'type'    => Controls_Manager::MEDIA,
        'default' => ['url' => Utils::get_placeholder_image_src()],
      ]
    );

    $this->add_control(
      $prefix . '_label',
      [
        'label'       => __('Bezeichnung', 'gastro-cool-products'),
        'type'        => Controls_Manager::TEXT,
        'default'     => $default_label,
        'label_block' => true,
      ]
    );

    $this->add_control(
      $prefix . '_quantity',
      [
        'label'       => __('Stückzahl', 'gastro-cool-products'),
        'type'        => Controls_Manager::TEXT,
        'default'     => '',
        'placeholder' => __('z.B. 96 Stk.', 'gastro-cool-products'),
        'dynamic'     => ['active' => true],
        'label_block' => true,
      ]
    );
  }

  protected function register_controls() {

    // ── Heading ──────────────────────────────────────────────────────────
    $this->start_controls_section(
      'section_heading',
      [
        'label' => __('Überschrift', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
      'heading',
      [
        'label'       => __('Überschrift', 'gastro-cool-products'),
        'type'        => Controls_Manager::TEXT,
        'default'     => __('Kapazität', 'gastro-cool-products'),
        'dynamic'     => ['active' => true],
        'label_block' => true,
      ]
    );

    $this->add_control(
      'heading_tag',
      [
        'label'   => __('Überschriften-Tag', 'gastro-cool-products'),
        'type'    => Controls_Manager::SELECT,
        'default' => 'h3',
        'options' => [
          'h2'  => 'H2',
          'h3'  => 'H3',
          'h4'  => 'H4',
          'h5'  => 'H5',
          'h6'  => 'H6',
          'p'   => 'p',
          'div' => 'div',
        ],
      ]
    );

    $this->end_controls_section();

    // ── Dosen ─────────────────────────────────────────────────────────────
    $this->start_controls_section(
      'section_cans',
      [
        'label' => __('Dosen', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
      'cans_heading',
      [
        'label' => __('— 0,25l Dose (cans_025) —', 'gastro-cool-products'),
        'type'  => Controls_Manager::HEADING,
      ]
    );
    $this->add_item_controls('cans_025', __('250ml Dosen', 'gastro-cool-products'));

    $this->add_control(
      'cans_033_heading',
      [
        'label'     => __('— 0,33l Dose (cans_033) —', 'gastro-cool-products'),
        'type'      => Controls_Manager::HEADING,
        'separator' => 'before',
      ]
    );
    $this->add_item_controls('cans_033', __('330ml Dosen', 'gastro-cool-products'));

    $this->add_control(
      'cans_033_slim_heading',
      [
        'label'     => __('— 0,33l Slim-Dose (cans_033_slim) —', 'gastro-cool-products'),
        'type'      => Controls_Manager::HEADING,
        'separator' => 'before',
      ]
    );
    $this->add_item_controls('cans_033_slim', __('330ml Slim Dosen', 'gastro-cool-products'));

    $this->add_control(
      'cans_050_heading',
      [
        'label'     => __('— 0,50l Dose (cans_050) —', 'gastro-cool-products'),
        'type'      => Controls_Manager::HEADING,
        'separator' => 'before',
      ]
    );
    $this->add_item_controls('cans_050', __('500ml Dosen', 'gastro-cool-products'));

    $this->end_controls_section();

    // ── Flaschen ──────────────────────────────────────────────────────────
    $this->start_controls_section(
      'section_bottles',
      [
        'label' => __('Flaschen', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
      'bottles_033_heading',
      [
        'label' => __('— 0,33l Flasche (bottles_033) —', 'gastro-cool-products'),
        'type'  => Controls_Manager::HEADING,
      ]
    );
    $this->add_item_controls('bottles_033', __('330ml Flaschen', 'gastro-cool-products'));

    $this->add_control(
      'bottles_050_heading',
      [
        'label'     => __('— 0,50l Flasche (bottles_050) —', 'gastro-cool-products'),
        'type'      => Controls_Manager::HEADING,
        'separator' => 'before',
      ]
    );
    $this->add_item_controls('bottles_050', __('500ml Flaschen', 'gastro-cool-products'));

    $this->add_control(
      'bottles_070_heading',
      [
        'label'     => __('— 0,70l Flasche (bottles_070) —', 'gastro-cool-products'),
        'type'      => Controls_Manager::HEADING,
        'separator' => 'before',
      ]
    );
    $this->add_item_controls('bottles_070', __('700ml Flaschen', 'gastro-cool-products'));

    $this->end_controls_section();
  }

  // ── Helper: collect item data from settings ───────────────────────────
  private function get_item( array $settings, string $prefix ): array {
    $quantity = trim($settings[ $prefix . '_quantity' ] ?? '');

    return [
      'image'    => $settings[ $prefix . '_image' ] ?? [],
      'label'    => trim($settings[ $prefix . '_label' ] ?? ''),
      'quantity' => $quantity !== '' ? $quantity : __('k.A.', 'gastro-cool-products'),
      'empty'    => $quantity === '',
    ];
  }

  // ── Helper: render one row of items ──────────────────────────────────
  private function render_row( array $items ): void {
    if (empty($items)) {
      return;
    }
    ?>
    <div class="gc-capacity-grid__row">
      <?php foreach ($items as $item) : ?>
        <div class="gc-capacity-grid__item">

          <?php if (! empty($item['image']['url'])) : ?>
            <div class="gc-capacity-grid__image">
              <img src="<?= esc_url($item['image']['url']) ?>" alt="<?= esc_attr($item['label']) ?>" loading="lazy">
            </div>
          <?php endif; ?>

          <div class="gc-capacity-grid__meta">

            <?php if ($item['label'] !== '') : ?>
              <span class="gc-capacity-grid__label"><?= esc_html($item['label']) ?></span>
            <?php endif; ?>

            <?php
            $qty_class = ! empty($item['empty']) ? 'gc-capacity-grid__quantity gc-capacity-grid__quantity--empty' : 'gc-capacity-grid__quantity';
            ?>
            <span class="<?= esc_attr($qty_class) ?>"><?= esc_html($item['quantity']) ?></span>

          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php
  }

  protected function render() {
    $settings = $this->get_settings_for_display();

    $heading     = trim($settings['heading']     ?? '');
    $heading_tag = $settings['heading_tag']       ?? 'h3';
    $allowed_tags = ['h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div'];
    if (! in_array($heading_tag, $allowed_tags, true)) {
      $heading_tag = 'h3';
    }

    $cans = [
      $this->get_item($settings, 'cans_025'),
      $this->get_item($settings, 'cans_033'),
      $this->get_item($settings, 'cans_033_slim'),
      $this->get_item($settings, 'cans_050'),
    ];

    $bottles = [
      $this->get_item($settings, 'bottles_033'),
      $this->get_item($settings, 'bottles_050'),
      $this->get_item($settings, 'bottles_070'),
    ];
    ?>
    <div class="gc-capacity-grid">

      <?php if ($heading !== '') : ?>
        <<?= esc_attr($heading_tag) ?> class="gc-capacity-grid__heading"><?= esc_html($heading) ?></<?= esc_attr($heading_tag) ?>>
      <?php endif; ?>

      <?php $this->render_row($cans); ?>

      <hr class="gc-capacity-grid__separator" aria-hidden="true">

      <?php $this->render_row($bottles); ?>

    </div>
    <?php
  }
}
