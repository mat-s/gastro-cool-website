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
        'default'     => __('Verfügbare Varianten', 'gastro-cool-products'),
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

    $this->end_controls_section();

    // ── Felder ────────────────────────────────────────────────────────────
    $this->start_controls_section(
      'section_fields',
      [
        'label' => __('Angezeigte Felder', 'gastro-cool-products'),
      ]
    );

    foreach ($this->field_labels() as $key => $label) {
      $this->add_control(
        'show_' . $key,
        [
          'label'        => $label,
          'type'         => Controls_Manager::SWITCHER,
          'label_on'     => __('Ja', 'gastro-cool-products'),
          'label_off'    => __('Nein', 'gastro-cool-products'),
          'return_value' => 'yes',
          'default'      => 'yes',
        ]
      );
    }

    $this->add_control(
      'show_description',
      [
        'label'        => __('Beschreibung', 'gastro-cool-products'),
        'type'         => Controls_Manager::SWITCHER,
        'label_on'     => __('Ja', 'gastro-cool-products'),
        'label_off'    => __('Nein', 'gastro-cool-products'),
        'return_value' => 'yes',
        'default'      => 'yes',
        'separator'    => 'before',
      ]
    );

    $this->add_control(
      'show_energylabel',
      [
        'label'        => __('Energie-Label (über Bild)', 'gastro-cool-products'),
        'type'         => Controls_Manager::SWITCHER,
        'label_on'     => __('Ja', 'gastro-cool-products'),
        'label_off'    => __('Nein', 'gastro-cool-products'),
        'return_value' => 'yes',
        'default'      => 'yes',
      ]
    );

    // Labels for the DL rows (editable)
    $this->add_control(
      'heading_labels',
      [
        'label'     => __('Feldbezeichnungen', 'gastro-cool-products'),
        'type'      => Controls_Manager::HEADING,
        'separator' => 'before',
      ]
    );

    $defaults = $this->field_label_defaults();
    foreach ($defaults as $key => $default_label) {
      $this->add_control(
        'label_' . $key,
        [
          'label'   => $default_label,
          'type'    => Controls_Manager::TEXT,
          'default' => $default_label,
        ]
      );
    }

    $this->end_controls_section();

    // ── Leer-Text ─────────────────────────────────────────────────────────
    $this->start_controls_section(
      'section_empty',
      [
        'label' => __('Leer-Zustand', 'gastro-cool-products'),
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

  // ── Field definitions ─────────────────────────────────────────────────
  private function field_labels(): array {
    return [
      'color_body'     => __('Gehäuse-Farbe', 'gastro-cool-products'),
      'color_canopy'   => __('Hauben-Farbe',  'gastro-cool-products'),
      'interior_color' => __('Innenfarbe',    'gastro-cool-products'),
      'energieklasse'  => __('Energieklasse', 'gastro-cool-products'),
      'artno'          => __('Art.-Nr.',       'gastro-cool-products'),
      'ean'            => __('EAN',            'gastro-cool-products'),
      'eprel'          => __('EPREL-Code',     'gastro-cool-products'),
      'weight'         => __('Gewicht',        'gastro-cool-products'),
    ];
  }

  private function field_label_defaults(): array {
    return [
      'color_body'     => __('Gehäuse',       'gastro-cool-products'),
      'color_canopy'   => __('Haube',         'gastro-cool-products'),
      'interior_color' => __('Innenraum',     'gastro-cool-products'),
      'energieklasse'  => __('Energieklasse', 'gastro-cool-products'),
      'artno'          => __('Art.-Nr.',       'gastro-cool-products'),
      'ean'            => __('EAN',            'gastro-cool-products'),
      'eprel'          => __('EPREL-Code',     'gastro-cool-products'),
      'weight'         => __('Gewicht',        'gastro-cool-products'),
    ];
  }

  // ── Build flat list of variant cards ─────────────────────────────────
  private function get_cards(): array {
    if (! function_exists('get_field')) {
      return [];
    }

    $variants = get_field('variants');
    if (! is_array($variants) || empty($variants)) {
      return [];
    }

    // Product-level fields (same for all variants)
    $eprel  = trim((string)(get_field('cust_gc_eprel')  ?? ''));
    $weight = trim((string)(get_field('weight_raw')     ?? ''));
    if ($weight === '') {
      $weight_kg = get_field('net_weight_kg');
      if ($weight_kg !== null && $weight_kg !== '') {
        $weight = $weight_kg . ' kg';
      }
    }

    $cards = [];

    foreach ($variants as $group) {
      $color_body     = trim($group['color_body']     ?? '');
      $color_canopy   = trim($group['color_canopy']   ?? '');
      $interior_color = trim($group['interior_color'] ?? '');
      $description    = trim($group['description']    ?? '');

      $options = is_array($group['options'] ?? null) ? $group['options'] : [];

      foreach ($options as $opt) {
        $img_raw   = $opt['image_link'] ?? '';
        $image_url = is_array($img_raw)
          ? trim($img_raw['url'] ?? '')
          : trim((string) $img_raw);

        $cards[] = [
          'title'          => trim($opt['value']      ?? ''),
          'description'    => $description,
          'image_url'      => $image_url,
          'link'           => trim($opt['link']       ?? ''),
          'color_body'     => $color_body,
          'color_canopy'   => $color_canopy,
          'interior_color' => $interior_color,
          'energylabel'    => trim($opt['energylabel'] ?? ''),
          'artno'          => trim($opt['artno']       ?? ''),
          'ean'            => trim($opt['ean']         ?? ''),
          'eprel'          => $eprel,
          'weight'         => $weight,
        ];
      }
    }

    return $cards;
  }

  // ── Render one DL row ─────────────────────────────────────────────────
  private function row( string $label, string $value ): void {
    if ($value === '') {
      return;
    }
    echo '<div class="gc-variant-picker__row">';
    echo '<dt class="gc-variant-picker__row-label">' . esc_html($label) . '</dt>';
    echo '<dd class="gc-variant-picker__row-value">' . esc_html($value) . '</dd>';
    echo '</div>';
  }

  protected function render() {
    $settings    = $this->get_settings_for_display();
    $heading     = trim($settings['heading']     ?? '');
    $heading_tag = $settings['heading_tag']       ?? 'h2';
    $empty_text  = trim($settings['empty_text']  ?? __('Keine Varianten verfügbar.', 'gastro-cool-products'));

    $show_description  = ($settings['show_description']  ?? '') === 'yes';
    $show_energylabel  = ($settings['show_energylabel']  ?? '') === 'yes';
    $show = [];
    foreach (array_keys($this->field_labels()) as $key) {
      $show[$key] = ($settings['show_' . $key] ?? '') === 'yes';
    }

    $labels = [];
    foreach (array_keys($this->field_label_defaults()) as $key) {
      $labels[$key] = trim($settings['label_' . $key] ?? '') ?: $this->field_label_defaults()[$key];
    }

    $allowed_tags = ['h2', 'h3', 'h4', 'h5', 'h6', 'div'];
    if (! in_array($heading_tag, $allowed_tags, true)) {
      $heading_tag = 'h2';
    }

    $cards = $this->get_cards();

    echo '<div class="gc-variant-picker">';

    if ($heading !== '') {
      echo '<' . esc_attr($heading_tag) . ' class="gc-variant-picker__heading">'
        . esc_html($heading)
        . '</' . esc_attr($heading_tag) . '>';
    }

    if (empty($cards)) {
      echo '<p class="gc-variant-picker__empty">' . esc_html($empty_text) . '</p>';
      echo '</div>';
      return;
    }

    echo '<div class="gc-variant-picker__grid">';

    foreach ($cards as $card) {
      $tag  = $card['link'] !== '' ? 'a' : 'div';
      $attr = $tag === 'a'
        ? ' href="' . esc_url($card['link']) . '" class="gc-variant-picker__card"'
        : ' class="gc-variant-picker__card"';

      echo '<' . $tag . $attr . '>';

      // Image area + energy label badge
      echo '<div class="gc-variant-picker__image-wrap">';
      if ($card['image_url'] !== '') {
        echo '<img class="gc-variant-picker__img"'
          . ' src="' . esc_url($card['image_url']) . '"'
          . ' alt="' . esc_attr($card['title']) . '"'
          . ' loading="lazy">';
      }
      if ($show_energylabel && $card['energylabel'] !== '') {
        echo '<span class="gc-variant-picker__energylabel">'
          . esc_html($card['energylabel'])
          . '</span>';
      }
      echo '</div>';

      // Content
      echo '<div class="gc-variant-picker__content">';

      if ($card['title'] !== '') {
        echo '<p class="gc-variant-picker__title">' . esc_html($card['title']) . '</p>';
      }

      if ($show_description && $card['description'] !== '') {
        echo '<p class="gc-variant-picker__description">' . esc_html($card['description']) . '</p>';
      }

      // Definition list
      echo '<dl class="gc-variant-picker__dl">';
      if ($show['color_body'])     $this->row($labels['color_body'],     $card['color_body']);
      if ($show['color_canopy'])   $this->row($labels['color_canopy'],   $card['color_canopy']);
      if ($show['interior_color']) $this->row($labels['interior_color'], $card['interior_color']);
      if ($show['energieklasse'])  $this->row($labels['energieklasse'],  $card['energylabel']);
      if ($show['artno'])          $this->row($labels['artno'],          $card['artno']);
      if ($show['ean'])            $this->row($labels['ean'],            $card['ean']);
      if ($show['eprel'])          $this->row($labels['eprel'],          $card['eprel']);
      if ($show['weight'])         $this->row($labels['weight'],         $card['weight']);
      echo '</dl>';

      echo '</div>'; // content
      echo '</' . $tag . '>';
    }

    echo '</div>'; // grid
    echo '</div>'; // gc-variant-picker
  }
}
