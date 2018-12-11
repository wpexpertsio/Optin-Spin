<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Class optinspin_Settings {

    function __construct() {
        add_action( 'carbon_fields_register_fields', array($this,'optinspin_add_settings_page') );
        add_action( 'after_setup_theme', array($this,'optinspin_crb_load') );
    }

    function optinspin_add_settings_page() {

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
		
		do_action('opt_before_settings');

        Container::make( 'theme_options', __( 'Optin Spin', 'optinspin ' ) )

            ->add_tab( __('General'), array(

                Field::make( 'separator', 'optinspin_wheel_style', 'Style' ),

                Field::make( 'image', 'optinspin_background_image', 'Background Image' )
                    ->set_value_type( 'url' ),

                Field::make( 'text', 'optinspin_text_size', 'Wheel Text Size' )
                    ->set_attribute('type','number')
                    ->set_default_value('2.3')
                    ->set_help_text('Adjust Text Size of Segments'),

                Field::make( 'text', 'optinspin_border_width', 'Border Width' )
                    ->set_attribute('type','number')
                    ->set_default_value('18')
                    ->set_help_text('Set Border Width'),

                Field::make( 'image', 'optinspin_logo', 'Logo' )
                    ->set_value_type( 'url' )
                    ->set_help_text( 'Set Logo in the above the form' ),

                Field::make( 'separator', 'optinspin_wheel_setting', 'Colors' ),

                Field::make( 'color', 'optinspin_background_color', 'Background' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Set background Color of Wheel' ),

                Field::make( 'color', 'optinspin_border_color', 'Outer Border' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Set outer border Color of wheel' ),

                Field::make( 'color', 'optinspin_inner_border_color', 'Inner Border' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Set inner border Color of wheel' ),

                Field::make( 'color', 'optinspin_text_color', 'Text Color' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Text Color Of Segment' ),

                Field::make( 'color', 'optinspin_buttons_color', 'Button Color' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Set Color of the Button' ),

                Field::make( 'color', 'optinspin_buttons_text_color', 'Button Text Color' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Set Color of the Button' ),

                Field::make( 'color', 'optinspin_buttons_hover_color', 'Button Hover Color' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Set Color of the Button Hover' ),

                Field::make( 'separator', 'optinspin_wheel_formating', 'Labels / Text' ),

                Field::make( 'text', 'optinspin_spinner_label', 'Spinner Label' )
                    ->set_help_text('Set Text of Spinner Label')
                    ->set_default_value('Try Spin to Win!'),

                Field::make( 'text', 'optinspin_cross_label', 'Cross Label' )
                    ->set_help_text('Cross Label'),

                Field::make( 'text', 'optinspin_email_label', 'Email Label' )
                    ->set_help_text( 'Set label of the Email Field' )
                    ->set_default_value('Your Email'),

                Field::make( 'text', 'optinspin_name_label', 'Name Label' )
                    ->set_help_text( 'Set label of the Name Field (Leave Empty if you don\t want to show this field on frontend)' )
                    ->set_default_value('Your Name'),

                Field::make( 'text', 'optinspin_button_label', 'Button Label' )
                    ->set_help_text( 'Set Label of the button' ),

                Field::make( 'textarea', 'optinspin_intro_text', 'Intro Text' )
                    ->set_default_value('<div id="optinspin-content" style="text-align: left;padding: 0px 0px 0px 10px;"><div class="wlo_title" style="font-family: sans-serif  !important;font-size: 25px;line-height: 1.3em;    font-weight: bold;color: white;margin-bottom: 20px;" >OptinSpins <b style="color: #f1c40f;focnt-size: 25px;line-height: 1.3em;font-family: sans-serif;font-weight:bold;">special offer</b> unlocked!</div><div class="wlo_text" style="color: white;font-size: 14px;text-shadow: none;">You have a chance to win a nice big fat discount. Are you feeling lucky? Give it a spin.</div>						<div class="wlo_small_text wlo_disclaimer_text"style="    font-size: 12px;color: #b1b1b1;" >* You can spin the wheel only once.<br>* If you win, you can claim your coupon within limited time <br>* OptinSpin reserves the right to cancel the coupon anytime </div></div>'),

                Field::make( 'html', 'crb_information_text' )
                    ->set_html( '<div class="optinspin-pro-head"><h1>OptinSpin <span>PRO</span></h1></div>
                        <div class="optinspin-pro-list">
                        <p>Enable Cart Redirect After winning</p>
                        <p>Enable Sound Of Wheel</p>
                        <p>Enable Party Poopers After winnign</p></div>'
                    )
                    ->set_classes( 'optinspin_general_pro' ),
            ))

            ->add_tab( __('Section'), array(

                Field::make( 'complex', 'crb_section' )
                    ->set_collapsed( true )
                    ->set_min(8)
                    ->set_max(8)
                    ->add_fields( 'no_prize', array(

                        Field::make( 'text', 'optinspin_section_label', 'Label' )
                            ->set_help_text('Label of wheel section')
                            ->set_classes( 'optinspin_section_label' ),

                        Field::make( 'color', 'segment_color', 'Section color' )
                            ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                            ->set_help_text( 'Set the color of the respective section/segment' ),
                        Field::make( 'textarea', 'optinspin_win_loss_text', 'Lossing text' ),
                        Field::make( 'text', 'optinspin_probability', 'Probability' )
                            ->set_attribute('type','number')
                            ->set_help_text( 'How much chances to stop at this segment ( 0 - 100 )' ),

                    ))->set_collapsed( true )

                    ->add_fields( 'win_prize', array(


                        Field::make( 'text', 'optinspin_section_label', 'Label' )
                            ->set_help_text('Label of wheel section')
                            ->set_classes( 'optinspin_section_label' ),

                        Field::make( 'radio', 'optinspin_coupon_type', 'Coupon Type' )
                            ->add_options( $this->optinspin_coupon_options() ),

                        Field::make( 'select', 'optinspin_coupon', 'Coupon' )
                            ->add_options( $this->get_list_coupons() )
                            ->set_help_text('Choose Coupon for this section')
                            ->set_classes( 'optinspin_coupon_list' )
                            ->set_conditional_logic( array(
                                array(
                                    'field' => 'optinspin_section_generate_coupon',
                                    'value' => false,
                                ),
								array(
                                    'field' => 'optinspin_coupon_type',
                                    'value' => 'woocommere_coupon',
                                )
                            ) ),

                        Field::make( 'select', 'optinspin_edd_coupon', 'EDD Coupon' )
                            ->add_options( $this->get_edd_coupon_list() )
                            ->set_help_text('Choose Coupon for this section')
                            ->set_classes( 'optinspin_coupon_list' )
                            ->set_conditional_logic( array(
                                array(
                                    'field' => 'optinspin_coupon_type',
                                    'value' => 'edd_coupon',
                                )
                            ) ),

                        Field::make( 'text', 'optinspin_coupon_text_label', 'Coupon Text Label' )
                            ->set_conditional_logic( array(
                                array(
                                    'field' => 'optinspin_coupon_type',
                                    'value' => 'coupon_text',
                                )
                            ) ),

                        Field::make( 'text', 'optinspin_coupon_link_label', 'Coupon Link Label' )
                            ->set_conditional_logic( array(
                                array(
                                    'field' => 'optinspin_coupon_type',
                                    'value' => 'coupon_link',
                                )
                            ) ),

                        Field::make( 'text', 'optinspin_coupon_link_url', 'Coupon Link URL' )
                            ->set_conditional_logic( array(
                                array(
                                    'field' => 'optinspin_coupon_type',
                                    'value' => 'coupon_link',
                                )
                            ) ),

                        Field::make( 'text', 'optinspin_probability', 'Probability' )
                            ->set_attribute('type','number'),
                        Field::make( 'color', 'segment_color', 'Section color' )
                            ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                            ->set_help_text( 'Set the color of the respective section/segment' ),
                        Field::make( 'textarea', 'optinspin_win_loss_text', 'Winning text' ),
                    ))->set_collapsed( true ),

                Field::make( 'html', 'crb_pro_text_section' )
                    ->set_html( '<div class="optinspin-pro-head"><h1>OptinSpin <span>PRO</span></h1></div>
                        <div class="optinspin-pro-list">
                        <p>Option to Drag& Drop Section</p>
                        <p>Unlimited Sections</p>
                        <p>Option to Generate Coupon Dynamically</p></div>'
                    )
                    ->set_classes( 'optinspin_general_pro' ),
            ) )

            ->add_tab( __('Triggers'), array(

                Field::make( 'separator', 'optinspin_wheel_clickable_tab', 'Open Spin By Clickable Tab' ),

                Field::make( 'checkbox', 'optinspin_enable_clickable_tab_desktop', 'Show Clickable Tab on Desktop' )
                    ->set_option_value( 'Enable Time Delay Popup on Deskto' ),

                Field::make( 'checkbox', 'optinspin_enable_clickable_tab_mobile', 'Show Clickable Tab on Mobile' )
                    ->set_option_value( 'Enable Intent Exit Popup on Mobile' ),

                Field::make( 'html', 'crb_pro_text_trigger' )
                    ->set_html( '<div class="optinspin-pro-head"><h1>OptinSpin <span>PRO</span></h1></div>
                        <div class="optinspin-pro-list">
                        <p>Enable Time Delay Optin on Desktop</p>
                        <p>Enable Time Delay Optin on Mobile</p>
                        <p>Enable Intent Exit Popup for Desktop</p>
                        <p>Enable Intent Exit Popup for Mobile</p></div>'
                    )
                    ->set_classes( 'optinspin_general_pro' ),
            ) )

            ->add_tab( __('Integration'), array(

                Field::make( 'separator', 'optinspin_mailchimp_label', 'Mail Chimp' )
                    ->set_classes( 'optinspin_mailchimp_label_class' ),

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

                Field::make( 'separator', 'optinspin_chatchamp', 'ChatChamp' )
                    ->set_classes( 'optinspin_chatchamp_label_class' ),

                Field::make( 'checkbox', 'optinspin_chatchamp_enabled', 'Enable ChatChamp' )
                    ->set_option_value( 'Enable Chatchamp' ),

                Field::make( 'text', 'optinspin_chatchamp_id', 'Enter ChatChamp ID' )
                    ->set_help_text('Enter ChatChamp ID'),

                Field::make( 'html', 'crb_pro_text_integration' )
                    ->set_html( '<div class="optinspin-pro-head"><h1>OptinSpin <span>PRO</span></h1></div>
                        <div class="optinspin-pro-list">
                        <p>Option To Enable Email with Remarkety</p>
                        </div>'
                    )
                    ->set_classes( 'optinspin_general_pro' ),
            ))

            ->add_tab( __('Winning/Lossing'), array(
                Field::make( 'color', 'optinspin_win_background_color', 'Background Color' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Set Background Color after winning or lossing' ),

                Field::make( 'color', 'optinspin_win_border_color', 'Border Color' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Set Border Color after winning or lossing' ),

                Field::make( 'color', 'optinspin_win_text_color', 'Text Color' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Set Text Color after winning or lossing' ),

                Field::make( 'color', 'optinspin_add_cart_link_color', 'Link Color' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Set Link Color after winning or lossing' ),

                Field::make( 'color', 'optinspin_skip_link_color', 'Link Color' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Set Link Color after winning or lossing' ),

                Field::make( 'color', 'optinspin_coupon_msg_bg', 'Coupon Message Background Color' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Set Link Color after winning or lossing' ),

                Field::make( 'color', 'optinspin_coupon_msg_text_color', 'Coupon Message Text Color' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Set Link Color after winning or lossing' ),

                Field::make( 'color', 'optinspin_add_cart_bg_color', 'Add to Cart Background Color' )
                    ->set_palette( array( '#FF0000', '#00FF00', '#0000FF' ) )
                    ->set_help_text( 'Add to Cart Button Background Color' ),

                Field::make( 'textarea', 'optinspin_coupon_message', 'Coupon Message Text' )
                    ->set_help_text( 'Add Message for coupon' )
                    ->set_default_value('You can use the coupon now by clicking the button below'),

                Field::make( 'text', 'optinspin_skip_btn', 'Skip Coupon Label' )
                    ->set_help_text( 'Skip Coupon Label' )
                    ->set_default_value('Skip for Now'),

                Field::make( 'html', 'crb_pro_text_win_loss' )
                    ->set_html( '<div class="optinspin-pro-head"><h1>OptinSpin <span>PRO</span></h1></div>
                        <div class="optinspin-pro-list">
                        <p>Add to cart Afer winning</p>
                        </div>'
                    )
                    ->set_classes( 'optinspin_general_pro' ),

                Field::make( 'textarea', 'optinspin_custom_css', 'Custom CSS' )
                    ->set_help_text( 'Apply Custom CSS' )
                    ->set_default_value('/**
You Custom CSS
**/')
                    ->set_rows( 10 ),
            ))

            ->add_tab( __('Get PRO'), array(
                Field::make( 'html', 'crb_more_pro' )
                    ->set_html( '<div class="optinspin-pro-head"><h1>OptinSpin <span>PRO</span></h1></div>
                        <div class="optinspin-pro-list">

                        <p>Enable Cart Redirect After winning</p>
                        <p>Enable Sound Of Wheel</p>
                        <p>Enable Party Poopers After winnign</p>

                        <p>Enable Time Delay Optin on Desktop</p>
                        <p>Enable Time Delay Optin on Mobile</p>
                        <p>Enable Intent Exit Popup for Desktop</p>
                        <p>Enable Intent Exit Popup for Mobile</p>

                        <p>Option to Drag& Drop Section</p>
                        <p>Unlimited Sections</p>
                        <p>Option to Generate Coupon Dynamically</p>

                        <p>Option To Enable Email with Remarkety</p>

                        <p>Add to cart Afer winning</p>

                        <p>Option to Add Privay Link</p>
                        <p>Option to Customize Email Templates</p>
                        <p>Option to Customize Coupon Bar</p>
                        <a href="https://codecanyon.net/item/optinspin-fortune-wheel-fully-integrated-with-woocommerce-coupons/20768678">and many more</a>
                        </div>'
                    )

                    ->set_classes( 'optinspin_general_pro' ),

                Field::make( 'html', 'crb_more_pro_link' )
                    ->set_html('<a href="https://codecanyon.net/item/optinspin-fortune-wheel-fully-integrated-with-woocommerce-coupons/20768678">Get Pro Now</a>')
                    ->set_classes( 'optinspin_get_pro' ),
            ));

			do_action('opt_after_settings');
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

    function optinspin_coupon_options() {

        $coupon_options = array();

        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
            $coupon_options['woocommere_coupon'] = 'WooCommerce Coupon';

        if( in_array( 'easy-digital-downloads/easy-digital-downloads.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
            $coupon_options['edd_coupon'] = 'Easy Digital Downloads Coupon';

        $coupon_values = array(
            'coupon_text' => 'Coupon Text',
            'coupon_link' => 'Coupon Link',
        );

        $coupon_values = array_merge($coupon_options,$coupon_values);

        return $coupon_values;
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

    function optinspin_pages_to_show() {
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
        $page_list['home'] = 'Home';
        foreach( $pages as $page ) {
            $page_list[$page->ID] = $page->post_title;
        }
        return $page_list;
    }

    function optinspin_lists_of_posts() {

        $posts = array();

        $posts['product'] = 'Product';
        $posts['post'] = 'Post';

        return $posts;
    }
}