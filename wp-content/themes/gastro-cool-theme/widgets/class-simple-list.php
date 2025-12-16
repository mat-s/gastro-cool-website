<?php

namespace GastroCoolTheme\Widgets;

if (! defined('ABSPATH')) {
  exit;
}

use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;

class Simple_List extends Widget_Base
{
  public function get_name()
  {
    return 'gc_simple_list';
  }

  public function get_title()
  {
    return __('Simple List', 'gastro-cool-theme');
  }

  public function get_icon()
  {
    return 'eicon-editor-list-ul';
  }

  public function get_categories()
  {
    return ['gastro-cool'];
  }

  protected function register_controls()
  {
    $this->start_controls_section(
      'section_list',
      [
        'label' => __('Listeneinträge', 'gastro-cool-theme'),
      ]
    );

    $repeater = new Repeater();

    $repeater->add_control(
      'text',
      [
        'label' => __('Text', 'gastro-cool-theme'),
        'type' => Controls_Manager::TEXT,
        'default' => __('Listeneintrag', 'gastro-cool-theme'),
        'label_block' => true,
      ]
    );

    $repeater->add_control(
      'link',
      [
        'label' => __('Link (optional)', 'gastro-cool-theme'),
        'type' => Controls_Manager::URL,
        'placeholder' => __('https://example.com', 'gastro-cool-theme'),
        'show_external' => true,
        'default' => [
          'url' => '',
          'is_external' => false,
          'nofollow' => false,
        ],
      ]
    );

    $repeater->add_control(
      'item_icon',
      [
        'label' => __('Icon (optional)', 'gastro-cool-theme'),
        'type' => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'skin' => 'inline',
				'label_block' => false,
      ]
    );

    $repeater->add_control(
      'icon_position',
      [
        'label' => __('Icon Position', 'gastro-cool-theme'),
        'type' => Controls_Manager::SELECT,
        'options' => [
          'before' => __('Vor dem Text', 'gastro-cool-theme'),
          'after' => __('Nach dem Text', 'gastro-cool-theme'),
        ],
        'default' => 'before',
        'condition' => [
          'item_icon[value]!' => '',
        ],
      ]
    );

    $repeater->add_control(
      'item_class',
      [
        'label' => __('Zusätzliche CSS-Klassen', 'gastro-cool-theme'),
        'type' => Controls_Manager::TEXT,
        'default' => '',
        'description' => __('Mehrere Klassen mit Leerzeichen trennen.', 'gastro-cool-theme'),
      ]
    );

    $this->add_control(
      'items',
      [
        'label' => __('Einträge', 'gastro-cool-theme'),
        'type' => Controls_Manager::REPEATER,
        'fields' => $repeater->get_controls(),
        'default' => [
          [
            'text' => __('Listeneintrag 1', 'gastro-cool-theme'),
            'icon_position' => 'before',
          ],
          [
            'text' => __('Listeneintrag 2', 'gastro-cool-theme'),
            'icon_position' => 'before',
          ],
        ],
        'title_field' => '{{{ text }}}',
      ]
    );

    $this->end_controls_section();
  }

  protected function render()
  {
    $settings = $this->get_settings_for_display();
    $items = isset($settings['items']) && is_array($settings['items']) ? $settings['items'] : [];

    if (empty($items)) {
      return;
    }

    ?>
    <ul class="gc-simple-list">
      <?php foreach ($items as $index => $item) :
        $text = isset($item['text']) ? $item['text'] : '';
        if ($text === '') {
          continue;
        }
        $icon = isset($item['item_icon']) ? $item['item_icon'] : null;
        $has_icon = $icon && ! empty($icon['value']);
        $icon_position = isset($item['icon_position']) ? $item['icon_position'] : 'before';
        $link = isset($item['link']) ? $item['link'] : [];
        $item_class = isset($item['item_class']) && $item['item_class'] ? ' ' . esc_attr($item['item_class']) : '';

        $link_url = ! empty($link['url']) ? esc_url($link['url']) : '';
        $target_attr = ! empty($link['is_external']) ? ' target="_blank"' : '';
        $rel = [];
        if (! empty($link['nofollow'])) {
          $rel[] = 'nofollow';
        }
        if (! empty($link['is_external'])) {
          $rel[] = 'noopener';
        }
        $rel_attr = ! empty($rel) ? ' rel="' . esc_attr(implode(' ', $rel)) . '"' : '';
        ?>
        <li class="gc-simple-list__item<?php echo $item_class; ?>">
          <?php if ($link_url) : ?>
            <a class="gc-simple-list__link" href="<?php echo $link_url; ?>"<?php echo $target_attr . $rel_attr; ?>>
          <?php else : ?>
            <span class="gc-simple-list__text">
          <?php endif; ?>

          <?php if ($has_icon && $icon_position === 'before') : ?>
            <span class="gc-simple-list__icon gc-simple-list__icon--before">
              <?php Icons_Manager::render_icon($icon, ['aria-hidden' => 'true']); ?>
            </span>
          <?php endif; ?>

          <span class="gc-simple-list__label"><?php echo esc_html($text); ?></span>

          <?php if ($has_icon && $icon_position === 'after') : ?>
            <span class="gc-simple-list__icon gc-simple-list__icon--after">
              <?php Icons_Manager::render_icon($icon, ['aria-hidden' => 'true']); ?>
            </span>
          <?php endif; ?>

          <?php if ($link_url) : ?>
            </a>
          <?php else : ?>
            </span>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
    <?php
  }
}
