<?php

Class optinspin_Protect {

    function __construct() {
        add_action( 'wp_footer', array($this,'optinspin_password_javascript') );
        add_action( 'wp_ajax_optinspin_password', array($this,'optinspin_password_callback') );
        add_action( 'wp_ajax_nopriv_optinspin_password', array($this,'optinspin_password_callback') );
    }

    function optinspin_password_protect_html( $wheel_id ) {
        $html = '<div class="optinspin-password">';
        $html .= '<div class="optinspin-label">'.optinspin_get_post_meta($wheel_id,'optinspin_protect_label_text').'</div>';
        $html .= '<div class="optinspin-password-field"><input class="optinspin-pass" type="text"  /></div>';
        $html .= '<div class="optinspin-password-check"><input class="optinspin-pass-apply" type="button" value="Enter" /></div>';
        $html .= '</div>';

        return $html;
    }

    function optinspin_protect_style() {
        ?>
        <style>
            .optinspin-pass-apply {
                background-color:<?php echo optinspin_get_post_meta('optinspin_password_button_background_color') ?> !important;
            }
            .optinspin-pass-apply:hover {
                background-color:<?php echo optinspin_get_post_meta('optinspin_password_button_background_color') ?> !important;
            }
        </style>
        <?php
    }

    function optinspin_protection_is_enabled( $wheel_id ) {
        $is_enabled = optinspin_get_post_meta($wheel_id,'optinspin_enabled_password_protect');
        if(!empty($is_enabled))
            return true;
        else
            return false;
    }

    function optinspin_hide_form() {
        ?>
        <script>
            jQuery(document).ready(function() {
               jQuery('.optinspin-from form').hide();
               jQuery('.optinspin-intro').css('opacity',0);
               jQuery('.wlo_small_text').hide();
                jQuery('.fb-send-to-messenger').hide();
            });
        </script>
        <?php
    }

    function optinspin_password_javascript() { ?>
        <script type="text/javascript" >
            jQuery(document).ready(function($) {
                jQuery('.optinspin-pass-apply').click(function() {
                    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
                    var optinspin_pass = jQuery('.optinspin-pass').val();

                    jQuery('.ng-scope').show();
                    jQuery('.optinspin-error').hide();
                    jQuery('.optinspin-password').css('opacity','0.5');

                    var wheel_id = jQuery('.optinspin_wheel_id').text();

                    var data = {
                        'action': 'optinspin_password',
                        'password': optinspin_pass,
                        'wheel_id': wheel_id
                    };
                    $.post(ajaxurl, data, function(response) {
                        jQuery('.ng-scope').hide();
                        jQuery('.optinspin-password').css('opacity','1');
                        if(response == 'MATCHED') {
                            jQuery('.optinspin-password').fadeOut("slow",function() {
                                jQuery('.optinspin-from form').fadeIn();
                                jQuery('.wlo_small_text').show();
                                jQuery('.optinspin-intro').css('opacity',1);
                                jQuery('.fb-send-to-messenger').show();
                            });
                        } else {
                            jQuery('.optinspin-error').html('<?php echo __('Invalid Password','optinspin')?>');
                            jQuery('.optinspin-error').show();
                        }
                    });
                });
            });
        </script> <?php
    }

    
    function optinspin_password_callback() {
        $entered_password = $_POST['password'];
        $wheel_id = $_POST['wheel_id'];
        $saved_password = optinspin_get_post_meta($wheel_id,'optinspin_password_protect');
        if( $saved_password == $entered_password )
            echo 'MATCHED';
        else
            echo 'NOT MATCHED';
        die();
    }
}