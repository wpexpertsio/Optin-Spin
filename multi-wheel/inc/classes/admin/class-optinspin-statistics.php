<?php

/**
 * Optinspin Stats
 */
class optinspin_Statistics {

    function __construct() {
        add_action( 'init', array($this,'optinspin_register_statistics') );
        add_action( 'wp_ajax_optinspin_statistics', array($this,'optinspin_statistics_callback') );
        add_action( 'wp_ajax_nopriv_optinspin_statistics', array($this,'optinspin_statistics_callback') );
        add_action( 'wp_ajax_optinspin_stats', array($this,'optinspin_stats_callback') );
        add_action( 'wp_ajax_nopriv_optinspin_stats', array($this,'optinspin_stats_callback') );
        add_filter( 'manage_edit-optin-statistics_columns', array($this,'optinspin_edit_columns') ) ;
        add_action( 'manage_optin-statistics_posts_custom_column', array($this,'optinspin_manage_columns'), 999, 2 );
        add_action( 'admin_menu', array($this,'optinspin_stats_menu') );
        add_filter( 'bulk_actions-edit-optin-statistics', array($this,'optinspin_bulk_edit') );
        add_action( 'init',array($this,'optinspin_write_csv') );
        add_action( 'pre_get_posts' , array($this, 'optinspin_filter_wheel_data' ) );
        add_action( 'restrict_manage_posts' , array($this, 'optinspin_wheel_filters' ),99 );
        add_action( 'pre_get_posts', array($this,'optinspin_wheel_search') );
    }

    function optinspin_wheel_search( $query ) {

        // Extend search for document post type
        $post_type = 'optin-statistics';
        // Custom fields to search for
        $custom_fields = array(
            "user_email",
            "username",
            "win_loss",
            "coupon",
        );

        if( ! is_admin() )
            return;

        if ( $query->query['post_type'] != $post_type )
            return;

        $search_term = $query->query_vars['s'];

        // Set to empty, otherwise it won't find anything
        $query->query_vars['s'] = '';

        if ( $search_term != '' ) {
            $meta_query = array( 'relation' => 'OR' );

            foreach( $custom_fields as $custom_field ) {
                array_push( $meta_query, array(
                    'key' => $custom_field,
                    'value' => $search_term,
                    'compare' => 'LIKE'
                ));
            }

            $query->set( 'meta_query', $meta_query );
        };
    }

    function optinspin_stats_callback() {
        $request_to = $_POST['request_to'];
        $current_wheel_id = $_POST['current_wheel_id'];
        $count_number = get_post_meta( $current_wheel_id, $request_to, true );
        if( !empty($count_number) ) {
            $count_number = $count_number + 1;
            update_post_meta( $current_wheel_id,$request_to,$count_number );
        } else {
            update_post_meta( $current_wheel_id,$request_to,1 );
        }
    }

    function optinspin_filter_wheel_data( $query ) {
        global $post_type;

        if( isset( $_GET['wheel_id'] ) && $_GET['wheel_id'] == 'previous_data' ) {
            if($post_type == 'optin-statistics' && $query->is_main_query() ) {

                $meta_query = array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'wheel_id',
                        'compare' => 'NOT EXISTS',
                    ),
                    array(
                        'key'     => 'wheel_id',
                        'value' => '',
                        'compare' => '=',
                    ),

                );
                $query->set( 'meta_query', $meta_query );

                /* $query->query_vars[ 'meta_key' ] = 'wheel_id';
                 $query->query_vars[ 'meta_value' ] = $_GET['wheel_id'];*/
            }
        } else {

            $wheel_id = 0;
            if( isset($_GET['wheel_id']) && !empty($_GET['wheel_id']) )
                $wheel_id = $_GET['wheel_id'];

            if($post_type == 'optin-statistics' && $query->is_main_query() ) {

                $meta_query = array(
                    array(
                        'key'     => 'wheel_id',
                        'value'   => '_'.$wheel_id,
                        'compare' => '=',
                    ),
                );
                $query->set( 'meta_query', $meta_query );

                /* $query->query_vars[ 'meta_key' ] = 'wheel_id';
                 $query->query_vars[ 'meta_value' ] = $_GET['wheel_id'];*/
            }
        }
    }

    function optinspin_wheel_filters() {
        // Only apply the filter to our specific post type
        global $typenow; global $post;


        $wheel_id = 0;
        if( isset($_GET['wheel_id']) && !empty($_GET['wheel_id']) )
            $wheel_id = $_GET['wheel_id'];

        $html = '';
        if( $typenow == 'optin-statistics' ) {
            $args = array( 'post_type' => 'optin-wheels', 'posts_per_page' => -1 );
            $lastposts = get_posts( $args );
            $html .= "<select name='wheel_id'>";
            $html .= '<option value="">Select Wheel</option>';
            foreach ( $lastposts as $post ) :
                setup_postdata( $post );
                $selected = '';
                if( get_the_ID() == $wheel_id )
                    $selected = 'selected';

                $html .= '<option '.$selected.' value="'.get_the_ID().'">'.get_the_title().'</option>';
            endforeach;

            $selected = '';
            if( isset($_GET['wheel_id']) && $_GET['wheel_id'] == 'previous_data' )
                $selected = 'selected';

            $html .= '<option value="previous_data" '.$selected.'>Previous Wheel Data</option>';
            wp_reset_postdata();

            $html .= '</select>';
        }

        echo $html;
    }

    function optinspin_bulk_edit( $actions ){
        unset( $actions[ 'edit' ] );
        return $actions;
    }

    function optinspin_register_statistics() {

        if( isset( $_GET['wheel_id'] ) ) {
            $wheel_id = $_GET['wheel_id'];
            $data = get_post_meta($wheel_id,'more_fields',true);
        }

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
            'not_found'          => __( 'No Optin List found Please Select another wheel from dropdown to display the result.', 'optinspin' ),
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

        if( !isset( $_POST['username'] ) || empty( $_POST['username'] ) || $_POST['username'] == 'undefined' )
            $username = 'GUEST';
        else
            $username = $_POST['username'];

        $wheel_id = $_POST['wheel_id'];



        if(optinspin_get_post_meta($wheel_id,'optinspin_cookie_expiry') == 0){
            $email = $_COOKIE['optinspin_email_for_zero'];
            $username = $_COOKIE['optinspin_user_for_zero'];
            unset($_COOKIE['optinspin_user_for_zero']);
            unset($_COOKIE['optinspin_email_for_zero']);
        }

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

        if( $_COOKIE['coupon-type_'.$_SESSION['wheel_id']] == 'coupon_link' )
            $coupon = '<a href="'. $_COOKIE['optinspin_coupon_link_'.$_SESSION['wheel_id']] .'">'. $_COOKIE['optinspin_coupon_code_'.$_SESSION['wheel_id']] .'</a>';
        else if( $_COOKIE['coupon-type_'.$_SESSION['wheel_id']] == 'coupon_text'  )
            $coupon = $_COOKIE['optinspin_coupon_code_'.$_SESSION['wheel_id']];

        update_post_meta($stats_id,'no_of_spins',$spin_count);
        update_post_meta($stats_id,'user_email',$email);
        update_post_meta($stats_id,'username',$username);

        update_post_meta($stats_id,'win_loss',$request_to);
        update_post_meta($stats_id,'coupon',$coupon);

        update_post_meta($stats_id,'ip_address',$this->optinspin_get_user_details()->ip_address);
        update_post_meta($stats_id,'location',$this->optinspin_get_user_details()->city.', '.$this->optinspin_get_user_details()->country);
        update_post_meta($stats_id,'country_code',$this->optinspin_get_user_details()->country_code);

        $more_fields = $_COOKIE['form_data'.$wheel_id];

        update_post_meta($stats_id,'more_fields',$more_fields);
        update_post_meta($stats_id,'wheel_id',$wheel_id);

        if( is_user_logged_in() ) {
            $user_info = get_userdata( get_current_user_id() );
            $user_roles = implode(', ', $user_info->roles);
            update_post_meta($stats_id,'user_role',$user_roles);
        } else {
            update_post_meta($stats_id,'user_role','GUEST');
        }
        do_action('optinspin_save_email',$email,$username,$stats_id);

        $restrict_ip_expire_time =  date('Y-m-d', strtotime('+1 day'));
        set_transient( 'ip_restrict_'.$wheel_id.'_'.$this->optinspin_getRealUserIp(), $restrict_ip_expire_time, 24 * HOUR_IN_SECONDS );

        echo 'DONE';
    }

    function optinspin_edit_columns( $columns ) {

        $more_fields_arr = array();
        if( isset( $_GET['wheel_id'] ) || !empty( $_GET['wheel_id'] ) ) {
            $wheel_id = $_GET['wheel_id'];

            $optinspin_form_fields = optinspin_get_post_meta($wheel_id,'optinspin_form_fields');
            foreach( $optinspin_form_fields as $optinspin_form_field ) {
                $field_key = $optinspin_form_field['optinspin_key'];
                $field = str_replace('optinspin_','',$optinspin_form_field['_type']);
                /*if($field == 'checkbox') {
                    if(strpos($optinspin_form_field['optinspin_label'],'[a') > 0 ) {
                        $checkbopx_label = str_replace('[a','<a',$optinspin_form_field['optinspin_label']);
                        $checkbopx_label = str_replace('"]','">',$checkbopx_label);
                        $checkbopx_label = str_replace('[/a]','</a>',$checkbopx_label);

                        $text = $optinspin_form_field['optinspin_label'];
                        $s_pos = strpos($text,']') + 1;
                        $e_pos = strpos($text,'[/') - $s_pos;
                        $checkbox_name= substr($text,$s_pos,$e_pos);
                        $more_fields_arr[$checkbox_name] = $checkbox_name;
                    } else {
                        $form_name = $optinspin_form_field['optinspin_label'];
                        $more_fields_arr[$form_name] = $form_name;
                    }
                } else {
                    $more_fields_arr[$optinspin_form_field['optinspin_label']] = $optinspin_form_field['optinspin_label'];
                }*/

                $checkbopx_label = str_replace('[a','<a',$optinspin_form_field['optinspin_label']);
                $checkbopx_label = str_replace('"]','">',$checkbopx_label);
                $checkbopx_label = str_replace('[/a]','</a>',$checkbopx_label);

                $more_fields_arr[$field_key] = $checkbopx_label;
            }
        }

        $columns = array(
            'cb' => '<input type="checkbox" />',
            'username' => __( 'Username' ),
            'email' => __( 'Email' ),
            'ip' => __( 'IP' ),
            'location' => __( 'Location' ),
            'win_loss' => __( 'Win / Loss' ),
            'coupon' => __( 'coupon' ),
            'user_role' => __( 'User Role' ),
            'date' => __( 'Date' )
        );

        $columns = array_merge($columns,$more_fields_arr);

        return $columns;
    }

    function optinspin_manage_columns( $column, $post_id ) {

        $more_fields_arr = array();
        if( isset( $_GET['wheel_id'] ) || !empty( $_GET['wheel_id'] ) ) {
            $wheel_id = $_GET['wheel_id'];

            $optinspin_form_fields = optinspin_get_post_meta($wheel_id,'optinspin_form_fields');
            foreach( $optinspin_form_fields as $optinspin_form_field ) {
                $field_key = $optinspin_form_field['optinspin_key'];
                $form_field_name = $optinspin_form_field['optinspin_label'];
                $field_type = str_replace('optinspin_','',$optinspin_form_field['_type']);

                if( $column == $field_key && isset($field_key) ) {
                    $more_fields_value = json_decode( get_post_meta($post_id,'more_fields',true),true  );
                    if( isset(  $more_fields_value[ $field_key ] ) ) {
                        echo $more_fields_value[ $field_key  ];
                    }
                }
            }
        }

        if( $column == 'username') {
            $username = get_post_meta($post_id,'username',true);
            if( !isset($username) || $username == 'undefined' || empty($username) )
                echo 'GUEST';
            else
                echo $username;
        }

        if( $column == 'email') {
            echo get_post_meta($post_id,'user_email',true);
        }

        if( $column == 'ip') {
            $ip_address = get_post_meta($post_id,'ip_address',true);
            echo ( $ip_address != '' ) ? $ip_address : '-';
        }

        if( $column == 'location') {
            $location = get_post_meta($post_id,'location',true);
            echo ( $location != '' ) ? $location : '-';
        }

        if( $column == 'country_code') {
            $country_code = get_post_meta($post_id,'country_code',true);
            echo ( $country_code != '' ) ? $country_code : '-';
        }

        if( $column == 'win_loss') {
            $win_loss = get_post_meta($post_id,'win_loss',true);
            echo ( $win_loss == 'no_of_wins' ) ? 'WIN' : 'LOSS';
        }

        if( $column == 'user_role') {
            echo get_post_meta($post_id,'user_role',true);
        }

        if( $column == 'coupon') {
            $coupon = get_post_meta($post_id,'coupon',true);
            if( !empty( $coupon ) ) {
                if( !empty( get_the_title( $coupon ) && is_numeric($coupon) ) ) {
                    echo get_the_title( $coupon );
                } else {
                    echo $coupon;
                }

            }
        }
    }

    function optinspin_get_user_details() {
        $json = file_get_contents("https://ipfind.co/?ip=".$this->optinspin_getRealUserIp());
        $data = json_decode($json);
        return $data;
    }

    function optinspin_stats_menu() {
        /*add_submenu_page( 'optinspin-settings', 'Settings', 'Settings',
            'manage_options', 'admin.php?page=optinspin-settings');*/
        /*add_submenu_page( 'optinspin-settings', 'Optin List', 'Optin List',
            'manage_options', 'edit.php?post_type=optin-statistics');*/
        add_submenu_page( 'edit.php?post_type=optin-wheels', 'Optin List', 'Optin List',
            'manage_options', 'edit.php?post_type=optin-statistics');
    }

    function optinspin_getRealUserIp(){
        /*if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }*/

        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        elseif(filter_var($forward, FILTER_VALIDATE_IP))
        {
            $ip = $forward;
        }
        else
        {
            $ip = $remote;
        }

        return $ip;
    }


    function optinspin_write_csv() {
        if( is_admin() && isset( $_GET['export'] ) && $_GET['wheel_id'] == 'previous_data' ) {
            $this->optinspin_write_previous_data();
        } else if( is_admin() && isset( $_GET['export'] ) ) {
            $csv_data = array();
            $columns_data = array();
            $args = array(
                'posts_per_page' => -1,
                'post_type' => 'optin-statistics',
                'fields ' => 'ids',
                'meta_query' => array(
                    array(
                        'key'     => 'wheel_id',
                        'value'   => '_'.$_GET['wheel_id'],
                        'compare' => '=',
                    ),
                ),
            );
            $more_fields_arr = array();
            $optinspin_form_fields = optinspin_get_post_meta($_GET['wheel_id'],'optinspin_form_fields');
            foreach( $optinspin_form_fields as $optinspin_form_field ) {
                $field_type = str_replace('optinspin_','',$optinspin_form_field['_type']);
                $form_field_name = $optinspin_form_field['optinspin_label'];
                if($field_type == 'checkbox') {
                    if(strpos($optinspin_form_field['optinspin_label'],'[a') > 0 ) {
                        $checkbopx_label = str_replace('[a','<a',$optinspin_form_field['optinspin_label']);
                        $checkbopx_label = str_replace('"]','">',$checkbopx_label);
                        $checkbopx_label = str_replace('[/a]','</a>',$checkbopx_label);

                        $text = $optinspin_form_field['optinspin_label'];
                        $s_pos = strpos($text,']') + 1;
                        $e_pos = strpos($text,'[/') - $s_pos;
                        $checkbox_name= substr($text,$s_pos,$e_pos);
                        $more_fields_arr[$checkbox_name] = $checkbox_name;
                    } else {
                        $more_fields_arr[] = $optinspin_form_field['optinspin_label'];
                    }
                } else {
                    $more_fields_arr[] = $optinspin_form_field['optinspin_label'];
                }
            }

            $columns_data[] = 'Username';
            $columns_data[] = 'Email';
            $columns_data[] = 'IP';
            $columns_data[] = 'Location';
            $columns_data[] = 'No of Win / No of Loss';
            $columns_data[] = 'User Role';
            $columns_data[] = 'Coupon';

            $columns_data = array_merge($columns_data,$more_fields_arr);

            $csv_data[] = $columns_data;

            $query = new WP_Query( $args );
            if ( $query->have_posts() ) {
                // The 2nd Loop
                while ( $query->have_posts() ) {
                    $query->the_post();

                    unset($columns_data);

                    $username = get_post_meta(get_the_ID(),'username',true);
                    if( !isset($username) || $username == 'undefined' )
                        $username = 'GUEST';

                    $columns_data[] = $username;

                    $columns_data[] = get_post_meta(get_the_ID(),'user_email',true);
                    $columns_data[] = get_post_meta(get_the_ID(),'ip_address',true);
                    $columns_data[] = get_post_meta(get_the_ID(),'location',true);


                    $win_loss = get_post_meta(get_the_ID(),'win_loss',true);
                    $columns_data[] = ( $win_loss == 'no_of_wins' ) ? 'WIN' : 'LOSS';

                    $columns_data[] = get_post_meta(get_the_ID(),'user_role',true);
                    $coupon = get_post_meta(get_the_ID(),'coupon',true);
                    if( !empty( $coupon ) ) {
                        if( !empty( get_the_title( $coupon ) && is_numeric($coupon) ) ) {
                            $columns_data[] = get_the_title( $coupon );
                        } else {
                            $columns_data[] = $coupon;
                        }

                    } else {
                        $columns_data[] = '-';
                    }

                    $optinspin_form_fields = optinspin_get_post_meta($_GET['wheel_id'],'optinspin_form_fields');
                    foreach( $optinspin_form_fields as $optinspin_form_field ) {
                        $field_key = $optinspin_form_field['optinspin_key'];
                        $field_type = str_replace('optinspin_','',$optinspin_form_field['_type']);
                        $form_field_name = $optinspin_form_field['optinspin_label'];
                        if($field_type == 'checkbox') {
                            if(strpos($optinspin_form_field['optinspin_label'],'[a') > 0 ) {
                                $checkbopx_label = str_replace('[a','<a',$optinspin_form_field['optinspin_label']);
                                $checkbopx_label = str_replace('"]','">',$checkbopx_label);
                                $checkbopx_label = str_replace('[/a]','</a>',$checkbopx_label);

                                $text = $optinspin_form_field['optinspin_label'];
                                $s_pos = strpos($text,']') + 1;
                                $e_pos = strpos($text,'[/') - $s_pos;
                                $checkbox_name= substr($text,$s_pos,$e_pos);
                                $form_field_name = $checkbox_name;
                            } else {
                                $form_field_name = $checkbox_name;
                            }
                        }

                        $more_fields_value = json_decode( get_post_meta(get_the_ID(),'more_fields',true),true  );
                        if( isset(  $more_fields_value[$field_key] ) ) {



                            $columns_data[] = $more_fields_value[$field_key];
                        }

                    }

                    $csv_data[] = $columns_data;

                }

                // Restore original Post Data
                wp_reset_postdata();
            }

            $fp = fopen(optinspin_PLUGIN_PATH.'/optinspin-list.csv', 'w');

            foreach ($csv_data as $fields) {
                fputcsv($fp, $fields);
            }

            fclose($fp);

            $fileurl = optinspin_PLUGIN_PATH.'/optinspin-list.csv';
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($fileurl));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($fileurl));
            ob_clean();
            flush();
            readfile($fileurl);
            exit;
        }
    }

    function optinspin_write_previous_data() {
        if( is_admin() ) {
            $csv_data = array();
            $columns_data = array();
            $args = array(
                'posts_per_page' => -1,
                'post_type' => 'optin-statistics',
                'fields ' => 'ids',
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'wheel_id',
                        'compare' => 'NOT EXISTS',
                    ),
                    array(
                        'key'     => 'wheel_id',
                        'value' => '',
                        'compare' => '=',
                    ),

                ),
            );
            $columns_data[] = 'Username';
            $columns_data[] = 'Email';
            $columns_data[] = 'IP';
            $columns_data[] = 'Location';
            $columns_data[] = 'No of Win / No of Loss';
            $columns_data[] = 'User Role';
            $columns_data[] = 'Coupon';

            $csv_data[] = $columns_data;

            $query = new WP_Query( $args );
            if ( $query->have_posts() ) {
                // The 2nd Loop
                while ( $query->have_posts() ) {
                    $query->the_post();

                    unset($columns_data);

                    $columns_data[] = get_post_meta($query->post->ID,'username',true);
                    $columns_data[] = get_post_meta($query->post->ID,'user_email',true);
                    $columns_data[] = get_post_meta($query->post->ID,'ip_address',true);
                    $columns_data[] = get_post_meta($query->post->ID,'location',true);
                    $columns_data[] = get_post_meta($query->post->ID,'win_loss',true);
                    $columns_data[] = get_post_meta($query->post->ID,'user_role',true);
                    $coupon = get_post_meta($query->post->ID,'coupon',true);
                    $columns_data[] = ( $coupon != '' ? get_the_title( $coupon ) : '');

                    $csv_data[] = $columns_data;
                    unset($columns_data);
                }

                // Restore original Post Data
                wp_reset_postdata();
            }

            $fp = fopen(optinspin_PLUGIN_PATH.'/optinspin-list.csv', 'w');

            foreach ($csv_data as $fields) {
                fputcsv($fp, $fields);
            }

            fclose($fp);

            $fileurl = optinspin_PLUGIN_PATH.'/optinspin-list.csv';
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($fileurl));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($fileurl));
            ob_clean();
            flush();
            readfile($fileurl);
            exit;
        }
    }
}