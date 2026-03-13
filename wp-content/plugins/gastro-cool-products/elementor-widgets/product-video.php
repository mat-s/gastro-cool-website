<?php

namespace GCP\Elementor\Widgets;

if (! defined('ABSPATH')) {
  exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Product_Video_Widget extends Widget_Base
{
  public function get_name() {
    return 'gcp_product_video';
  }

  public function get_title() {
    return __('Produktvideo', 'gastro-cool-products');
  }

  public function get_icon() {
    return 'eicon-youtube';
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
        'default'     => __('Produktvideo', 'gastro-cool-products'),
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
        'default' => ['value' => 'fas fa-video', 'library' => 'fa-solid'],
      ]
    );

    $this->end_controls_section();

    // ── Einstellungen ─────────────────────────────────────────────────────
    $this->start_controls_section(
      'section_settings',
      [
        'label' => __('Einstellungen', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
      'empty_text',
      [
        'label'   => __('Text bei fehlendem Video', 'gastro-cool-products'),
        'type'    => Controls_Manager::TEXT,
        'default' => __('Kein Video verfügbar', 'gastro-cool-products'),
      ]
    );

    $this->add_control(
      'show_title',
      [
        'label'        => __('Videotitel anzeigen', 'gastro-cool-products'),
        'type'         => Controls_Manager::SWITCHER,
        'label_on'     => __('Ja', 'gastro-cool-products'),
        'label_off'    => __('Nein', 'gastro-cool-products'),
        'return_value' => 'yes',
        'default'      => 'yes',
      ]
    );

    $this->add_control(
      'privacy_enhanced',
      [
        'label'        => __('Datenschutzmodus (youtube-nocookie.com)', 'gastro-cool-products'),
        'type'         => Controls_Manager::SWITCHER,
        'label_on'     => __('Aktiv', 'gastro-cool-products'),
        'label_off'    => __('Inaktiv', 'gastro-cool-products'),
        'return_value' => 'yes',
        'default'      => 'yes',
      ]
    );

    $this->end_controls_section();
  }

  // ── Helper: collect valid videos from ACF ────────────────────────────
  private function get_videos(): array {
    if (! function_exists('get_field')) {
      return [];
    }

    $raw = get_field('videos');
    if (! is_array($raw) || empty($raw)) {
      return [];
    }

    $videos = [];
    foreach ($raw as $row) {
      $youtube_id = trim($row['youtube_id'] ?? '');
      $video_file = $row['video_file']      ?? null;
      $title      = trim($row['title']      ?? '');

      if ($youtube_id !== '' || ! empty($video_file)) {
        $videos[] = [
          'youtube_id' => $youtube_id,
          'video_file' => $video_file,
          'title'      => $title,
        ];
      }
    }

    return $videos;
  }

  // ── Helper: render YouTube iframe ────────────────────────────────────
  private function render_youtube( string $youtube_id, bool $privacy, string $title ): void {
    $domain = $privacy ? 'www.youtube-nocookie.com' : 'www.youtube.com';
    $src    = 'https://' . $domain . '/embed/' . rawurlencode($youtube_id)
            . '?rel=0&modestbranding=1';

    echo '<div class="gc-product-video__ratio">';
    echo '<iframe'
      . ' class="gc-product-video__iframe"'
      . ' src="' . esc_url($src) . '"'
      . ' title="' . esc_attr($title !== '' ? $title : __('Produktvideo', 'gastro-cool-products')) . '"'
      . ' allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"'
      . ' allowfullscreen'
      . ' loading="lazy"'
      . '></iframe>';
    echo '</div>';
  }

  // ── Helper: render HTML5 video ────────────────────────────────────────
  private function render_video_file( $video_file, string $title ): void {
    // video_file may be an attachment ID (int) or array
    $url = '';
    if (is_array($video_file) && ! empty($video_file['url'])) {
      $url = $video_file['url'];
    } elseif (is_numeric($video_file)) {
      $url = wp_get_attachment_url((int) $video_file);
    }

    if (! $url) {
      return;
    }

    echo '<div class="gc-product-video__ratio">';
    echo '<video class="gc-product-video__html5" controls preload="metadata"'
      . ($title !== '' ? ' aria-label="' . esc_attr($title) . '"' : '') . '>';
    echo '<source src="' . esc_url($url) . '">';
    echo '</video>';
    echo '</div>';
  }

  // ── Helper: empty state ───────────────────────────────────────────────
  private function render_empty( string $text ): void {
    echo '<div class="gc-product-video__ratio gc-product-video__ratio--empty">';
    echo '<div class="gc-product-video__empty">';
    echo '<svg class="gc-product-video__play-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="5 3 19 12 5 21 5 3"/></svg>';
    echo '<span>' . esc_html($text) . '</span>';
    echo '</div>';
    echo '</div>';
  }

  protected function render() {
    $settings      = $this->get_settings_for_display();
    $heading       = trim($settings['heading']       ?? '');
    $heading_tag   = $settings['heading_tag']         ?? 'h2';
    $heading_icon  = $settings['heading_icon']        ?? [];
    $empty_text    = trim($settings['empty_text']    ?? __('Kein Video verfügbar', 'gastro-cool-products'));
    $show_title    = ($settings['show_title']        ?? '') === 'yes';
    $privacy       = ($settings['privacy_enhanced']  ?? '') === 'yes';

    $allowed_tags = ['h2', 'h3', 'h4', 'h5', 'h6', 'div'];
    if (! in_array($heading_tag, $allowed_tags, true)) {
      $heading_tag = 'h2';
    }

    $videos = $this->get_videos();

    if (empty($videos)) {
      return;
    }

    echo '<div class="gc-product-video">';

    // Heading
    if ($heading !== '') {
      echo '<' . esc_attr($heading_tag) . ' class="gc-product-video__heading">';
      if (! empty($heading_icon['value'])) {
        echo '<span class="gc-product-video__heading-icon" aria-hidden="true">';
        \Elementor\Icons_Manager::render_icon($heading_icon, ['aria-hidden' => 'true']);
        echo '</span>';
      }
      echo esc_html($heading);
      echo '</' . esc_attr($heading_tag) . '>';
    }

    // Card
    echo '<div class="gc-product-video__card">';

    {
      // Render all videos; if multiple, show tabs header first
      $has_multiple = count($videos) > 1;

      if ($has_multiple) {
        echo '<div class="gc-product-video__tabs" role="tablist">';
        foreach ($videos as $index => $video) {
          $tab_label = $video['title'] !== ''
            ? $video['title']
            : sprintf(__('Video %d', 'gastro-cool-products'), $index + 1);
          echo '<button'
            . ' class="gc-product-video__tab' . ($index === 0 ? ' is-active' : '') . '"'
            . ' type="button"'
            . ' role="tab"'
            . ' aria-selected="' . ($index === 0 ? 'true' : 'false') . '"'
            . ' data-index="' . esc_attr($index) . '"'
            . '>' . esc_html($tab_label) . '</button>';
        }
        echo '</div>';
      }

      foreach ($videos as $index => $video) {
        $panel_class = 'gc-product-video__panel';
        if ($has_multiple) {
          $panel_class .= $index === 0 ? ' is-active' : ' is-hidden';
        }

        echo '<div class="' . esc_attr($panel_class) . '" data-index="' . esc_attr($index) . '">';

        if ($video['youtube_id'] !== '') {
          $this->render_youtube($video['youtube_id'], $privacy, $video['title']);
        } else {
          $this->render_video_file($video['video_file'], $video['title']);
        }

        if ($show_title && $video['title'] !== '') {
          echo '<p class="gc-product-video__title">' . esc_html($video['title']) . '</p>';
        }

        echo '</div>';
      }
    }

    echo '</div>'; // .gc-product-video__card

    echo '</div>'; // .gc-product-video
  }
}
