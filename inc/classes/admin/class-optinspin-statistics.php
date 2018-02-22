<?php

/**
 * Class optinspin_Statistics
 */
class optinspin_Statistics {

    function __construct() {
        add_action( 'init', array($this,'optinspin_register_statistics') );
        add_action( 'wp_ajax_optinspin_statistics', array($this,'optinspin_statistics_callback') );
        add_action( 'wp_ajax_nopriv_optinspin_statistics', array($this,'optinspin_statistics_callback') );
        add_filter( 'manage_edit-optin-statistics_columns', array($this,'optinspin_edit_columns') ) ;
        add_action( 'manage_optin-statistics_posts_custom_column', array($this,'optinspin_manage_columns'), 999, 2 );
        add_action( 'admin_menu', array($this,'optinspin_stats_menu') );
        add_filter( 'bulk_actions-edit-optin-statistics', array($this,'optinspin_bulk_edit') );
    }

    function optinspin_bulk_edit( $actions ){
        unset( $actions[ 'edit' ] );
        return $actions;
    }

    function optinspin_register_statistics() {
        $labels = array(
            'name'               => _x( 'Optin List', 'post type general name', 'optinspin' ),
            'singular_name'      => _x( 'Optin List', 'post type singular name', 'optinspin' ),
            'menu_name'          => _x( 'Optin List', 'admin menu', 'optinspin' ),
            'name_admin_bar'     => _x( 'Optin List', 'add new on admin bar', 'optinspin' ),
            'add_new'            => _x( 'Add New', 'Optin List', 'optinspin' ),
            'add_new_item'       => __( 'Add New Optin List', 'optinspin' ),
            'new_item'           => __( 'New Optin List', 'optinspin' ),
            'edit_item'          => __( 'Edit Optin List', 'optinspin' ),
            'view_item'          => __( 'View Optin List', 'optinspin' ),
            'all_items'          => __( 'All Optin List', 'optinspin' ),
            'search_items'       => __( 'Search Optin List', 'optinspin' ),
            'parent_item_colon'  => __( 'Parent Optin List:', 'optinspin' ),
            'not_found'          => __( 'No Optin List found.', 'optinspin' ),
            'not_found_in_trash' => __( 'No Optin List found in Trash.', 'optinspin' )
        );

        $args = array(
            'labels'             => $labels,
            'description'        => __( 'Description.', 'optinspin' ),
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'optin-statistics' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title' )
        );

        register_post_type( 'optin-statistics', $args );
    }

    function optinspin_statistics_callback() {

        $request_to = $_POST['request_to'];
        $coupon = $_POST['coupon'];
        $email = $_POST['email'];
        $username = $_POST['username'];

        if( empty($username) )
            $username = 'GUEST USER';

        // Create post object
        $optin_stats = array(
            'post_title'    => wp_strip_all_tags( $username ),
            'post_type'    => 'optin-statistics',
            'post_status'    => 'publish',
            'post_author'    => 0,
        );

        $stats_id = wp_insert_post( $optin_stats );

        $count = (int) get_post_meta($stats_id,$request_to,true);
        if(!empty($count))
            $count = $count + 1;
        else
            $count = 1;

        update_post_meta($stats_id,$request_to,$count);

        $spin_count = (int) get_post_meta($stats_id,'no_of_spins',true);
        if(!empty($count))
            $spin_count = $spin_count + 1;
        else
            $spin_count = 1;

        update_post_meta($stats_id,'no_of_spins',$spin_count);
        update_post_meta($stats_id,'user_email',$email);
        update_post_meta($stats_id,'username',$username);
        update_post_meta($stats_id,'win_loss',$request_to);

        do_action('optinspin_save_email',$email,$username,$stats_id);

        echo 'DONE';
    }

    function optinspin_edit_columns( $columns ) {

        $columns = array( 
            'cb' => '<input type="checkbox" />',
            'username' => __( 'Username' ),
            'email' => __( 'Email' ),
            'win_loss' => __( 'Win / Loss' ),
            'date' => __( 'Date' )
        );

        return $columns;
    }

    function optinspin_manage_columns( $column, $post_id ) {

        if( $column == 'username') {
            echo get_post_meta($post_id,'username',true);
        }

        if( $column == 'email') {
            echo get_post_meta($post_id,'user_email',true);
        }

        if( $column == 'win_loss') {
            $win_loss = get_post_meta($post_id,'win_loss',true);
            echo ( $win_loss == 'no_of_wins' ) ? 'WIN' : 'LOSS';
        }
    }

    function optinspin_stats_menu() {
        add_submenu_page( 'crb_carbon_fields_container_optin_spin.php', 'Optin List', 'Optin List',
            'manage_options', 'edit.php?post_type=optin-statistics');
    }
}