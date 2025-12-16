<?php

namespace GCP\Elementor\Widgets;

if (! defined('ABSPATH')) {
  exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Inquiry_Form_Widget extends Widget_Base
{
  public function get_name() {
    return 'gcp_inquiry_form';
  }

  public function get_title() {
    return __('Inquiry Form', 'gastro-cool-products');
  }

  public function get_icon() {
    return 'eicon-mail';
  }

  public function get_categories() {
    return ['gastro-cool'];
  }

  public function get_script_depends() {
    return ['gcp-inquiry-form-widget'];
  }

  public function get_style_depends() {
    return ['gcp-inquiry-form-widget'];
  }

  protected function register_controls() {
    $this->start_controls_section(
      'section_form',
      [
        'label' => __('Formularfelder', 'gastro-cool-products'),
      ]
    );

    $repeater = new Repeater();

    $repeater->add_control(
      'label',
      [
        'label' => __('Beschriftung', 'gastro-cool-products'),
        'type' => Controls_Manager::TEXT,
        'default' => __('Feld', 'gastro-cool-products'),
      ]
    );

    $repeater->add_control(
      'name',
      [
        'label' => __('Feldname (ohne Leerzeichen)', 'gastro-cool-products'),
        'type' => Controls_Manager::TEXT,
        'default' => 'field',
        'description' => __('Nur Buchstaben, Zahlen und Unterstrich verwenden.', 'gastro-cool-products'),
      ]
    );

    $repeater->add_control(
      'type',
      [
        'label' => __('Typ', 'gastro-cool-products'),
        'type' => Controls_Manager::SELECT,
        'default' => 'text',
        'options' => [
          'text' => __('Text', 'gastro-cool-products'),
          'email' => __('E-Mail', 'gastro-cool-products'),
          'tel' => __('Telefon', 'gastro-cool-products'),
          'url' => __('URL', 'gastro-cool-products'),
          'number' => __('Nummer', 'gastro-cool-products'),
          'textarea' => __('Textarea', 'gastro-cool-products'),
          'select' => __('Select', 'gastro-cool-products'),
          'checkbox' => __('Checkbox', 'gastro-cool-products'),
          'radio' => __('Radio', 'gastro-cool-products'),
          'date' => __('Datum', 'gastro-cool-products'),
          'time' => __('Zeit', 'gastro-cool-products'),
        ],
      ]
    );

    $repeater->add_control(
      'options',
      [
        'label' => __('Optionen (eine pro Zeile, optional Label|Wert)', 'gastro-cool-products'),
        'type' => Controls_Manager::TEXTAREA,
        'rows' => 5,
        'default' => '',
        'condition' => [
          'type' => ['select', 'checkbox', 'radio'],
        ],
      ]
    );

    $repeater->add_control(
      'placeholder',
      [
        'label' => __('Platzhalter', 'gastro-cool-products'),
        'type' => Controls_Manager::TEXT,
        'default' => '',
      ]
    );

    $repeater->add_control(
      'required',
      [
        'label' => __('Pflichtfeld', 'gastro-cool-products'),
        'type' => Controls_Manager::SWITCHER,
        'label_on' => __('Ja', 'gastro-cool-products'),
        'label_off' => __('Nein', 'gastro-cool-products'),
        'return_value' => 'yes',
        'default' => '',
      ]
    );

    $repeater->add_control(
      'width',
      [
        'label' => __('Breite', 'gastro-cool-products'),
        'type' => Controls_Manager::SELECT,
        'default' => '100',
        'options' => [
          '100' => __('100%', 'gastro-cool-products'),
          '50' => __('50%', 'gastro-cool-products'),
        ],
      ]
    );

    $this->add_control(
      'fields',
      [
        'label' => __('Felder', 'gastro-cool-products'),
        'type' => Controls_Manager::REPEATER,
        'fields' => $repeater->get_controls(),
        'default' => [
          [
            'label' => __('Vorname', 'gastro-cool-products'),
            'name' => 'first_name',
            'type' => 'text',
            'placeholder' => __('Vorname', 'gastro-cool-products'),
            'required' => 'yes',
            'width' => '50',
          ],
          [
            'label' => __('Nachname', 'gastro-cool-products'),
            'name' => 'last_name',
            'type' => 'text',
            'placeholder' => __('Nachname', 'gastro-cool-products'),
            'required' => 'yes',
            'width' => '50',
          ],
          [
            'label' => __('Firma', 'gastro-cool-products'),
            'name' => 'company',
            'type' => 'text',
            'placeholder' => __('Firma', 'gastro-cool-products'),
            'required' => '',
            'width' => '100',
          ],
          [
            'label' => __('Telefonnummer', 'gastro-cool-products'),
            'name' => 'phone',
            'type' => 'tel',
            'placeholder' => __('Telefonnummer', 'gastro-cool-products'),
            'required' => '',
            'width' => '50',
          ],
          [
            'label' => __('E-Mail-Adresse', 'gastro-cool-products'),
            'name' => 'email',
            'type' => 'email',
            'placeholder' => __('E-Mail-Adresse', 'gastro-cool-products'),
            'required' => 'yes',
            'width' => '50',
          ],
          [
            'label' => __('Nachricht', 'gastro-cool-products'),
            'name' => 'message',
            'type' => 'textarea',
            'placeholder' => __('Ihre Nachricht', 'gastro-cool-products'),
            'required' => '',
            'width' => '100',
          ],
        ],
        'title_field' => '{{{ label }}}',
      ]
    );

    $this->end_controls_section();

    $this->start_controls_section(
      'section_settings',
      [
        'label' => __('Einstellungen', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
      'recipient_email',
      [
        'label' => __('Empfänger E-Mail', 'gastro-cool-products'),
        'type' => Controls_Manager::TEXT,
        'default' => get_option('admin_email'),
        'description' => __('Wird an wp_mail() übergeben. Fallback ist die Admin-E-Mail.', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
      'submit_label',
      [
        'label' => __('Button-Text', 'gastro-cool-products'),
        'type' => Controls_Manager::TEXT,
        'default' => __('Anfrage absenden', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
      'success_message',
      [
        'label' => __('Erfolgsmeldung', 'gastro-cool-products'),
        'type' => Controls_Manager::TEXT,
        'default' => __('Vielen Dank für Ihre Anfrage.', 'gastro-cool-products'),
      ]
    );

    $this->end_controls_section();
  }

  protected function render() {
    $settings = $this->get_settings_for_display();
    $fields = isset($settings['fields']) && is_array($settings['fields']) ? $settings['fields'] : [];
    $submit_label = ! empty($settings['submit_label']) ? $settings['submit_label'] : __('Anfrage absenden', 'gastro-cool-products');
    $success_message = ! empty($settings['success_message']) ? $settings['success_message'] : __('Vielen Dank für Ihre Anfrage.', 'gastro-cool-products');
    $recipient_email = ! empty($settings['recipient_email']) ? $settings['recipient_email'] : get_option('admin_email');
    $nonce = wp_create_nonce('gcp_inquiry_form_nonce');
    $ajax_url = admin_url('admin-ajax.php');

    $field_config = [];
    foreach ($fields as $field) {
      $name = isset($field['name']) ? sanitize_key($field['name']) : '';
      if (! $name) {
        continue;
      }
      $options = [];
      if (! empty($field['options']) && is_string($field['options'])) {
        $lines = array_filter(array_map('trim', explode("\n", $field['options'])));
        foreach ($lines as $line) {
          $parts = array_map('trim', explode('|', $line));
          $options[] = [
            'label' => isset($parts[0]) ? $parts[0] : '',
            'value' => isset($parts[1]) && $parts[1] !== '' ? $parts[1] : (isset($parts[0]) ? $parts[0] : ''),
          ];
        }
      }
      $field_config[] = [
        'name' => $name,
        'label' => isset($field['label']) ? $field['label'] : $name,
        'type' => isset($field['type']) ? $field['type'] : 'text',
        'required' => isset($field['required']) && $field['required'] === 'yes',
        'options' => $options,
      ];
    }

    ?>
    <form
      class="gc-inquiry-form"
      method="post"
      data-gc-inquiry-form
      data-ajax-url="<?php echo esc_url($ajax_url); ?>"
      data-success-message="<?php echo esc_attr($success_message); ?>"
    >
      <input type="hidden" name="action" value="gcp_submit_inquiry">
      <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">
      <input type="hidden" name="fields_config" value="<?php echo esc_attr(wp_json_encode($field_config)); ?>">
      <input type="hidden" name="recipient" value="<?php echo esc_attr($recipient_email); ?>">
      <div class="gc-inquiry-form__fields">
        <?php foreach ($fields as $field) :
          $name = isset($field['name']) ? sanitize_key($field['name']) : '';
          if (! $name) {
            continue;
          }
          $type = isset($field['type']) ? $field['type'] : 'text';
          $label = isset($field['label']) ? $field['label'] : $name;
          $placeholder = isset($field['placeholder']) ? $field['placeholder'] : '';
          $required = isset($field['required']) && $field['required'] === 'yes';
          $width = isset($field['width']) ? $field['width'] : '100';
          $input_id = 'gc-inquiry-field-' . esc_attr($name) . '-' . uniqid();
          $options = [];
          if (! empty($field['options']) && is_string($field['options'])) {
            $lines = array_filter(array_map('trim', explode("\n", $field['options'])));
            foreach ($lines as $line) {
              $parts = array_map('trim', explode('|', $line));
              $options[] = [
                'label' => isset($parts[0]) ? $parts[0] : '',
                'value' => isset($parts[1]) && $parts[1] !== '' ? $parts[1] : (isset($parts[0]) ? $parts[0] : ''),
              ];
            }
          }
          ?>
          <div class="gc-inquiry-form__field gc-inquiry-form__field--<?php echo esc_attr($width); ?>">
            <label class="gc-inquiry-form__label" for="<?php echo $input_id; ?>">
              <?php echo esc_html($label); ?>
              <?php if ($required) : ?><span class="gc-inquiry-form__required">*</span><?php endif; ?>
            </label>
            <?php if ($type === 'textarea') : ?>
              <textarea
                class="gc-inquiry-form__input"
                id="<?php echo $input_id; ?>"
                name="fields[<?php echo esc_attr($name); ?>]"
                placeholder="<?php echo esc_attr($placeholder); ?>"
                <?php echo $required ? 'required' : ''; ?>
                rows="4"
              ></textarea>
            <?php elseif (in_array($type, ['select', 'radio'], true)) : ?>
              <?php if (! empty($options)) : ?>
                <?php if ($type === 'select') : ?>
                  <select
                    class="gc-inquiry-form__input"
                    id="<?php echo $input_id; ?>"
                    name="fields[<?php echo esc_attr($name); ?>]"
                    <?php echo $required ? 'required' : ''; ?>
                  >
                    <option value=""><?php echo esc_html__('Bitte wählen', 'gastro-cool-products'); ?></option>
                    <?php foreach ($options as $option) : ?>
                      <option value="<?php echo esc_attr($option['value']); ?>">
                        <?php echo esc_html($option['label']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                <?php else : ?>
                  <div class="gc-inquiry-form__choices">
                    <?php foreach ($options as $idx => $option) :
                      $choice_id = $input_id . '-' . $idx;
                      ?>
                      <label class="gc-inquiry-form__choice">
                        <input
                          type="radio"
                          name="fields[<?php echo esc_attr($name); ?>]"
                          value="<?php echo esc_attr($option['value']); ?>"
                          id="<?php echo esc_attr($choice_id); ?>"
                          <?php echo $required ? 'required' : ''; ?>
                        />
                        <span><?php echo esc_html($option['label']); ?></span>
                      </label>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              <?php endif; ?>
            <?php elseif ($type === 'checkbox') : ?>
              <?php if (! empty($options)) : ?>
                <div class="gc-inquiry-form__choices">
                  <?php foreach ($options as $idx => $option) :
                    $choice_id = $input_id . '-' . $idx;
                    ?>
                    <label class="gc-inquiry-form__choice">
                      <input
                        type="checkbox"
                        name="fields[<?php echo esc_attr($name); ?>][]"
                        value="<?php echo esc_attr($option['value']); ?>"
                        id="<?php echo esc_attr($choice_id); ?>"
                        <?php echo $required ? 'required' : ''; ?>
                      />
                      <span><?php echo esc_html($option['label']); ?></span>
                    </label>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            <?php else : ?>
              <input
                class="gc-inquiry-form__input"
                id="<?php echo $input_id; ?>"
                type="<?php echo esc_attr($type); ?>"
                name="fields[<?php echo esc_attr($name); ?>]"
                placeholder="<?php echo esc_attr($placeholder); ?>"
                <?php echo $required ? 'required' : ''; ?>
              />
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="gc-inquiry-form__actions">
        <button type="submit" class="gc-inquiry-form__submit">
          <?php echo esc_html($submit_label); ?>
        </button>
      </div>
      <div class="gc-inquiry-form__message" aria-live="polite"></div>
    </form>
    <?php
  }
}
