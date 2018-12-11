<?php

define('optinspin_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('optinspin_PLUGIN_PATH', plugin_dir_path( __FILE__ ));

// ADD CARBON FIELD LIBRARY
if(!function_exists('carbon_fields_boot_plugin')){ // CHECK IF CARBON ALREADY EXIST OR NOT
    include 'inc/settings/carbon-fields/carbon-fields-plugin.php';
}
/*include 'inc/settings/carbon-fields/carbon-fields-plugin.php';*/

// Woo The Wheel
include 'inc/classes/class-optinspin-chatchamp.php';
$optinspin_Chatchamp = new optinspin_Chatchamp();
//$optinspin_Chatchamp = '';

// Woo The Wheel Admin Settings
include 'inc/classes/class-optinspin-settings.php';
$optinspin_settigns = new optinspin_Settings();

// Email Subscribers
include 'inc/classes/admin/class-optinspin-subscribers.php';
$optinspin_email_subscriber = new optinspin_Subscriber();

// Woo The Wheel
include 'inc/classes/class-optinspin-wheel.php';
$optinspin_woo_the_wheel = new optinspin_Wheel();

// Woo The Wheel
include 'inc/classes/class-optinspin-coupon-request.php';
$optinspin_coupon_request = new optinspin_Coupon_Request();

// Woo Stats
include 'inc/classes/admin/class-optinspin-statistics.php';
$optinspin_statistics = new optinspin_Statistics();

function optinspin_wheel_script_style() {

	$cart_url = '';
    if( function_exists('wc_get_cart_url') ) {
	    $cart_url = wc_get_cart_url();
    }
	
	$disable_optinbar = carbon_get_theme_option('optinspin_disable_coupon_bar');
	if( !empty($disable_optinbar) )
		$disable_optinbar = 'off';

	$coupon_expire_label = carbon_get_theme_option('optinspin_coupon_bar_expire_label');
	if( empty($coupon_expire_label) )
		$coupon_expire_label = 'Coupon Time Left';

	$sparkle_enable = carbon_get_theme_option('optinspin_enable_sparkle');
	if( empty( $sparkle_enable ) )
		$sparkle_enable = 0;
	else
		$sparkle_enable = 1;

	$cookie_expiry = carbon_get_theme_option('optinspin_cookie_expiry');
	if( empty($cookie_expiry) )
		$cookie_expiry = 2;

	$coupon_msg = carbon_get_theme_option('optinspin_coupon_bar_msg');

	if( empty($coupon_msg) )
		$coupon_msg = 'Congrats! You Win a Free Coupon "{coupon}", Enjoy & Keep shopping!!';

	$optinspin_enable_cart_redirect = carbon_get_theme_option('optinspin_enable_cart_redirect');
	if( empty( $optinspin_enable_cart_redirect ) )
		$optinspin_enable_cart_redirect = 0;
	else
		$optinspin_enable_cart_redirect = 1;

	wp_enqueue_style( 'optinspin-wheel-style', optinspin_PLUGIN_URL . 'assets/css/wheel-style.css' );
	wp_enqueue_style( 'optinspin-google-font', optinspin_PLUGIN_URL . 'assets/css/google-font.css' );
	wp_enqueue_style( 'optinspin-wheel-main-style', optinspin_PLUGIN_URL . 'assets/css/style.css' );
	wp_enqueue_script( 'jquery' );

	wp_enqueue_script( 'optinspin-grunt-scripts', optinspin_PLUGIN_URL . 'assets/js/optinspin-merge.js', null, '', true );
	$param = array(
		'plugin_url' => optinspin_PLUGIN_URL,
		'ajax_url' => admin_url('admin-ajax.php'),
		'coupon_msg' => $coupon_msg,
		'cart_url' => $cart_url,
		'disable_optinbar' => $disable_optinbar,
		'coupon_expire_label' => $coupon_expire_label,
		'coupon_expire_label' => $coupon_expire_label,
		'wheel_data' => optinspin_PLUGIN_URL .'inc/wheel_data.php',
		'sparkle_enable' => $sparkle_enable,
		'cookie_expiry' => $cookie_expiry,
		'ajaxurl' => admin_url('admin-ajax.php'),
		'enable_cart_redirect' => $optinspin_enable_cart_redirect
	);
	wp_localize_script( 'optinspin-grunt-scripts', 'optinspin_wheel_spin', $param );
	wp_enqueue_script( 'optinspin-grunt-scripts' );
}
add_action( 'wp_enqueue_scripts', 'optinspin_wheel_script_style',99 );

function optinspin_admin_wheel_script() {
	wp_enqueue_style( 'optinspin-admin-style', optinspin_PLUGIN_URL . 'assets/css/admin-style.css' );
	wp_enqueue_script( 'optinspin-admin-script', optinspin_PLUGIN_URL . 'assets/js/admin-script.js' );
	$ajaxurl = array(
		'ajaxurl' => admin_url('admin-ajax.php')
	);
	wp_localize_script( 'optinspin-admin-script', 'php_data', $ajaxurl );
}
add_action( 'admin_enqueue_scripts', 'optinspin_admin_wheel_script' );



add_action('wp_ajax_optinspin_mailchimp_get_list', '_optinspin_mailchimp_get_list');
add_action('wp_ajax_nopriv_optinspin_mailchimp_get_list', '_optinspin_mailchimp_get_list');

function _optinspin_mailchimp_get_list(){
	if(isset($_POST['action']) && isset($_POST['_optinspin_mailchimp_api_key']) && $_POST['action'] == 'optinspin_mailchimp_get_list' and  !empty(trim($_POST['_optinspin_mailchimp_api_key']))){
		$_optinspin_mailchimp_api_keyful = $_POST['_optinspin_mailchimp_api_key'];
		$_optinspin_mailchimp_api_key = explode('-',$_optinspin_mailchimp_api_keyful);
		$prefix = $_optinspin_mailchimp_api_key[1]; //at the end of your API Key, there is a -us1, or us2, etc......you want the prefix to be the us2 for examples.
		//Let's go Get a LIST ID for the subscriber list we are going to be putting content in.
		$get_lists = 'http://'.$prefix.'.api.mailchimp.com/1.3/?method=lists';
		$data = array();
		$data['apikey'] = $_optinspin_mailchimp_api_keyful;
		$post_str = '';
		foreach($data as $key=>$val) {
			$post_str .= $key.'='.urlencode($val).'&';
		}
		$post_str = substr($post_str, 0, -1);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $get_lists);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($ch);
		if(curl_error($ch)){
			$curl_error = curl_error($ch);
		}
		curl_close($ch);
		if(!empty($response)){
			update_option( 'optinspin_mailchimp_get_list', $response);
			$jsondecoded = json_decode($response);
			if(!empty($jsondecoded->data)){
				$options .= '<option value="" >Select Email List</option>';
				foreach($jsondecoded->data as $datavalue){
					$options .= '<option value="'.$datavalue->id.'" >'.trim(ucfirst($datavalue->name)).'</option>';
				}
			}
			$return = array(
				'statuss' => true,
				'response' => $options
			);
			echo json_encode($return);
		} else {
			$return = array(
				'statuss' => false,
				'response' => '',
				'error' => $curl_error
			);
			echo json_encode($return);
		}
		die();
	}
	die();
}

add_action('optinspin_save_email','optinspin_save_email_to_email_subcriber',10,3);

function optinspin_save_email_to_email_subcriber($email,$name,$Post_id){
	//email save to mailchimp
	if(
		!empty(get_option( '_optinspin_mailchimp_api_key' ))
			and
		!empty($email)
			and
		!empty($name)
			and
		!empty($Post_id)
			and
		!empty(get_option( '_crb_show_socials' ))
		){
			$apiKey = get_option( '_optinspin_mailchimp_api_key' );
			$listId = get_option( '_crb_show_socials' );
			if(get_option( '_opt_ins' ) == 'single'){
				$status = 'subscribed';
			} else if(get_option( '_opt_ins' ) == 'double') {
				$status = 'pending';
			}

			$data = [
				'email'     => $email,
				'status'    => $status,
				'firstname' => $name,
				'lastname'  => ''
			];
			$memberId = md5(strtolower($data['email']));
			$dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
			$url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listId . '/members/' . $memberId;

			$json = json_encode([
				'email_address' => $data['email'],
				'status'        => $data['status'], // "subscribed","unsubscribed","cleaned","pending"
				'merge_fields'  => [
					'FNAME'     => $data['firstname'],
					'LNAME'     => $data['lastname']
				]
			]);
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			$result = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if(curl_error($ch))
			{
				$cur_error =  curl_error($ch);
			}
			curl_close($ch);
			if(!empty($result)){
				update_post_meta($Post_id,'mailchimp_response',$result);
			} else {
				update_post_meta($Post_id,'mailchimp_response_error',$cur_error);
			}

	}
}

function optinspin_activate_plugin( $plugin ) {
    if( $plugin == plugin_basename( __FILE__ ) ) {
        exit( wp_redirect( admin_url() . 'admin.php?page=optinspin-how-to-use' ) );
    }
}
add_action( 'activated_plugin', 'optinspin_activate_plugin' );

function optinspin_intro_menu(){
    add_submenu_page( 'crb_carbon_fields_container_optin_spin.php', 'Help', 'Help',
        'manage_options', 'optinspin-how-to-use','optinspin_how_to_use');
}
add_action( 'admin_menu', 'optinspin_intro_menu',99 );

function optinspin_how_to_use() {
    $html = '<div class="optinspin-help-wrapper" style="background-color: white;">
                <div class="optinspin-help"><img src="'. optinspin_PLUGIN_URL .'assets/img/how-to.jpg' .'" /></div>                
            </div>';
    echo $html;
}