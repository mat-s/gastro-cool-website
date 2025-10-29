<?php
/**
 * Theme Setup: Textdomain & Supports
 */

add_action( 'after_setup_theme', function() {
    // Übersetzungen laden
    load_child_theme_textdomain( 'gastro-cool-theme', get_stylesheet_directory() . '/languages' );

    // Optional: verbreitete Theme-Supports
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script' ] );
} );

