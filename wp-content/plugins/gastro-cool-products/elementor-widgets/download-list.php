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
    ?>
    <div class="gc-download-list__group">

      <div class="gc-download-list__group-heading">
        <?php if ($heading !== '') : ?>
          <span class="gc-download-list__group-title"><?= esc_html($heading) ?></span>
        <?php endif; ?>
      </div>

      <?php if (! empty($items)) : ?>
        <ul class="gc-download-list__files">
          <?php foreach ($items as $row) : ?>
            <?php
            $title     = trim($row['title']     ?? '');
            $file_url  = $this->resolve_url($row, $acf_name);
            $language  = strtoupper(trim($row['language']  ?? ''));
            $file_type = strtoupper(trim($row['file_type'] ?? ''));
            $file_size = trim($row['file_size'] ?? '');

            if ($file_url === '' && $title === '') {
              continue;
            }

            $has_meta = ($language !== '' || $file_type !== '' || $file_size !== '');
            ?>
            <li class="gc-download-list__file">
              <span class="gc-download-list__file-body">
                <?php if ($file_url !== '') : ?>
                  <a class="gc-download-list__link" href="<?= esc_url($file_url) ?>" target="_blank" rel="noopener"
                    aria-label="<?= esc_attr(($title !== '' ? $title : $file_url) . ' – ' . __('öffnet in neuem Tab', 'gastro-cool-products')) ?>">
                    <?php if (! empty($icon['value'])) : ?>
                      <span class="gc-download-list__file-icon" aria-hidden="true">
                        <?php \Elementor\Icons_Manager::render_icon($icon, ['aria-hidden' => 'true']); ?>
                      </span>
                    <?php endif; ?>
                    <?= esc_html($title !== '' ? $title : $file_url) ?>
                  </a>
                <?php else : ?>
                  <span class="gc-download-list__file-title">
                    <?php if (! empty($icon['value'])) : ?>
                      <span class="gc-download-list__file-icon" aria-hidden="true">
                        <?php \Elementor\Icons_Manager::render_icon($icon, ['aria-hidden' => 'true']); ?>
                      </span>
                    <?php endif; ?>
                    <?= esc_html($title) ?>
                  </span>
                <?php endif; ?>
                <?php if ($has_meta) : ?>
                  <span class="gc-download-list__file-meta">
                    <?php if ($language !== '') : ?>
                      <span class="gc-download-list__meta-lang"><?= esc_html($language) ?></span>
                    <?php endif; ?>
                    <?php if ($file_type !== '') : ?>
                      <span class="gc-download-list__meta-type"><?= esc_html($file_type) ?></span>
                    <?php endif; ?>
                    <?php if ($file_size !== '') : ?>
                      <span class="gc-download-list__meta-size"><?= esc_html($file_size) ?></span>
                    <?php endif; ?>
                  </span>
                <?php endif; ?>
              </span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else : ?>
        <p class="gc-download-list__empty"><?= esc_html($empty_text) ?></p>
      <?php endif; ?>

    </div>
    <?php
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
    ?>
    <div class="gc-download-list">

      <?php if ($heading !== '') : ?>
        <<?= esc_attr($heading_tag) ?> class="gc-download-list__heading">
          <?php if (! empty($heading_icon['value'])) : ?>
            <span class="gc-download-list__heading-icon" aria-hidden="true">
              <?php \Elementor\Icons_Manager::render_icon($heading_icon, ['aria-hidden' => 'true']); ?>
            </span>
          <?php endif; ?>
          <?= esc_html($heading) ?>
        </<?= esc_attr($heading_tag) ?>>
      <?php endif; ?>

      <div class="gc-download-list__grid">
        <?php foreach ($this->groups() as [$prefix, $acf_name]) : ?>
          <?php $this->render_group($settings, $prefix, $acf_name, $empty_text); ?>
        <?php endforeach; ?>
      </div>

    </div>

    <?php
    // ── JSON-LD: DigitalDocument schema per download ──────────────────────
    $schema_docs = [];
    foreach ($this->groups() as [$prefix, $acf_name]) {
      $items = [];
      if (function_exists('get_field')) {
        $raw = get_field($acf_name);
        if (is_array($raw)) {
          $items = $raw;
        }
      }
      foreach ($items as $row) {
        $title    = trim($row['title']    ?? '');
        $file_url = $this->resolve_url($row, $acf_name);
        if ($file_url === '') {
          continue;
        }

        $doc = [
          '@context' => 'https://schema.org',
          '@type'    => 'DigitalDocument',
          'name'     => $title !== '' ? $title : basename($file_url),
          'url'      => $file_url,
        ];

        $lang = strtolower(trim($row['language'] ?? ''));
        if ($lang !== '') {
          $doc['inLanguage'] = $lang;
        }

        $file_type = strtolower(trim($row['file_type'] ?? ''));
        if ($file_type === 'pdf') {
          $doc['encodingFormat'] = 'application/pdf';
        }

        $schema_docs[] = $doc;
      }
    }

    if (! empty($schema_docs)) {
      echo '<script type="application/ld+json">'
        . wp_json_encode($schema_docs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . '</script>';
    }
  }
}
