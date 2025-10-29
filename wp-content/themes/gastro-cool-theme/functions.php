<?php
/**
 * Functions for the Astra child theme "Gastro Cool Theme".
 */

// Astra already enqueues the parent CSS. We only enqueue the child CSS.
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

// Load theme setup
require_once get_stylesheet_directory() . '/inc/setup.php';

/**
 * Allow SVG uploads (with proper MIME/ext detection).
 * Note: SVGs can contain active code. Only upload trusted files.
 */
add_filter( 'upload_mimes', function( $mimes ) {
    // Allow SVG and SVGZ
    $mimes['svg']  = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
} );

// Ensure WordPress detects extension/MIME correctly
add_filter( 'wp_check_filetype_and_ext', function( $data, $file, $filename, $mimes ) {
    $filetype = wp_check_filetype( $filename, $mimes );

    if ( in_array( $filetype['ext'], array( 'svg', 'svgz' ), true ) ) {
        $data['ext']  = $filetype['ext'];
        $data['type'] = 'image/svg+xml';
    }

    return $data;
}, 10, 4 );

// Adjust SVG preview/thumbnails in admin
add_action( 'admin_head', function() {
    echo '<style>
        img[src$=".svg"].attachment-post-thumbnail,
        .media-icon img[src$=".svg"] {
            width: 100% !important;
            height: auto !important;
        }
    </style>';
} );
