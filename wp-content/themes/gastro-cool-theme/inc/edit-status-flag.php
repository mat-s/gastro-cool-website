<?php
/**
 * Admin flag to mark posts as "Überarbeitet" vs. Originalfassung.
 * Default: Originalfassung (nicht bearbeitet).
 *
 * Implemented as a local ACF field for easy removal later.
 */

const GASTRO_COOL_EDIT_STATUS_META_KEY      = 'gc_edit_status';
const GASTRO_COOL_EDIT_STATUS_META_KEY_OLD  = '_gc_edit_status'; // legacy from pre-ACF version
const GASTRO_COOL_EDIT_STATUS_ORIGINAL      = 'original';
const GASTRO_COOL_EDIT_STATUS_REVISED       = 'revised';

/**
 * Helper to get sanitized status with default fallback.
 */
function gastro_cool_get_edit_status( $post_id ) {
    $status = get_post_meta( $post_id, GASTRO_COOL_EDIT_STATUS_META_KEY, true );

    // Fallback to legacy meta key if needed
    if ( '' === $status ) {
        $status = get_post_meta( $post_id, GASTRO_COOL_EDIT_STATUS_META_KEY_OLD, true );
    }

    if ( ! in_array( $status, [ GASTRO_COOL_EDIT_STATUS_ORIGINAL, GASTRO_COOL_EDIT_STATUS_REVISED ], true ) ) {
        return GASTRO_COOL_EDIT_STATUS_ORIGINAL;
    }

    return $status;
}

/**
 * Register ACF local field group.
 */
add_action( 'acf/init', function() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( [
        'key'                   => 'group_gc_edit_status',
        'title'                 => __( 'Bearbeitungsstatus', 'gastro-cool' ),
        'fields'                => [
            [
                'key'               => 'field_gc_edit_status',
                'label'             => __( 'Bearbeitungsstatus', 'gastro-cool' ),
                'name'              => GASTRO_COOL_EDIT_STATUS_META_KEY,
                'type'              => 'button_group',
                'choices'           => [
                    GASTRO_COOL_EDIT_STATUS_ORIGINAL => __( 'Originalfassung', 'gastro-cool' ),
                    GASTRO_COOL_EDIT_STATUS_REVISED  => __( 'Überarbeitet', 'gastro-cool' ),
                ],
                'default_value'     => GASTRO_COOL_EDIT_STATUS_ORIGINAL,
                'layout'            => 'horizontal',
                'return_format'     => 'value',
                'wrapper'           => [
                    'width' => '',
                ],
                'instructions'      => __( 'Markiere, ob der Beitrag bereits überarbeitet wurde.', 'gastro-cool' ),
            ],
        ],
        'location'              => [
            [
                [
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'post',
                ],
            ],
        ],
        'position'              => 'side',
        'style'                 => 'default',
        'active'                => true,
        'show_in_rest'          => 0,
    ] );
} );

/**
 * Admin list column: show edit status at a glance.
 */
add_filter( 'manage_posts_columns', function( $columns ) {
    $new_columns = [];

    foreach ( $columns as $key => $label ) {
        $new_columns[ $key ] = $label;

        if ( 'title' === $key ) {
            $new_columns['gastro_cool_edit_status'] = __( 'Bearbeitung', 'gastro-cool' );
        }
    }

    return $new_columns;
} );

add_action( 'manage_posts_custom_column', function( $column, $post_id ) {
    if ( 'gastro_cool_edit_status' !== $column ) {
        return;
    }

    $status = gastro_cool_get_edit_status( $post_id );

    if ( GASTRO_COOL_EDIT_STATUS_REVISED === $status ) {
        echo esc_html__( 'Überarbeitet', 'gastro-cool' );
    } else {
        echo esc_html__( 'Originalfassung', 'gastro-cool' );
    }
}, 10, 2 );
