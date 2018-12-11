<?php
/**
 * Plugin Name: Optin Spin
 * Version: 2.2
 * Description: Optinspin converts website visitors into subscribers and customers. Optin Spin uses the old concept of fortune wheel in a new way to make things fun for both the site owner and the customer at the same time.
 * Plugin URI:  https://wpexperts.io/
 * Author:      wpexpertsio
 * Author URI:  https://wpexperts.io/
 * Text Domain: optinspin
 */

define('optin_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('optin_PLUGIN_PATH', plugin_dir_path( __FILE__ ));

include 'optin-settings.php';

if( optinspin_get_active_version() == 'multiwheel' )
    include 'multi-wheel/optinspin.php';
else
    include 'single-wheel/optinspin.php';

include 'preview/class-optinspin-preview.php';

function cyb_activation_redirect( $plugin ) {
    if( $plugin == plugin_basename( __FILE__ ) && optinspin_get_active_version() != 'multiwheel' ) {
        exit( wp_redirect( admin_url() . 'admin.php?page=optinspin-switch-v2' ) );
    }
}
add_action( 'activated_plugin', 'cyb_activation_redirect' );