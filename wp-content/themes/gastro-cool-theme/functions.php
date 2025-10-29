<?php
/**
 * Funktionen für das Astra Child Theme "Gastro Cool Theme".
 */

// Astra lädt das Parent-CSS bereits selbst. Wir hängen nur das Child-CSS an.
add_action( 'wp_enqueue_scripts', 'gastro_cool_enqueue_styles', 15 );
function gastro_cool_enqueue_styles() {
    $style_path = get_stylesheet_directory() . '/style.css';
    wp_enqueue_style(
        'gastro-cool-style',
        get_stylesheet_uri(),
        array( 'astra-theme-css' ),
        file_exists( $style_path ) ? filemtime( $style_path ) : null
    );
}

// Theme-Setup auslagern
require_once get_stylesheet_directory() . '/inc/setup.php';
