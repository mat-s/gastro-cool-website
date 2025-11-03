<?php
/**
 * Theme setup: textdomain & supports
 */

add_action( 'after_setup_theme', function() {
    // Load translations
    load_child_theme_textdomain( 'gastro-cool-theme', get_stylesheet_directory() . '/languages' );

    // Common theme supports
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script' ] );
} );
