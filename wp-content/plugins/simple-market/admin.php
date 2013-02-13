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
$sm_table_name = $wpdb->prefix . 'simple_market';
$sm_initialize_options = array(
	'plugin_name' 	 						=> 'simple_market',
	'plugin_version' 						=> '1.0.9',
	'target_post_name' 						=> 'markt',
	'target_post_id'						=> 49,
	'ad_max_active_in_days'					=> 30,
	'ad_reactivation_treshold_in_days'		=> 5,
	'ad_max_images'							=> 4,
	'webmaster_mail'						=> 'e.gopp@mccm-feldkirch.at'
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

	//TODO: make mail_approval_key unique
	
	$sql = "CREATE TABLE $sm_table_name (
		id INT NOT NULL AUTO_INCREMENT,
		first_name varchar(".$sm_mysql_column_length['first_name'].") DEFAULT '' NOT NULL,
		last_name varchar(".$sm_mysql_column_length['last_name'].") DEFAULT '' NOT NULL,
		mail varchar(".$sm_mysql_column_length['mail'].") DEFAULT '' NOT NULL,
		phone varchar(".$sm_mysql_column_length['phone'].") DEFAULT '' NOT NULL,	
		zip_code varchar(".$sm_mysql_column_length['zip_code'].") DEFAULT '' NOT NULL,
		city varchar(".$sm_mysql_column_length['city'].") DEFAULT '' NOT NULL,
		country varchar(".$sm_mysql_column_length['country'].") DEFAULT '' NOT NULL,
		ip varchar(15) DEFAULT '' NOT NULL,
		submit_date_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		keep_alive_date_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	  	text longtext NOT NULL,
		image_uuid char(".$sm_mysql_column_length['image_uuid'].") DEFAULT '' NOT NULL,
		mail_approve int(1) DEFAULT 0 NOT NULL,
	  	mail_approval_key varchar(".$sm_mysql_column_length['mail_approval_key'].") DEFAULT '' NOT NULL,
	  	webmaster_approve int(1) DEFAULT 0 NOT NULL,
	  	UNIQUE KEY id (id),
	  	UNIQUE KEY mail_approval_key_UNIQUE (mail_approval_key)
	) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

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
		
			
		//jQuery File Upload Plugin Files
// 		wp_enqueue_style('blueimp_bootstrap_min_css', "http://blueimp.github.com/cdn/css/bootstrap.min.css");
// 		wp_enqueue_style('blueimp_bootstrap_responsive_min_css', "http://blueimp.github.com/cdn/css/bootstrap-responsive.min.css");
// 		wp_enqueue_style('blueimp_bootstrap_image_gallery_min_css', "http://blueimp.github.com/Bootstrap-Image-Gallery/css/bootstrap-image-gallery.min.css");
//  	wp_enqueue_script('blueimp_tmp_min_js', 'http://blueimp.github.com/JavaScript-Templates/tmpl.min.js');
//  	wp_enqueue_script('blueimp_load_img_min_js', 'http://blueimp.github.com/JavaScript-Load-Image/load-image.min.js');
// 		wp_enqueue_script('blueimp_canvas_to_blob_min_js', 'http://blueimp.github.com/JavaScript-Canvas-to-Blob/canvas-to-blob.min.js');
		//serve blueimp files from own server
		wp_enqueue_script('blueimp_tmp_min_js', plugins_url('/js/tmpl.min.js', __FILE__), false, $sm_options['plugin_version']);
		wp_enqueue_script('blueimp_load_img_min_js', plugins_url('/js/load-image.min.js', __FILE__), false, $sm_options['plugin_version']);
		wp_enqueue_script('blueimp_canvas_to_blob_min_js', plugins_url('/js/canvas-to-blob.min.js', __FILE__), false, $sm_options['plugin_version']);
 		wp_enqueue_style('blueimp_bootstrap_min_css', plugins_url('/css/bootstrap.min.css', __FILE__), false, $sm_options['plugin_version']);
 		wp_enqueue_style('blueimp_bootstrap_responsive_min_css', plugins_url('css/bootstrap-responsive.min.css', __FILE__), false, $sm_options['plugin_version']);
 		wp_enqueue_style('blueimp_bootstrap_image_gallery_min_css', plugins_url('css/bootstrap-image-gallery.min.css', __FILE__), false, $sm_options['plugin_version']);
 				
 		
 		wp_enqueue_style('blueimp_jquery_fileupload_ui_css', plugins_url('/jquery-file-upload/css/jquery.fileupload-ui.css', __FILE__));
 		wp_enqueue_script('blueimp_jquery_ui_widget_js', plugins_url('/jquery-file-upload/js/vendor/jquery.ui.widget.js', __FILE__));
		wp_enqueue_script('blueimp_jquery_iframe_transport_js', plugins_url('/jquery-file-upload/js/jquery.iframe-transport.js', __FILE__), false, $sm_options['plugin_version']);
		wp_enqueue_script('blueimp_jquery_fileupload_js', plugins_url('/jquery-file-upload/js/jquery.fileupload.js', __FILE__), false, $sm_options['plugin_version']);
		wp_enqueue_script('blueimp_jquery_fileupload_fp_js', plugins_url('/jquery-file-upload/js/jquery.fileupload-fp.js', __FILE__), false, $sm_options['plugin_version']);
		wp_enqueue_script('blueimp_jquery_fileupload_ui_js', plugins_url('/jquery-file-upload/js/jquery.fileupload-ui.js', __FILE__), false, $sm_options['plugin_version']);
	} 
}

add_action('wp_enqueue_scripts', 'simple_market_add_scripts');

function get_the_market($content) {
	
	//should be allready loaded @see simple_market_add_scripts()
	global $sm_options;
	
	if($GLOBALS['post']->post_name == $sm_options['target_post_name']) {
		
		$_SESSION['market_item_to_submit'] = NULL;
		unset($_SESSION['market_item_to_submit']);
		
		$mail_action = $_GET['action'];
		$key = $_GET['mccm_activation_key'];
		if(isset($mail_action) === true && isset($key) === true) {
			require_once __DIR__ . '/mail_functions.php';
			switch($mail_action) {
				case 'mccm_activate_ad'		: $the_market = sm_mail_activate_ad($key); break;
				case 'mccm_reactivate_ad'	: $the_market = sm_mail_reactivate_ad($key); break;
				case 'mccm_deactivate_ad'	: $the_market = sm_mail_deactivate_ad($key); break;
				default						: $the_market = 'Action not known <br/><br/>';
			}
		} else {
			include_once __DIR__ . '/the_market.php';
		}
	}
	return $content . $the_market;
}
add_action('the_content', 'get_the_market');

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
	
	//UserInputPreprocessor::prepare_the_text($text);
	
	if(UserInputValidator::is_first_name_valid($first_name) === false) {
		$form_response->set_first_name_error(true);
	}
	if(UserInputValidator::is_last_name_valid($last_name) === false) {
		$form_response->set_last_name_error(true);
	}
	if(UserInputValidator::is_mail_valid($mail) === false) {
		$form_response->set_mail_error(true);
	}
	if(UserInputValidator::is_phone_valid($phone) === false) {
		$form_response->set_phone_error(true);
	}
	if(UserInputValidator::is_zip_code_valid($zip_code) === false) {
		$form_response->set_zip_code_error(true);
	}
	if(UserInputValidator::is_country_valid($country) === false) {
		$form_response->set_country_error(true);
	}
	if(UserInputValidator::is_text_valid($text) === false) {
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
	
	$market_item_renderer = new MarketItemRenderer($sm_submit_item);
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
	
	/*
	 * ------------------------------------------------------------------------------- send mail
	 */
	
	$headers[] = 'From: MCCM Feldkirch <markt@mccm-feldkirch.at>';
	$headers[] = 'MIME-Version: 1.0';
	$headers[] = 'Content-type: text/html; charset=UTF-8';
	
	if(!isset($sm_options))
		$sm_options = get_site_option('simple_market');
	
	$market_permalink = get_permalink($sm_options['target_post_id']);
	$market_permalink = rtrim($market_permalink, '/');
	
	$sm_activation_link = 	$market_permalink . '?action=mccm_activate_ad&mccm_activation_key='.$mail_approval_key;
	$sm_reactivation_link = $market_permalink . '?action=mccm_reactivate_ad&mccm_activation_key='.$mail_approval_key;
	$sm_deactivate_link =	$market_permalink . '?action=mccm_deactivate_ad&mccm_activation_key='.$mail_approval_key;
	
	
	$the_message =
	'<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
Hallo und Danke f&uuml;r Ihre Online-Anzeige auf dem <a href="'.$market_permalink.'". target="_blank">"MCCM Online Market"</a>.<br/><br/>
Sollte Sie keine Online-Anzeige bei uns aufgegeben haben, k&ouml;nnen Sie dieses Mail einfach l&ouml;schen.<br/><br/>
<span style="font-weight: bold; color: #990000;">Ansonsten behalten Sie dieses E-Mail bitte nach dem Klick auf den Aktivierungs-Link, bis Ihr Verkauf abgeschlossen ist!</span><br/><br/>
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
				'keep_alive_date_time'	=> $the_market_item_to_submit->get_keep_alive_date_time(), 
				'text'					=> $the_market_item_to_submit->get_text(),
				'image_uuid'			=> $the_market_item_to_submit->get_image_uuid(),
				'mail_approval_key' 	=> $mail_approval_key
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
	
	$upload_dir = $_SERVER['DOCUMENT_ROOT']."/wp-content/simple-market/tmp/";
	//just to handle malicious requests
	die_if_image_upload_count_reached($submit_id, $upload_dir);
	
	if(!isset($sm_options))
		$sm_options = get_site_option('simple_market');	
	
	require_once __DIR__ . '/UploadHandler.php';
		
	$options = array(
			'access_control_allow_methods' 	=> array('POST'),
			'max_number_of_files'			=> 1000,
			'max_width'						=> 1600,
			'max_height'					=> 1200,
			'thumbnail' => array(
					'max_width' => 120,
					'max_height' => 120
			),
			'script_url' 					=> admin_url('admin-ajax.php'),
			'upload_dir'					=> $upload_dir,
			'upload_url'					=> content_url("simple-market/tmp/"),
			'max_number_of_files'			=> 1000,						//complete directory setting
			'max_file_size'					=> 500000,
			'delete_type'					=> 'POST'
	);
	//max_file_size -> 1KB consider to be 1000 from Uploader (500 000 = 500KB)
	
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

function die_if_image_upload_count_reached($image_uuid, $tmp_upload_dir) {
	global $sm_options;
	
	if(!isset($sm_options))
		$sm_options = get_site_option('simple_market');
	
	$max_images = $sm_options['ad_max_images'];
	
	$tmp_images = scandir($tmp_upload_dir);
	
	$image_count = 0;
	$uuid_length = strlen($image_uuid);

	if(is_array($tmp_images)) {
		$image_to_compare = "";
		$uuid_sub_str = "";
		for($i = 0; $i < count($tmp_images); $i++) {
			$image_to_compare = $tmp_images[$i];
			$uuid_sub_str = substr($image_to_compare, 0, $uuid_length);
			if(strcmp($image_uuid,  $uuid_sub_str) == 0) {
				$image_count += 1;
				_log("Image $image_to_compare matches $image_uuid. Image Count: $image_count");
			} else {
				_log("Image $image_to_compare does not match $image_uuid ... substr is $uuid_sub_str");
			}
		}
		if($image_count > $max_images) {
			_log("Image count for $image_uuid reached, die!!! Currently $image_count images.");
			die();
		}
	}
	
}
?>