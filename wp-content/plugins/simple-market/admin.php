<?php
/*
Plugin Name: Simple Market
Plugin URI: http://www.mccm-feldkirch.at/
Description: Simple market plugin initially coded for Motocross Club Montfort.
Version: 1.0.0
Author: Eduard Gopp
Author URI: http://www.mccm-feldkirch.at/
*/


/*
 * ---------------------------------------------------- Activation, deactivation and update control
 */

//TODO: causes an error on plugin activation, where to get the prefix from? 
//$sm_table_name = $wpdb->prefix . 'wp_simple_market';
$sm_table_name = 'wp_simple_market';

$sm_initialize_options = array(
	'plugin_name' 	 						=> 'simple_market',
	'plugin_version' 						=> '1.0.0',
	'target_post_name' 						=> 'markt',
	'target_post_id'						=> 49,
	'terms_post_id'							=> 1312,
	'ad_max_active_in_days'					=> 30,
	'ad_reactivation_treshold_in_days'		=> 10,
	'ad_max_images'							=> 4,
	'webmaster_mail'						=> 'e.gopp@mccm-feldkirch.at',
	'reviewer_mail_addresses'				=> array('eduard.gopp@mccm-feldkirch.at', 'a.walser@mccm-feldkirch.at')
//	'reviewer_mail_adresses'				=> 'e.gopp@gmail.com'
);
$sm_options = NULL;
$sm_mysql_column_length = array (
	'first_name' 		=> 50,
	'last_name'  		=> 50,
	'zip_code'			=> 15,
	'city'				=> 30,
	'country'			=> 30,
	'mail'				=> 80,
	'phone'				=> 50,
	'image_uuid'		=> 13,
	'mail_approval_key' => 23		//uniqid with more entropy returns 23 symbols, do not change!
);

function on_plugin_activate() {
	global $wpdb;
	global $sm_table_name;
	global $sm_initialize_options;
	global $sm_mysql_column_length;

	//TODO: unfortunatelly called twice and first call produces a serios bug
	//TODO: create directory structure if not exists
	
	$sql = "CREATE TABLE $sm_table_name (
		id INT NOT NULL AUTO_INCREMENT,
		first_name varchar(".$sm_mysql_column_length['first_name'].") DEFAULT '' NOT NULL,
		last_name varchar(".$sm_mysql_column_length['last_name'].") DEFAULT '' NOT NULL,
		mail varchar(".$sm_mysql_column_length['mail'].") DEFAULT '' NOT NULL,
		phone varchar(".$sm_mysql_column_length['phone'].") DEFAULT '' NOT NULL,	
		zip_code varchar(".$sm_mysql_column_length['zip_code'].") DEFAULT '' NOT NULL,
		city varchar(".$sm_mysql_column_length['city'].") DEFAULT '' NOT NULL,
		country varchar(".$sm_mysql_column_length['country'].") DEFAULT '' NOT NULL,
		ip varchar(39) DEFAULT '' NOT NULL,
		submit_date_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		keep_alive_date_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	  	text longtext NOT NULL,
		image_uuid char(".$sm_mysql_column_length['image_uuid'].") DEFAULT '' NOT NULL,
		mail_approve int(1) DEFAULT 0 NOT NULL,
	  	mail_approval_key varchar(".$sm_mysql_column_length['mail_approval_key'].") DEFAULT '' NOT NULL,
	  	webmaster_approve int(1) DEFAULT 0 NOT NULL,
	  	webmaster_approval_key varchar(".$sm_mysql_column_length['mail_approval_key'].") DEFAULT '' NOT NULL,
	  	UNIQUE KEY id (id),
	  	UNIQUE KEY mail_approval_key_UNIQUE (mail_approval_key)
	)  ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
 	dbDelta($sql); //create or compute differences and alter
	
	//protect future users from being responsible for a "unique" salt on their own
	require_once __DIR__ . '/utility_functions.php';
	$sm_initialize_options['mail_approval_salt'] = get_rand_string(6);
		
	update_option('simple_market', $sm_initialize_options);
	
	//update immediatelly
	global $sm_options;
	$sm_options = $sm_initialize_options;
}

function on_plugin_deactivate() {
	drop_table_and_clean_opt_version();	
}
function on_plugin_uninstall() {
	drop_table_and_clean_opt_version();
}

register_activation_hook(__FILE__, 'on_plugin_activate');
register_deactivation_hook(__FILE__, 'on_plugin_deactivate');
register_uninstall_hook(__FILE__, 'on_plugin_uninstall');

/**
 * Since 3.1. we have to call activation hook manually on update.
 * Don't call this method directly. Function is called on 'plugins_loaded' event.
 */
function on_plugin_update() {
	global $sm_initialize_options;
	global $sm_options;
	
	if(!isset($sm_options))
		$sm_options = get_site_option('simple_market');
	
	if($sm_options !== FALSE && $sm_options['plugin_version'] != $sm_initialize_options['plugin_version']) {
		on_plugin_activate();
		update_option('simple_market', $sm_initialize_options);
	}
}
add_action('plugins_loaded', 'on_plugin_update');

function drop_table_and_clean_opt_version() {
	
	return false;
	
	global $wpdb;
	global $sm_table_name;
	$wpdb->query("DROP TABLE IF EXISTS $sm_table_name");
	delete_option('simple_market');
}

/*
 * -------------------------------------------------------------------- the content
 */

function simple_market_add_scripts() {

	global $sm_options;
	if(!isset($sm_options))
		$sm_options = get_site_option('simple_market');
		
	if(is_page($sm_options['target_post_name'])) {
		wp_enqueue_style('sm_css', plugins_url('/css/style.css', __FILE__), false, $sm_options['plugin_version']);
		
		wp_enqueue_script('sm_form_js', plugins_url('/js/form.js', __FILE__), array( 'jquery' ) , $sm_options['plugin_version']);
		wp_localize_script('sm_form_js', 'SMInject', array( 
															'url' => admin_url( 'admin-ajax.php' ),
															'nonce' => wp_create_nonce('sm_nonce'),
															'img_map' => array()));
		
		wp_enqueue_script('google-recptcha', "http://www.google.com/recaptcha/api/js/recaptcha_ajax.js");
				
		//http://blueimp.github.com/cdn/css/bootstrap.min.css
		//http://blueimp.github.com/JavaScript-Templates/tmpl.min.js
		//http://blueimp.github.com/JavaScript-Load-Image/load-image.min.js
		//http://blueimp.github.com/JavaScript-Canvas-to-Blob/canvas-to-blob.min.js
		wp_enqueue_style('blueimp_bootstrap_min_css', plugins_url('/jquery-file-upload/css/bootstrap.min.css', __FILE__));
		wp_enqueue_style('blueimp_jquery_fileupload_ui_css', plugins_url('/jquery-file-upload/css/jquery.fileupload-ui.css', __FILE__));
	
		wp_enqueue_script('blueimp_jquery_ui_widget_js', plugins_url('/jquery-file-upload/js/vendor/jquery.ui.widget.js', __FILE__));
		wp_enqueue_script('blueimp_tmp_min_js', plugins_url('/jquery-file-upload/js/blueimp/tmpl.min.js', __FILE__));	
		wp_enqueue_script('blueimp_load_img_min_js', plugins_url('/jquery-file-upload/js/blueimp/load-image.min.js', __FILE__));
		wp_enqueue_script('blueimp_canvas_to_blob_min_js',  plugins_url('/jquery-file-upload/js/blueimp/canvas-to-blob.min.js', __FILE__));

		wp_enqueue_script('blueimp_jquery_iframe_transport_js', plugins_url('/jquery-file-upload/js/jquery.iframe-transport.js', __FILE__));
		wp_enqueue_script('blueimp_jquery_fileupload_js', plugins_url('/jquery-file-upload/js/jquery.fileupload.js', __FILE__));
		wp_enqueue_script('blueimp_jquery_fileupload_fp_js', plugins_url('/jquery-file-upload/js/jquery.fileupload-fp.js', __FILE__));
		wp_enqueue_script('blueimp_jquery_fileupload_ui_js', plugins_url('/jquery-file-upload/js/jquery.fileupload-ui.js', __FILE__));
	} 
}

add_action('wp_enqueue_scripts', 'simple_market_add_scripts');

$the_market_admin_preview_mode = false;
$admin_key_received = NULL;

function get_the_market($content) {
	
	//should be allready loaded @see simple_market_add_scripts()
	global $sm_options;
	global $the_market_admin_preview_mode;
	global $admin_key_received;
	
	$the_market = "";
	global $post;
	
	if($sm_options['target_post_id'] == $post->ID) {
		
			$_SESSION['market_item_to_submit'] = NULL;
			unset($_SESSION['market_item_to_submit']);
		
			$user_action = $_GET['action'];
			$user_key_received = $_GET['mccm_activation_key'];
		
			$admin_action = $_GET['admin-action'];
			$admin_key_received = $_GET['key'];
		
			include_once __DIR__ . '/the_market.php';
			require_once __DIR__ . '/mail_functions.php';
		
			if(isset($user_action) === true && isset($user_key_received) === true) {
				switch($user_action) {
					case 'mccm_activate_ad'		: $the_market = sm_mail_activate_ad($user_key_received); break;
					case 'mccm_reactivate_ad'	: $the_market = sm_mail_reactivate_ad($user_key_received); break;
					case 'mccm_deactivate_ad'	: $the_market = sm_mail_deactivate_ad($user_key_received); break;
					default						: $the_market = 'Action not known <br/><br/>';
				}
			} else {
					
				if(isset($admin_action) === true && isset($admin_key_received) === true) {
					switch($admin_action) {
						case 'activate' 	: $the_market = sm_admin_mail_activate_ad($admin_key_received); break;
						case 'deactivate' 	: $the_market = sm_admin_mail_deactivate_ad($admin_key_received); break;
						case 'preview'		: $the_market =  sm_get_admin_preview(); break;
						default				: $the_market = 'Admin action invalid <br/><br/>';
					}
				} else {
					$the_market = sm_get_the_market_page();
				}
		
			}
			
			if($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
				$the_market = "";
			} else {
				$content = "";
			}
	}

	return $content . $the_market;
}

add_filter('the_content', 'get_the_market');

/*
 * ---------------------------------------------------------------------- ajax hooks
 */

//source 1: http://www.garyc40.com/2010/03/5-tips-for-using-ajax-in-wordpress/
//source 2: http://wp.smashingmagazine.com/2011/10/18/how-to-use-ajax-in-wordpress/

add_action('wp_ajax_nopriv_sm_submit_form', 'sm_form_submit_handler');

function sm_form_submit_handler() {
	global $sm_mysql_column_length;
	
	require_once __DIR__ . '/classes.php';
	$form_response = new SimpleMarketFormResponse();
	
	$market_item_to_submit = $_SESSION['market_item_to_submit'];
	$unique_submit_id = NULL;
	if(isset($_POST['sm_submit_id']) && isset($market_item_to_submit)) {
		$unique_submit_id_in_session = $market_item_to_submit->get_image_uuid();		
		if(strcmp($_POST['sm_submit_id'], $unique_submit_id_in_session) != 0) {
			die();
		}
		$unique_submit_id = $unique_submit_id_in_session;
	} else {
		
		if(! wp_verify_nonce($_POST['sm_nonce'], 'sm_nonce'))
			die();
		
		require_once __DIR__ . '/recaptcha-1.11/recaptchalib.php';
		
		$challange_field = $_POST['recaptcha_challenge_field'];
		$response_field = $_POST['recaptcha_response_field'];
		
		$resp = recaptcha_check_answer('6LdPfdwSAAAAAA_wdOwQLNf5ILdwXbAHL17C_s5g', $_SERVER['REMOTE_ADDR'], $challange_field, $response_field);
		if( $resp->is_valid !== true ) {
			$form_response->set_captcha_error(true);
			echo $form_response->get_json_response();
			exit();
		}
		
		//this is the first submission of the main form,
		//and the recaptcha validation passed allready successfully as seen some lines above
		//generate unique id so user must not enter again a captcha in this submission process
		//additionally this information is used as tag for images refered to this ad during activation process
		$unique_submit_id = uniqid($sm_mysql_column_length['image_uuid']);		
	}
	
	$first_name = $_POST['sm_first_name'];
	$last_name = $_POST['sm_last_name'];
	$mail = $_POST['sm_mail'];
	$phone = $_POST['sm_phone'];
	$zip_code = $_POST['sm_zip_code'];
	$city =  $_POST['sm_city'];
	$country = $_POST['sm_country'];
	$text = $_POST['sm_text'];
		
	if(UserInputValidator::is_first_name_valid($first_name, $sm_mysql_column_length['first_name']) === false) {
		$form_response->set_first_name_error(true);
	}
	if(UserInputValidator::is_last_name_valid($last_name, $sm_mysql_column_length['last_name']) === false) {
		$form_response->set_last_name_error(true);
	}
	if(UserInputValidator::is_mail_valid($mail, $sm_mysql_column_length['mail']) === false) {
		$form_response->set_mail_error(true);
	}
	if(UserInputValidator::is_phone_valid($phone, $sm_mysql_column_length['phone'], 6) === false) {
		$form_response->set_phone_error(true);
	}
	if(UserInputValidator::is_zip_code_valid($zip_code, $sm_mysql_column_length['zip_code']) === false) {
		$form_response->set_zip_code_error(true);
	}
	if(UserInputValidator::is_country_valid($country, $sm_mysql_column_length['country']) === false) {
		$form_response->set_country_error(true);
	}
	if(UserInputValidator::is_city_valid($city, $sm_mysql_column_length['city']) === false) {
		$form_response->set_city_error(true);
	}
	if(UserInputValidator::is_text_valid($text, 500) === false) {
		$form_response->set_text_error(true);
	}
	
	$mysql_date_time_now_gmt = current_time('mysql', 1);
	$sm_properties = array(
		'first_name' 			=> $first_name,
		'last_name' 			=> $last_name,
		'mail'					=> $mail,
		'phone'					=> $phone,
		'zip_code'				=> $zip_code,
		'city'					=> $city,
		'country'				=> $country,
		'text'					=> $text,
		'submit_date_time'		=> $mysql_date_time_now_gmt,
		'keep_alive_date_time'	=> $mysql_date_time_now_gmt,
		'image_uuid'			=> $unique_submit_id
	);
	
	$sm_submit_item = new SimpleMarketItem($sm_properties);
	
	$market_item_renderer = new PreviewMarketItemRenderer($sm_submit_item);
	$form_response->set_market_item_renderer($market_item_renderer);
	$form_response->set_market_item($sm_submit_item);
	
	echo $form_response->get_json_response();
	exit();	
	
}

add_action('wp_ajax_nopriv_sm_preview_submit', 'sm_preview_submit_handler');

function sm_preview_submit_handler() {
	global $wpdb;
	global $sm_table_name;
	global $sm_mysql_column_length;
	global $sm_initialize_options;
	global $sm_options;
	
	header("Content-Type: application/json");
	
	die_if_request_not_authorized();
	$the_market_item_to_submit = $_SESSION['market_item_to_submit'];

	$mail_approval_key = uniqid('', true);
	$webmaster_approval_key = uniqid('', true);
	
	/*
	 * ------------------------------------------------------------------------------- send mail
	 */
	
	$headers[] = 'From: MCCM Feldkirch <markt@mccm-feldkirch.at>';
	$headers[] = 'MIME-Version: 1.0';
	$headers[] = 'Content-type: text/html; charset=UTF-8';
	
	if(!isset($sm_options))
		$sm_options = get_site_option('simple_market');
	
	$market_permalink = get_market_permalink();
	
	$sm_activation_link = 	$market_permalink . '?action=mccm_activate_ad&mccm_activation_key='.$mail_approval_key;
	$sm_reactivation_link = $market_permalink . '?action=mccm_reactivate_ad&mccm_activation_key='.$mail_approval_key;
	$sm_deactivate_link =	$market_permalink . '?action=mccm_deactivate_ad&mccm_activation_key='.$mail_approval_key;
	
	
	$the_message =
	'<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
Hallo und Danke f&uuml;r Ihre Online-Anzeige auf der <a href="'.$market_permalink.'". target="_blank">"Webseite des MCCM"</a>.<br/><br/>
Sollte Sie keine Online-Anzeige bei uns aufgegeben haben, k&ouml;nnen Sie dieses Mail einfach l&ouml;schen.<br/><br/>
<span style="font-weight: bold; color: #990000;">Ansonsten behalten Sie diese E-Mail bitte nach dem Klick auf den Aktivierungslink, bis Ihr Verkauf abgeschlossen ist!</span><br/><br/>
<span style="font-weight: bold; color: #990000;">Bitte antworten Sie nicht auf dieses Mail, da dies automatisch generiert wurde.</span><br/><br/>
Mit den hier gelisteten Links k&ouml;nnen Sie:
<ul>
<li>Ihr Inserat aktivieren</li>
<li>Ihr Inserat wird nach '.$sm_options['ad_max_active_in_days'].' Tagen automatisch deaktiviert, dieses E-Mail beinhalted auch einen Link f&uuml;r die Reaktivierung sollten Sie Ihr Inserat l&auml;nger schalten wollen.</li>
<li>Ihr Inserat auf wunsch deaktivieren</li>
</ul>
<span style="font-weight: bold;">Ihr Aktivierungs-Link:</span><br/>
'.$sm_activation_link.'<br/>
Ihr Reaktivierungs-Link:<br/>
'.$sm_reactivation_link.'<br/>
Ihr Deaktivierungs-Link:<br/>
'.$sm_deactivate_link.'<br/><br/>
	
Mit freundlichen Gr&uuml;&szlig;en<br/>
Eduard Gopp - Webmaster MCCM Feldkirch
</body>
</html>
';
	
	
	if(!wp_mail($the_market_item_to_submit->get_mail(), "Ihres Online Inserat MCCM Feldkirch", $the_message, $headers)) {
		echo json_encode(array('success' => false, 'error' => '0'));
		exit();
	}
	
	/*
	 * ---------------------------------------------------------------- mail success, now insert
	 */
	
	if($wpdb->insert(
			$sm_table_name,
			array(
				'first_name' 			=> $the_market_item_to_submit->get_first_name(),
				'last_name'	 			=> $the_market_item_to_submit->get_last_name(),
				'mail'					=> $the_market_item_to_submit->get_mail(),
				'phone'					=> $the_market_item_to_submit->get_phone(),
				'zip_code'				=> $the_market_item_to_submit->get_zip_code(),
				'city'					=> $the_market_item_to_submit->get_city(),
				'country'				=> $the_market_item_to_submit->get_country(),
				'ip'					=> $_SERVER['REMOTE_ADDR'],
				'submit_date_time'		=> $the_market_item_to_submit->get_submit_date_time(), 
				'text'					=> $the_market_item_to_submit->get_text(),
				'image_uuid'			=> $the_market_item_to_submit->get_image_uuid(),
				'mail_approval_key' 	=> $mail_approval_key,
				'webmaster_approval_key'=> $webmaster_approval_key
			)) === false) {
		echo json_encode(array('success' => false, 'error' => '1'));		
		exit();
	}
	
	echo json_encode(array('success' => true));
	die();
}

add_action('wp_ajax_nopriv_sm_submit_form_images', 'sm_form_images_submit_handler');

function sm_form_images_submit_handler() {
		
	die_if_request_not_authorized();
	
	$the_market_item_to_submit = $_SESSION['market_item_to_submit'];
	$submit_id = $the_market_item_to_submit->get_image_uuid();
	
	//just to handle malicious requests
	$options = array('check_for_malicious' => true);
	perform_action_on_uploaded_images($the_market_item_to_submit, $options);
	
	if(!isset($sm_options))
		$sm_options = get_site_option('simple_market');
	
	require_once __DIR__ . '/UploadHandler.php';
		
	$options = array(
			'access_control_allow_methods' 	=> array('POST'),
			'max_width'						=> 1600,
			'max_height'					=> 1200,
			'thumbnail' => array(
					'max_width' => 120,
					'max_height' => 120
			),
			'script_url' 					=> admin_url('admin-ajax.php'),
			'upload_dir'					=> get_tmp_image_upload_dir(),
			'upload_url'					=> content_url('simple-market/tmp/'),
			'max_number_of_files'			=> 1000,						//complete directory setting
			'max_file_size'					=> 512000,
			'delete_type'					=> 'POST',
			'image_versions'				=> array(                
														'thumbnail' => array(
                    													'max_width' => 120,
                    													'max_height' => 120,
                														'jpeg_quality' => 100
                														)
													)
	);
	
	_log("Script URL: ".$options['script_url']);
	_log("Upload DIR: ".$options['upload_dir']);
	_log("Upload URL: ".$options['upload_url']);
	_log($_FILES['files']);
	
	$upload_handler = new UploadHandler($options, false);
	
	$response = json_encode(array('success' => false));
	switch($_SERVER['REQUEST_METHOD']) {
		case 'POST'	:
			$upload_handler->set_image_uuid($submit_id);
			$upload_handler->post();
			exit();
		default : break;
	}
	
	header("Content-Type: application/json");
	echo $response;
	exit();
}

add_action('wp_ajax_nopriv_sm_get_contact', 'sm_get_contact_handler');

function sm_get_contact_handler() {

	$form_response = new SimpleMarketFormResponse();
	require_once __DIR__ . '/recaptcha-1.11/recaptchalib.php';
	$challange_field = $_POST['recaptcha_challenge_field'];
	$response_field = $_POST['recaptcha_response_field'];
	$resp = recaptcha_check_answer('6LdPfdwSAAAAAA_wdOwQLNf5ILdwXbAHL17C_s5g', $_SERVER['REMOTE_ADDR'], $challange_field, $response_field);
	if( $resp->is_valid !== true ) {
		$form_response->set_captcha_error(true);
		echo $form_response->get_json_response();
		exit();
	}
		
	global $wpdb;
	global $sm_table_name;
		
	//TODO: only serve contact details if ad is active
	
	$prepared_stmt = $wpdb->prepare("SELECT * FROM $sm_table_name WHERE id = %s LIMIT 1", $_POST['contact_id']);
	$row = $wpdb->get_row($prepared_stmt, ARRAY_A);
	$sm_item = new SimpleMarketItem($row);
	$sm_renderer = new ContactDetailsMarketItemRenderer($sm_item);
	$contact_detail_markup = $sm_renderer->get_markup();
	$response = array();
	header("Content-Type: application/json");
	$response['markup'] = $contact_detail_markup;
	echo json_encode($response);
	exit();
}

/*
 * ---------------------------------------------------------------------- session hooks
*/

add_action('init', 'sm_start_session');
function sm_start_session() {
	require_once __DIR__.'/classes.php';
	if(!session_id()) {
		session_start();
	}
}

//http://devondev.com/2012/02/03/using-the-php-session-in-wordpress/
//add_action('wp_logout', bla)
//add_action('wp_login', bla)
//both currently not needed

/*
 * ------------------------------------------------------------------------ log utily
 */

//http://fuelyourcoding.com/simple-debugging-with-wordpress/
if(!function_exists('_log')){
	function _log( $message ) {
		if( WP_DEBUG === true ){
			if( is_array( $message ) || is_object( $message ) ){
				error_log( print_r( $message, true ) );
			} else {
				error_log( $message );
			}
		}
	}
}

/*
 * --------------------------------------------------------------------- request authorization
 */

/**
 * In first request the user data is validated (+ captcha challange).
 * On validation success 'market_item_to_submit' is generated with unique_id 'sm_submit_id'.
 * This 'market_item_to_submit' is stored in session and 'sm_submit_id' is passed back to client.
 * 
 * All further POST requests in ad creation process must contain the 'sm_submit_id' stored in SESSION.
 * This function checks the 'sm_submit_id' and script exectuion gets stopped on authorization errors.
 */
function die_if_request_not_authorized() {
	
	$the_market_item_to_submit = $_SESSION['market_item_to_submit'];
	if(isset($the_market_item_to_submit) === false) {
		_log("the_market_item_to_submit is not set!!!");
		die();
	}
	
	$sm_submit_id = $the_market_item_to_submit->get_image_uuid();
	if(isset($sm_submit_id) === false) {
		_log("sm_submit_id ist not set!!!");
		die();
	}
	
	$posted_submit_id = $_POST['sm_submit_id'];
	if(isset($posted_submit_id) === false) {
		_log("post submit_id not set!!!");
		die();
	}
	
	if(strcmp($posted_submit_id, $sm_submit_id) != 0) {
		_log("strcmp $posted_submit_id and $sm_submit_id failed!!");
		die();
	}
}

/*
 * ---------------------------------------------------------------------- image helpers
 */

function get_tmp_image_upload_dir() {
	return $_SERVER['DOCUMENT_ROOT']."/wp-content/simple-market/tmp/";
}
function get_main_image_dir($sm_item, $give_me_the_thumbnail_sub_dir = false) {
	$main_image_dir = $_SERVER['DOCUMENT_ROOT']."/wp-content/simple-market/";
	$main_image_dir .= $sm_item->get_image_folder_name();
	if($give_me_the_thumbnail_sub_dir) {
		$main_image_dir .= DIRECTORY_SEPARATOR . "thumbnail";
	}
	return $main_image_dir;
}
function get_main_image_url($sm_item, $the_image_name, $give_me_the_thumbnail_url = false) {
	$content_url = content_url();
	$content_url .= "/simple-market/".$sm_item->get_image_folder_name()."/";
	if($give_me_the_thumbnail_url) {
		$content_url .= "thumbnail/";
	}
	$content_url  .= $the_image_name;
	return $content_url;
}

/**
 * Options:
 * if only 'check_for_malicious' is set
 * of
 * if 'check_for_malicious' and 'move_to_main_dir' is set
 * --- it is checked that the temp images of the item not exceeds max allowed images
 * 
 * if 'check_for_malicious' and 'get_the_image_urls_of_the_sm_item' 
 * --- it is checked that returned image count not exceeds max allowed images
 * 
 * @param unknown $image_uuid
 * @param unknown $check_for_malicious - normally set to true all the times
 * @param string $move_to_main_dir - false
 */
function perform_action_on_uploaded_images($sm_item, $options = array()) {
		
	$move_to_main_dir = false;
	$check_for_malicious = false;
	$get_image_urls_of_passed_sm_item = false;
	$the_images = array();
	
	foreach ($options as $key => $value) {
		switch($key) {
			case 'move_to_main_dir' : 					
				$move_to_main_dir = true; 
				break;
			case 'get_image_urls_of_passed_sm_item' : 	
				$get_image_urls_of_passed_sm_item = true; 
				break;
			case 'check_for_malicious' : 
				$check_for_malicious = true;
				break;
			default: throw new Exception("option $key not known in perform_action_on_uploaded_images");
		}
	}
	
	_log("perform_action_on_uploaded_images called move to main dir: $move_to_main_dir, get the images: $get_image_urls_of_passed_sm_item");
	_log("check for malicious : $check_for_malicious");
	
	$tmp_upload_dir = get_tmp_image_upload_dir();
	$tmp_upload_dir_thumbnails = $tmp_upload_dir . "thumbnail" . DIRECTORY_SEPARATOR;
	$main_dir = get_main_image_dir($sm_item);
	$main_dir_thumbnails = get_main_image_dir($sm_item, true);
	
	if(!is_dir($main_dir)) {
		if(!mkdir($main_dir)) {
			_log("Cant create $main_dir!!!");
		}
	}
	if(!is_dir($main_dir_thumbnails)) {
		if(!mkdir($main_dir_thumbnails)) {
			_log("Cant create $main_dir_thumbnails!!!");
		}
	}
	
	_log("Temp upload dir: $tmp_upload_dir");
	_log("Main dir: $main_dir");
	
	global $sm_options;
	
	if(!isset($sm_options))
		$sm_options = get_site_option('simple_market');
	
	$max_images = $sm_options['ad_max_images'];
	
	_log("Max images: $max_images");
		
	$image_count = 0;
	$uuid_length = strlen($sm_item->get_image_uuid());

	if($get_image_urls_of_passed_sm_item) {
		$main_images 		= scandir($main_dir);
		$main_thumb_images 	= scandir($main_dir_thumbnails);
		
		if(is_array($main_images) && is_array($main_thumb_images)) {
			for($i = 0; $i < count($main_images); $i++) {
				$image_to_check = $main_images[$i];
				$item_id = $sm_item->get_id();
				if(preg_match("/^".$item_id."_\d+\.(jpg|jpe?g|png)$/i", $image_to_check, $match)) {
					$the_image = $match[0];
					$url = get_main_image_url($sm_item, $the_image);
					$thumb_url = get_main_image_url($sm_item, $the_image, true);  
					_log("get_image_urls_of_passed_sm_item - image $the_image matches to id: $id url: $url thumb_url: $thumb_url");
					$an_image = array();
					$an_image['url'] = $url;
					$an_image['thumb'] = $thumb_url;
					array_push($the_images, $an_image);
				} else {
					_log("get_image_urls_of_passed_sm_item - image $image_to_check does not match with pattern.");
				}
			}
		} else {
			_log("main images or thumb images folder seemingly does not exist!");
		}
			
	} else if ($move_to_main_dir || $check_for_malicious) {
		$tmp_images = scandir($tmp_upload_dir);

		if(is_array($tmp_images)) {
			$image_to_compare = "";
			$uuid_sub_str = "";
			for($i = 0; $i < count($tmp_images); $i++) {
				$image_to_compare = $tmp_images[$i];
				$uuid_sub_str = substr($image_to_compare, 0, $uuid_length);
				if(strcmp($sm_item->get_image_uuid(),  $uuid_sub_str) == 0) {
					_log("Image found matching $sm_item->get_image_uuid() image name in tmp: $image_to_compare");
					$ext = pathinfo($image_to_compare, PATHINFO_EXTENSION);
					_log("Extension of image is: $ext");
					$image_count += 1;
					if($move_to_main_dir) {
						$the_image = $sm_item->get_id().'_'.$image_count.'.'.$ext;
						$tmp_image = $tmp_upload_dir.$image_to_compare;
						$tmp_image_thumb = $tmp_upload_dir_thumbnails.$image_to_compare;
						$main_image = $main_dir.DIRECTORY_SEPARATOR.$the_image;
						$main_image_thumb = $main_dir_thumbnails.DIRECTORY_SEPARATOR.$the_image;
						_log("Try to rename $tmp_image to $main_image");
						if(!rename($tmp_image, $main_image)) {
							_log("can't move $tmp_image to $main_image");
						}
						if(!rename($tmp_image_thumb, $main_image_thumb)) {
							_log("can't move thumbnail $tmp_image_thumb to $main_image_thumb");
						}
					}
					_log("Image $image_to_compare matches $sm_item->get_image_uuid(). Image Count: $image_count");
				} else {
					_log("Image $image_to_compare does not match $sm_item->get_image_uuid() ... substr is $uuid_sub_str");
				}
			}
		} else {
			_log("tmp image folder seemingly not exist!");
		}
	} else {
		throw new Exception("Ups, it seems there is nothing to do in perform_action_on_uploaded_images!");
	}
	
	if( $check_for_malicious == true && ( $image_count > $max_images || count($the_images) > $max_images ) ) {
		_log("Image count for $sm_item->get_image_uuid() reached, die!!!");
		throw new Exception("Max image count reached for sm_item ".$sm_item->get_image_uuid());
	}
	
	_log("perform_action_on_uploaded_images returns normally");
	return $the_images;
}

/*
 * ---------------------------------------------------------------------- url helpers
*/
function get_market_permalink() {
	global $sm_options;
	if(!isset($sm_options))
		$sm_options = get_site_option('simple_market');

	$market_permalink = get_permalink($sm_options['target_post_id']);
	$market_permalink = rtrim($market_permalink, '/');
	return $market_permalink;
}
?>