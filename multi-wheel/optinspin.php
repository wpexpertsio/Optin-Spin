<?php

define('optinspin_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('optinspin_PLUGIN_PATH', plugin_dir_path( __FILE__ ));

function optinspin_session_start() {
    if ( session_status() !== PHP_SESSION_ACTIVE )
        session_start();
}

function optinspin_crb_get_i18n_suffix() {
    $suffix = '';
    if ( ! defined( 'ICL_LANGUAGE_CODE' ) ) {
        return $suffix;
    }
    $suffix = '_' . ICL_LANGUAGE_CODE;
    return $suffix;
}

function optinspin_get_post_meta($post_id,$key) {

    if( !empty( carbon_get_post_meta($post_id,$key) ) ) {
        return carbon_get_post_meta($post_id,$key);
    } else {
        return carbon_get_post_meta($post_id,$key.optinspin_crb_get_i18n_suffix());
    }

}

add_action('wp_loaded','optinspin_session_start');

$wheel_main_id = 0;

// ADD CARBON FIELD LIBRARY
if( is_admin() && get_post_type() == 'optin-wheels' ) {
    include 'inc/settings/carbon-fields/carbon-fields-plugin.php';
}

// Email Subscribers
include 'inc/classes/admin/class-optinspin-subscribers.php';
$optinspin_email_subscriber = new optinspin_Subscriber();

// MyCred
include 'inc/classes/mycred/class-mycred-settings.php';
$mycred_optinspin = new OptinSpin_MyCred_Settings();

// Woo The Wheel
include 'inc/classes/class-optinspin-wheel.php';
$optinspin_woo_the_wheel = new optinspin_Wheel();

// OptinSpino Protect
include 'inc/classes/class-optinspin-protect.php';
$optinspin_protect_post = new optinspin_Protect();

// Woo The Wheel
include 'inc/classes/class-optinspin-chatchamp.php';
$optinspin_Chatchamp = new optinspin_Chatchamp();

// Pages Suggestion
//include 'inc/classes/admin/class-optispin-pages-suggestion.php';

// Woo The Wheel Admin Settings
include 'inc/classes/class-optinspin-settings.php';
$optinspin_settigns = new optinspin_Settings();

// Woo The Wheel
include 'inc/classes/class-optinspin-coupon-request.php';
$optinspin_coupon_request = new optinspin_Coupon_Request();

// Woo The Wheel
include 'inc/classes/admin/class-optinspin-wheel-preview.php';
//$optinspin_woo_the_wheel_preview = new optinspin_Wheel_Preview();

// Woo Stats
include 'inc/classes/admin/class-optinspin-statistics.php';
$optinspin_statistics = new optinspin_Statistics();

// Woo Stats
include 'inc/classes/admin/class-optinspin-setting-steps.php';
$setting_sections = new Setting_Sections();

// OptinSpin MultiWheel PostType
include 'inc/classes/class-optinspin-post-type.php';
$optin_wheels = new Optin_Wheels();

// OptinSpin MultiWheel PostType
include 'inc/classes/admin/class-optinspin-pro-page.php';
$Optinspin_Get_Pro = new Optinspin_Get_Pro();

function myplugin_init() {
    foreach( $_POST as $key => $value ) {
        if( is_array($value) )
            echo '$key ' . $key . ' - ' . print_r($value ). '<br>';
        else
            echo '$key ' . $key . ' - ' . $value . '<br>';
    }
}

//add_action( 'init', 'myplugin_init' );

add_filter( 'views_edit-optin-statistics', 'so_13813805_add_button_to_views' );
function so_13813805_add_button_to_views( $views ) {
    $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $views['export-optin-list'] = '<a href="'.$actual_link.'&export=1" id="export-optin-list" type="button" class="button button-primary button-large" title="Export Optin List" style="margin: 0px;height: 20px;line-height: 16px;font-size: 12px;">Export Optin List</a>';
    return $views;
}

function optinspin_crb_get_i18n_theme_option( $option_name ) {
    $suffix = optinspin_crb_get_i18n_suffix();
    if(!empty(carbon_get_theme_option( $option_name . $suffix ))){
        return carbon_get_theme_option( $option_name . $suffix );
    } else {
        return carbon_get_theme_option( $option_name );
    }
}

function optinspin_wheel_script_style() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_style( 'optinspin-style-css', optinspin_PLUGIN_URL . 'assets/css/style-css.css' );
    wp_enqueue_script( 'optinspin-script-js', optinspin_PLUGIN_URL . 'assets/js/script.js' );
}
add_action( 'wp_enqueue_scripts', 'optinspin_wheel_script_style',99 );

function optinspin_admin_wheel_script() {
    if( get_post_type() == 'optin-wheels' ) {
        wp_enqueue_style( 'optinspin-font-awesome-style', 'https://use.fontawesome.com/releases/v5.0.10/css/all.css' );
        wp_enqueue_style( 'optinspin-admin-setting-style', optinspin_PLUGIN_URL . 'assets/css/setting-style.css' );
        wp_enqueue_script( 'optinspin-admin-setting-script', optinspin_PLUGIN_URL . 'assets/js/setting-script.js' );

        wp_enqueue_style( 'optinspin-admin-style', optinspin_PLUGIN_URL . 'assets/css/admin-style.css' );
        wp_enqueue_script( 'optinspin-admin-script-backend', optinspin_PLUGIN_URL . 'assets/js/admin-script.js' );
    }
        $ajaxurl = array(
            'ajaxurl' => admin_url('admin-ajax.php')
        );
        wp_localize_script( 'optinspin-admin-script', 'php_data', $ajaxurl );

}
add_action( 'admin_enqueue_scripts', 'optinspin_admin_wheel_script' );

function optinspin_update_posts() {

	$args = array(
			'public'   => true,
			'_builtin' => false
	);

	$output = 'names'; // names or objects, note names is the default
	$operator = 'or'; // 'and' or 'or'

	$post_types = get_post_types(  $args, $output, $operator );
	update_option('optinspin_available_posts',$post_types);
}
register_activation_hook( __FILE__, 'optinspin_update_posts' );

add_action('wp_ajax_optinspin_mailchimp_get_list', '_optinspin_mailchimp_get_list');
add_action('wp_ajax_nopriv_optinspin_mailchimp_get_list', '_optinspin_mailchimp_get_list');

function _optinspin_mailchimp_get_list() {

	if (isset($_POST['action']) && $_POST['action'] == 'optinspin_mailchimp_get_list' && isset($_POST['_optinspin_mailchimp_api_key'])) {
		if (!empty(trim($_POST['_optinspin_mailchimp_api_key']))) {
			$_optinspin_mailchimp_api_keyful = $_POST['_optinspin_mailchimp_api_key'];
			$_optinspin_mailchimp_api_key = explode('-', $_optinspin_mailchimp_api_keyful);
			$prefix = $_optinspin_mailchimp_api_key[1]; //at the end of your API Key, there is a -us1, or us2, etc......you want the prefix to be the us2 for examples.
			//Let's go Get a LIST ID for the subscriber list we are going to be putting content in.
			$get_lists = 'http://' . $prefix . '.api.mailchimp.com/1.3/?method=lists';
			$data = array();
			$data['apikey'] = $_optinspin_mailchimp_api_keyful;
			$post_str = '';
			foreach ($data as $key => $val) {
				$post_str .= $key . '=' . urlencode($val) . '&';
			}
			$post_str = substr($post_str, 0, -1);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://'.$prefix.'.api.mailchimp.com/1.3/?method=lists&apikey='.$_optinspin_mailchimp_api_keyful);
			// curl_setopt($ch, CURLOPT_POST, TRUE);
			// curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$response = curl_exec($ch);
			if (curl_error($ch)) {
				$curl_error = curl_error($ch);
			}
			curl_close($ch);
			if (!empty($response)) {
				update_option('optinspin_mailchimp_get_list', $response);
				$jsondecoded = json_decode($response);
				if (!empty($jsondecoded->data)) {
					$options .= '<option value="" >Select Email List</option>';
					foreach ($jsondecoded->data as $datavalue) {
						$options .= '<option value="' . $datavalue->id . '" >' . trim(ucfirst($datavalue->name)) . '</option>';
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
	}
	die();
}



add_action('wp_ajax_optinspin_active_campaign_get_list', '_optinspin_active_campaign_get_list');
add_action('wp_ajax_nopriv_optinspin_active_campaign_get_list', '_optinspin_active_campaign_get_list');

function _optinspin_active_campaign_get_list() {

	if (isset($_POST['action']) && $_POST['action'] == 'optinspin_active_campaign_get_list'
		&& isset($_POST['_optinspin_active_campaign_api_key'])
		&& isset($_POST['_optinspin_active_campaign_url'])) {

		if (
				!empty(trim($_POST['_optinspin_active_campaign_url']))
				&&
				!empty(trim($_POST['_optinspin_active_campaign_api_key']))
			){


			$_optinspin_active_campaign_url = $_POST['_optinspin_active_campaign_url'];
			$_optinspin_active_campaign_api_key = $_POST['_optinspin_active_campaign_api_key'];
			$url = $_optinspin_active_campaign_url;
			$params = array(
				// the API Key can be found on the "Your Settings" page under the "API" tab.
				// replace this with your API Key
				'api_key'      => $_optinspin_active_campaign_api_key,
				// this is the action that fetches a list info based on the ID you provide
				'api_action'   => 'list_list',
				// define the type of output you wish to get back
				// possible values:
				// - 'xml'  :      you have to write your own XML parser
				// - 'json' :      data is returned in JSON format and can be decoded with
				//                 json_decode() function (included in PHP since 5.2.0)
				// - 'serialize' : data is returned in a serialized format and can be decoded with
				//                 a native unserialize() function
				'api_output'   => 'serialize',
				// a comma-separated list of IDs of lists you wish to fetch
				'ids'          => 'all',
				// filters: supply filters that will narrow down the results
				//'filters[name]'      => 'General',  // perform a pattern match (LIKE) for List Name
				// include global custom fields? by default, it does not
				//'global_fields'      => true,

				// whether or not to return ALL data, or an abbreviated portion (set to 0 for abbreviated)
				// 'full'         => 1,
			);
			// This section takes the input fields and converts them to the proper format
			$query = "";
			foreach( $params as $key => $value ) $query .= urlencode($key) . '=' . urlencode($value) . '&';
			$query = rtrim($query, '& ');
			// clean up the url
			$url = rtrim($url, '/ ');
			// This sample code uses the CURL library for php to establish a connection,
			// submit your request, and show (print out) the response.
			if ( !function_exists('curl_init') ) die('CURL not supported. (introduced in PHP 4.0.2)');
			// If JSON is used, check if json_decode is present (PHP 5.2.0+)
			if ( $params['api_output'] == 'json' && !function_exists('json_decode') ) {
				die('JSON not supported. (introduced in PHP 5.2.0)');
			}
			// define a final API request - GET
			$api = $url . '/admin/api.php?' . $query;
			$request = curl_init($api); // initiate curl object
			curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
			curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
			//curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment if you get no gateway response and are using HTTPS
			curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
			$response = (string)curl_exec($request); // execute curl fetch and store results in $response
			if (curl_error($request)) {
				$curl_error = curl_error($request);
			}
			// additional options may be required depending upon your server configuration
			// you can find documentation on curl options at http://www.php.net/curl_setopt
			curl_close($request); // close curl object


			if (!empty($response)){
				$jsondecoded = unserialize($response);

				update_option('optinspin_active_campaign_get_list', $jsondecoded);

				if (!empty($jsondecoded)) {
					$options .= '<option value="" >Select Email List</option>';
					foreach ($jsondecoded as $datavalue) {
						if(!empty($datavalue['id']) and !empty($datavalue['name'])  ){
							$options .= '<option value="' . $datavalue['id'] . '" >' . trim(ucfirst($datavalue['name'])) . '</option>';
						}
					}
				}

				$return = array(
						'statuss' => true,
						'response' => $options
				);
				echo json_encode($return);
				die();
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
	}
	die();
}

add_action('wp_ajax_test_zapier_webhook', 'test_zapier_webhook_fun');
add_action('wp_ajax_nopriv_test_zapier_webhook', 'test_zapier_webhook_fun');

function test_zapier_webhook_fun() {
	if (isset($_POST['action']) && $_POST['action'] == 'test_zapier_webhook'
		&& isset($_POST['_optinspin_zapier_url'])) {

		if ( !empty(trim($_POST['_optinspin_zapier_url'])) ) {

				$curl = curl_init();
				update_option('_optinspin_zapier_url',$_POST['_optinspin_zapier_url']);
				curl_setopt_array($curl, array(
				  CURLOPT_URL => $_POST['_optinspin_zapier_url'],
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "POST",
				  CURLOPT_POSTFIELDS => "{\"email\":\"test_webhook@\",\"Name\":\"OptinSpinByWpexperts\",\"subscriber_id\":\"1\"}",
				  CURLOPT_HTTPHEADER => array(
					"accept: application/json",
					"cache-control: no-cache",
					"content-type: application/json"
				  ),
				));

				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);

				if (!$err) {

				  update_option('optinSpin_zapier_updated',true);
				  update_option('optinSpin_zapier_test_webhook_response',$response);
				  die('Success');
				} else {
				update_option('optinSpin_zapier_updated',false);
				  update_option('optinSpin_zapier_test_webhook_response_error',$response);
				  die('Error_json_'.json_decode());
				}

			}
		}
}

add_action('optinspin_save_email','optinspin_save_email_to_email_subcriber',10,3);

function optinspin_save_email_to_email_subcriber($email,$name,$Post_id){
	//email save to mailchimp

    if(empty($name)){
        $name = 'GUEST';
    }

	if(
		!empty(carbon_get_post_meta($_SESSION['wheel_id'], 'optinspin_mailchimp_api_key' ))
			and
		!empty($email)
			and
		!empty($name)
			and
		!empty($Post_id)
			and
		!empty(carbon_get_post_meta($_SESSION['wheel_id'], 'crb_show_socials' ))
		){
			$apiKey = carbon_get_post_meta($_SESSION['wheel_id'], 'optinspin_mailchimp_api_key' );
			$listId = carbon_get_post_meta($_SESSION['wheel_id'], 'crb_show_socials' );
			if(carbon_get_post_meta($_SESSION['wheel_id'], 'opt_ins' ) == 'single'){
				$status = 'subscribed';
			} else if(carbon_get_post_meta($_SESSION['wheel_id'], 'opt_ins' ) == 'double') {
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
	// email to drip
	$account_id=carbon_get_post_meta($_SESSION['wheel_id'], 'optinspin_drip_account_id' );
	$api_token=carbon_get_post_meta($_SESSION['wheel_id'],'_optinspin_drip_api_token');
	if(
		!empty($account_id)
			and
		!empty($email)
			and
		!empty($name)
			and
		carbon_get_post_meta($_SESSION['wheel_id'],'_optinspin_enable_drip')==='yes'
			and
		!empty($api_token)
	)
	{

		/* Test Drip */
		include 'inc/API/drip_api.php';

		$drip= new Drip_Api($api_token);
		$subscriber = array();
		$subscriber["account_id"] = $account_id; // Drip account ID which you can find in the javascript under Settings
		$subscriber["email"] = $email;
		$response=$drip->create_or_update_subscriber($subscriber);

	/* end test drip */
	}
	//Email save to Remarkety
	if(
		carbon_get_post_meta($_SESSION['wheel_id'], 'optinspin_enable_remarkety' ) == 'yes'
		and
		!empty(carbon_get_post_meta($_SESSION['wheel_id'], 'optinspin_remarkety_store_id'))
		and
		!empty($name)
		and
		!empty($email)
	){
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => "https://app.remarkety.com/api/v1/stores/".carbon_get_post_meta($_SESSION['wheel_id'], 'optinspin_remarkety_store_id')."/contacts",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => "{\r\n  \"email\": \"".$email."\",\r\n  \"firstName\": \"".$name."\",\r\n  \"lastName\": \"\",\r\n  \"tags\": [\"Optin Spin\"],\r\n  \"properties\": {\r\n    \"country\": \"\",\r\n    \"state\": \"\",\r\n    \"city\": \"\",\r\n    \"address\": \"\",\r\n    \"phone\": \"\"\r\n  }\r\n}",
		CURLOPT_HTTPHEADER => array(
		"cache-control: no-cache",
		"content-type: application/json",
		"postman-token: bdbca667-d2d3-25ac-f0a4-54fe50e50893"
		),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if(!empty($response)){
			update_post_meta($Post_id,'remarkety_response',json_decode($response));
		} else {
			update_post_meta($Post_id,'remarkety_response_error',$err);
		}
	}

	//Send Emails to Active Campaign
	if(
		!empty(carbon_get_post_meta($_SESSION['wheel_id'], 'crb_show_socials_active_campaign' ))
			and
		!empty($email)
			and
		!empty(carbon_get_post_meta($_SESSION['wheel_id'], 'optinspin_active_campaign_api_key' ))
			and
		!empty(carbon_get_post_meta($_SESSION['wheel_id'], 'optinspin_active_campaign_url' ))
			and
		!empty(carbon_get_post_meta($_SESSION['wheel_id'], 'crb_show_socials_active_campaign' ))
			and
		!empty($Post_id)
		){
			if(!empty(carbon_get_post_meta($_SESSION['wheel_id'], 'optinspin_active_campaign_form_id' ))){
				$formId = carbon_get_post_meta($_SESSION['wheel_id'], 'optinspin_active_campaign_form_id' );
			} else {
				$formId = null;
			}
			// By default, this sample code is designed to get the result from your ActiveCampaign installation and print out the result
			$url = carbon_get_post_meta($_SESSION['wheel_id'], 'optinspin_active_campaign_url' );
			$params = array(
				// the API Key can be found on the "Your Settings" page under the "API" tab.
				// replace this with your API Key
				'api_key'      => carbon_get_post_meta($_SESSION['wheel_id'], 'optinspin_active_campaign_api_key' ),
				// this is the action that adds a contact
				'api_action'   => 'contact_add',
				// define the type of output you wish to get back
				// possible values:
				// - 'xml'  :      you have to write your own XML parser
				// - 'json' :      data is returned in JSON format and can be decoded with
				//                 json_decode() function (included in PHP since 5.2.0)
				// - 'serialize' : data is returned in a serialized format and can be decoded with
				//                 a native unserialize() function
				'api_output'   => 'serialize',
			);
			// here we define the data we are posting in order to perform an update
			$post = array(
				'email'                    => $email,
				'first_name'               => $name,
				'last_name'                => '',
				'phone'                    => '',
				'orgname'                  => '',
				//'tags'                     => 'api',
				//'ip4'                    => '127.0.0.1',
				// any custom fields
				//'field[345,0]'           => 'field value', // where 345 is the field ID
				//'field[%PERS_1%,0]'      => 'field value', // using the personalization tag instead (make sure to encode the key)
				// assign to lists:
				'p[1]'                   => carbon_get_post_meta($_SESSION['wheel_id'], 'crb_show_socials_active_campaign' ), // example list ID (REPLACE '123' WITH ACTUAL LIST ID, IE: p[5] = 5)
				'status[1]'              => 1, // 1: active, 2: unsubscribed (REPLACE '123' WITH ACTUAL LIST ID, IE: status[5] = 1)
				'form'          => $formId, // Subscription Form ID, to inherit those redirection settings
				//'noresponders[123]'      => 1, // uncomment to set "do not send any future responders"
				//'sdate[123]'             => '2009-12-07 06:00:00', // Subscribe date for particular list - leave out to use current date/time
				// use the folowing only if status=1
				'instantresponders[1]' => 0, // set to 0 to if you don't want to sent instant autoresponders
				//'lastmessage[123]'       => 1, // uncomment to set "send the last broadcast campaign"
				//'p[]'                    => 345, // some additional lists?
				//'status[345]'            => 1, // some additional lists?
			);
			// This section takes the input fields and converts them to the proper format
			$query = "";
			foreach( $params as $key => $value ) $query .= urlencode($key) . '=' . urlencode($value) . '&';
			$query = rtrim($query, '& ');
			// This section takes the input data and converts it to the proper format
			$data = "";
			foreach( $post as $key => $value ) $data .= urlencode($key) . '=' . urlencode($value) . '&';
			$data = rtrim($data, '& ');
			// clean up the url
			$url = rtrim($url, '/ ');
			// This sample code uses the CURL library for php to establish a connection,
			// submit your request, and show (print out) the response.
			if ( !function_exists('curl_init') ) die('CURL not supported. (introduced in PHP 4.0.2)');
			// If JSON is used, check if json_decode is present (PHP 5.2.0+)
			if ( $params['api_output'] == 'json' && !function_exists('json_decode') ) {
				//die('JSON not supported. (introduced in PHP 5.2.0)');
			}
			// define a final API request - GET
			$api = $url . '/admin/api.php?' . $query;
			$request = curl_init($api); // initiate curl object
			curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
			curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
			curl_setopt($request, CURLOPT_POSTFIELDS, $data); // use HTTP POST to send form data
			//curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment if you get no gateway response and are using HTTPS
			curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
			$response = (string)curl_exec($request); // execute curl post and store results in $response
			// additional options may be required depending upon your server configuration
			// you can find documentation on curl options at http://www.php.net/curl_setopt
			if(curl_error($request))
			{
				$cur_error =  curl_error($request);
			}
			curl_close($request); // close curl object
			if ( !$response ) {
				die('Nothing was returned. Do you have a connection to Email Marketing server?');
			}
			// This line takes the response and breaks it into an array using:
			// JSON decoder
			//$result = json_decode($response);
			// unserializer
			$result = unserialize($response);
			if(!empty($result)){
				update_post_meta($Post_id,'active_campaign_response',$result);
			} else {
				update_post_meta($Post_id,'active_campaign_response_error',$cur_error);
			}

	}


	if(
		!empty(carbon_get_post_meta($_SESSION['wheel_id'], 'crb_show_socials_amailster' ))
			and
		!empty($email)
			and
		!empty($name)
			and
		!empty($Post_id)
			and
		function_exists( 'mailster' )
		){
		    $optimailster = 1;
		    if(carbon_get_post_meta($_SESSION['wheel_id'], 'opt_ins_mailster') == 'single'){
	            $optimailster = 1;
            } else {
                $optimailster = 0;
            }
			$overwrite = true;
			$userdata = array(
				'email' => $email,
				'firstname' => $name,
				'lastname' => '',
				'custom-field' => 'Mailster for OptinSpin',
				'referer' => $_SERVER['REQUEST_URI'],
				'status' => $optimailster,
			);

			// add a new subscriber and $overwrite it if exists
			$subscriber_id = mailster( 'subscribers' )->add( $userdata, $overwrite );

			// if result isn't a WP_error assign the lists
			if ( ! is_wp_error( $subscriber_id ) ) {
				// your list ids
				$list_ids = carbon_get_post_meta($_SESSION['wheel_id'], 'crb_show_socials_amailster' );
				mailster( 'subscribers' )->assign_lists( $subscriber_id, $list_ids );
				update_post_meta($Post_id,'mailster_response',$subscriber_id);
			}
		}


		if(
		!empty($email)
			and
		!empty($name)
			and
		!empty($Post_id)
			and
		!empty(carbon_get_post_meta($_SESSION['wheel_id'], 'optinspin_zapier_url' ))
		){
				$curl = curl_init();
				curl_setopt_array($curl, array(
				  CURLOPT_URL => carbon_get_post_meta($_SESSION['wheel_id'], 'optinspin_zapier_url' ),
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "POST",
				  CURLOPT_POSTFIELDS => "{\"email\":\"".$email."\",\"Name\":\"".$name."\",\"subscriber_id\":\"".$Post_id."\"}",
				  CURLOPT_HTTPHEADER => array(
					"accept: application/json",
					"cache-control: no-cache",
					"content-type: application/json"
				  ),
				));

				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);
				if (!$err) {
				  update_option('optinSpin_zapier_form_response',$response);
				} else {
				  update_option('optinSpin_zapier_form_response_error',$response);
				}
		}


}

function optinspin_admin_notice() {

	if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', carbon_get_post_meta($_SESSION['wheel_id'], 'active_plugins' ) ) ) ) {
		$class = 'notice notice-error';
		$message = __("Error! <a href='https://wordpress.org/plugins/woocommerce/' target='_blank'>WooCommerce</a> Plugin is required to activate OptinSpin", 'optinspin');

		printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);

		deactivate_plugins( plugin_basename( __FILE__ ) );
	}
}

//add_action( 'admin_notices', 'optinspin_admin_notice' );

add_action('admin_print_scripts', 'ure_remove_admin_notices');
function ure_remove_admin_notices() {
    global $wp_filter;
    if (is_user_admin()) {
        if (isset($wp_filter['user_admin_notices'])) {
            unset($wp_filter['user_admin_notices']);
        }
    } elseif (isset($wp_filter['admin_notices'])) {
        unset($wp_filter['admin_notices']);
    }
    if (isset($wp_filter['all_admin_notices'])) {
        unset($wp_filter['all_admin_notices']);
    }
}