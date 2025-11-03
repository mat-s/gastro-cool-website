<?php
/**
 * Disable all comment functionality and remove WordPress emojis.
 */

// Remove emoji scripts, styles, filters, and editor plugin
add_action( 'init', function() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
} );

add_filter( 'tiny_mce_plugins', function( $plugins ) {
    if ( is_array( $plugins ) ) {
        return array_diff( $plugins, array( 'wpemoji' ) );
    }
    return array();
} );

add_filter( 'wp_resource_hints', function( $urls, $relation_type ) {
    if ( 'dns-prefetch' === $relation_type ) {
        $emoji_cdn = 'https://s.w.org/images/core/emoji/';
        $urls = array_filter( (array) $urls, function( $url ) use ( $emoji_cdn ) {
            return strpos( $url, $emoji_cdn ) === false;
        } );
    }
    return $urls;
}, 10, 2 );

// Remove comment and trackback support from all post types
add_action( 'admin_init', function() {
    foreach ( get_post_types() as $post_type ) {
        if ( post_type_supports( $post_type, 'comments' ) ) {
            remove_post_type_support( $post_type, 'comments' );
            remove_post_type_support( $post_type, 'trackbacks' );
        }
    }
} );

// Close comments and pings on the front end
add_filter( 'comments_open', '__return_false', 20, 2 );
add_filter( 'pings_open', '__return_false', 20, 2 );

// Hide existing comments
add_filter( 'comments_array', '__return_empty_array', 10, 2 );

// Remove Comments page from the admin menu
add_action( 'admin_menu', function() {
    remove_menu_page( 'edit-comments.php' );
} );

// Redirect users away from the comments admin screen
add_action( 'admin_init', function() {
    global $pagenow;
    if ( 'edit-comments.php' === $pagenow ) {
        wp_safe_redirect( admin_url() );
        exit;
    }
} );

// Remove comments metabox from the dashboard
add_action( 'admin_init', function() {
    remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
} );

// Remove comments from the admin bar
add_action( 'init', function() {
    if ( is_admin_bar_showing() ) {
        remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
    }
} );

