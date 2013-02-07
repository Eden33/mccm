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
	'plugin_version' 						=> '1.0.8',
	'target_post_name' 						=> 'markt',
	'target_post_id'						=> 49,
	'ad_max_active_in_days'					=> 30,
	'ad_reactivation_treshold_in_days'		=> 5,
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
		date_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
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
															'nonce' => wp_create_nonce('sm_nonce')));
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
	
	$sm_submit_item = new SimpleMarketItem(NULL, $first_name, $last_name, $mail, $phone, $zip_code, $city, $country,
			$text, current_time('mysql', 1), $unique_submit_id);
	
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
	
	//a few authorization checks
	
	$the_market_item_to_submit = $_SESSION['market_item_to_submit'];
	if(isset($the_market_item_to_submit) === false)
		die();
	
	$sm_submit_id = $the_market_item_to_submit->get_image_uuid(); 
	if(isset($sm_submit_id) === false)
		die();

	$posted_submit_id = $_POST['sm_submit_id'];
	if(isset($posted_submit_id) === false)
		die();

	if(strcmp($posted_submit_id, $sm_submit_id) != 0)
		die();
	
	//authorization end

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
				'first_name' 		=> $the_market_item_to_submit->get_first_name(),
				'last_name'	 		=> $the_market_item_to_submit->get_last_name(),
				'mail'				=> $the_market_item_to_submit->get_mail(),
				'phone'				=> $the_market_item_to_submit->get_phone(),
				'zip_code'			=> $the_market_item_to_submit->get_zip_code(),
				'city'				=> $the_market_item_to_submit->get_city(),
				'country'			=> $the_market_item_to_submit->get_country(),
				'ip'				=> $_SERVER['REMOTE_ADDR'],
				'date_time'			=> $the_market_item_to_submit->get_date_time(),
				'text'				=> $the_market_item_to_submit->get_text(),
				'image_uuid'		=> $the_market_item_to_submit->get_image_uuid(),
				'mail_approval_key' => $mail_approval_key
			)) === false) {
		echo json_encode(array('success' => false, 'error' => '1'));		
		exit();
	}
	
	echo json_encode(array('success' => true));
	die();
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

?>