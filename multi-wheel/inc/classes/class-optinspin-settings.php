<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

if( ( isset( $_GET['post'] ) && get_post_status( $_GET['post'] ) == 'draft' ) || !isset( $_GET['post'] ) )
    $ready_msg = '<div class="opt-note"><strong>NOTE:</strong> Your wheel is not visible to your users, until you publish it!</div>';

Class optinspin_Settings {

    public $wheel_slices_labels = array('slices' => 'Slices','slice' => 'Slice');

    function __construct() {
        add_action( 'carbon_fields_register_fields', array($this,'optinspin_add_settings_page') );
        add_action( 'after_setup_theme', array($this,'optinspin_crb_load') );
    }

    function optinspin_add_settings_page() {
        global $wheel_slices_labels,$ready_msg;
        $optinspin_mailchimp_get_list = get_option( 'optinspin_mailchimp_get_list');
        if(!empty($optinspin_mailchimp_get_list)){
            $optinspin_mailchimp_get_list = json_decode($optinspin_mailchimp_get_list);
            if(!empty($optinspin_mailchimp_get_list->data)){
                $arraysfor_optinspin_mailchimp_get_list[''] = 'Select Email List';
                foreach($optinspin_mailchimp_get_list->data as $data){
                    $arraysfor_optinspin_mailchimp_get_list[$data->id] =  $data->name;
                }
            } else {
                $arraysfor_optinspin_mailchimp_get_list[''] = 'Email List not found!';
            }
        }else{
            $arraysfor_optinspin_mailchimp_get_list[''] = 'Email List not found!';
        }

        $optinspin_active_campaign_get_list = get_option( 'optinspin_active_campaign_get_list');
        if(!empty($optinspin_active_campaign_get_list)){
            if(!empty($optinspin_active_campaign_get_list)){
                $arraysfor_optinspin_active_campaign_get_list[''] = 'Select Email List';
                foreach($optinspin_active_campaign_get_list as $data){
                    if(!empty($data['id']) and !empty($data['name'])){
                        $arraysfor_optinspin_active_campaign_get_list[$data['id']] =  $data['name'];
                    }
                }

            } else {
                $arraysfor_optinspin_active_campaign_get_list[''] = 'Email List not found!';
            }
        }else{
            $arraysfor_optinspin_active_campaign_get_list[''] = 'Email List not found!';
        }
        if(function_exists( 'mailster' )){
            $lists = mailster( 'lists' )->get();
            $mailsteractive = 1;
            update_option('_optinspin_mailsteractive',1);
            if(!empty($lists)){
                $arraysfor_optinspin_mailster_get_list[''] = 'Select Email List';
                foreach($lists as $list){
                    $arraysfor_optinspin_mailster_get_list[$list->ID] = $list->name;
                }
            } else {
                $arraysfor_optinspin_mailster_get_list[''] = 'Email List not found!';
            }
        } else {
            update_option('_optinspin_mailsteractive',0);
            $arraysfor_optinspin_mailster_get_list = array();
            $mailsteractive = 0;
        }

        $form_fileds_labels = array(
            'plural_name' => 'Fields',
            'singular_name' => 'New Field',
        );

        Container::make( 'post_meta', 'Wheel Settings' )
            ->where( 'post_type', '=', 'optin-wheels' )
            ->set_priority( 'low' )
            // ->set_page_file( 'optinspin-settings' )
            ->add_tab( __('Title'), array(
                Field::make( 'text', 'optinspin_title'.optinspin_crb_get_i18n_suffix(), 'Set Your Wheel Title' ),
            ) )

            ->add_tab( __('Slices'), array(

                Field::make( 'complex', 'crb_section'.optinspin_crb_get_i18n_suffix(), 'Slices' )
                    ->set_collapsed( true )
                    ->setup_labels( array(
                        'plural_name' => 'Slices',
                        'singular_name' => 'Slice',
                    ) )
                    ->add_fields( 'no_prize', array(

                        Field::make( 'text', 'optinspin_section_label', 'Label' )
                            ->set_help_text('Label of wheel section')
                            ->set_classes( 'optinspin_section_label' ),

                        Field::make( 'checkbox', 'optinspin_mycred_points_check', 'Enable MyCred Points' )
                            ->set_option_value( 'enable_mycred_points' ),

                        Field::make( 'select', 'optinspin_mycred_types', 'Point Type' )
                            ->add_options( $this->mycred_get_types_settings() )
                            ->set_help_text('Select Your Point Type')
                            ->set_conditional_logic( array(
                                array(
                                    'field' => 'optinspin_mycred_points_check',
                                    'value' => true,
                                )
                            ) ),

                        Field::make( 'text', 'optinspin_mycred_points', 'Points' )
                            ->set_help_text('Set Lossing Points')
                            ->set_conditional_logic( array(
                                array(
                                    'field' => 'optinspin_mycred_points_check',
                                    'value' => true,
                                )
                            ) ),

                        Field::make( 'text', 'optinspin_mycred_log_template', 'Loss Log Tempalte' )
                            ->set_help_text('Set Lossing Points')
                            ->set_default_value('Deduct %plural% for lossing Spin from OptinSpin')
                            ->set_conditional_logic( array(
                                array(
                                    'field' => 'optinspin_mycred_points_check',
                                    'value' => true,
                                )
                            ) ),

                        Field::make( 'color', 'segment_color', 'Section color' )
                            ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                            ->set_help_text( 'Set the color of the respective section/segment' ),
                        Field::make( 'textarea', 'optinspin_win_loss_text', 'Lossing text' ),
                        Field::make( 'text', 'optinspin_probability', 'Probability' )
                            ->set_attribute('type','number')
                            ->set_required( true )
                            ->set_help_text( 'How much chances to stop at this segment ( 0 - 100 )' ),

                    ))->set_collapsed( true )
                    ->add_fields( 'win_prize', array(

                        Field::make( 'text', 'optinspin_section_label', 'Label' )
                            ->set_help_text('Label of wheel section')
                            ->set_classes( 'optinspin_section_label' ),


                        Field::make( 'radio', 'optinspin_woocommerce_type', 'Coupon Type' )
                            ->add_options( $this->optinspin_coupon_options() ),

                        Field::make( 'checkbox', 'optinspin_section_generate_coupon', 'Generate Coupon Automatically' )
                            ->set_option_value( 'Generate Coupon Automatically' )
                            ->set_classes( 'optinspin_generate_coupon_checkbox' )
                            ->set_conditional_logic( array(
                                array(
                                    'field' => 'optinspin_woocommerce_type',
                                    'value' => 'woocommere_coupon',
                                )
                            ) ),

                        Field::make( 'text', 'optinspin_section_discount', 'Coupon Discount in %' )
                            ->set_help_text('Set Coupon Discount')
                            ->set_conditional_logic( array(
                                array(
                                    'field' => 'optinspin_section_generate_coupon',
                                    'value' => true,
                                ), array(
                                    'field' => 'optinspin_woocommerce_type',
                                    'value' => 'woocommere_coupon',
                                )
                            ) ),

                        Field::make( 'select', 'optinspin_mycred_types', 'Point Type' )
                            ->add_options( $this->mycred_get_types_settings() )
                            ->set_help_text('Select Your Point Type')
                            ->set_conditional_logic( array(
                                array(
                                    'field' => 'optinspin_woocommerce_type',
                                    'value' => 'optinspin_mycred',
                                )
                            ) ),

                        Field::make( 'text', 'optinspin_mycred_points', 'Points' )
                            ->set_help_text('Set Winning Points')
                            ->set_conditional_logic( array(
                                array(
                                    'field' => 'optinspin_woocommerce_type',
                                    'value' => 'optinspin_mycred',
                                )
                            ) ),

                        Field::make( 'text', 'optinspin_mycred_log_template', 'Win Log Tempalte' )
                            ->set_help_text('Set Lossing Points')
                            ->set_default_value('Reward %plural% for winning Spin from OptinSpin')
                            ->set_conditional_logic( array(
                                array(
                                    'field' => 'optinspin_woocommerce_type',
                                    'value' => 'optinspin_mycred',
                                )
                            ) ),

                        Field::make( 'text', 'optinspin_section_discount_expiry_day', 'Coupon Expire Time' )
                            ->set_help_text('Coupon Expire in Days')
                            ->set_conditional_logic( array(
                                array(
                                    'field' => 'optinspin_section_generate_coupon',
                                    'value' => true,
                                ),
                                array(
                                    'field' => 'optinspin_woocommerce_type',
                                    'value' => 'woocommere_coupon',
                                )
                            ) ),

                        Field::make( 'select', 'optinspin_coupon', 'Coupon' )
                            ->add_options( $this->get_list_coupons() )
                            ->set_help_text('Choose Coupon for this section')
                            ->set_classes( 'optinspin_coupon_list' )
                            ->set_conditional_logic( array(
                                array(
                                    'field' => 'optinspin_section_generate_coupon',
                                    'value' => false,
                                ), array(
                                    'field' => 'optinspin_woocommerce_type',
                                    'value' => 'woocommere_coupon',
                                )
                            ) ),

                        Field::make( 'select', 'optinspin_edd_coupon', 'EDD Coupon' )
                            ->add_options( $this->get_edd_coupon_list() )
                            ->set_help_text('Choose Coupon for this section')
                            ->set_classes( 'optinspin_coupon_list' )
                            ->set_conditional_logic( array(
                                array(
                                    'field' => 'optinspin_woocommerce_type',
                                    'value' => 'edd_coupon',
                                )
                            ) ),

                        Field::make( 'text', 'optinspin_coupon_text_label', 'Coupon Text Label' )
                            ->set_conditional_logic( array(
                                array(
                                    'field' => 'optinspin_woocommerce_type',
                                    'value' => 'coupon_text',
                                )
                            ) ),

                        Field::make( 'text', 'optinspin_coupon_link_label', 'Coupon Link Label' )
                            ->set_conditional_logic( array(
                                array(
                                    'field' => 'optinspin_woocommerce_type',
                                    'value' => 'coupon_link',
                                )
                            ) ),

                        Field::make( 'text', 'optinspin_coupon_link_url', 'Coupon Link URL' )
                            ->set_conditional_logic( array(
                                array(
                                    'field' => 'optinspin_woocommerce_type',
                                    'value' => 'coupon_link',
                                )
                            ) ),

                        Field::make( 'text', 'optinspin_max_availability', 'Max Availability' )
                            ->set_attribute('type','number'),

                        Field::make( 'text', 'optinspin_unique_section_id', 'unique id' )
                            ->set_attribute( 'type', 'hidden' )
                            ->set_classes( 'optinspin_section_class' ),

                        Field::make( 'text', 'optinspin_flush_availability', 'Clear number of wins' )
                            ->set_attribute( 'type', 'button' )
                            ->set_classes( 'optinspin_flush_availability' )
                            ->set_default_value('Clear number of wins'),

                        Field::make( 'text', 'optinspin_probability', 'Probability' )
                            ->set_attribute('type','number')
                            ->set_required( true ),
                        Field::make( 'color', 'segment_color', 'Section color' )
                            ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                            ->set_help_text( 'Set the color of the respective section/segment' ),
                        Field::make( 'textarea', 'optinspin_win_loss_text', 'Winning text' ),
                    ))->set_collapsed( true ),
            ) )

            ->add_tab( __('Integration'), array(

                Field::make( 'html', 'optinpsin_integration_mailchimp' )
                    ->set_html( '<div class="opt-integration-icon">
                                    <img src="'.optinspin_PLUGIN_URL.'assets/img/mailchimp.png" width="30">
                                <span>MailChimp</span></div>' ),

                Field::make( 'text', 'optinspin_mailchimp_api_key', 'API KEY' )
                    ->set_help_text('Enter Mailchimp API Key'),

                Field::make( 'select', 'crb_show_socials', 'Mailchimp Email Lists' )
                    ->add_options( $arraysfor_optinspin_mailchimp_get_list ),

                Field::make( 'radio', 'opt_ins', 'Single Opt-in Or Double Opt-in' )
                    ->add_options( array(
                        'single' => 'Single Opt-in',
                        'double' => 'Double Opt-in'
                    ) ),

                Field::make( 'text', 'optinspin_mailchimp_get_list', 'Get Mailchimp Email List' )
                    ->set_default_value( 'Get Mailchimp Email List' )
                    ->set_attribute( 'type', 'button' )
                    ->set_classes( 'get_mailchimp' ),

                Field::make( 'html', 'optinpsin_integration_chatchamp' )
                    ->set_html( '<div class="opt-integration-icon">
                                    <img src="'.optinspin_PLUGIN_URL.'assets/img/chatchamp.png" width="120">
                                <span></span></div>' ),

                Field::make( 'checkbox', 'optinspin_chatchamp_enabled', 'Enable ChatChamp' )
                    ->set_option_value( 'Enable Chatchamp' ),

                Field::make( 'text', 'optinspin_chatchamp_id', 'Enter ChatChamp ID' )
                    ->set_help_text('Enter ChatChamp ID'),

            ))

            ->add_tab( __('Ready'), array(
                Field::make( 'html', 'crb_information_text' )
                    ->set_html( '<div class="opt-wheel-ready-wrapper">
                                    <h1>GREAT! YOUR WHEEL IS READY <i class="fas fa-thumbs-up"></i></h1> 
                                    <p class="opt-ready-text">Your wheel is ready to spin. You can configured General Settings, Triggers & Privacy options from advanced settings.</p>
                                    <p><i class="fas fa-cog"></i></p>
                                    <div class="opt-adv-setting-btn">Go to Advanced Settings</div><br>'.$ready_msg.'
                                </div>' )
            ))


            ->add_tab( __('Form Fields'), array(

                Field::make( 'html', 'optinpsin_sub_tabs' )
                    ->set_html( '<div class="opt-wheel-sub-tabs">
                                    <ul class="optinspin-sub-tabs">
                                        <li><span class="dashicons dashicons-admin-generic"></span> General Settings</li>                                                                                
                                        <li><span class="dashicons dashicons-share-alt"></span> Privacy</li>
                                        <li><span class="dashicons dashicons-art"></span> Additional CSS</li>
                                        <a href="https://goo.gl/Lmrtfb" target="_blank"><li><span class="dashicons dashicons-carrot"></span> Get Pro</li></a>' ),

                Field::make( 'separator', 'optinspin_general', 'General Settings' )
                    ->set_classes( 'optinspin_separator' ),

                Field::make( 'text', 'optinspin_spin_speed'.optinspin_crb_get_i18n_suffix(), 'Wheel Spin Speed' )
                    ->set_attribute('type','number')
                    ->set_default_value('0.5')
                    ->set_help_text('Control the speed of the wheel'),

                Field::make( 'separator', 'wheel_access_settings', 'Wheel Access Settings' ),

                Field::make( 'checkbox', 'optinspin_restricted_by_cooike'.optinspin_crb_get_i18n_suffix(), 'Restrict by Cookie' )
                    ->set_option_value( 'Restricted by Cookie' ),

                Field::make( 'checkbox', 'optinspin_restricted_by_ip'.optinspin_crb_get_i18n_suffix(), 'Restrict by IP (NOT RECOMMENDED SINCE 2 PERSON SHOULD BE ABLE TO PLAY WITH SAME IP)' )
                    ->set_option_value( 'Restricted by IP' ),

                Field::make( 'select', 'optinspin_duration_type', 'Duration Type' )
                    ->add_options( array(
                        'day' =>'Day',
                        'hour' =>'Hour',
                    ) ),
                Field::make( 'text', 'optinspin_cookie_expiry'.optinspin_crb_get_i18n_suffix(), 'Cookie Expiry Time' )
                    ->set_attribute('type','number')
                    ->set_default_value('2')
                    ->set_help_text('Expire Cookie Time (please enter 1 or greater number)'),

                Field::make( 'text', 'optinspin_number_chances_play'.optinspin_crb_get_i18n_suffix(), 'Number Of Chances To Play' )
                    ->set_default_value('1')
                    ->set_help_text('Numbner of chances per user in specified duration'),

                Field::make( 'checkbox', 'optinspin_enable_cart_redirect'.optinspin_crb_get_i18n_suffix(), 'Enable Cart Redirect' )
                    ->set_help_text( 'Enable Cart Redirect after successfuly added coupon to cart' )
                    ->set_option_value( 'Enable Cart Redirect after successfuly added coupon to cart' ),

                Field::make( 'separator', 'optinspin_wheel_style', 'Style' ),

                Field::make( 'image', 'optinspin_background_image'.optinspin_crb_get_i18n_suffix(), 'Background Image' )
                    ->set_value_type( 'url' ),

                Field::make( 'text', 'optinspin_text_size'.optinspin_crb_get_i18n_suffix(), 'Wheel Text Size' )
                    ->set_attribute('type','number')
                    ->set_default_value('2.3')
                    ->set_help_text('Adjust Text Size of Segments'),

                Field::make( 'text', 'optinspin_border_width'.optinspin_crb_get_i18n_suffix(), 'Border Width' )
                    ->set_attribute('type','number')
                    ->set_default_value('18')
                    ->set_help_text('Set Border Width'),

                Field::make( 'image', 'optinspin_logo'.optinspin_crb_get_i18n_suffix(), 'Logo' )
                    ->set_value_type( 'url' )
                    ->set_help_text( 'Set Logo in the above the form' ),

                Field::make( 'checkbox', 'optinspin_enable_sound'.optinspin_crb_get_i18n_suffix(), 'Enable Sound' )
                    ->set_option_value( 'Enable Sound' ),

                Field::make( 'separator', 'optinspin_wheel_setting', 'Colors' ),

                Field::make( 'color', 'optinspin_background_color'.optinspin_crb_get_i18n_suffix(), 'Background' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Set background Color of Wheel' ),

                Field::make( 'color', 'optinspin_border_color'.optinspin_crb_get_i18n_suffix(), 'Outer Border' )
                    ->set_palette( array( '#FF0000', '#00FF00'.optinspin_crb_get_i18n_suffix(), '#0000FF' ) )
                    ->set_help_text( 'Set outer border Color of wheel' ),

                Field::make( 'color', 'optinspin_inner_border_color'.optinspin_crb_get_i18n_suffix(), 'Inner Border' )
                    ->set_palette( array( '#FF0000', '#00FF00'.optinspin_crb_get_i18n_suffix(), '#0000FF' ) )
                    ->set_help_text( 'Set inner border Color of wheel' ),

                Field::make( 'color', 'optinspin_text_color'.optinspin_crb_get_i18n_suffix(), 'Text Color' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Text Color Of Segment' ),

                Field::make( 'color', 'optinspin_buttons_color'.optinspin_crb_get_i18n_suffix(), 'Button Color' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Set Color of the Button' ),

                Field::make( 'color', 'optinspin_buttons_text_color'.optinspin_crb_get_i18n_suffix(), 'Button Text Color' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Set Color of the Button' ),

                Field::make( 'color', 'optinspin_buttons_hover_color'.optinspin_crb_get_i18n_suffix(), 'Button Hover Color' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Set Color of the Button Hover' ),

                Field::make( 'separator', 'optinspin_result', 'Configure Winning Message' ),

                Field::make( 'textarea', 'optinspin_coupon_message'.optinspin_crb_get_i18n_suffix(), 'Coupon Message Text' )
                    ->set_help_text( 'Add Message for coupon' )
                    ->set_default_value('Your coupon have been sent to you via email. You can also use the coupon now by clicking the button below:'),

                Field::make( 'text', 'optinspin_add_to_cart_btn'.optinspin_crb_get_i18n_suffix(), 'Add To Cart Label' )
                    ->set_help_text( 'Add To Cart Label' )
                    ->set_default_value('Continue and Apply To Cart'),

                Field::make( 'text', 'optinspin_skip_btn'.optinspin_crb_get_i18n_suffix(), 'Skip Coupon Label' )
                    ->set_help_text( 'Skip Coupon Label' )
                    ->set_default_value('Skip for Now'),

                Field::make( 'separator', 'optinspin_wheel_formating', 'Labels / Text' ),

                Field::make( 'text', 'optinspin_spinner_label'.optinspin_crb_get_i18n_suffix(), 'Spinner Label' )
                    ->set_help_text('Set Text of Spinner Label')
                    ->set_default_value('Try Spin to Win!'),

                Field::make( 'text', 'optinspin_cross_label'.optinspin_crb_get_i18n_suffix(), 'Cross Label' )
                    ->set_help_text('Cross Label'),

                Field::make( 'text', 'optinspin_email_label'.optinspin_crb_get_i18n_suffix(), 'Email Label' )
                    ->set_help_text( 'Set label of the Email Field' )
                    ->set_default_value('Your Email'),

                Field::make( 'text', 'optinspin_button_label'.optinspin_crb_get_i18n_suffix(), 'Button Label' )
                    ->set_help_text( 'Set Label of the button' ),

                Field::make( 'textarea', 'optinspin_intro_text'.optinspin_crb_get_i18n_suffix(), 'Intro Text' )
                    ->set_default_value('<div id="optinspin-content" style="text-align: left;padding: 0px 0px 0px 10px;"><div class="wlo_title" style="font-family: sans-serif  !important;font-size: 25px;line-height: 1.3em;    font-weight: bold;color: white;margin-bottom: 20px;" >OptinSpins <b style="color: #f1c40f;focnt-size: 25px;line-height: 1.3em;font-family: sans-serif;font-weight:bold;">special offer</b> unlocked!</div><div class="wlo_text" style="color: white;font-size: 14px;text-shadow: none;">You have a chance to win a nice big fat discount. Are you feeling lucky? Give it a spin.</div>						<div class="wlo_small_text wlo_disclaimer_text"style="    font-size: 12px;color: #b1b1b1;" >* You can spin the wheel only once.<br>* If you win, you can claim your coupon within limited time <br>* OptinSpin reserves the right to cancel the coupon anytime </div></div>'),

                Field::make( 'separator', 'optinspin_privacy', 'Privacy' )
                    ->set_classes( 'optinspin_separator' ),

                Field::make( 'text', 'optinspin_privacy_label'.optinspin_crb_get_i18n_suffix(), 'Label' )
                    ->set_help_text('Enter Privacy Label')
                    ->set_default_value('Privacy'),

                Field::make( 'select', 'optinspin_privacy_page'.optinspin_crb_get_i18n_suffix(), 'Privacy Page' )
                    ->add_options( $this->optinspin_lists_of_pages() )
                    ->set_help_text('Select Privacy Page'),

                Field::make( 'separator', 'optinspin_additionalcss', 'Additional CSS' )
                    ->set_classes( 'optinspin_separator' ),

                Field::make( 'textarea', 'optinspin_custom_css'.optinspin_crb_get_i18n_suffix(), 'Custom CSS' )
                    ->set_help_text( 'Apply Custom CSS' )
                    ->set_default_value('')
                    ->set_rows( 10 ),

            ));

    }

    function mycred_get_types_settings() {
        $mycred_types = array();
        if( function_exists( 'mycred_get_types' ) ) {
            return mycred_get_types();
        }

        return $mycred_types;
    }

    function optinspin_crb_load() {
        require_once( optinspin_PLUGIN_PATH . '/inc/settings/carbon-fields/vendor/autoload.php' );
        \Carbon_Fields\Carbon_Fields::boot();
    }

    function get_list_coupons() {
        $coupons_list = array();
        $args = array(
            'posts_per_page'   => -1,
            'orderby'          => 'title',
            'order'            => 'asc',
            'post_type'        => 'shop_coupon',
            'post_status'      => 'publish',
        );

        $coupons = get_posts( $args );
        foreach( $coupons as $coupon ) {
            $coupons_list[$coupon->ID] = $coupon->post_title;
        }

        return $coupons_list;
    }

    function get_edd_coupon_list() {
        $coupons_list = array();
        $args = array(
            'posts_per_page'   => -1,
            'orderby'          => 'title',
            'order'            => 'asc',
            'post_type'        => 'edd_discount',
            'post_status'      => 'any',
        );

        $coupons = get_posts( $args );
        foreach( $coupons as $coupon ) {
            $coupons_list[$coupon->ID] = $coupon->post_title;
        }

        return $coupons_list;
    }
    
    function optinspin_lists_of_pages() {
        $page_list = array();
        $args = array(
            'posts_per_page'   => -1,
            'orderby'          => 'title',
            'order'            => 'asc',
            'post_type'        => 'page',
            'post_status'      => 'publish',
        );

        $pages = get_posts( $args );
        $page_list['none'] = 'none';
        foreach( $pages as $page ) {
            $page_list[get_permalink($page->ID)] = $page->post_title;
        }

        $data = Field::make( 'checkbox', 'optinspin_disable_coupon_bar', 'Disable Coupon Bar' )
            ->set_option_value( 'Disable Coupon Bar' );

        return $page_list;
    }

    /*function optinspin_pages_to_show() {
        $page_list = array();
        $args = array(
            'posts_per_page'   => 100,
            'orderby'          => 'title',
            'order'            => 'asc',
            'post_type'        => array('page','product','post'),
            'post_status'      => 'publish',
        );

        $pages = get_posts( $args );
        $page_list['none'] = 'none';
        $page_list['home'] = 'Home';
        foreach( $pages as $page ) {
            $page_list[$page->ID] = $page->post_title;
        }
        return $page_list;
    }*/

    function optinspin_coupon_options() {
        global $mycred_optinspin;

        $coupon_options = array();

        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
            $coupon_options['woocommere_coupon'] = 'WooCommerce Coupon';

        if( in_array( 'easy-digital-downloads/easy-digital-downloads.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
            $coupon_options['edd_coupon'] = 'Easy Digital Downloads Coupon';

        $coupon_values = array(
            'coupon_text' => 'Coupon Text',
            'coupon_link' => 'Coupon Link',
        );

        if( $mycred_optinspin->optinspin_is_mycred_emabled() == true )
            $coupon_values['optinspin_mycred'] = 'MyCred Points';

        $coupon_values = array_merge($coupon_options,$coupon_values);

        return $coupon_values;
    }

    function optinspin_lists_of_posts() {

        $default_value = array();
        $default_value['page'] = 'page';
        $default_value['post'] = 'post';
        $available_posts = get_option('optinspin_available_posts',false);

        if($available_posts) {
            $available_posts = array_merge($default_value, $available_posts);
            return $available_posts;
        } else {
            return $default_value;
        }
    }
}