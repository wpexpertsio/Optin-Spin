<?php

class optinspin_Coupon_Request extends optinspin_Subscriber {

    function __construct() {
        add_action( 'wp_footer', array($this,'optinspin_coupon_request_javascript') );
        add_action( 'wp_footer', array($this,'optinspin_coupon_request_style') );
        add_action( 'wp_ajax_optinspin_coupon_request', array($this,'optinspin_coupon_request_callback') );
        add_action( 'wp_ajax_nopriv_optinspin_coupon_request', array($this,'optinspin_coupon_request_callback') );
        add_action( 'wp_footer', array($this,'optinspin_coupon_bar') );
    }
	
	function optinspin_coupon_request_style() {
		?>
		<style>
		.optinspin-optin-bar {
			background-color: red;
			width: 100%;
			color: white;
			font-weight: bold;
			padding: 8px;
			text-align: center;
			position: fixed;
			z-index: 999999;
			bottom: 0px;
			font-size: 14px;
			box-shadow: 0px 0px 4px #ababab;
		}
		</style>
		<?php
	}

    function optinspin_coupon_bar() {
        if( !isset( $_SESSION['wheel_id'] ) )
            return;
		
        $coupon_msg = optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_coupon_bar_msg');
        $html = '';
		if( isset($_COOKIE['optinspin_coupon_code_'.$_SESSION['wheel_id']]) ) {
            $coupon_msg = str_replace('{coupon}',$_COOKIE['optinspin_coupon_code_'.$_SESSION['wheel_id']],$coupon_msg);
		}

        $style = '';
        if( !empty( optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_disable_coupon_bar') ) || isset( $_COOKIE['optinspin-cross-couponbar'] )  || !isset($_COOKIE['optinspin_coupon_code_'.$_SESSION['wheel_id']])  )
            $style = 'hide';

        $html = '<div class="optinspin-optin-bar '.$style.'"><span class="optinspin-congo">'.$coupon_msg.'</span><span class="cancel">X</span></div>';
        echo $html;
    }

    function optinspin_coupon_request_javascript() {

        global $optinspin_Chatchamp;
        $chatchamp_subscriber = json_decode( $optinspin_Chatchamp->optinspin_get_chatchamp_subscriber_name()['body'] );
        $u_name = 'OptinSpin';
        if( is_object( $chatchamp_subscriber ) && $chatchamp_subscriber->subscriber->firstName != '' )
            $u_name = $chatchamp_subscriber->subscriber->firstName;

        $this->optinspin_win_loss_style(); ?>
        <script type="text/javascript" >
            jQuery(document).ready(function($) {
                var click_test = 0;
                jQuery('.optinspin-name, .optinspin-email').keyup(function (e) {
                    if (e.keyCode === 13 && click_test == 0) {
                        click_test = 1;
                        jQuery('.optinspin-form-btn').trigger('click');
                    }
                });

                var is_required = 0;
                var form_data = '';
                var form_save_data = [];
                var error_msg = '<?php echo __('Please Fill Up all the Required Fields','optinspin') ?>';

                jQuery('.optinspin-form-btn').click(function(e) {

                    var counter = 0;

                    jQuery('.optinspin-notify-field').remove();
                    jQuery('.optinspin-error').hide();

                    is_required = 0;
                    jQuery('.optinspin-from input,select').css('border','solid 0px');

                    form_data = '{'; var comma = ',';
                    var total_fields = jQuery('.optinspin-from input,select').length;
                    jQuery('.optinspin-from input,select').each(function() {
                        counter++;
                        if( counter == total_fields )
                            comma = '';

                        form_data += '"'+jQuery(this).attr('name')+ '":"'+jQuery(this).val()+'"'+comma;

                            var required_field = jQuery(this).attr('required');
                            var field_name = jQuery(this).attr('name');
                            var field_type = jQuery(this).attr('type');

                            if( field_type == 'checkbox' ) {
                                if (typeof required_field !== typeof undefined && required_field !== false) {
                                    if ( !jQuery(this).is(':checked') ) {
                                        jQuery('.optinspin-error').html('<?php echo __('Please Check ','optinspin') ?>' + field_name );
                                        jQuery('.optinspin-error').show();
                                        is_required = 1;
                                        error_msg = '<?php echo __('Please Check ','optinspin') ?>' + field_name;
                                    }
                                }
                            }

                            if (typeof required_field !== typeof undefined && required_field !== false) {
                                if( jQuery(this).val() == '' ) {
                                    is_required = 1;
                                    error_msg = '<?php echo __('Please Fill Up all the Required Fields','optinspin') ?>';
                                    jQuery(this).css('border','solid 2px #d83c3c');
                                    e.preventDefault();
                                }
                            }

                    });

                    form_data += '}';

                    var cookie_expiry = 1;
                    <?php if( isset( $_SESSION['wheel_id'] ) ) { ?>
                        cookie_expiry = <?php echo ( optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_cookie_expiry') == '' ? 0 : optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_cookie_expiry') ) ?>;
                    <?php } ?>

                    setCookie('form_data'+current_wheel_id,form_data,cookie_expiry);

                    if( is_required == 1 ) {
                        jQuery('.optinspin-error').html(error_msg);
                        jQuery('.optinspin-error').show();
                        return;
                    }

                    var name = ''; var error_count = 1;
                    name = jQuery('.name-field').val();
                    if( name == '' ){
                        name = 'Guest';
                        error_count = 0;
                    }
                    var email = jQuery('.optinspin-form-field.optinspin-email').val();

                    var chatchamp_validate = getCookie('chatchamp_validate'+current_wheel_id);
                    var chatchamp_enabled = 0;
                    <?php if( !empty( optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_chatchamp_enabled') ) ) { ?>
                    chatchamp_enabled = 1;
                    <?php } ?>
                    if( ( name != '' && email != '' ) || ( ( chatchamp_validate == '' || chatchamp_validate == 1 ) && chatchamp_enabled == 1) ) {
                        if( optinspin_isValidEmailAddress( email ) || ( ( chatchamp_validate == '' || chatchamp_validate == 1 ) && chatchamp_enabled == 1) ) {
                            if( chatchamp_validate == 1 ) {
                                name = '<?php echo $u_name?>';
                                email = 'ChatChamp';
                            }
                            optinspin_add_subsriber( name, email );
                        } else {
                            jQuery('.optinspin-error').html('<?php echo __('Email is invalid','optinspin') ?>');
                            jQuery('.optinspin-error').show();
                            jQuery('.optinspin-right').animate({
                                scrollTop: 100
                            }, 'slow');
                        }
                    } else {
                        if( error_count == 1)
                            jQuery('.optinspin-error').html('<?php echo __('Email is required','optinspin') ?>');
                        else if( chatchamp_validate == 0 && chatchamp_enabled == 1 )
                            jQuery('.optinspin-error').html('<?php echo __('You must sign in with Facebook','optinspin') ?>');
                        else
                            jQuery('.optinspin-error').html('<?php echo __('Email & name are required','optinspin') ?>');

                        jQuery('.optinspin-error').show();
                        jQuery('.optinspin-right').animate({
                            scrollTop: 100
                        }, 'slow');
                    }
                    var spin_width = jQuery(window).width();
                    setCookie('optinspin_spin_start'+current_wheel_id,1,1);
                    setCookie('optinspin_spin_width'+current_wheel_id,spin_width,1);
                });
            });

            function optinspin_isValidEmailAddress(emailAddress) {
                var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
                return pattern.test(emailAddress);
            }

            function optinspin_add_subsriber( name, email) {

                jQuery('.lds-css.ng-scope').show();
                jQuery('.optinspin-from').css('opacity','0.5');
                jQuery('.optinspin-error').html('');

                var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

                var wheel_id = current_wheel_id.split('_');

                var data = {
                    'action': 'optinspin_coupon_request',
                    'request_to': 'coupon_request',
                    'name': name,
                    'email': email,
                    'current_wheel_id': wheel_id[1],
                };

                var cookie_expiry = <?php echo ( optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_cookie_expiry') == '' ? 0 : optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_cookie_expiry') ) ?>;

                if(cookie_expiry == 0){
                    setCookie('optinspin_email_for_zero'+current_wheel_id,email,1000);
                    setCookie('optinspin_user_for_zero'+current_wheel_id,name,1000);
                }

                setCookie('optinspin_email'+current_wheel_id,email,cookie_expiry);
                setCookie('optinspin_user'+current_wheel_id,name,cookie_expiry);

                jQuery.post(ajaxurl, data, function(response) {
                    var width = jQuery(window).width();
                    var chatchamp_enabled = 0;
                    <?php if( !empty( optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_chatchamp_enabled') ) ) { ?>
                    chatchamp_enabled = 1;
                    <?php } ?>

                    var allow_same_email = false;
                    <?php
                    if( !empty( optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_allow_same_email') ) ) { ?>
                    allow_same_email = true;
                    <?php } ?>
                    var chatchamp_validate = getCookie('chatchamp_validate'+current_wheel_id);
                    if( response == true || ( ( chatchamp_validate == '' || chatchamp_validate == 1 ) && chatchamp_enabled == 1 ) || allow_same_email == true ) {
                        jQuery('.optinspin-from').fadeOut();
                        jQuery('.optinspin-error').hide();
//                        jQuery('.optinspin-right').fadeOut();

                        setTimeout(function() {
                            jQuery('.lds-css.ng-scope').hide();
                            jQuery('.optinspin-from').css('opacity','1');
                            jQuery('.optinspin-intro').hide();
                            if( width > 480 ) {
                                jQuery('.wheelContainer').animate({
                                    'marginLeft': "30%"
                                }, 300, function () {
                                    jQuery('.optinspin-error').hide();
                                    jQuery('.optinspin-cross-wrapper').hide();
                                    jQuery('.spinBtn').trigger('click');
                                    click_test = 0;
                                });
                            } else if( width <= 480 ) {
                                jQuery('.wheelContainer').animate({
                                    'marginLeft': "0%"
                                }, 300, function () {
                                    jQuery('.optinspin-error').hide();
                                    jQuery('.optinspin-cross-wrapper').hide();
                                    jQuery('.spinBtn').trigger('click');
                                    if (getCookie('optinspin_slide' + current_wheel_id) == 1) {
                                        spin_480_start();
                                    } else {
                                        jQuery( ".optinspin-right" ).animate({
                                            height: "100px"
                                        }, 2000);
                                    }
                                    click_test = 0;
                                });
                            }
                        },1000);
                    } else {
                        jQuery('.lds-css.ng-scope').hide();
                        jQuery('.optinspin-from').css('opacity','1');
                        jQuery('.optinspin-error').html('<?php echo __('Email Already Exist!','optinspin')?>');
                        jQuery('.optinspin-error').show();
                        jQuery('.optinspin-right').animate({
                            scrollTop: 100
                        }, 'slow');
                        click_test = 0;
                    }
                });
            }
        </script> <?php
    }

    function optinspin_coupon_request_callback() {

        $wheel_id = $_POST['current_wheel_id'];

        if( $_POST['request_to'] == 'coupon_request' ) {
            $name = sanitize_text_field( $_POST['name'] );
            $email = sanitize_text_field( $_POST['email'] );
            $subscribe = $this->optinspin_add_new_subscriber( $name, $email );
            echo $subscribe;
            die();
        } else if( $_POST['request_to'] == 'apply_coupon' ) {
            $coupon = get_the_title( $_POST['coupon'] );
            $this->optinspin_apply_coupon_on_cart( $coupon );
        } else if( $_POST['request_to'] == 'send_email' ) {
            $coupon = get_the_title( $_POST['coupon'] );
            $email_temp = $_POST['email_temp'];
            if(empty(optinspin_get_post_meta($wheel_id,'optinspin_disable_email_shoot')))
                $this->optinspin_send_coupon_email( $_COOKIE['optinspin_email_'.$wheel_id],$coupon,$email_temp,$wheel_id);
        } else if( $_POST['request_to'] == 'get_coupon' ) {
            echo get_the_title( $_POST['coupon'] );
        } else if( $_POST['request_to'] == 'get_coupon_expiry' ) {
            $coupon_id = (int) $_POST['coupon_id'];
            echo get_post_meta($coupon_id,'expiry_date',true);
        } else if( $_POST['request_to'] == 'generate_coupon' ) {
            $coupon_code = $this->optinspin_unique_coupon(); // Code
            if( !empty( post_exists($coupon_code) ) ) {
                $coupon_code = $this->optinspin_unique_coupon(); // Code
            }
            $amount = $_POST['coupon_discount']; // Amount
            $days = $_POST['coupon_expire'];
            $coupon_expire = date('Y-m-d', strtotime("+".$days." days")); // Amount
            $discount_type = 'percent'; // Type: fixed_cart, percent, fixed_product, percent_product

            $coupon = array(
                'post_title' => $coupon_code,
                'post_content' => '',
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type'		=> 'shop_coupon'
            );

            $new_coupon_id = wp_insert_post( $coupon );

            // Add meta
            update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
            update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
            update_post_meta( $new_coupon_id, 'individual_use', 'no' );
            update_post_meta( $new_coupon_id, 'product_ids', '' );
            update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
            update_post_meta( $new_coupon_id, 'usage_limit', '1' );
            update_post_meta( $new_coupon_id, 'expiry_date', $coupon_expire );
            update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );
            update_post_meta( $new_coupon_id, 'free_shipping', 'no' );

            echo $new_coupon_id.'_'.$coupon_code.'_'.$coupon_expire;
        } else if( $_POST['request_to'] == 'count_section_win' ) {
            $section_id = $_POST['section_id'];
            $count_section_win = (int) get_option('complex_'.optinspin_crb_get_i18n_suffix().'_'.$section_id);
            if( !empty($section_id) ) {
                $count_section_win++;
                update_option('complex_'.optinspin_crb_get_i18n_suffix().'_'.$section_id,$count_section_win);
            } else {
                update_option('complex_'.optinspin_crb_get_i18n_suffix().'_'.$section_id,1);
            }
        } else if( $_POST['request_to'] == 'flush_availablity' ) {
            $section_id = $_POST['section_id'];
            delete_option('complex_'.optinspin_crb_get_i18n_suffix().'_'.$section_id);
        } else if( $_POST['request_to'] == 'save_form_data' ) {

        } else if( $_POST['request_to'] == 'mycred_points' ) {
            if( $_POST['win'] == 'true' ) {
                $log_template = $_POST['mycred_log_template'];
                $mycred_point_type = $_POST['mycred_point_type'];
                mycred_add( 'optinspin', get_current_user_id(), $_POST['mycred_points'], $log_template,0,'',$mycred_point_type );
            } else if( $_POST['win'] == 'false' ) {
                $points = -1 * abs($_POST['mycred_points']);
                $log_template = $_POST['mycred_log_template'];
                $mycred_point_type = $_POST['mycred_point_type'];
                mycred_add( 'optinspin', get_current_user_id(), $points, $log_template,0,'',$mycred_point_type );
            }
//            echo get_current_user_id();
        }
        die(); // this is required to terminate immediately and return a proper response
    }

    function optinspin_apply_coupon_on_cart( $coupon_code ) {
        global $woocommerce;
        $coupon_code = $coupon_code;
        $woocommerce->cart->add_discount( $coupon_code );

        die(); // this is required to terminate immediately and return a proper response
    }

    function optinspin_send_coupon_email( $to, $coupon, $email_temp,$wheel_id ) {

        if( $email_temp == 'win' ) {
            $subject = optinspin_get_post_meta($wheel_id,'optinspin_email_subject');
            $msg = optinspin_get_post_meta($wheel_id,'optinspin_email_body');
        } else {
            $subject = optinspin_get_post_meta($wheel_id,'optinspin_loss_email_subject');
            $msg = optinspin_get_post_meta($wheel_id,'optinspin_loss_email_body');
        }

        if( !empty( $_COOKIE['optinspin_user_'.$wheel_id] ) && $_COOKIE['optinspin_user_'.$wheel_id] != 'undefined' )
            $username = $_COOKIE['optinspin_user_'.$wheel_id];
        else
            $username = 'Guest';

        $expire_date = ''; $link = '';

        if( $_COOKIE['coupon-type_'.$wheel_id] == 'woocommerce' ) {
            if ( class_exists('WC_Coupon') ) {
                $coupon_obj = new WC_Coupon( $coupon );
                $expire_date = $coupon_obj->get_date_expires();
            }
        } else if( $_COOKIE['coupon-type_'.$wheel_id] == 'coupon_link'  ) {
            $coupon = $_COOKIE['optinspin_coupon_code_'.$wheel_id];
            $coupon = '<a style="text-decoration:none" href="'.$_COOKIE['optinspin_coupon_link_'.$wheel_id].'">'.$coupon.'</a>';
        } else {
            $coupon = $_COOKIE['optinspin_coupon_code_'.$wheel_id];
        }

        $msg = str_replace('{user}',$username,$msg);
        $msg = str_replace('{coupon}',$coupon,$msg);
        $msg = str_replace('{validity}',$expire_date,$msg);
        $msg = str_replace('{label}',$_COOKIE['section-label_'.$wheel_id],$msg);

        if ( class_exists('WC_Emails') ) {
            $wc_email = WC()->mailer();
            $wc_email->send( $to, $subject, $wc_email->wrap_message($subject,$msg) );
        } else if( $_COOKIE['coupon-type_'.$wheel_id] == 'edd_coupon' ) {
            $emails = EDD()->emails;
            $emails->send( $to, $subject, $msg );
        } else {
            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail( $to, $subject, $msg, $headers );
        }
    }

    function optinspin_win_loss_style() {
        ?>
        <canvas id="world"></canvas>
        <style>
            .winning_lossing {
                background-color: <?php echo optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_win_background_color') ?>;
                color: <?php echo optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_win_text_color') ?>;
                border: <?php echo optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_win_border_color') ?>;
                font-size: <?php echo optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_font_size') ?>;
            }
            .winning_lossing a {
                color: <?php echo optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_add_cart_link_color') ?> !important;
                text-decoration: none;
            }
            .optinspin-decline-coupon a {
                color: <?php echo optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_skip_link_color') ?>  !important;
            }
            .optinspin-win-info {
                color: <?php echo optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_coupon_msg_text_color') ?>  !important;
                background-color: <?php echo optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_coupon_msg_bg') ?>  !important;
            }
            .optinspin-win-info a {
                text-decoration: underline;
            }
            .optinspin-optin-bar {
                color: <?php echo optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_coupon_bar_color') ?>  !important;
                background-color: <?php echo optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_coupon_bar_bg') ?>  !important;
            }
            span.exp-time {
                background-color: <?php echo optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_coupon_bar_timer_color') ?>  !important;
                color: <?php echo optinspin_get_post_meta($_SESSION['wheel_id'],'optinspin_coupon_bar_timer_text_color') ?>  !important;
            }
			#world {
				display:none;
			}
        </style>
        <?php
    }

    function optinspin_unique_coupon() {
        $str = 'abcdefghijklmnopqrstuvwxyz01234567891011121314151617181920212223242526';
        $shuffled = str_shuffle($str);
        $shuffled = substr($shuffled,1,5);
        return $shuffled;
    }
}