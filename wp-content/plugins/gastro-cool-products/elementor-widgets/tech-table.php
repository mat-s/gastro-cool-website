<?php

namespace GCP\Elementor\Widgets;

if (! defined('ABSPATH')) {
  exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Tech_Table_Widget extends Widget_Base
{
  public function get_name() {
    return 'gcp_tech_table';
  }

  public function get_title() {
    return __('Technische Daten', 'gastro-cool-products');
  }

  public function get_icon() {
    return 'eicon-table';
  }

  public function get_categories() {
    return ['gastro-cool'];
  }

  public function get_style_depends() {
    return ['gcp-plugin'];
  }

  public function get_script_depends() {
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

    // ── Inhalt (Titel + Zeilen) ────────────────────────────────────────────
    $this->start_controls_section(
      'section_content',
      [
        'label' => __('Inhalt', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
      'title',
      [
        'label'       => __('Titeltext', 'gastro-cool-products'),
        'type'        => Controls_Manager::TEXT,
        'default'     => __('Technische Daten', 'gastro-cool-products'),
        'placeholder' => __('z.B. Technische Daten', 'gastro-cool-products'),
        'dynamic'     => ['active' => true],
        'label_block' => true,
      ]
    );

    $this->add_control(
      'title_tag',
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
          'p'   => 'p',
          'div' => 'div',
        ],
      ]
    );

    $this->add_control(
      'title_divider',
      [
        'type' => Controls_Manager::DIVIDER,
      ]
    );

    $repeater = new Repeater();

    $repeater->add_control(
      'label',
      [
        'label'       => __('Bezeichnung', 'gastro-cool-products'),
        'type'        => Controls_Manager::TEXT,
        'default'     => '',
        'placeholder' => __('z.B. Breite', 'gastro-cool-products'),
        'dynamic'     => ['active' => true],
        'label_block' => true,
      ]
    );

    $repeater->add_control(
      'acf_field',
      [
        'label'   => __('ACF-Feld (Wert)', 'gastro-cool-products'),
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
        'placeholder' => __('z.B. mm, kg, °C', 'gastro-cool-products'),
        'label_block' => false,
      ]
    );

    $repeater->add_control(
      'hide_if_empty',
      [
        'label'        => __('Ausblenden wenn kein Wert', 'gastro-cool-products'),
        'type'         => Controls_Manager::SWITCHER,
        'label_on'     => __('Ja', 'gastro-cool-products'),
        'label_off'    => __('Nein', 'gastro-cool-products'),
        'return_value' => 'yes',
        'default'      => '',
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
            'label'     => __('Breite', 'gastro-cool-products'),
            'acf_field' => 'width_mm',
            'appendix'  => 'mm',
          ],
          [
            'label'     => __('Höhe', 'gastro-cool-products'),
            'acf_field' => 'height_mm',
            'appendix'  => 'mm',
          ],
          [
            'label'     => __('Tiefe', 'gastro-cool-products'),
            'acf_field' => 'depth_mm',
            'appendix'  => 'mm',
          ],
          [
            'label'     => __('Gewicht', 'gastro-cool-products'),
            'acf_field' => 'net_weight_kg',
            'appendix'  => 'kg',
          ],
          [
            'label'     => __('Kältemittel', 'gastro-cool-products'),
            'acf_field' => 'coolant',
            'appendix'  => '',
          ],
          [
            'label'     => __('Klimaklasse', 'gastro-cool-products'),
            'acf_field' => 'climate_class',
            'appendix'  => '',
          ],
        ],
        'title_field' => "{{{ label }}}",
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
      'show_divider',
      [
        'label'        => __('Trennlinie oben', 'gastro-cool-products'),
        'type'         => Controls_Manager::SWITCHER,
        'label_on'     => __('Ja', 'gastro-cool-products'),
        'label_off'    => __('Nein', 'gastro-cool-products'),
        'return_value' => 'yes',
        'default'      => 'yes',
      ]
    );

    $this->add_control(
      'accordion',
      [
        'label'        => __('Akkordeon (ein-/ausklappbar)', 'gastro-cool-products'),
        'type'         => Controls_Manager::SWITCHER,
        'label_on'     => __('Ja', 'gastro-cool-products'),
        'label_off'    => __('Nein', 'gastro-cool-products'),
        'return_value' => 'yes',
        'default'      => 'yes',
      ]
    );

    $this->add_control(
      'accordion_open',
      [
        'label'        => __('Standard: geöffnet', 'gastro-cool-products'),
        'type'         => Controls_Manager::SWITCHER,
        'label_on'     => __('Ja', 'gastro-cool-products'),
        'label_off'    => __('Nein', 'gastro-cool-products'),
        'return_value' => 'yes',
        'default'      => 'yes',
        'condition'    => ['accordion' => 'yes'],
      ]
    );

    $this->end_controls_section();
  }

  protected function render() {
    $settings = $this->get_settings_for_display();

    $show_divider   = $settings['show_divider']   ?? 'yes';
    $accordion      = $settings['accordion']      ?? 'yes';
    $accordion_open = $settings['accordion_open'] ?? 'yes';
    $title          = trim($settings['title']     ?? '');
    $title_tag      = $settings['title_tag']      ?? 'h2';
    $rows           = is_array($settings['rows']  ?? null) ? $settings['rows'] : [];

    $allowed_tags = ['h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div'];
    if (! in_array($title_tag, $allowed_tags, true)) {
      $title_tag = 'h2';
    }

    $is_accordion = $accordion === 'yes';
    $is_open      = ! $is_accordion || $accordion_open === 'yes';
    $body_id      = 'gc-tt-body-' . $this->get_id();

    // Pre-filter rows
    $visible_rows = [];
    foreach ($rows as $row) {
      $acf_field = isset($row['acf_field']) ? sanitize_key($row['acf_field']) : '';
      $label     = isset($row['label'])     ? trim($row['label'])             : '';
      $appendix  = isset($row['appendix'])  ? trim($row['appendix'])          : '';

      $hide_if_empty = isset($row['hide_if_empty']) && $row['hide_if_empty'] === 'yes';

      $value = '';
      if ($acf_field && function_exists('get_field')) {
        $raw   = get_field($acf_field);
        $value = $this->format_acf_value($raw);
      }

      if ($value === '' && $label === '') {
        continue;
      }

      if ($value === '' && $hide_if_empty) {
        continue;
      }

      $visible_rows[] = [
        'id'       => $row['_id'] ?? '',
        'label'    => $label,
        'value'    => $value,
        'appendix' => $appendix,
      ];
    }

    if (empty($visible_rows) && $title === '') {
      return;
    }

    // Wrapper classes
    $wrapper_classes = 'gc-tech-table';
    if ($is_accordion) {
      $wrapper_classes .= ' gc-tech-table--accordion';
      $wrapper_classes .= $is_open ? ' is-open' : ' is-closed';
    }

    ?>
    <div class="<?php echo esc_attr($wrapper_classes); ?>">

      <?php if ($show_divider === 'yes') : ?>
        <hr class="gc-tech-table__divider" aria-hidden="true">
      <?php endif; ?>

      <?php if ($title !== '') : ?>
        <?php if ($is_accordion) : ?>
          <button class="gc-tech-table__toggle" type="button"
            aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
            aria-controls="<?php echo esc_attr($body_id); ?>">
            <<?php echo esc_attr($title_tag); ?> class="gc-tech-table__title"><?php echo esc_html($title); ?></<?php echo esc_attr($title_tag); ?>>
            <span class="gc-tech-table__chevron" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
            </span>
          </button>
        <?php else : ?>
          <<?php echo esc_attr($title_tag); ?> class="gc-tech-table__title"><?php echo esc_html($title); ?></<?php echo esc_attr($title_tag); ?>>
        <?php endif; ?>
      <?php endif; ?>

      <div class="gc-tech-table__body"<?php if ($is_accordion) : ?> id="<?php echo esc_attr($body_id); ?>"<?php endif; ?>>

        <?php if (! empty($visible_rows)) : ?>
          <dl class="gc-tech-table__rows">
            <?php foreach ($visible_rows as $row) : ?>
              <div class="gc-tech-table__row elementor-repeater-item-<?php echo esc_attr($row['id']); ?>">
                <dt class="gc-tech-table__label"><?php echo esc_html($row['label']); ?></dt>
                <dd class="gc-tech-table__value">
                  <?php if ($row['value'] !== '') : ?>
                    <?php echo esc_html($row['value']); ?>
                    <?php if ($row['appendix'] !== '') : ?>
                      <span class="gc-tech-table__appendix"> <?php echo esc_html($row['appendix']); ?></span>
                    <?php endif; ?>
                  <?php else : ?>
                    <span class="gc-tech-table__empty" aria-label="<?php echo esc_attr__('Kein Wert', 'gastro-cool-products'); ?>">&ndash;</span>
                  <?php endif; ?>
                </dd>
              </div>
            <?php endforeach; ?>
          </dl>
        <?php endif; ?>

      </div><!-- /.gc-tech-table__body -->
    </div><!-- /.gc-tech-table -->

    <?php
    // ── JSON-LD: Product schema with tech specs as additionalProperty ─────
    $schema_props = [];
    foreach ($visible_rows as $row) {
      if ($row['value'] === '') {
        continue;
      }
      $schema_props[] = [
        '@type' => 'PropertyValue',
        'name'  => $row['label'],
        'value' => $row['value'] . ($row['appendix'] !== '' ? ' ' . $row['appendix'] : ''),
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
}
