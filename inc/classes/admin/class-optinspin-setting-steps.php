<?php
/**
 * Opt
 */

class Setting_Sections {

    function __construct() {
        add_action('admin_menu', array($this,'optinspin_remove_auther_meta_box') );
        add_action('admin_footer', array($this,'optinspin_action_sidebar') );
    }
	
    function optinspin_action_sidebar() {

        if( !isset($_GET['page']) && $_GET['page'] != 'crb_carbon_fields_container_optin_spin.php' )
            return;

        $html = '<div class="opt-action-sidebar">
                    <div class="opt-sidebar-cross"><span class="dashicons dashicons-no-alt"></span></div>
                    <div class="opt-act-btn publish"><input type="button" id="opt-publish" value="'. ( ( ( isset( $_GET['post'] ) && get_post_status( $_GET['post'] ) == 'draft' ) || !isset( $_GET['post'] ) ) ? 'PUBLISH' : 'UPDATE' ) .'"></div>
                    <div class="opt-act-btn draft"><input type="button" id="opt-draft" value="DRAFT"></div>';
                $html .= '</div>';

        $html .= '<div class="opt-pull-settings"><span class="dashicons dashicons-admin-generic"></span></div>';

        echo $html;
    }

    function optinspin_remove_auther_meta_box() {
        remove_meta_box( 'authordiv','optin-wheels','normal' );
    }

    function optinspin_setting_sections() {
        add_menu_page(
            __( 'Optin Wheel', 'textdomain' ),
            __( 'Unsub Emails','textdomain' ),
            'manage_options',
            'wpdocs-unsub-email-list',
            'wpdocs_unsub_page_callback',
            ''
        );
    }

    function wpdocs_unsub_page_callback() {
        echo 'Unsubscribe Email List';
    }
}