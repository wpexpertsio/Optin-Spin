<?php

Class OptinSpin_MyCred_Settings {

    function __construct() {
        add_filter('optinspin_wheel_segments',array($this,'optinspin_wheel_segments'),99,3);
        add_action('wp_footer',array($this,'optinspin_custom_event'));
    }

    function optinspin_custom_event() {
        ?>
        <script>
            jQuery(document).ready(function() {
                jQuery(window).on('optinspin_wheel_result', function (result) {

                    if( result.win ) {
                        if (result.coupon_type != 'wocoommerce' && result.coupon_type != 'mycred_points') {
                            jQuery('.winning_lossing .optinspin-add-to-cart').hide();
                        }

                        // When use MyCred Points
                        if (result.coupon_type == 'mycred_points') {
                            var ajaxurl = optinspin_wheel_spin.ajax_url;
                            var data = {
                                'action': 'optinspin_coupon_request',
                                'request_to': 'mycred_points',
                                'mycred_log_template': result.mycred_log_template,
                                'mycred_points': result.coupon,
                                'mycred_point_type': result.point_type,
                                'win': 'true'
                            };
                            jQuery.post(ajaxurl, data, function (response) {
                            });
                        }
                    } else {
                        // When use MyCred Points
                        if( result.loss_section_have_mycred_points == 'enabled' ) {
                            var ajaxurl = optinspin_wheel_spin.ajax_url;
                            var data = {
                                'action': 'optinspin_coupon_request',
                                'request_to': 'mycred_points',
                                'mycred_points': result.mycred_loss_points,
                                'mycred_log_template': result.mycred_log_template,
                                'mycred_point_type': result.point_type,
                                'win': 'false'
                            };
                            jQuery.post(ajaxurl, data, function(response) {
                            });
                        }
                    }
                });
            });
        </script>
        <?php
    }

    function optinspin_wheel_segments( $segments_each, $sections, $section ) {
       
        if( isset( $section['optinspin_mycred_points_check'] ) && !empty( $section['optinspin_mycred_points_check'] ) ) {
            $mycred_points_check_for_loss = 'enabled';
            $mycred_loss_points = $section['optinspin_mycred_points'];
            $mycred_point_types = $section['optinspin_mycred_types'];
        }

        $mycred_log_template = '';
        if( isset( $section['optinspin_mycred_log_template'] ) && !empty( $section['optinspin_mycred_log_template'] ) ) {
            $mycred_point_types = $section['optinspin_mycred_types'];
            $mycred_log_template = $section['optinspin_mycred_log_template'];
        }

        $segments_mycred = array();
        $segments_mycred['loss_section_have_mycred_points'] = $mycred_points_check_for_loss;
        $segments_mycred['mycred_loss_points'] = $mycred_loss_points;
        $segments_mycred['mycred_point_types'] = $mycred_point_types;
        $segments_mycred['mycred_log_template'] = $mycred_log_template;

        $segments_each = array_merge($segments_each,$segments_mycred);

        return $segments_each;

    }

    // Check mycred Enabled or not
    function optinspin_is_mycred_emabled() {

        if( class_exists ( 'myCRED_Core' ) ) {
            return true;
        }

        return false;
    }
}