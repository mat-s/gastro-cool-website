<?php
/**
 * Admin flag "updated" (ja/nein).
 * Default: nein (nicht bearbeitet).
 *
 * Implemented as a local ACF field for easy removal later.
 */

const GASTRO_COOL_UPDATED_META_KEY          = 'gc_updated';
const GASTRO_COOL_UPDATED_META_KEY_OLD      = 'gc_edit_status';  // from previous iteration
const GASTRO_COOL_UPDATED_META_KEY_OLDER    = '_gc_edit_status'; // legacy from pre-ACF version
const GASTRO_COOL_UPDATED_NO                = 'no';
const GASTRO_COOL_UPDATED_YES               = 'yes';

/**
 * Helper to get sanitized status with default fallback.
 */
function gastro_cool_get_edit_status( $post_id ) {
    $status = get_post_meta( $post_id, GASTRO_COOL_UPDATED_META_KEY, true );

    // Fallback to legacy meta keys if needed
    if ( '' === $status ) {
        $status = get_post_meta( $post_id, GASTRO_COOL_UPDATED_META_KEY_OLD, true );
    }
    if ( '' === $status ) {
        $status = get_post_meta( $post_id, GASTRO_COOL_UPDATED_META_KEY_OLDER, true );
    }

    if ( ! in_array( $status, [ GASTRO_COOL_UPDATED_NO, GASTRO_COOL_UPDATED_YES ], true ) ) {
        return GASTRO_COOL_UPDATED_NO;
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
        'key'                   => 'group_gc_updated_flag',
        'title'                 => __( 'Updated', 'gastro-cool' ),
        'fields'                => [
            [
                'key'               => 'field_gc_updated_flag',
                'label'             => __( 'Updated', 'gastro-cool' ),
                'name'              => GASTRO_COOL_UPDATED_META_KEY,
                'type'              => 'button_group',
                'choices'           => [
                    GASTRO_COOL_UPDATED_NO  => __( 'Nein', 'gastro-cool' ),
                    GASTRO_COOL_UPDATED_YES => __( 'Ja', 'gastro-cool' ),
                ],
                'default_value'     => GASTRO_COOL_UPDATED_NO,
                'layout'            => 'horizontal',
                'return_format'     => 'value',
                'wrapper'           => [
                    'width' => '',
                ],
                'instructions'      => __( 'Flag "updated": Ist der Beitrag überarbeitet?', 'gastro-cool' ),
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
            $new_columns['gastro_cool_edit_status'] = __( 'Updated', 'gastro-cool' );
        }
    }

    return $new_columns;
} );

add_action( 'manage_posts_custom_column', function( $column, $post_id ) {
    if ( 'gastro_cool_edit_status' !== $column ) {
        return;
    }

    $status = gastro_cool_get_edit_status( $post_id );

    $label = ( GASTRO_COOL_UPDATED_YES === $status ) ? __( 'Ja', 'gastro-cool' ) : __( 'Nein', 'gastro-cool' );
    $class = ( GASTRO_COOL_UPDATED_YES === $status ) ? 'gc-updated-yes' : 'gc-updated-no';

    printf(
        '<span class="gc-updated-flag %s">%s</span>',
        esc_attr( $class ),
        esc_html( $label )
    );
}, 10, 2 );

/**
 * Minimal styling for the list column.
 */
add_action( 'admin_head', function() {
    ?>
    <style>
        .column-gastro_cool_edit_status { width: 120px; }
        .gc-updated-flag {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            color: #1d2327;
            background: #e1e3e8;
        }
        .gc-updated-yes {
            color: #0f5132;
            background: #d1e7dd;
            border: 1px solid #badbcc;
        }
        .gc-updated-no {
            color: #842029;
            background: #f8d7da;
            border: 1px solid #f5c2c7;
        }
    </style>
    <?php
} );

/**
 * Make column sortable.
 */
add_filter( 'manage_edit-post_sortable_columns', function( $columns ) {
    $columns['gastro_cool_edit_status'] = 'gastro_cool_edit_status';
    return $columns;
} );

add_action( 'pre_get_posts', function( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }

    // Sorting
    if ( $query->get( 'orderby' ) === 'gastro_cool_edit_status' ) {
        $query->set( 'meta_key', GASTRO_COOL_UPDATED_META_KEY );
        $query->set( 'orderby', 'meta_value' );
    }

    // Filtering
    $filter_value = isset( $_GET['gc_updated_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['gc_updated_filter'] ) ) : '';
    if ( in_array( $filter_value, [ GASTRO_COOL_UPDATED_YES, GASTRO_COOL_UPDATED_NO ], true ) ) {
        $meta_query = (array) $query->get( 'meta_query' );
        $meta_query[] = [
            'key'     => GASTRO_COOL_UPDATED_META_KEY,
            'value'   => $filter_value,
            'compare' => '=',
        ];
        $query->set( 'meta_query', $meta_query );
    }
} );

/**
 * Add filter dropdown above posts list.
 */
add_action( 'restrict_manage_posts', function( $post_type ) {
    if ( 'post' !== $post_type ) {
        return;
    }

    $current = isset( $_GET['gc_updated_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['gc_updated_filter'] ) ) : '';
    ?>
    <select name="gc_updated_filter">
        <option value=""><?php esc_html_e( 'Alle Updated-Status', 'gastro-cool' ); ?></option>
        <option value="<?php echo esc_attr( GASTRO_COOL_UPDATED_YES ); ?>" <?php selected( $current, GASTRO_COOL_UPDATED_YES ); ?>><?php esc_html_e( 'Ja', 'gastro-cool' ); ?></option>
        <option value="<?php echo esc_attr( GASTRO_COOL_UPDATED_NO ); ?>" <?php selected( $current, GASTRO_COOL_UPDATED_NO ); ?>><?php esc_html_e( 'Nein', 'gastro-cool' ); ?></option>
    </select>
    <?php
} );
