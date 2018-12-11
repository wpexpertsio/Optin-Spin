<?php

Class Optin_Wheels {

    function __construct() {
        add_action( 'init', array($this,'optinspin_multiwheel_posttype') );
        add_action( 'add_meta_boxes', array($this,'optinspin_register_meta_boxes') );
        add_action( 'save_post', array($this,'optinspin_save_meta_box') );
    }

    function optinspin_register_meta_boxes() {
        add_meta_box( 'optinspin-shortcode', __( 'OptinSpin ShortCode', 'optinspin' ), array($this,'optinspin_my_display_callback'), 'optin-wheels','side' );
    }

    function optinspin_my_display_callback( $post ) {
        if( isset($_GET['post']) && !empty($_GET['post']) ){

            echo '<span class="shortcode"><input type="text" onfocus="this.select();" readonly="readonly"
                value="[optinspin wheel_id='.$_GET['post'].']"
                class="large-text code"></span>';

            echo '<p>1. Only single shortcode can be used on a page</p>';
            echo '<p>2. Slider will not work on pages where shortcode is being used';

        } else {

            echo __( '<p>Save wheel to get shortcode</p>','wpcs');

        }

        /*$html = '<span class="shortcode wp-ui-highlight"><input type="text" id="optinspin-shortcide" onfocus="this.select();" readonly="readonly" class="large-text code" value="[contact-form-7 id=&quot;158&quot; title=&quot;Contact form 1&quot;]"></span>';
        echo $html;*/
    }

    function optinspin_save_meta_box( $post_id ) {
        // Save logic goes here. Don't forget to include nonce checks!
    }

    function optinspin_multiwheel_posttype() {
        $labels = array(
            'name'               => _x( 'Optin Wheels', 'post type general name', 'optinspin' ),
            'singular_name'      => _x( 'Optin Wheel', 'post type singular name', 'optinspin' ),
            'menu_name'          => _x( 'Optin Wheels', 'admin menu', 'optinspin' ),
            'name_admin_bar'     => _x( 'Optin Wheel', 'add new on admin bar', 'optinspin' ),
            'add_new'            => _x( 'Add New Wheel', 'Optin Wheel', 'optinspin' ),
            'add_new_item'       => __( 'Add New Optin Wheel', 'optinspin' ),
            'new_item'           => __( 'New Optin Wheel', 'optinspin' ),
            'edit_item'          => __( 'Edit Optin Wheel', 'optinspin' ),
            'view_item'          => __( 'View Optin Wheel', 'optinspin' ),
            'all_items'          => __( 'All Optin Wheels', 'optinspin' ),
            'search_items'       => __( 'Search Optin Wheels', 'optinspin' ),
            'parent_item_colon'  => __( 'Parent Optin Wheels:', 'optinspin' ),
            'not_found'          => __( 'No Optin Wheels found.', 'optinspin' ),
            'not_found_in_trash' => __( 'No Optin Wheels found in Trash.', 'optinspin' )
        );

        $args = array(
            'labels'             => $labels,
            'description'        => __( 'Description.', 'optinspin' ),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'optin-wheels' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'           => 'dashicons-marker',
            'supports'           => array( 'title', 'author')
        );

        register_post_type( 'optin-wheels', $args );
    }
}