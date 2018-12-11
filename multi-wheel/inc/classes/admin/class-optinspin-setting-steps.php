<?php
/**
 * Opt
 */


class Setting_Sections {

    function __construct() {
        add_action( 'edit_form_top', array($this,'optinspin_step_meta'),0 );
        add_action('admin_menu', array($this,'optinspin_remove_auther_meta_box') );
        add_action('admin_footer', array($this,'optinspin_action_sidebar') );
    }

    function optinspin_action_sidebar() {

        if( get_post_type() != 'optin-wheels' )
            return;

        $wheel_id = get_the_ID();
        $count_click = get_post_meta( $wheel_id,'count_clicks',true);
        $count_wins = get_post_meta( $wheel_id,'count_wins',true);
        $count_loss = get_post_meta( $wheel_id,'count_loss',true);
        $count_play = get_post_meta( $wheel_id,'count_play',true);

        $win_percent = 0; $loss_percent = 0; $avg_play = 0;
        if( !empty( $count_wins ) && !empty( $count_play ) )
            $win_percent = ( $count_wins / $count_play ) * 100;

        if( !empty( $count_loss ) && !empty( $count_loss ) )
            $loss_percent = ( $count_loss / $count_play ) * 100;

        if( !empty( $count_play ) && !empty( $count_play ) )
            $avg_play = ( $count_play / $count_click ) * 100;

        $html = '<div class="opt-action-sidebar">
                    <div class="opt-sidebar-cross"><span class="dashicons dashicons-no-alt"></span></div>
                    <div class="opt-post-details">
                    <div class="opt-act-btn publish"><input type="button" id="opt-publish" value="'. ( ( ( isset( $_GET['post'] ) && get_post_status( $_GET['post'] ) == 'draft' ) || !isset( $_GET['post'] ) ) ? 'PUBLISH' : 'UPDATE' ) .'"></div>
                    <div class="opt-act-btn draft"><input type="button" id="opt-draft" value="DRAFT"></div>';

                $html .= '</div>';

        $html .= '<div class="opt-pull-settings"><span class="dashicons dashicons-admin-generic"></span></div>';

        echo $html;
    }

    function optinspin_remove_auther_meta_box() {
        remove_meta_box( 'authordiv','optin-wheels','normal' );
    }

    function optinspin_step_meta( $post ) {

        if( get_post_type() != 'optin-wheels' )
            return;

        echo '<div class="opt-steps">
            <div class="opt-step title active"><span>1</span> <div class="opt-text">Title</div></div>
            <div class="opt-step wheel-slices"><span>2</span> <div class="opt-text">Wheel Slices</div></div>
            <div class="opt-step integration"><span>4</span> <div class="opt-text">Integration</div></div>
            <div class="opt-step ready"><span>5</span> <div class="opt-text">Ready <i class="fas fa-check-circle"></i></div></div>
            <div class="opt-step adv"><span>6</span> <div class="opt-text">Advanced Settings <i class="fas fa-cog"></i></div></div>';

            if( isset( $_GET['post'] ) ) {
                echo '<div class="opt-step stats"><span>7</span> <div class="opt-text">View Stats  <i class="fas fa-chart-pie"></i></div></div>';
            }

            echo '</div>';
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