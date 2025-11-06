<?php
/**
 * Navigation/Menu filters and helpers.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Append an extra link to a specific menu's last sub-menu.
 *
 * This is primarily used to add an additional entry to the language switcher
 * without customizing theme CSS/JS. It targets a specific rendered menu
 * container id as produced by Elementor's WordPress Menu widget.
 *
 * The targeted menu id and link output are filterable:
 * - gastro_cool_extra_language_menu_id (string)
 * - gastro_cool_extra_language_item_html (string)
 *
 * @param string   $items Menu items HTML.
 * @param stdClass $args  Menu arguments object.
 * @return string  Modified menu items HTML.
 */
function gastro_cool_append_extra_language_link( $items, $args ) {
    // Target a specific menu container id (can be overridden via filter).
    $target_menu_id = apply_filters( 'gastro_cool_extra_language_menu_id', 'menu-1-045042e' );

    if ( isset( $args->menu_id ) && $args->menu_id === $target_menu_id ) {
        // Default extra item HTML; can be overridden via filter.
        $extra_item  = '<li class="menu-item gc-extra-link">';
        $extra_item .= '<a href="#zusaetzliche-sprachen" class="elementor-sub-item menu-link">Weitere Sprachen</a>';
        $extra_item .= '</li>';

        $extra_item = apply_filters( 'gastro_cool_extra_language_item_html', $extra_item, $args );

        // Inject at the end of the last sub-menu list.
        $pattern = '/(<ul[^>]*class=\"[^\"]*sub-menu[^\"]*\"[^>]*>)(.*?)(<\/ul>)/is';
        $items   = preg_replace_callback( $pattern, function( $matches ) use ( $extra_item ) {
            return $matches[1] . $matches[2] . $extra_item . $matches[3];
        }, $items );
    }

    return $items;
}

add_filter( 'wp_nav_menu_items', 'gastro_cool_append_extra_language_link', 10, 2 );

