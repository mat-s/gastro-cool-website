<?php

namespace GastroCoolTheme\Widgets;

if (! defined('ABSPATH')) {
  exit;
}

/**
 * Widget Manager for custom Elementor widgets.
 */
class WidgetManager
{

  /**
   * Initialize widget manager.
   *
   * Hooks into Elementor to register categories and widgets.
   *
   * @return void
   */
  public function __construct()
  {
    add_action('elementor/widgets/register', [$this, 'register_widgets']);
    add_action('elementor/elements/categories_registered', [$this, 'register_widget_categories']);

    // Backward compatibility for older Elementor versions
    add_action('elementor/widgets/widgets_registered', [$this, 'register_widgets']);
  }

  /**
   * Register custom widget categories.
   *
   * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager instance.
   * @return void
   */
  public function register_widget_categories($elements_manager)
  {
    $categories = [];
    $categories['gastro-cool'] =
      [
        'title' => esc_html__('Gastro Cool', 'gastro-cool-theme'),
        'icon'  => 'fa fa-plug',
      ];
    $old_categories = $elements_manager->get_categories();
    $categories = array_merge($categories, $old_categories);
    $set_categories = function ($categories) {
      $this->categories = $categories;
    };
    $set_categories->call($elements_manager, $categories);
  }

  /**
   * Register custom widgets.
   *
   * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager instance.
   * @return void
   */
  public function register_widgets($widgets_manager)
  {
    // Simple List widget
    require_once get_stylesheet_directory() . '/widgets/class-simple-list.php';
    if (class_exists('\GastroCoolTheme\Widgets\Simple_List')) {
      $widgets_manager->register(new \GastroCoolTheme\Widgets\Simple_List());
    }
  }
}
