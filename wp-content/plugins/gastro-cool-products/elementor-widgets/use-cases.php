<?php

namespace GCP\Elementor\Widgets;

if (! defined('ABSPATH')) {
  exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Use_Cases_Widget extends Widget_Base
{
  public function get_name() {
    return 'gcp_use_cases';
  }

  public function get_title() {
    return __('Einsatzbereiche (ACF)', 'gastro-cool-products');
  }

  public function get_icon() {
    return 'eicon-apps';
  }

  public function get_categories() {
    return ['gastro-cool'];
  }

  public function get_style_depends() {
    return ['gcp-plugin'];
  }

  protected function register_controls() {

    // ── Heading ───────────────────────────────────────────────────────────
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
        'default'     => __('Einsatzbereiche', 'gastro-cool-products'),
        'dynamic'     => ['active' => true],
        'label_block' => true,
      ]
    );

    $this->add_control(
      'heading_tag',
      [
        'label'   => __('Überschriften-Tag', 'gastro-cool-products'),
        'type'    => Controls_Manager::SELECT,
        'default' => 'h2',
        'options' => [
          'h2'  => 'H2',
          'h3'  => 'H3',
          'h4'  => 'H4',
          'h5'  => 'H5',
          'h6'  => 'H6',
          'div' => 'div',
        ],
      ]
    );

    $this->add_control(
      'heading_icon',
      [
        'label'   => __('Icon zur Überschrift', 'gastro-cool-products'),
        'type'    => Controls_Manager::ICONS,
        'default' => ['value' => 'fas fa-grip', 'library' => 'fa-solid'],
      ]
    );

    $this->end_controls_section();

    // ── Cards ─────────────────────────────────────────────────────────────
    $this->start_controls_section(
      'section_cards',
      [
        'label' => __('Karten', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
      'empty_text',
      [
        'label'   => __('Text bei fehlenden Einträgen', 'gastro-cool-products'),
        'type'    => Controls_Manager::TEXT,
        'default' => __('Keine Einsatzbereiche verfügbar.', 'gastro-cool-products'),
      ]
    );

    $this->end_controls_section();
  }

  protected function render() {
    $settings    = $this->get_settings_for_display();
    $heading      = trim($settings['heading']      ?? '');
    $heading_tag  = $settings['heading_tag']       ?? 'h2';
    $heading_icon = $settings['heading_icon']      ?? [];
    $empty_text   = trim($settings['empty_text']   ?? __('Keine Einsatzbereiche verfügbar.', 'gastro-cool-products'));

    $allowed_tags = ['h2', 'h3', 'h4', 'h5', 'h6', 'div'];
    if (! in_array($heading_tag, $allowed_tags, true)) {
      $heading_tag = 'h2';
    }

    // Fetch ACF use_cases repeater
    $items = [];
    if (function_exists('get_field')) {
      $raw = get_field('use_cases');
      if (is_array($raw)) {
        $items = $raw;
      }
    }
    ?>
    <div class="gc-use-cases">

      <?php if ($heading !== '') : ?>
        <<?= esc_attr($heading_tag) ?> class="gc-use-cases__heading">
          <?php if (! empty($heading_icon['value'])) : ?>
            <span class="gc-use-cases__heading-icon" aria-hidden="true">
              <?php \Elementor\Icons_Manager::render_icon($heading_icon, ['aria-hidden' => 'true']); ?>
            </span>
          <?php endif; ?>
          <?= esc_html($heading) ?>
        </<?= esc_attr($heading_tag) ?>>
      <?php endif; ?>

      <?php if (! empty($items)) : ?>
        <ul class="gc-use-cases__grid">
          <?php foreach ($items as $row) : ?>
            <?php
            $text = trim(is_array($row) ? ($row['text'] ?? '') : (string) $row);
            if ($text === '') {
              continue;
            }
            ?>
            <li class="gc-use-cases__card">
              <span class="gc-use-cases__card-icon" aria-hidden="true">
                <?php \Elementor\Icons_Manager::render_icon(['value' => 'fas fa-circle-dot', 'library' => 'fa-solid'], ['aria-hidden' => 'true']); ?>
              </span>
              <p class="gc-use-cases__card-text"><?= esc_html($text) ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else : ?>
        <p class="gc-use-cases__empty"><?= esc_html($empty_text) ?></p>
      <?php endif; ?>

    </div>
    <?php
  }
}
