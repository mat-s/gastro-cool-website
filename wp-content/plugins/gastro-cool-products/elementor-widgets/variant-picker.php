<?php

namespace GCP\Elementor\Widgets;

if (! defined('ABSPATH')) {
  exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Variant_Picker_Widget extends Widget_Base
{
  public function get_name() {
    return 'gcp_variant_picker';
  }

  public function get_title() {
    return __('Varianten', 'gastro-cool-products');
  }

  public function get_icon() {
    return 'eicon-form-horizontal';
  }

  public function get_categories() {
    return ['gastro-cool'];
  }

  public function get_style_depends() {
    return ['gcp-plugin'];
  }

  protected function register_controls() {

    // ── Überschrift ───────────────────────────────────────────────────────
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
        'default'     => __('Varianten', 'gastro-cool-products'),
        'dynamic'     => ['active' => true],
        'label_block' => true,
      ]
    );

    $this->add_control(
      'heading_tag',
      [
        'label'   => __('HTML-Tag', 'gastro-cool-products'),
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
        'label'   => __('Icon', 'gastro-cool-products'),
        'type'    => Controls_Manager::ICONS,
        'default' => ['value' => '', 'library' => ''],
      ]
    );

    $this->end_controls_section();

    // ── Darstellung ───────────────────────────────────────────────────────
    $this->start_controls_section(
      'section_display',
      [
        'label' => __('Darstellung', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
      'show_value',
      [
        'label'        => __('Varianten-Label anzeigen', 'gastro-cool-products'),
        'type'         => Controls_Manager::SWITCHER,
        'label_on'     => __('Ja', 'gastro-cool-products'),
        'label_off'    => __('Nein', 'gastro-cool-products'),
        'return_value' => 'yes',
        'default'      => 'yes',
      ]
    );

    $this->add_control(
      'show_artno',
      [
        'label'        => __('Artikelnummer anzeigen', 'gastro-cool-products'),
        'type'         => Controls_Manager::SWITCHER,
        'label_on'     => __('Ja', 'gastro-cool-products'),
        'label_off'    => __('Nein', 'gastro-cool-products'),
        'return_value' => 'yes',
        'default'      => 'no',
      ]
    );

    $this->add_control(
      'group_label_field',
      [
        'label'       => __('Gruppen-Label', 'gastro-cool-products'),
        'type'        => Controls_Manager::SELECT,
        'default'     => 'value',
        'options'     => [
          'value'      => __('Varianten-Wert (value)', 'gastro-cool-products'),
          'base_model' => __('Basis-Modell (base_model)', 'gastro-cool-products'),
          'none'       => __('Kein Gruppen-Titel', 'gastro-cool-products'),
        ],
        'description' => __('Welches Feld wird als Gruppenüberschrift genutzt?', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
      'empty_text',
      [
        'label'   => __('Text bei fehlenden Varianten', 'gastro-cool-products'),
        'type'    => Controls_Manager::TEXT,
        'default' => __('Keine Varianten verfügbar.', 'gastro-cool-products'),
      ]
    );

    $this->end_controls_section();
  }

  // ── Helper: detect active artno from current product ─────────────────
  private function get_current_artno(): string {
    if (! function_exists('get_field')) {
      return '';
    }
    // external_id is the primary product artno
    $ext = get_field('external_id');
    if ($ext) {
      return trim((string) $ext);
    }
    return '';
  }

  // ── Helper: flatten all options from variants repeater ────────────────
  private function get_options(): array {
    if (! function_exists('get_field')) {
      return [];
    }

    $variants = get_field('variants');
    if (! is_array($variants) || empty($variants)) {
      return [];
    }

    $groups = [];

    foreach ($variants as $group) {
      $base_model = trim($group['base_model']  ?? '');
      $description = trim($group['description'] ?? '');
      $options_raw = is_array($group['options'] ?? null) ? $group['options'] : [];

      $options = [];
      foreach ($options_raw as $opt) {
        $artno      = trim($opt['artno']      ?? '');
        $value      = trim($opt['value']      ?? '');
        $type       = trim($opt['type']       ?? '');
        $image_url  = trim($opt['image_link'] ?? '');  // stored as URL
        $additional = trim($opt['additional_info'] ?? '');

        // Skip completely empty options
        if ($artno === '' && $value === '' && $image_url === '') {
          continue;
        }

        $options[] = [
          'artno'      => $artno,
          'value'      => $value,
          'type'       => $type,
          'image_url'  => $image_url,
          'additional' => $additional,
        ];
      }

      if (! empty($options)) {
        $groups[] = [
          'base_model'  => $base_model,
          'description' => $description,
          'options'     => $options,
        ];
      }
    }

    return $groups;
  }

  protected function render() {
    $settings    = $this->get_settings_for_display();
    $heading     = trim($settings['heading']     ?? '');
    $heading_tag = $settings['heading_tag']       ?? 'h2';
    $heading_icon = $settings['heading_icon']     ?? [];
    $show_value  = ($settings['show_value']       ?? '') === 'yes';
    $show_artno  = ($settings['show_artno']       ?? '') === 'no' ? false : ($settings['show_artno'] === 'yes');
    $group_label = $settings['group_label_field'] ?? 'value';
    $empty_text  = trim($settings['empty_text']  ?? __('Keine Varianten verfügbar.', 'gastro-cool-products'));

    $allowed_tags = ['h2', 'h3', 'h4', 'h5', 'h6', 'div'];
    if (! in_array($heading_tag, $allowed_tags, true)) {
      $heading_tag = 'h2';
    }

    $groups      = $this->get_options();
    $current_artno = $this->get_current_artno();

    echo '<div class="gc-variant-picker">';

    // Heading
    if ($heading !== '') {
      echo '<' . esc_attr($heading_tag) . ' class="gc-variant-picker__heading">';
      if (! empty($heading_icon['value'])) {
        echo '<span class="gc-variant-picker__heading-icon" aria-hidden="true">';
        \Elementor\Icons_Manager::render_icon($heading_icon, ['aria-hidden' => 'true']);
        echo '</span>';
      }
      echo esc_html($heading);
      echo '</' . esc_attr($heading_tag) . '>';
    }

    if (empty($groups)) {
      echo '<p class="gc-variant-picker__empty">' . esc_html($empty_text) . '</p>';
      echo '</div>';
      return;
    }

    foreach ($groups as $group) {
      echo '<div class="gc-variant-picker__group">';

      // Group label
      if ($group_label !== 'none') {
        $g_label = $group_label === 'base_model'
          ? $group['base_model']
          : ($group['options'][0]['type'] ?? '');

        if ($g_label !== '') {
          echo '<p class="gc-variant-picker__group-label">' . esc_html($g_label) . '</p>';
        }
      }

      echo '<div class="gc-variant-picker__options">';

      foreach ($group['options'] as $option) {
        $is_active = ($current_artno !== '' && $option['artno'] === $current_artno);
        $card_class = 'gc-variant-picker__option' . ($is_active ? ' is-active' : '');

        echo '<div class="' . esc_attr($card_class) . '"'
          . ' aria-current="' . ($is_active ? 'true' : 'false') . '"'
          . '>';

        // Image
        echo '<div class="gc-variant-picker__image">';
        if ($option['image_url'] !== '') {
          echo '<img'
            . ' src="' . esc_url($option['image_url']) . '"'
            . ' alt="' . esc_attr($option['value'] !== '' ? $option['value'] : $option['artno']) . '"'
            . ' loading="lazy"'
            . '>';
        } else {
          echo '<span class="gc-variant-picker__no-image" aria-hidden="true"></span>';
        }
        echo '</div>';

        // Meta
        echo '<div class="gc-variant-picker__meta">';

        if ($show_value && $option['value'] !== '') {
          echo '<span class="gc-variant-picker__value">' . esc_html($option['value']) . '</span>';
        }

        if ($option['additional'] !== '') {
          echo '<span class="gc-variant-picker__additional">' . esc_html($option['additional']) . '</span>';
        }

        if ($show_artno && $option['artno'] !== '') {
          echo '<span class="gc-variant-picker__artno">' . esc_html($option['artno']) . '</span>';
        }

        echo '</div>'; // meta
        echo '</div>'; // option
      }

      echo '</div>'; // options
      echo '</div>'; // group
    }

    echo '</div>'; // gc-variant-picker
  }
}
