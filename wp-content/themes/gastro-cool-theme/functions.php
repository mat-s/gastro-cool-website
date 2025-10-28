<?php
/**
 * Funktionen für das Astra Child Theme "Gastro Cool Theme".
 */

// Parent- und Child-Styles korrekt laden
add_action( 'wp_enqueue_scripts', 'gastro_cool_enqueue_styles', 15 );
function gastro_cool_enqueue_styles() {
    $parent_handle = 'astra-parent-style';

    // Parent-Stylesheet (Astra)
    wp_enqueue_style(
        $parent_handle,
        get_template_directory_uri() . '/style.css',
        array(),
        function_exists( 'wp_get_theme' ) ? wp_get_theme( 'astra' )->get( 'Version' ) : null
    );

    // Child-Stylesheet
    wp_enqueue_style(
        'gastro-cool-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_handle ),
        function_exists( 'wp_get_theme' ) ? wp_get_theme()->get( 'Version' ) : null
    );
}

