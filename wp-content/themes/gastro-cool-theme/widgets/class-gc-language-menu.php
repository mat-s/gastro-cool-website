<?php

/**
 * Elementor widget: WPML Language Menu with extra links.
 */

if (! defined('ABSPATH')) {
  exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Icons_Manager;

class GC_Language_Menu_Widget extends Widget_Base
{

  /**
   * Unique widget identifier.
   *
   * @return string
   */
  public function get_name()
  {
    return 'gc-language-menu';
  }

  /**
   * Widget display title shown in Elementor.
   *
   * @return string
   */
  public function get_title()
  {
    return __('Language Menu', 'gastro-cool-theme');
  }

  /**
   * Widget icon for Elementor panel.
   *
   * @return string
   */
  public function get_icon()
  {
    // Use an existing Elementor icon
    return 'eicon-globe';
  }

  /**
   * Elementor categories this widget belongs to.
   *
   * @return array
   */
  public function get_categories()
  {
    return ['gastro-cool'];
  }

  /**
   * Register widget controls (content/settings).
   *
   * @return void
   */
  protected function register_controls()
  {
    $this->start_controls_section('section_content', [
      'label' => __('Content', 'gastro-cool-theme'),
    ]);

    // Optional: attach to an existing WordPress menu for styling/behavior
    $menus = [];
    foreach (wp_get_nav_menus() as $menu_obj) {
      $menus[$menu_obj->term_id] = $menu_obj->name;
    }
    $this->add_control('menu_id', [
      'label' => __('Attach to WP Menu', 'gastro-cool-theme'),
      'type'  => Controls_Manager::SELECT,
      'options' => $menus,
      'default' => '',
      'description' => __('If set, the languages and extra links will be appended to this menu so existing theme styles apply.', 'gastro-cool-theme'),
    ]);

    $repeater = new Repeater();
    $repeater->add_control('label', [
      'label' => __('Label', 'gastro-cool-theme'),
      'type' => Controls_Manager::TEXT,
      'default' => '',
      'label_block' => true,
    ]);
    $repeater->add_control('url', [
      'label' => __('URL', 'gastro-cool-theme'),
      'type' => Controls_Manager::URL,
      'placeholder' => 'https://',
      'show_external' => true,
      'default' => ['url' => '', 'is_external' => false, 'nofollow' => false],
    ]);

    $this->add_control('extra_links', [
      'label' => __('Extra Links', 'gastro-cool-theme'),
      'type' => Controls_Manager::REPEATER,
      'fields' => $repeater->get_controls(),
      'title_field' => '{{{ label }}}',
    ]);

    $this->end_controls_section();
  }

  /**
   * Render the widget output on the frontend.
   *
   * @return void
   */
  protected function render()
  {
    // Pull languages from WPML. If WPML is not active, show a friendly note in editor.
    $languages = apply_filters('wpml_active_languages', null, [
      'skip_missing' => 0,
      'orderby'      => 'code',
    ]);

    $settings  = $this->get_settings_for_display();
    $widget_id = 'gc-ls-' . $this->get_id();

    // Determine current language code/label
    $current_code  = '';
    $current_label = '';
    if (is_array($languages)) {
      foreach ($languages as $code => $lang) {
        if (! empty($lang['active'])) {
          $current_code  = $code;
          $current_label = strtoupper($lang['language_code'] ?? $code);
          break;
        }
      }
      if (! $current_label && ! empty($languages)) {
        // Fallback to first language label
        $first = reset($languages);
        $current_label = strtoupper($first['language_code'] ?? 'LANG');
      }
    }

    // If a WP menu is chosen, append language + extra items to that menu via filter
    if ( ! empty( $settings['menu_id'] ) ) {
      $menu_id = (int) $settings['menu_id'];

      // Build one parent item with a sub-menu (matches theme dropdown styles)
      $build_items = function() use ( $languages, $settings, $current_label ) {
        $parent  = '<li class="menu-item menu-item-has-children wpml-ls-item wpml-ls-current-language">';
        $parent .= '<a href="#" class="wpml-ls-link" aria-current="true">' . esc_html( $current_label ?: 'LANG' ) . '</a>';
        $parent .= '<ul class="sub-menu">';

        // Languages (exclude current)
        if ( is_array( $languages ) ) {
          foreach ( $languages as $code => $lang ) {
            if ( ! empty( $lang['active'] ) ) { continue; }
            $url   = isset( $lang['url'] ) ? $lang['url'] : '';
            $label = strtoupper( $lang['language_code'] ?? $code );
            $parent .= '<li class="menu-item wpml-ls-item">'
                    .  '<a class="wpml-ls-link" href="' . esc_url( $url ) . '" hreflang="' . esc_attr( $code ) . '" lang="' . esc_attr( $code ) . '">' . esc_html( $label ) . '</a>'
                    .  '</li>';
          }
        }

        // Extra links
        if ( ! empty( $settings['extra_links'] ) && is_array( $settings['extra_links'] ) ) {
          foreach ( $settings['extra_links'] as $item ) {
            $label       = isset( $item['label'] ) ? $item['label'] : '';
            $url         = isset( $item['url']['url'] ) ? $item['url']['url'] : '';
            $is_external = ! empty( $item['url']['is_external'] );
            $nofollow    = ! empty( $item['url']['nofollow'] );
            if ( $label && $url ) {
              $target_attr = $is_external ? ' target="_blank"' : '';
              $rel_parts   = [];
              if ( $is_external ) { $rel_parts[] = 'noopener'; }
              if ( $nofollow )    { $rel_parts[] = 'nofollow'; }
              $rel_attr = $rel_parts ? ' rel="' . esc_attr( implode( ' ', $rel_parts ) ) . '"' : '';
              $parent  .= '<li class="menu-item wpml-ls-item gc-extra-link">'
                        .  '<a class="wpml-ls-link" href="' . esc_url( $url ) . '"' . $target_attr . $rel_attr . '>' . esc_html( $label ) . '</a>'
                        .  '</li>';
            }
          }
        }

        $parent .= '</ul></li>';
        return $parent;
      };

      $filter = function( $items, $args ) use ( $menu_id, $build_items ) {
        // Only affect our selected menu instance
        $is_match = false;
        if ( isset( $args->menu ) ) {
          if ( is_object( $args->menu ) && isset( $args->menu->term_id ) ) {
            $is_match = (int) $args->menu->term_id === $menu_id;
          } elseif ( is_numeric( $args->menu ) ) {
            $is_match = (int) $args->menu === $menu_id;
          }
        }
        if ( ! $is_match ) { return $items; }
        return $items . $build_items();
      };

      add_filter( 'wp_nav_menu_items', $filter, 10, 2 );
      $menu_html = wp_nav_menu( [
        'menu'            => $menu_id,
        'container'       => '',
        'echo'            => false,
        'fallback_cb'     => false,
        'depth'           => 2,
      ] );
      remove_filter( 'wp_nav_menu_items', $filter, 10 );

      // Output the menu as-is to inherit theme styles/behaviors
      echo $menu_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      return;
    }

    // Templated HTML output using WPML classes so we inherit styles; single <ul>
?>
    <div class="wpml-ls wpml-ls-legacy-dropdown gc-lang-switcher" id="<?php echo esc_attr($widget_id); ?>">
      <ul class="wpml-ls-list">
        <?php if ($current_label) : ?>
          <li class="wpml-ls-item wpml-ls-current-language"><span class="wpml-ls-link" aria-current="true"><?php echo esc_html($current_label); ?></span></li>
        <?php endif; ?>

        <?php if (is_array($languages)) : ?>
          <?php foreach ($languages as $code => $lang) :
            $is_active = ! empty($lang['active']);
            $url       = isset($lang['url']) ? $lang['url'] : '';
            $label     = strtoupper($lang['language_code'] ?? $code);
            if ($is_active) { continue; }
          ?>
            <li class="wpml-ls-item">
              <a class="wpml-ls-link" href="<?php echo esc_url($url); ?>" hreflang="<?php echo esc_attr($code); ?>" lang="<?php echo esc_attr($code); ?>"><?php echo esc_html($label); ?></a>
            </li>
          <?php endforeach; ?>
        <?php elseif (\Elementor\Plugin::$instance->editor->is_edit_mode()) : ?>
          <li class="wpml-ls-item"><span class="wpml-ls-link"><?php echo esc_html__('WPML not active — showing placeholder.', 'gastro-cool-theme'); ?></span></li>
        <?php endif; ?>

        <?php if (! empty($settings['extra_links']) && is_array($settings['extra_links'])) : ?>
          <?php foreach ($settings['extra_links'] as $item) :
            $label       = isset($item['label']) ? $item['label'] : '';
            $url         = isset($item['url']['url']) ? $item['url']['url'] : '';
            $is_external = ! empty($item['url']['is_external']);
            $nofollow    = ! empty($item['url']['nofollow']);
            $target_attr = $is_external ? ' target="_blank"' : '';
            $rel_parts   = [];
            if ($is_external) { $rel_parts[] = 'noopener'; }
            if ($nofollow)  { $rel_parts[] = 'nofollow'; }
            $rel_attr = $rel_parts ? ' rel="' . esc_attr(implode(' ', $rel_parts)) . '"' : '';
            if ($label && $url) : ?>
              <li class="wpml-ls-item gc-extra-link">
                <a class="wpml-ls-link" href="<?php echo esc_url($url); ?>"<?php echo $target_attr; ?><?php echo $rel_attr; ?>><?php echo esc_html($label); ?></a>
              </li>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
    </div>
<?php
  }
}
