<?php
/*
Plugin Name: Goliath Post Terms Order
Description: Sort Taxonomy Terms per Post based using a Drag and Drop Sortable
Author: Studio Goliath
Author URI: https://www.studio-goliath.com
Version: 1.0.0
*/


add_action( 'admin_menu', 'sg_sortable_tax_add_remove_meta_boxes' );

function sg_sortable_tax_add_remove_meta_boxes() {

    // Get all category with the sort paramaters set to true
    $sortable_taxo = get_taxonomies( array( 'sort' => true ), 'objects'  );

    foreach ( $sortable_taxo as $taxo ){

        if( $taxo->hierarchical ){

            remove_meta_box( "{$taxo->name}div", $taxo->object_type, 'side');

        } else{

            remove_meta_box( "tagsdiv-{$taxo->name}", $taxo->object_type, 'side');
        }

        add_meta_box( "{$taxo->name}-sortable", $taxo->label, 'sg_sortable_tax_meta_boxes', $taxo->object_type, 'side', 'default', $taxo );

    }


}


function sg_sortable_tax_meta_boxes( $post, $param ){

    $taxo = $param['args'];

    $post_terms =  wp_get_object_terms( $post->ID, $taxo->name, array( 'orderby' => 'term_order' ) );
    $post_terms_id = wp_list_pluck( $post_terms, 'term_id' );

    $terms = get_terms(array(
        'taxonomy'          => $taxo->name,
    ) );

    if( $terms && ! is_wp_error( $terms ) ){

        echo "<select id='{$taxo->name}' name='sg-sortable-{$taxo->name}' class='sortable-tax-select' multiple='multiple' style='width: 100%;' aria-hidden='true'>";

        foreach ( $terms as $term ){

            $disabled = in_array( $term->term_id, $post_terms_id ) ? 'disabled="disabled"' : '';
            echo "<option value='{$term->term_id}' {$disabled}>{$term->name}</option>";
        }
        echo '</select>';
    }

    echo '<ul class="terms-draggable">';

    foreach ( $post_terms as $term ){

        echo '<li>';
        echo "<input type='hidden' name='sg-terms-sort[{$taxo->name}][]' value='{$term->term_id}' />";
        echo '<span class="dashicons dashicons-sort"></span>';
        echo "<span class='text'>{$term->name}</span>";
        echo '<span class="dashicons dashicons-dismiss"></span>';
        echo '</li>';
    }
    echo '</ul>';

    wp_nonce_field( "sg-terms-sort-action", 'sg-terms-sort-nonce-name' );
}


add_action( 'admin_enqueue_scripts', 'sg_sortable_tax_add_admin_script' );

function sg_sortable_tax_add_admin_script( $hook ) {

    wp_register_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css', array(), '4.0.3' );
    wp_register_style( 'sg_sortable_tax_style', plugin_dir_url( __FILE__ ) . 'css/admin-styles.css', array(), '1.0.0' );

    wp_register_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array( 'jquery'), '4.0.3', true );


    wp_register_script( 'sg_sortable_tax_scripts', plugin_dir_url( __FILE__ ) . 'js/admin-scripts.js', array( 'select2', 'jquery-ui-sortable' ), '1.0.0', true );

    $sortable_taxo = get_taxonomies( array( 'sort' => true ), 'objects'  );

    $screen = get_current_screen();

    foreach ( $sortable_taxo as $taxo ){

        if( in_array( $hook, array( 'post-new.php', 'post.php' ) ) && in_array( $screen->post_type, $taxo->object_type ) ){
            wp_enqueue_style( 'sg_sortable_tax_style' );
            wp_enqueue_script( 'sg_sortable_tax_scripts' );
        }
    }
}

add_action( 'save_post', 'sg_save_taxo_sortable_metabox' );

function sg_save_taxo_sortable_metabox( $post_id ){

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if( isset( $_POST['sg-terms-sort'] ) && check_admin_referer( "sg-terms-sort-action", 'sg-terms-sort-nonce-name' ) ) {
        $sg_terms_sort = $_POST['sg-terms-sort'];

        foreach( $sg_terms_sort as $taxo => $terms ){

            $clean_terms = array();
            foreach ( $terms as $term ){
                $clean_terms[] = is_numeric( $term ) ? intval( $term ) : $term;
            }

            wp_set_object_terms( $post_id, $clean_terms, $taxo );
        }

    }
}