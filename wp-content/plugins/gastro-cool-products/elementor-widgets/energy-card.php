<?php

namespace GCP\Elementor\Widgets;

if (! defined('ABSPATH')) {
  exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Energy_Card_Widget extends Widget_Base
{
  public function get_name() {
    return 'gcp_energy_card';
  }

  public function get_title() {
    return __('Energieeffizienz', 'gastro-cool-products');
  }

  public function get_icon() {
    return 'eicon-flash';
  }

  public function get_categories() {
    return ['gastro-cool'];
  }

  public function get_style_depends() {
    return ['gcp-plugin'];
  }

  private const SCALAR_TYPES = [
    'text', 'textarea', 'number', 'range', 'email', 'url',
    'password', 'select', 'checkbox', 'radio', 'true_false',
    'date_picker', 'date_time_picker', 'time_picker',
    'color_picker', 'wysiwyg',
  ];

  private function get_acf_scalar_field_options(): array {
    $options = ['' => __('— Feld wählen —', 'gastro-cool-products')];

    if (! function_exists('acf_get_field_groups') || ! function_exists('acf_get_fields')) {
      return $options;
    }

    $groups = acf_get_field_groups(['post_type' => 'product']);
    foreach ($groups as $group) {
      $fields = acf_get_fields($group['key']);
      if (! $fields) {
        continue;
      }
      foreach ($fields as $field) {
        if (in_array($field['type'], self::SCALAR_TYPES, true)) {
          $options[$field['name']] = $field['label'] . ' (' . $field['name'] . ')';
        }
      }
    }

    return $options;
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
        'default'     => __('Energieeffizienz', 'gastro-cool-products'),
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
        'default' => ['value' => 'fas fa-bolt', 'library' => 'fa-solid'],
      ]
    );

    $this->end_controls_section();

    // ── Zeilen ────────────────────────────────────────────────────────────
    $this->start_controls_section(
      'section_rows',
      [
        'label' => __('Zeilen', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
      'empty_text',
      [
        'label'   => __('Text bei fehlendem Wert', 'gastro-cool-products'),
        'type'    => Controls_Manager::TEXT,
        'default' => __('Nicht verfügbar', 'gastro-cool-products'),
      ]
    );

    $repeater = new Repeater();

    $repeater->add_control(
      'label',
      [
        'label'       => __('Bezeichnung', 'gastro-cool-products'),
        'type'        => Controls_Manager::TEXT,
        'default'     => '',
        'placeholder' => __('z.B. Energieeffizienzklasse', 'gastro-cool-products'),
        'label_block' => true,
      ]
    );

    $repeater->add_control(
      'acf_field',
      [
        'label'   => __('ACF-Feld', 'gastro-cool-products'),
        'type'    => Controls_Manager::SELECT,
        'default' => '',
        'options' => $this->get_acf_scalar_field_options(),
      ]
    );

    $repeater->add_control(
      'appendix',
      [
        'label'       => __('Appendix (optional)', 'gastro-cool-products'),
        'type'        => Controls_Manager::TEXT,
        'default'     => '',
        'placeholder' => __('z.B. kWh', 'gastro-cool-products'),
        'label_block' => false,
      ]
    );

    $this->add_control(
      'rows',
      [
        'label'       => __('Zeilen', 'gastro-cool-products'),
        'type'        => Controls_Manager::REPEATER,
        'fields'      => $repeater->get_controls(),
        'default'     => [
          [
            'label'     => __('Energieeffizienzklasse', 'gastro-cool-products'),
            'acf_field' => 'energy_label',
            'appendix'  => '',
          ],
          [
            'label'     => __('Verbrauch pro 24h', 'gastro-cool-products'),
            'acf_field' => 'energy_consumption_24h_raw',
            'appendix'  => '',
          ],
          [
            'label'     => __('EEK EU', 'gastro-cool-products'),
            'acf_field' => 'eei_raw',
            'appendix'  => '',
          ],
        ],
        'title_field' => "{{{ label }}}",
      ]
    );

    $this->end_controls_section();

    // ── Energie-Label Button ──────────────────────────────────────────────
    $this->start_controls_section(
      'section_energy_button',
      [
        'label' => __('Energie-Label Button', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
      'show_energy_button',
      [
        'label'        => __('Energie-Label Button anzeigen', 'gastro-cool-products'),
        'type'         => Controls_Manager::SWITCHER,
        'label_on'     => __('Ja', 'gastro-cool-products'),
        'label_off'    => __('Nein', 'gastro-cool-products'),
        'return_value' => 'yes',
        'default'      => 'yes',
        'description'  => __('Bettet den HTML-Button aus dem ACF-Feld "energy_button_html" ein.', 'gastro-cool-products'),
      ]
    );

    $this->end_controls_section();
  }

  private function format_acf_value( $raw ): string {
    if ($raw === null || $raw === false || $raw === '') {
      return '';
    }
    if (is_bool($raw)) {
      return $raw ? __('Ja', 'gastro-cool-products') : __('Nein', 'gastro-cool-products');
    }
    if (is_array($raw)) {
      return implode(', ', array_map('strval', $raw));
    }
    return trim((string) $raw);
  }

  protected function render() {
    $settings     = $this->get_settings_for_display();
    $heading      = trim($settings['heading']      ?? '');
    $heading_tag  = $settings['heading_tag']        ?? 'h2';
    $heading_icon = $settings['heading_icon']       ?? [];
    $empty_text   = trim($settings['empty_text']   ?? __('Nicht verfügbar', 'gastro-cool-products'));
    $rows         = is_array($settings['rows'] ?? null) ? $settings['rows'] : [];
    $show_button  = ($settings['show_energy_button'] ?? '') === 'yes';

    $allowed_tags = ['h2', 'h3', 'h4', 'h5', 'h6', 'div'];
    if (! in_array($heading_tag, $allowed_tags, true)) {
      $heading_tag = 'h2';
    }

    // Heading row (title left, energy label button right)
    $button_html = '';
    if ($show_button && function_exists('get_field')) {
      $button_html = get_field('energy_button_html') ?? '';
    }
    ?>
    <div class="gc-energy-card">

      <?php if ($heading !== '' || $button_html !== '') : ?>
        <div class="gc-energy-card__header">

          <?php if ($heading !== '') : ?>
            <<?= esc_attr($heading_tag) ?> class="gc-energy-card__heading">
              <?php if (! empty($heading_icon['value'])) : ?>
                <span class="gc-energy-card__heading-icon" aria-hidden="true">
                  <?php \Elementor\Icons_Manager::render_icon($heading_icon, ['aria-hidden' => 'true']); ?>
                </span>
              <?php endif; ?>
              <?= esc_html($heading) ?>
            </<?= esc_attr($heading_tag) ?>>
          <?php endif; ?>

          <?php if ($button_html !== '') : ?>
            <div class="gc-energy-card__label-button">
              <?php echo wp_kses_post($button_html); ?>
            </div>
          <?php endif; ?>

        </div>
      <?php endif; ?>

      <div class="gc-energy-card__card">
        <dl class="gc-energy-card__rows">

          <?php foreach ($rows as $row) : ?>
            <?php
            $label     = trim($row['label']    ?? '');
            $acf_field = sanitize_key($row['acf_field'] ?? '');
            $appendix  = trim($row['appendix'] ?? '');

            $value = '';
            if ($acf_field && function_exists('get_field')) {
              $value = $this->format_acf_value(get_field($acf_field));
            }

            $is_empty  = ($value === '');
            $display   = $is_empty ? $empty_text : $value . ($appendix !== '' ? ' ' . $appendix : '');
            $row_class = 'gc-energy-card__row elementor-repeater-item-' . esc_attr($row['_id'] ?? '');
            if ($is_empty) {
              $row_class .= ' gc-energy-card__row--empty';
            }
            ?>
            <div class="<?= esc_attr($row_class) ?>">
              <dt class="gc-energy-card__label"><?= esc_html($label) ?></dt>
              <dd class="gc-energy-card__value"><?= esc_html($display) ?></dd>
            </div>
          <?php endforeach; ?>

        </dl>
      </div><!-- .gc-energy-card__card -->

    </div><!-- .gc-energy-card -->

    <?php
    // ── JSON-LD: Product schema with energy additionalProperty ────────────
    $schema_props = [];
    foreach ($rows as $row) {
      $label     = trim($row['label']    ?? '');
      $acf_field = sanitize_key($row['acf_field'] ?? '');
      $appendix  = trim($row['appendix'] ?? '');

      if ($acf_field === '' || ! function_exists('get_field')) {
        continue;
      }

      $value = $this->format_acf_value(get_field($acf_field));
      if ($value === '') {
        continue;
      }

      $schema_props[] = [
        '@type' => 'PropertyValue',
        'name'  => $label,
        'value' => $value . ($appendix !== '' ? ' ' . $appendix : ''),
      ];
    }

    if (! empty($schema_props)) {
      $schema = [
        '@context'           => 'https://schema.org',
        '@type'              => 'Product',
        'name'               => (string) get_the_title(),
        'additionalProperty' => $schema_props,
      ];
      echo '<script type="application/ld+json">'
        . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . '</script>';
    }
  }
}
