<?php

namespace GCP\Elementor\Widgets;

if (! defined('ABSPATH')) {
  exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Download_List_Widget extends Widget_Base
{
  public function get_name() {
    return 'gcp_download_list';
  }

  public function get_title() {
    return __('Download-Liste', 'gastro-cool-products');
  }

  public function get_icon() {
    return 'eicon-download-button';
  }

  public function get_categories() {
    return ['gastro-cool'];
  }

  public function get_style_depends() {
    return ['gcp-plugin'];
  }

  // ── Groups config: [prefix, acf_field_name, default_heading] ─────────
  private function groups(): array {
    return [
      ['datasheets',           'datasheets',           __('Datenblätter',           'gastro-cool-products')],
      ['manuals',              'manuals',              __('Bedienungsanleitungen',   'gastro-cool-products')],
      ['cad_files',            'cad_files',            __('CAD-Dateien',             'gastro-cool-products')],
      ['certificates',         'certificates_downloads', __('Zertifikate',           'gastro-cool-products')],
      ['installation_guides',  'installation_guides',  __('Installationsanleitungen','gastro-cool-products')],
      ['other_documents',      'other_documents',      __('Weitere Downloads',       'gastro-cool-products')],
    ];
  }

  // ── Helper: add heading + icon controls for one group ─────────────────
  private function add_group_controls( string $prefix, string $default_heading ): void {
    $this->add_control(
      $prefix . '_heading',
      [
        'label'       => __('Überschrift', 'gastro-cool-products'),
        'type'        => Controls_Manager::TEXT,
        'default'     => $default_heading,
        'label_block' => true,
      ]
    );

    $this->add_control(
      $prefix . '_icon',
      [
        'label'   => __('Icon', 'gastro-cool-products'),
        'type'    => Controls_Manager::ICONS,
        'default' => ['value' => 'far fa-file', 'library' => 'fa-regular'],
      ]
    );
  }

  protected function register_controls() {

    // ── Widget heading ────────────────────────────────────────────────────
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
        'default'     => __('Downloads', 'gastro-cool-products'),
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
        'default' => ['value' => 'fas fa-download', 'library' => 'fa-solid'],
      ]
    );

    $this->add_control(
      'empty_text',
      [
        'label'   => __('Text bei fehlenden Downloads', 'gastro-cool-products'),
        'type'    => Controls_Manager::TEXT,
        'default' => __('Keine Downloads verfügbar.', 'gastro-cool-products'),
      ]
    );

    $this->end_controls_section();

    // ── One section per download group ────────────────────────────────────
    foreach ($this->groups() as [$prefix, $acf_name, $default_heading]) {
      $this->start_controls_section(
        'section_' . $prefix,
        [
          'label' => $default_heading,
        ]
      );

      $this->add_group_controls($prefix, $default_heading);

      $this->end_controls_section();
    }
  }

  // ── Helper: resolve file URL from ACF repeater row ────────────────────
  private function resolve_url( array $row, string $acf_name ): string {
    // cad_files uses 'url', all others use 'file_url'
    $key = ($acf_name === 'cad_files') ? 'url' : 'file_url';
    return trim($row[ $key ] ?? '');
  }

  // ── Helper: render one download group column ──────────────────────────
  private function render_group(
    array  $settings,
    string $prefix,
    string $acf_name,
    string $empty_text
  ): void {
    $heading = trim($settings[ $prefix . '_heading' ] ?? '');
    $icon    = $settings[ $prefix . '_icon' ]           ?? [];

    $items = [];
    if (function_exists('get_field')) {
      $raw = get_field($acf_name);
      if (is_array($raw)) {
        $items = $raw;
      }
    }

    echo '<div class="gc-download-list__group">';

    // Group heading with icon
    echo '<div class="gc-download-list__group-heading">';
    if (! empty($icon['value'])) {
      echo '<span class="gc-download-list__group-icon" aria-hidden="true">';
      \Elementor\Icons_Manager::render_icon($icon, ['aria-hidden' => 'true']);
      echo '</span>';
    }
    if ($heading !== '') {
      echo '<span class="gc-download-list__group-title">' . esc_html($heading) . '</span>';
    }
    echo '</div>';

    // Files or empty state
    if (! empty($items)) {
      echo '<ul class="gc-download-list__files">';
      foreach ($items as $row) {
        $title    = trim($row['title']    ?? '');
        $file_url = $this->resolve_url($row, $acf_name);

        if ($file_url === '' && $title === '') {
          continue;
        }

        echo '<li class="gc-download-list__file">';

        if ($file_url !== '') {
          echo '<a class="gc-download-list__link" href="' . esc_url($file_url) . '" target="_blank" rel="noopener">';
          echo '<span class="gc-download-list__file-icon" aria-hidden="true"><i class="far fa-file"></i></span>';
          echo '<span class="gc-download-list__file-title">' . esc_html($title !== '' ? $title : $file_url) . '</span>';
          echo '</a>';
        } else {
          echo '<span class="gc-download-list__file-icon" aria-hidden="true"><i class="far fa-file"></i></span>';
          echo '<span class="gc-download-list__file-title">' . esc_html($title) . '</span>';
        }

        echo '</li>';
      }
      echo '</ul>';
    } else {
      echo '<p class="gc-download-list__empty">' . esc_html($empty_text) . '</p>';
    }

    echo '</div>';
  }

  protected function render() {
    $settings   = $this->get_settings_for_display();
    $heading     = trim($settings['heading']      ?? '');
    $heading_tag = $settings['heading_tag']        ?? 'h2';
    $heading_icon = $settings['heading_icon']      ?? [];
    $empty_text  = trim($settings['empty_text']   ?? __('Keine Downloads verfügbar.', 'gastro-cool-products'));

    $allowed_tags = ['h2', 'h3', 'h4', 'h5', 'h6', 'div'];
    if (! in_array($heading_tag, $allowed_tags, true)) {
      $heading_tag = 'h2';
    }

    echo '<div class="gc-download-list">';

    // Widget heading
    if ($heading !== '') {
      echo '<' . esc_attr($heading_tag) . ' class="gc-download-list__heading">';
      if (! empty($heading_icon['value'])) {
        echo '<span class="gc-download-list__heading-icon" aria-hidden="true">';
        \Elementor\Icons_Manager::render_icon($heading_icon, ['aria-hidden' => 'true']);
        echo '</span>';
      }
      echo esc_html($heading);
      echo '</' . esc_attr($heading_tag) . '>';
    }

    // Grid of groups
    echo '<div class="gc-download-list__grid">';

    foreach ($this->groups() as [$prefix, $acf_name]) {
      $this->render_group($settings, $prefix, $acf_name, $empty_text);
    }

    echo '</div>';
    echo '</div>';
  }
}
