<?php

class optinspin_Wheel extends optinspin_Subscriber {

    function __construct() {
        add_shortcode('optinspin',array($this,'optinspin_woo_the_wheel'));
        add_action('wp_footer',array($this,'optinspin_wheel_slide'),0);
        add_action( 'admin_menu', array($this,'optinspin_settings_menu'),5 );
        add_action('init',array($this,'optinspin_preview'));
        add_action( 'wp_ajax_optinspin_get_wheel_attributes', array($this,'optinspin_get_wheel_attributes_callback') );
        add_action( 'wp_ajax_nopriv_optinspin_get_wheel_attributes', array($this,'optinspin_get_wheel_attributes_callback') );
//		add_action( 'wp_print_scripts', array($this,'revslider_scripts_cleanup'),999 );
        add_action('admin_menu', array($this,'disable_new_posts') );
    }

    function disable_new_posts() {
        // Hide sidebar link
        global $submenu;
        unset($submenu['edit.php?post_type=optin-wheels'][10]);

        $posts_count = wp_count_posts( 'optin-wheels' );

        // Hide link on listing page
        if ( $posts_count->publish > 0 || $posts_count->draft > 0 ) {
            echo '<style type="text/css">
            .page-title-action { display:none; }
            </style>';
        }
    }

    function optinspin_trigger_wheel_preview() {
        if( isset( $_GET['optinspin_preview'] ) ) {
            ?>
            <script>
                jQuery(document).ready(function () {
                    setTimeout(function() {
                        open_wheel_slide();
                    },2000);
                });
            </script>
            <?php
        }
    }

    function optinspin_preview() {
        if( isset( $_GET['optinspin_preview'] ) ) {
            echo do_shortcode('[optinspin wheeL_id='.$_GET['optinspin_preview'].' slide=1]');
        }
    }

    function optinspin_wheel_slide() {
        global $wp_query,$post;

        if( wp_doing_ajax() )
            return;

        $page_id = $wp_query->get_queried_object_id();

        if( empty($page_id) )
            $page_id = get_the_ID();


        $wheel_id = $this->get_optinspin_wheel( $page_id );

        if( !empty( $wheel_id ) ) {
            $content_post = get_post($page_id);
            $content = $content_post->post_content;

            if( isset( $_GET['optinspin_preview']) ) {
                $this->optinspin_trigger_wheel_preview();
            } else if( !has_shortcode( $content, 'optinspin' ) && !isset( $_GET['optinspin_preview']) ) {
                echo do_shortcode('[optinspin wheel_id="'.$wheel_id.'" slide="1"]');
            }
        }
    }

    function optinspin_clickable_tab( $wheel_id ) {
        $clickable_desktop = 0; $clickable_mobile = 0;
//        echo 'total'.print_r( $this->optinspin_get_segments() );
        ?>
        <script>

            jQuery(document).ready(function() {
                var clickable_desktop = 0; var clickable_mobile = 0;
                <?php
                if( !empty(optinspin_get_post_meta($wheel_id,'optinspin_enable_clickable_tab_desktop') ) ) {
                ?> clickable_desktop = 1; <?php
                }

                if( !empty( optinspin_get_post_meta($wheel_id,'optinspin_enable_clickable_tab_mobile') ) ) {
                ?> clickable_mobile = 1; <?php
                }
                ?>

                var window_width = jQuery(window).width();

                if( window_width <= 768 && clickable_mobile == 1 && getCookie('optinspin_use') == '' ) {
                    jQuery('#bottom_spin_icon').removeClass('hide');
                } else if ( window_width > 768 && clickable_desktop == 1 && getCookie('optinspin_use') == '') {
                    jQuery('#bottom_spin_icon').removeClass('hide');
                }
            });

        </script>
        <?php
    }

    function optinspin_rotate_mobile_popup() {
        $html = '<div class="optinspin-rotate-mob">
                    <div class="optinsin-rotote-content">
                        <div class="optinspin-rotate-img"><img src="'. optinspin_PLUGIN_URL . 'assets/img/rotate-mobile.png"> </div>
                        <div class="optinspin-rotate-msg">Kindly get back to your previous orientation view... your wheel is rolling there...</div>
                    </div>
                </div>';
        echo $html;
    }

    function optinspin_exit_intent( $wheel_id ) {

        if( !empty( optinspin_get_post_meta($wheel_id,'optinspin_enable_intent_exit_popup_desktop')) ) {
            ?>
            <script>
                jQuery(window).load(function(event) {
                    setTimeout(function() {
                        jQuery(window).mouseleave(function(event) {
                            var cookie_expiry = <?php echo optinspin_get_post_meta($wheel_id,'optinspin_cookie_expiry'); ?>;
                            var window_width = jQuery(window).width();
                            if (event.toElement == null && getCookie('optinspin_use') == '' && window_width > 768 && getCookie('desktopIntent') == '') {
                                open_wheel_slide();

                                setCookie('desktopIntent',1,cookie_expiry);
                            }
                        });
                    },2000);
                });
            </script>
            <?php
        }

        if( !empty( optinspin_get_post_meta($wheel_id,'optinspin_enable_intent_exit_popup_mobile')) ) {
            ?>
            <script>
                jQuery('document').ready(function() {
                    var window_width = jQuery(window).width();
                    var cookie_expiry = <?php echo optinspin_get_post_meta($wheel_id,'optinspin_cookie_expiry'); ?>;
                    if( getCookie('optinspin_use') == '' && window_width <= 768 && getCookie('mobileIntent') == '') {
                        var lastScrollTop = 0;
                        jQuery(window).scroll(function(event){
                            var st = jQuery(this).scrollTop();
                            if (st > lastScrollTop){
                            }
                            else {
                                setTimeout(function() {
                                    open_wheel_slide();
                                },1000);
                            }
                            lastScrollTop = st;
                        });

                        setCookie('mobileIntent',1,cookie_expiry);
                    }
                });
            </script>
            <?php
        }
    }

    function optinspin_interval( $wheel_id ) {
        if( optinspin_get_post_meta($wheel_id,'optinspin_wheel_open_at') != 'none' && empty($_COOKIE['optinspin_use'])) {

            $wheel_open_after = optinspin_get_post_meta($wheel_id,'optinspin_open_wheel_after');
            if( !empty($wheel_open_after) ) {

                if( optinspin_get_post_meta($wheel_id,'optinspin_wheel_open_at') == 'once'  && ( !empty(optinspin_get_post_meta($wheel_id,'optinspin_enable_time_delay_desktop')) || !empty(optinspin_get_post_meta($wheel_id,'optinspin_enable_time_delay_mobile')) ) ) {
                    $enable_desktop = 0; $enable_mob = 0;

                    ?>
                    <script>
                        jQuery(window).load(function() {
                            var enable_desktop = 0;
                            var enable_mobile = 0;
                            <?php
                                if( !empty(optinspin_get_post_meta($wheel_id,'optinspin_enable_time_delay_desktop')) ) {
                                ?>enable_desktop = 1; <?php
                                }
                                if( !empty(optinspin_get_post_meta($wheel_id,'optinspin_enable_time_delay_mobile')) ) {
                                ?>enable_mobile = 1; <?php
                            }
                            ?>
                            var window_width = jQuery(window).width();
                            if( getCookie('optinspin_use') == '' && ( (window_width <= 768 && enable_mobile == 1) || (window_width > 768 && enable_desktop == 1) ) ) {

                                if( getCookie('optinspin_wheel_open_intetval') != 1) {
                                    setTimeout( function() {
                                        open_wheel_slide();
                                    }, <?php echo $wheel_open_after?>000);
                                }
                                setCookie('optinspin_wheel_open_intetval',1);
                            }
                        });
                    </script>
                    <?php
                } else if( optinspin_get_post_meta($wheel_id,'optinspin_wheel_open_at') == 'every'  && ( !empty(optinspin_get_post_meta($wheel_id,'optinspin_enable_time_delay_desktop')) || !empty(optinspin_get_post_meta($wheel_id,'optinspin_enable_time_delay_mobile')) ) ) {
                    ?>
                    <script>
                        jQuery(window).load(function() {
                            var enable_desktop = 0;
                            var enable_mobile = 0;

                            <?php
                                if( !empty(optinspin_get_post_meta($wheel_id,'optinspin_enable_time_delay_desktop')) ){
                                ?>enable_desktop = 1; <?php
                                }
                                if( !empty(optinspin_get_post_meta($wheel_id,'optinspin_enable_time_delay_mobile')) ) {
                                ?>enable_mobile = 1; <?php
                            }
                            ?>
                            var window_width = jQuery(window).width();

                            if( getCookie('optinspin_use') == '' && ( (window_width <= 768 && enable_mobile == 1) || (window_width > 768 && enable_desktop == 1))) {
                                setInterval( function(){
                                    open_wheel_slide();
                                }, <?php echo $wheel_open_after?>000);
                            }
                        });
                    </script>
                    <?php
                }
            }
        }

    }

    function optinspin_woo_the_wheel( $atts ) {
        global $wheel_ID;

        static $counter = 0;

        $atts = shortcode_atts(
            array(
                'wheel_id' => 0,
                'slide' => 0,
            ), $atts );

        $_SESSION['wheel_id'] = $atts['wheel_id'];

        $counter++;

        if( count( $this->optinspin_get_segments( $atts['wheel_id'] ) ) == 0 ) {
            return '<div style="color:red;">Your wheel doesn\'t have any section. please add some section to appear the wheel</div>';
        }

        if( count( $this->optinspin_get_segments( $atts['wheel_id'] ) ) > 0 && $counter == 1 && $this->optinspin_is_user_allowed_to_play( $atts['wheel_id'] ) == true ) {


            $this->optinspin_wheel_attributes($atts['wheel_id']);
            $this->optinspin_wheel_script( $atts['wheel_id'], $atts['slide'] );

            $cart_url = '';
            if( function_exists( 'wc_get_cart_url' ) )
                $cart_url = wc_get_cart_url();
            $disable_optinbar = optinspin_get_post_meta($atts['wheel_id'],'optinspin_disable_coupon_bar');
            if( !empty($disable_optinbar) )
                $disable_optinbar = 'off';

            $coupon_expire_label = optinspin_get_post_meta($atts['wheel_id'],'optinspin_coupon_bar_expire_label');
            if( empty($coupon_expire_label) )
                $coupon_expire_label = 'Coupon Time Left';

            $sparkle_enable = optinspin_get_post_meta($atts['wheel_id'],'optinspin_enable_sparkle');
            if( empty( $sparkle_enable ) )
                $sparkle_enable = 0;
            else
                $sparkle_enable = 1;

            $cookie_expiry = optinspin_get_post_meta($atts['wheel_id'],'optinspin_cookie_expiry');
            if( empty($cookie_expiry) )
                $cookie_expiry = 0;

            $coupon_msg = optinspin_get_post_meta($atts['wheel_id'],'optinspin_coupon_bar_msg');

            if( empty($coupon_msg) )
                $coupon_msg = 'Check your email to get your winning coupon!';

            $optinspin_enable_cart_redirect = optinspin_get_post_meta($atts['wheel_id'],'optinspin_enable_cart_redirect');
            if( empty( $optinspin_enable_cart_redirect ) )
                $optinspin_enable_cart_redirect = 0;
            else
                $optinspin_enable_cart_redirect = 1;

            $enable_snow_feature = optinspin_get_post_meta($atts['wheel_id'],'optinspin_snowflak_enable');
            if( empty( $enable_snow_feature ) )
                $enable_snow_feature = 0;
            else
                $enable_snow_feature = 1;

            wp_enqueue_style( 'optinspin-wheel-style', optinspin_PLUGIN_URL . 'assets/css/wheel-style.css' );
            wp_enqueue_style( 'optinspin-google-font', optinspin_PLUGIN_URL . 'assets/css/google-font.css' );
            wp_enqueue_style( 'optinspin-wheel-main-style', optinspin_PLUGIN_URL . 'assets/css/style.css' );
            wp_enqueue_style( 'optinspin-phone-number-style', optinspin_PLUGIN_URL . 'assets/css/intlTelInput.css' );

            wp_enqueue_script( 'jquery' );
            wp_register_script( 'optinspin-grunt-scripts', optinspin_PLUGIN_URL . 'assets/js/optinspin-merge.js' );
            wp_enqueue_script( 'optinspin-phone-number', optinspin_PLUGIN_URL . 'assets/js/intlTelInput.js' );
            wp_enqueue_script( 'tp-tools',null,'',true );
            wp_enqueue_script( 'revmin',null,'',true );

            if($enable_snow_feature == 1){
                $snowparam = array(
                    'no_of_flake' => optinspin_get_post_meta($atts['wheel_id'],'optinspin_snow_numfla'),
                    'speed_of_flake' => optinspin_get_post_meta($atts['wheel_id'],'speed_of_flake'),

                );
                wp_register_script( 'optinspin-snow-scripts', optinspin_PLUGIN_URL . 'assets/js/fallingsnow_v6.js', null, '', true );
                wp_enqueue_script( 	'optinspin-snow-scripts' );
                wp_localize_script( 'optinspin-snow-scripts', 'optinspin_snowparam', $snowparam );

            }

            $param = array(
                'plugin_url' => optinspin_PLUGIN_URL,
                'ajax_url' => admin_url('admin-ajax.php'),
                'coupon_msg' => $coupon_msg,
                'cart_url' => $cart_url,
                'disable_optinbar' => $disable_optinbar,
                'coupon_expire_label' => $coupon_expire_label,
                'wheel_data' => optinspin_PLUGIN_URL .'inc/wheel_data.php',
                'snow_fall' => $enable_snow_feature,
                'sparkle_enable' => $sparkle_enable,
                'cookie_expiry' => $cookie_expiry,
                'ajaxurl' => admin_url('admin-ajax.php'),
                'enable_cart_redirect' => $optinspin_enable_cart_redirect
            );
            wp_localize_script( 'optinspin-grunt-scripts', 'optinspin_wheel_spin', $param );
            wp_enqueue_script( 'optinspin-grunt-scripts' );

            $_SESSION['wheel_id_'.$atts['wheel_id']] = $atts['wheel_id'];
            $_SESSION['wheel_id'] = $atts['wheel_id'];

            $wheel_data_var =  $this->optinspin_wheel_canvas( $atts['wheel_id'], $atts['slide'] );
            $wheel_data_var .=  '<div class="optinspin_wheel_id" style="display:none">'.$atts['wheel_id'].'</div>';
            if( $atts['slide'] == 0 ) {
                ?>
                <script>
                    jQuery(document).ready(function() {
                        setCookie('optinspin_slide_<?php echo $atts['wheel_id']?>',<?php echo $atts['slide']?>);
                        var actual_height = jQuery('.optinspin-right').height() + 60;
                        jQuery( ".optinspin-right" ).animate({
                            opacity: 1,
                            height: actual_height +"px"
                        }, 1000, function() {
                            jQuery( ".optinspin-right").show();
                        });
                    });
                </script>
                <?php
            }
            return $wheel_data_var;
        } else if ( $counter > 1 ) {
            if( !isset( $_GET['optinspin_preview'] ) )
                return '<div style="color:red;">You can use only one shortcode on single page</div>';
        } else if( isset( $_COOKIE['optinspin_use_'.$atts['wheel_id'] ] ) && !empty( $_COOKIE['optinspin_use_'.$atts['wheel_id'] ] ) ) {

            if( isset( $_COOKIE['optinspin_coupon_code_'.$atts['wheel_id'] ] ) && !empty( $_COOKIE['optinspin_coupon_code_'.$atts['wheel_id'] ] ) ) {
                return '<div class="optinspin-played win"></div>
                    <div class="optinspin_wheel_id" style="display:none">'.$atts['wheel_id'].'</div>';
                ?> <script> jQuery(document).ready(function() { show_optin_bar('<?php echo $_COOKIE['optinspin_coupon_code_'.$atts['wheel_id'] ] ?>'); }); </script> <?php
            } else
                return '<div class="optinspin-played loss"></div>
                    <div class="optinspin_wheel_id" style="display:none">'.$atts['wheel_id'].'</div>';
        } else {
            return '<div class="optinspin_wheel_id" style="display:none">'.$atts['wheel_id'].'</div>';
        }
    }

    function optinspin_scripts_load( $wheel_id, $slide ) {
        $this->optinspin_wheel_attributes($wheel_id);
        $this->optinspin_wheel_script( $wheel_id, $slide );

        $cart_url = '';
        if( function_exists( 'wc_get_cart_url' ) )
            $cart_url = wc_get_cart_url();
        $disable_optinbar = optinspin_get_post_meta($wheel_id,'optinspin_disable_coupon_bar');
        if( !empty($disable_optinbar) )
            $disable_optinbar = 'off';

        $coupon_expire_label = optinspin_get_post_meta($wheel_id,'optinspin_coupon_bar_expire_label');
        if( empty($coupon_expire_label) )
            $coupon_expire_label = 'Coupon Time Left';

        $sparkle_enable = optinspin_get_post_meta($wheel_id,'optinspin_enable_sparkle');
        if( empty( $sparkle_enable ) )
            $sparkle_enable = 0;
        else
            $sparkle_enable = 1;

        $cookie_expiry = optinspin_get_post_meta($wheel_id,'optinspin_cookie_expiry');
        if( empty($cookie_expiry) )
            $cookie_expiry = 0;

        $coupon_msg = optinspin_get_post_meta($wheel_id,'optinspin_coupon_bar_msg');

        if( empty($coupon_msg) )
            $coupon_msg = 'Check your email to get your winning coupon!';

        $optinspin_enable_cart_redirect = optinspin_get_post_meta($wheel_id,'optinspin_enable_cart_redirect');
        if( empty( $optinspin_enable_cart_redirect ) )
            $optinspin_enable_cart_redirect = 0;
        else
            $optinspin_enable_cart_redirect = 1;

        $enable_snow_feature = optinspin_get_post_meta($wheel_id,'optinspin_snowflak_enable');
        if( empty( $enable_snow_feature ) )
            $enable_snow_feature = 0;
        else
            $enable_snow_feature = 1;

        wp_enqueue_style( 'optinspin-wheel-style', optinspin_PLUGIN_URL . 'assets/css/wheel-style.css' );
        wp_enqueue_style( 'optinspin-google-font', optinspin_PLUGIN_URL . 'assets/css/google-font.css' );
        wp_enqueue_style( 'optinspin-wheel-main-style', optinspin_PLUGIN_URL . 'assets/css/style.css' );
        wp_enqueue_style( 'optinspin-phone-number-style', optinspin_PLUGIN_URL . 'assets/css/intlTelInput.css' );

        wp_enqueue_script( 'jquery' );
        wp_register_script( 'optinspin-grunt-scripts', optinspin_PLUGIN_URL . 'assets/js/optinspin-merge.js', null, '', true );
        wp_enqueue_script( 'optinspin-phone-number', optinspin_PLUGIN_URL . 'assets/js/intlTelInput.js', null, '', true );



        if($enable_snow_feature == 1){
            $snowparam = array(
                'no_of_flake' => optinspin_get_post_meta($wheel_id,'optinspin_snow_numfla'),
                'speed_of_flake' => optinspin_get_post_meta($wheel_id,'speed_of_flake'),

            );
            wp_register_script( 'optinspin-snow-scripts', optinspin_PLUGIN_URL . 'assets/js/fallingsnow_v6.js', null, '', true );
            wp_enqueue_script( 	'optinspin-snow-scripts' );
            wp_localize_script( 'optinspin-snow-scripts', 'optinspin_snowparam', $snowparam );

        }



        $param = array(
            'plugin_url' => optinspin_PLUGIN_URL,
            'ajax_url' => admin_url('admin-ajax.php'),
            'coupon_msg' => $coupon_msg,
            'cart_url' => $cart_url,
            'disable_optinbar' => $disable_optinbar,
            'coupon_expire_label' => $coupon_expire_label,
            'wheel_data' => optinspin_PLUGIN_URL .'inc/wheel_data.php',
            'snow_fall' => $enable_snow_feature,
            'sparkle_enable' => $sparkle_enable,
            'cookie_expiry' => $cookie_expiry,
            'ajaxurl' => admin_url('admin-ajax.php'),
            'enable_cart_redirect' => $optinspin_enable_cart_redirect
        );
        wp_localize_script( 'optinspin-grunt-scripts', 'optinspin_wheel_spin', $param );
        wp_enqueue_script( 'optinspin-grunt-scripts' );
    }

    function optinspin_is_user_allowed_to_play( $wheel_id ) {
//        return true;
        // Restrict By IP
        $is_ip_restect = optinspin_get_post_meta( $wheel_id,'optinspin_restricted_by_ip' );

        if( !empty( $is_ip_restect ) ) {
            $user_ip = $this->optinspin_user_ip();

            $data_timeout = get_option('_transient_timeout_' . 'ip_restrict__'.$wheel_id.'_'.$user_ip);
            $data_timeout_exp = date("Y-m-d", $data_timeout);

            $current_date = date("Y-m-d");

            if ( $data_timeout_exp > $current_date && !empty( $data_timeout ) ) {
                return false;
            }
        }

        $play_chances = optinspin_get_post_meta( $wheel_id,'optinspin_number_chances_play' );
        $is_cookie_restricted = optinspin_get_post_meta( $wheel_id,'optinspin_restricted_by_cooike' );

        /*if( isset( $_COOKIE['optinspin_play_'.$wheel_id ] ) ) {
            return true;
        }*/
        $optinspin_play_cookie = 0;
        if( isset( $_COOKIE['optinspin_play_'.$wheel_id ] ) || !empty( $_COOKIE['optinspin_play_'.$wheel_id ] ) ) {
            $optinspin_play_cookie = $_COOKIE['optinspin_play_'.$wheel_id ];
        }

        if( !empty($is_cookie_restricted) && $optinspin_play_cookie < $play_chances ) {
            return true;
        } else if( !empty($is_cookie_restricted) && $optinspin_play_cookie >= $play_chances )
            return false;

        return true;
    }

    function optinspin_get_segment_colors( $wheel_id ) {

        $sections = optinspin_get_post_meta($wheel_id,'crb_section');
        $segments_colors = array();

        if( !empty( $sections ) ) {
            // Getting All Section colors in the loop and save them in array
            foreach( $sections as $section ) {

                $color = $section['segment_color'];

                if(empty($color)) // IF Don't have any coupon
                    $color = '#364c62';

                $segments_colors[] = $color;

            }

            // segment_color
            return $segments_colors;
        }
    }

    function optinspin_wheel_canvas( $wheel_id, $slide ) {

        if( empty($wheel_id) )
            return;

//        $html = $this->optinspin_wheel_attributes($wheel_id);

        $html = '<div class="woo-wheel-roll-bg"></div>
                
                <div class="woo-wheel-roll" id="opinspin-wheel-roll">
                <div class="woo-wheel-bg-img"></div>';
        $html .= '<div class="optinspin-right">
                    <div class="optinspin-cross-wrapper"><div class="optinspin-cross-label">'.optinspin_get_post_meta($wheel_id,'optinspin_cross_label').'</div><div class="optinspin-cross"></div></div>
                    <div class="toast">
                        <p/>
                    </div>';


        $html .= $this->optinspin_get_logo( $wheel_id );
        $html .= $this->winning_lossing_text( $wheel_id,$slide );
        $html .= $this->optinspin_form_fields( $wheel_id );
//                $html .= '<div class="optinspin-intro">'.$this->optinspin_get_general_settings()['optinspin_intro_text'].'</div>';
//                $html .= $this->optinspin_initially_try_luck_btn();
        $html .= $this->optinspin_error_notify( $wheel_id );
        $html .= $this->optinspin_privacy_link( $wheel_id );



        $html .= '</div>';
        $html .= '<div class="optinspin-left">

                    <div class="optinspin-canvas">
                    <div class="wheelContainer">
                    <svg class="wheelSVG" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" text-rendering="optimizeSpeed">
                        <defs>
                            <filter id="shadow" x="-100%" y="-100%" width="550%" height="550%">
                                <feOffset in="SourceAlpha" dx="0" dy="0" result="offsetOut"></feOffset>
                                <feGaussianBlur stdDeviation="9" in="offsetOut" result="drop" />
                                <feColorMatrix in="drop" result="color-out" type="matrix" values="0 0 0 0   0
                          0 0 0 0   0
                          0 0 0 0   0
                          0 0 0 .3 0" />
                                <feBlend in="SourceGraphic" in2="color-out" mode="normal" />
                            </filter>
                        </defs>
                        <g class="mainContainer">
                        <g class="wheel">
                            <!-- <image  xlink:href="http://example.com/images/wheel_graphic.png" x="0%" y="0%" height="100%" width="100%"></image> -->
                        </g>
                        </g>
                        <g class="centerCircle" />
                        <g class="wheelOutline" />
                        <g class="pegContainer" opacity="1">
                            <path class="peg" fill="#EEEEEE" d="M22.139,0C5.623,0-1.523,15.572,0.269,27.037c3.392,21.707,21.87,42.232,21.87,42.232 s18.478-20.525,21.87-42.232C45.801,15.572,38.623,0,22.139,0z" />
                        </g>
                        <g class="valueContainer" />';
        $logo_url = optinspin_get_post_meta($wheel_id,'optinspin_wheel_center_logo');
        $html .= '<image xlink:href="'.$logo_url.'" width="160" height="160" x="430" y="300" />';
        $html .= '</svg>
                    </div>
                    </div>
                </div>';
        $enable_snow_feature = optinspin_get_post_meta($wheel_id,'optinspin_snowflak_enable');

        if($enable_snow_feature == '1'){
            $_optinspin_image_snowflake = optinspin_get_post_meta($wheel_id,'optinspin_image_snowflake');
            $optinspin_snowflake_width = optinspin_get_post_meta($wheel_id,'optinspin_snowflake_width');

            if(!empty($_optinspin_image_snowflake)){
                $img = "<img src=".$_optinspin_image_snowflake." width=".$optinspin_snowflake_width." />";
            } else {
                $img = "*";
            }
            $html .= '<div id="snowflakeContainer">
								<p class="snowflake" style="opacity: 0.87828; font-size: 54.7671px; transform: translate3d(223px, 462px, 0px);">'.$img.'</p>
								<p class="snowflake" style="opacity: 0.235512; font-size: 54.7058px; transform: translate3d(1244px, 404px, 0px);">'.$img.'</p>
								<p class="snowflake" style="opacity: 0.304743; font-size: 56.2731px; transform: translate3d(681px, 373px, 0px);">'.$img.'</p>
								<p class="snowflake" style="opacity: 0.301569; font-size: 26.2554px; transform: translate3d(1100px, 147px, 0px);">'.$img.'</p>
								<p class="snowflake" style="opacity: 0.930111; font-size: 44.3089px; transform: translate3d(162px, 346px, 0px);">'.$img.'</p>
							</div>';
        }
        $html .= '</div>';

        $html .= $this->optinspin_fortune_open($wheel_id);
        $html .= $this->optinspin_interval($wheel_id);
        $html .= $this->optinspin_exit_intent($wheel_id);
        $html .= $this->optinspin_clickable_tab($wheel_id);
        $html .= $this->optinspin_rotate_mobile_popup($wheel_id);


        //$html .= $this->optinspin_side_luck_btn();
        return $html;
    }

    function optinspin_error_notify() {
        $html = '<div class="optinspin-error"></div>';
        return $html;
    }

    function optinspin_get_logo( $wheel_id ) {
        $html = '<div class="optinspin-logo">
                    <img src="'.optinspin_get_post_meta($wheel_id,'optinspin_logo').'" class="optinspin-wheel-logo" />
                </div>';

        return $html;
    }

    function optinspin_side_luck_btn() {
        $html = '<div class="woo-try_btn" id="optinspin-simple-btn">Try Your Luck</div>';
        return $html;
    }

    function optinspin_is_mycred_exist_in_wheel( $wheel_id ) {

        $sections = optinspin_get_post_meta($wheel_id,'crb_section');

        $mycred_exist = false;
        if( !empty($sections) ) {
            // Getting All Section in the loop
            foreach( $sections as $section ) {
                if( isset( $section['optinspin_woocommerce_type'] ) && $section['optinspin_woocommerce_type'] == 'optinspin_mycred' ) {
                    $mycred_exist = true;
                    break;
                }

                if( isset( $section['optinspin_mycred_points_check'] ) && !empty( $section['optinspin_mycred_points_check'] ) ) {
                    $mycred_exist = true;
                    break;
                }
            }
        }

        return $mycred_exist;
    }

    function optinspin_form_fields( $wheel_id ) {
        global $optinspin_Chatchamp,$optinspin_protect_post,$mycred_optinspin;

        if( !is_user_logged_in() && $this->optinspin_is_mycred_exist_in_wheel( $wheel_id ) && $mycred_optinspin->optinspin_is_mycred_emabled() ) {
            $html = '<div class="optinspin-wrapper-notice">
                        <div class="optinspin-notice-msg">'. __('<i class="fas fa-sign-in-alt"></i> You must login to play OptinSpin','optinspin') .'
                        <div class="optinspin-notice-link"><a href="'.wp_login_url().'">'. __('Click here to get Login','optinspin') .'</a></div></div>
                    </div>';

            return $html;
        } else if( $this->optinspin_is_mycred_exist_in_wheel( $wheel_id ) && $mycred_optinspin->optinspin_is_mycred_emabled() == false ) {
            $html = '<div class="optinspin-wrapper-notice">
                        <div class="optinspin-notice-msg">'. __('<strong><i class="far fa-frown"></i> Warning</strong> : Seems like wheel was setup with <link to mycred>myCRED Plugin</link to mycred>, and later mycred was deactivated.','optinspin') .'</div>
                    </div>';

            return $html;
        }

        do_action('optinspin_before_form_fields');

        $name_field = optinspin_get_post_meta($wheel_id,'optinspin_name_label');
        $name_field = ($name_field != '') ? 'block' : 'none';
        $username = ''; $user_email = '';
        if( is_user_logged_in() ) {
            $user_info = get_userdata(get_current_user_id());
            $username = $user_info->user_login;
            $user_email = $user_info->user_email;
        }

        $html = '<div class="optinspin-intro">'.$this->optinspin_get_general_settings( $wheel_id )['optinspin_intro_text'].'</div>
                    <div class="optinspin-from">';

        if( $optinspin_protect_post->optinspin_protection_is_enabled( $wheel_id ) ) {
            $html .= $optinspin_protect_post->optinspin_password_protect_html( $wheel_id );
            $html .= $optinspin_protect_post->optinspin_hide_form();
        }

        if( $optinspin_Chatchamp->optinspin_chatchamp_is_enabled() ) {
            //                            $html .= do_action('optinspin_before_form');
            $html .= $optinspin_Chatchamp->optinspin_chatchamp_html();
            $html .= $optinspin_Chatchamp->optinspin_hide_form();
        }

        $html .= '<form class="toggle-disabled">
                    <div class="optinspin-name field-'.$name_field.'" style="display: '. $name_field .'">
                        <input type="text" name="your name" placeholder="'.optinspin_get_post_meta($wheel_id,'optinspin_name_label').'" autocomplete="off" class="optinspin-form-field optinspin-name optinspin-'.$name_field.'" value="'.$username.'" name="optinspin-name">
                    </div>
                    <div class="optinspin-email">
                        <input type="text" name="your email" placeholder="'.optinspin_get_post_meta($wheel_id,'optinspin_email_label').'" autocomplete="off"  class="optinspin-form-field optinspin-email" value="'.$user_email.'" name="optinspin-email">
                    </div>';
        
        $html .= '<div class="optinspin-sub-btn">

                        <input type="button" class="optinspin-form-btn" id="optinspin-simple-btn" value="'.optinspin_get_post_meta($wheel_id,'optinspin_button_label').'" name="optinspin-sub-btn">
                        <input type="button" class="spinBtn" style="display:none">
                    </div>
                    </form>

                    <div class="lds-css ng-scope">
                          <div style="width:100%;height:100%" class="lds-rolling">
                            <div></div>
                          </div>
                      </div>
                </div>';
        return $html;
    }

    function optinspin_get_segments( $wheel_id ) {

        $sections = optinspin_get_post_meta($wheel_id,'crb_section');

        $segments_each = array(); $segments_array = array();
        $counter = 0;

        /* echo '<pre>';
         print_r($sections);
         echo '</pre>';*/
        if( !empty($sections) ) {
            // Getting All Section in the loop
            foreach( $sections as $section ) {
                $counter++;

                $label = $section['optinspin_section_label'];

                $generate_coupon = ''; $coupon_discount = ''; $coupon_expire_days = '';
                if( !empty($section['optinspin_section_generate_coupon']) )
                    $generate_coupon = $section['optinspin_section_generate_coupon'];

                if( !empty($section['optinspin_section_discount']) )
                    $coupon_discount = $section['optinspin_section_discount'];

                if( !empty($section['optinspin_section_discount_expiry_day']) )
                    $coupon_expire_days = $section['optinspin_section_discount_expiry_day'];

                $probability = $section['optinspin_probability'];
                $winning_lossing_text = $section['optinspin_win_loss_text'];

                if(empty($section['optinspin_coupon'])) // IF Don't have any coupon
                    $coupon = ' - ';
                else
                    $coupon = $section['optinspin_coupon'];

                $win = true;

                if( $section['_type'] == 'no_prize' )
                    $win = false;

                $section_id = '';
                if( isset( $section['optinspin_unique_section_id'] ) ) {
                    $section_id = $section['optinspin_unique_section_id'];
                    $total_usage = $section['optinspin_max_availability'];

                    if( $this->optionspin_check_availability($section_id,$total_usage) == false )
                        $probability = '0';
                }

                if( $coupon != ' - ' && $this->optinspin_coupon_valid( $coupon ) == false ) {
                    $probability = '0';
                }

                $coupon_type = ''; $coupon_code_label = ''; $coupon_link_label = ''; $coupon_link_url = '';
                if( isset($section['optinspin_woocommerce_type']) && $section['optinspin_woocommerce_type'] == 'woocommere_coupon' ) {
                    $coupon_type = 'woocommerce';
                } else if( isset($section['optinspin_woocommerce_type']) && $section['optinspin_woocommerce_type'] == 'coupon_text' ) {
                    $coupon_type = 'coupon_text';
                    $coupon_code_label = $section['optinspin_coupon_text_label'];
                } else if( isset($section['optinspin_woocommerce_type']) && $section['optinspin_woocommerce_type'] == 'coupon_link' ) {
                    $coupon_type = 'coupon_link';

                    $coupon_link_label = $section['optinspin_coupon_link_label'];
                    $coupon_link_url = $section['optinspin_coupon_link_url'];
                } else if( isset($section['optinspin_woocommerce_type']) && $section['optinspin_woocommerce_type'] == 'edd_coupon' ) {
                    $coupon_type = 'edd_coupon';
                    $coupon = get_the_title( $section['optinspin_edd_coupon'] );
                } else if( isset($section['optinspin_woocommerce_type']) && $section['optinspin_woocommerce_type'] == 'optinspin_mycred' ) {
                    $coupon_type = 'mycred_points';
                    $coupon = $section['optinspin_mycred_points'];
                }

                if( empty( optinspin_get_post_meta($wheel_id,'optinspin_duration_type' ) ) )
                    $wheel_duration_type = 'day';
                else
                    $wheel_duration_type = optinspin_get_post_meta($wheel_id,'optinspin_duration_type' );

                // Set Section Type
                $section_type = 'string';
                if( $section['optinspin_section_type'] == 'image' ) {
                    $section_type = 'image';
                    $label = $section['optinspin_section_image'];
                }

                $segments_each['probability'] = $probability;
                $segments_each['type'] = $section_type;
                $segments_each['value'] = $label;
                $segments_each['win'] = $win;
                $segments_each['resultText'] = $winning_lossing_text;
                $segments_each['userData'] = array("score" => 10);
                $segments_each['couponCode'] = $coupon;
                $segments_each['generated_coupon'] = $generate_coupon;
                $segments_each['generated_discount_coupon'] = $coupon_discount;
                $segments_each['coupon_expiry_day'] = $coupon_expire_days;
                $segments_each['section_id'] = $section_id;
                $segments_each['coupon_type'] = $coupon_type;
                $segments_each['coupon_label'] = $coupon_code_label;
                $segments_each['coupon_link_label'] = $coupon_link_label;
                $segments_each['coupon_link_url'] = $coupon_link_url;
                $segments_each['duration_type'] = $wheel_duration_type;
                $segments_each['user_ip'] = $this->optinspin_user_ip();

                $segments_array[] = apply_filters('optinspin_wheel_segments',$segments_each,$sections,$section);
            }
            return $segments_array;
        }
    }

    function optinspin_user_ip() {
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

    function optionspin_check_availability( $section_id,$total_usage_limit ) {
        $total_win = get_option('complex_'.optinspin_crb_get_i18n_suffix().'_'.$section_id);
        if( $total_usage_limit == '' )
            return true;
        else if( $total_win < $total_usage_limit )
            return true;
        else
            return false;
    }

    function optinspin_coupon_valid( $coupon_id ) {
        if( class_exists( 'WC_Coupon' ) ){
            $coupon = new WC_Coupon( $coupon_id );
            $get_usage_limit = $coupon->get_usage_limit();
            $get_usage_count = $coupon->get_usage_count();

            if( empty( $get_usage_limit ) )
                return true;


            if( $get_usage_count < $get_usage_limit ) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    // General Settings of Optin Spin
    function optinspin_get_general_settings( $wheel_id ) {
        $general = array();
        $general['optinspin_allowed_users'] = optinspin_get_post_meta($wheel_id,'optinspin_allowed_users');
        $general['optinspin_spin_speed'] = optinspin_get_post_meta($wheel_id,'optinspin_spin_speed');
        $general['optinspin_no_of_spin'] = optinspin_get_post_meta($wheel_id,'optinspin_no_of_spin');
        $general['optinspin_text_size'] = optinspin_get_post_meta($wheel_id,'optinspin_text_size');
        $general['optinspin_wheel_logo'] = optinspin_get_post_meta($wheel_id,'optinspin_wheel_logo');
        $general['optinspin_logo'] = optinspin_get_post_meta($wheel_id,'optinspin_logo');
        $general['optinspin_background_color'] = optinspin_get_post_meta($wheel_id,'optinspin_background_color');
        $general['optinspin_border_color'] = optinspin_get_post_meta($wheel_id,'optinspin_border_color');
        $general['optinspin_inner_border_color'] = optinspin_get_post_meta($wheel_id,'optinspin_inner_border_color');
        $general['optinspin_border_width'] = optinspin_get_post_meta($wheel_id,'optinspin_border_width');
        $general['optinspin_text_color'] = optinspin_get_post_meta($wheel_id,'optinspin_text_color');
        $general['optinspin_background_image'] = optinspin_get_post_meta($wheel_id,'optinspin_background_image');
        $general['optinspin_wheel_border_color'] = optinspin_get_post_meta($wheel_id,'optinspin_wheel_border_color');
        $general['optinspin_email_label'] = optinspin_get_post_meta($wheel_id,'optinspin_email_label');
        $general['optinspin_button_label'] = optinspin_get_post_meta($wheel_id,'optinspin_button_label');
        $general['optinspin_intro_text'] = optinspin_get_post_meta($wheel_id,'optinspin_intro_text');
        if( !empty( optinspin_get_post_meta($wheel_id,'optinspin_enable_sound') )) // Check Sound is enable or not
            $general['optinspin_enable_sound'] = true;
        else
            $general['optinspin_enable_sound'] = false;

        return $general;
    }

    function optinspin_total_segments( $wheel_id ) {
        $total_sections = count( optinspin_get_post_meta($wheel_id,'crb_section') );
        return $total_sections; // Total Segments in the Wheel
    }

    function winning_lossing_text( $wheel_id, $slide ) {

        $coupn_msg_txt = optinspin_get_post_meta($wheel_id,'optinspin_coupon_message');
        $coupn_msg_txt = str_replace('{coupon}','*****',$coupn_msg_txt);
        $html = '<div class="winning_lossing"> 
                    <div class="optinspin-win-info">'.$coupn_msg_txt.'</div>
                    <div class="optinspin-btn">';

        if( isset($_COOKIE['coupon-type_'.$wheel_id]) &&  $_COOKIE['coupon-type_'.$wheel_id] == 'woocommerce' || empty( $_COOKIE['coupon-type_'.$wheel_id] ) )
            $html .= '<div class="optinspin-add-to-cart"><a href="javascript:void(0)" class="optinspin-add-coupon">'.optinspin_get_post_meta($wheel_id,'optinspin_add_to_cart_btn').'</a></div>';

        if( $slide == 1 ){
            $html .= '<div class="optinspin-decline-coupon" style="display: none;"><a href="javascript:void(0)" class="optinspin-coupon-decline">'.optinspin_get_post_meta($wheel_id,'optinspin_skip_btn').'</a></div>';
        }
        $html .= '</div>
                </div>';
        $html .= '<div class="win-coupon" style="display:none"></div>';
        return $html; // Text after winning or losing
    }

    function optinspin_spin_speed( $wheel_id ) {
        return (float) optinspin_get_post_meta($wheel_id,'optinspin_spin_speed');
    }

    function optinspin_number_of_spin( $wheel_id ) {
        return (float) optinspin_get_post_meta($wheel_id,'optinspin_no_of_spin');
    }

    function optinspin_form() {
        $html = '<div class="woo-form"><form>';
        $html .= '<label>Email</label>';
        $html .= '<input type="text" />';
        $html .= '<label>Name</label>';
        $html .= '<input type="text" />';
        $html .= '<input type="button" value="Try Your Luck" />';
        $html .= '</form></div>';

        return $html;
    }

    function optinspin_wheel_script( $wheel_id, $slide ) {
//        echo do_shortcode('[woo_the_wheel]');
        ?>
        <style>
            .woo-wheel-roll {
                background-color: <?php echo optinspin_get_post_meta($wheel_id,'optinspin_background_color') ?> !important;
                top: 0%;
            <?php
            if( $slide == 0 ) { ?>
                position: initial !important;
                width: 100% !important;
                min-height: 600px !important;
                height: auto !important;
                margin-left: 0% !important;
                visibility: visible !important;
            <?php } ?>
            }

            <?php if( $slide == 0 )  {?>
            #bottom_spin_icon {
                display: none !important;
            }

            .woo-wheel-roll-bg {
                display: none !important;
            }

            .wheelContainer {
                left: 46% !important;
                visibility: visible !important;
                opacity: 1 !important;
                top: 60px !important;
            }

            @media only screen and (max-width: 480px) {
                .wheelContainer {
                    left: 0% !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                    top: 60px !important;
                }
            }

            .optinspin-cross-wrapper {
                display:none !important;
            }

            .woo-wheel-roll {
                background-image: url(<?php echo optinspin_get_post_meta($wheel_id,'optinspin_background_image')?>);
            }
            <?php } ?>

            <?php if( $slide == 1 )  {?>
            .woo-wheel-bg-img:before {
                background-image: url(<?php echo optinspin_get_post_meta($wheel_id,'optinspin_background_image')?>);
                width: 100% !important;
                height: 100% !important;
                bottom: 20px;
                opacity: 1;
            }
            <?php } ?>
            #optinspin-simple-btn {
                background-color: <?php echo optinspin_get_post_meta($wheel_id,'optinspin_buttons_color') ?> !important;
                color: <?php echo optinspin_get_post_meta($wheel_id,'optinspin_buttons_text_color') ?>  !important;;
            }
            #optinspin-simple-btn:hover {
                background-color: <?php echo optinspin_get_post_meta($wheel_id,'optinspin_buttons_hover_color') ?>  !important;
            }
            .optinspin-add-to-cart {
                background-color: <?php echo optinspin_get_post_meta($wheel_id,'optinspin_add_cart_bg_color') ?>  !important;
                margin: 10px;
            }
            <?php echo optinspin_get_post_meta($wheel_id,'optinspin_custom_css')?>
        </style>
        <?php
    }

    function optinspin_initially_try_luck_btn() {
        $html = '<div class="optinspin-try-luck-btn" id="optinspin-simple-btn">Want To Try Your Luck!</div>';
        return $html;
    }

    function optinspin_privacy_link( $wheel_id ) {
        $label = optinspin_get_post_meta($wheel_id,'optinspin_privacy_label');
        $page = optinspin_get_post_meta($wheel_id,'optinspin_privacy_page');
        $html = '';
        if( $page != 'none' ){
            $html = '<div class="optinspin-privacy"><a href="'.$page.'">'.$label.'</a></div>';
        }

        return $html ;
    }

    function optinspin_fortune_open( $wheel_id ) {
        global $wp_query;
        $page_id = $wp_query->get_queried_object_id();

        if( !$this->get_optinspin_wheel( $page_id ) )
            return;

        /*foreach( optinspin_get_post_meta($wheel_id,'optinspin_pages_to_show') as $pages ) {
            $pages_to_show[] = $pages['optinspin_show_pages'];
        }*/
        $this->optinspin_get_segments($wheel_id);
        $click_popup = optinspin_get_post_meta($wheel_id,'optinspin_enable_clickable_tab_desktop');
        $btn_class = '';
        if (empty($click_popup)) {
            $btn_class = 'hide';
        }
        $html = '<div id="bottom_spin_icon" class="optinspin-click-btn ' . $btn_class . '">
                    <div class="spin_icon_text">
                        <span class="privy-floating-text">' . optinspin_get_post_meta($wheel_id,'optinspin_spinner_label') . '  </span>
                    </div>
                    <div class="spin_icon_img">
                            <img src="' . optinspin_PLUGIN_URL . '/assets/img/fortune-icon.png" >
                    </div>

                </div>';
        echo $html;
    }

    function optinspin_settings_menu() {
        add_submenu_page( 'crb_carbon_fields_container_optin_spin.php', 'Settings', 'Settings',
            'manage_options', '?page=crb_carbon_fields_container_optin_spin.php');
    }

    function optinspin_wheel_attributes( $wheel_id ){

        $datas= array(

            "colorArray" => $this->optinspin_get_segment_colors( $wheel_id ),

            "segmentValuesArray" => $this->optinspin_get_segments( $wheel_id ),
            "svgWidth" => 1024,
            "svgHeight" => 768,
            "wheelStrokeColor" => $this->optinspin_get_general_settings( $wheel_id )['optinspin_border_color'],
            "wheelStrokeWidth" => $this->optinspin_get_general_settings( $wheel_id )['optinspin_border_width'],
            "wheelSize" => 800,
            "wheelTextOffsetY" => 110,
            "wheelTextColor" => $this->optinspin_get_general_settings( $wheel_id )['optinspin_text_color'],
            "wheelTextSize" => $this->optinspin_get_general_settings( $wheel_id )['optinspin_text_size']."em",
            "wheelImageOffsetY" => 40,
            "wheelImageSize" => 50,
            "centerCircleSize" => 100,
            "centerCircleStrokeColor" =>  $this->optinspin_get_general_settings( $wheel_id )['optinspin_inner_border_color'],
            "centerCircleStrokeWidth" => 12,
            "centerCircleFillColor" => "#EDEDED",
            //"segmentStrokeColor" => "#E2E2E2",
            "segmentStrokeColor" => "#000",
            "segmentStrokeWidth" => 2,
            "centerX" => 512,
            "centerY" => 384,
            "hasShadows" => false,
            "numSpins" => 2,
            "spinDestinationArray" => array(),
            "minSpinDuration" => $this->optinspin_spin_speed( $wheel_id ),
            "gameOverText" => "THANK YOU FOR PLAYING SPIN2WIN WHEEL. COME AND PLAY AGAIN SOON!",
            "invalidSpinText" =>"INVALID SPIN. PLEASE SPIN AGAIN.",
            "introText" => "YOU HAVE TO<br>SPIN IT <span style='color=>#F282A9;'>2</span> WIN IT!",
            "hasSound" => $this->optinspin_get_general_settings( $wheel_id )['optinspin_enable_sound'],
            "gameId" => "9a0232ec06bc431114e2a7f3aea03bbe2164f1aa",
            "clickToSpin" => true
        );

        $_SESSION['wheeldata_'.$wheel_id] = $datas;

        return $datas;
    }

    function optinspin_get_wheel_attributes_callback() {
        $wheel_id = $_POST['wheel_id'];
        $wheel_id = str_replace('_','',$wheel_id);
        $wheel_json = $this->optinspin_wheel_attributes( $wheel_id );
        echo  json_encode($wheel_json, true);
        die();
    }

    function get_optinspin_wheel( $current_page_id ) {

        // GET WHEEL BY CURRENT PAGE ID
        $args = array(
            'post_type'  => 'optin-wheels',
            'fields' => 'ids',
            //'post_status' => array('publish', 'pending', 'draft', 'auto-draft')
        );
        $query = new WP_Query( $args );
        $wheel_id = 0;

        // CHECK CURRENT PAGE AND THIER WHEEL
        if ( $query->have_posts() ) {
            // The 2nd Loop
            while ( $query->have_posts() ) {
                $query->the_post();
                $wheel_id = get_the_ID();

                return $wheel_id;
            }

            // Restore original Post Data
            wp_reset_postdata();
        }


        // APPLY WHEEL
        return 0;
    }

    function revslider_scripts_cleanup() {
        global $wp_scripts;
        if( !is_admin() ) {
            wp_deregister_script('tp-tools');
            wp_dequeue_script('tp-tools');
            wp_deregister_script('revmin');
            wp_dequeue_script('revmin');
        }
    }

}

function optinspin_is_displayed( $wheel_id ) {
    global $post;

    return true;

    global $wp_query;
    $page_id = $wp_query->get_queried_object_id();

    $post_id = get_the_ID();
    $pages_to_show = array();  $posts_to_show =  array();

    if( !empty(optinspin_get_post_meta($wheel_id,'optinspin_display_all_pages') ) )
        return true;


    foreach( optinspin_get_post_meta($wheel_id,'optinspin_show_posts_complex') as $posts ) {
        $posts_to_show[] = $posts['optinspin_show_posts'];
    }

    if( in_array(get_post_type( $post_id ), $posts_to_show) || $this->get_optinspin_wheel( $page_id ) ) {
        return true;
    }

    return false;
}