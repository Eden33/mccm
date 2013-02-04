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
	'plugin_name' 	 => 'simple_market',
	'plugin_version' => '1.0.3',
	'target_post_name' => 'markt'
);
$sm_options = NULL;
$mysql_column_length = array (
	'first_name' 	=> 50,
	'last_name'  	=> 50,
	'zip_code'		=> 15,
	'city'			=> 30,
	'country'		=> 30,
	'mail'			=> 80,
	'phone'			=> 50
);

function on_plugin_activate() {
	global $wpdb;
	global $sm_table_name;
	global $sm_initialize_options;
	global $mysql_column_length;

	$sql = "CREATE TABLE $sm_table_name (
		id INT NOT NULL AUTO_INCREMENT,
		first_name varchar(".$mysql_column_length['first_name'].") DEFAULT '' NOT NULL,
		last_name varchar(".$mysql_column_length['last_name'].") DEFAULT '' NOT NULL,
		zip_code varchar(".$mysql_column_length['zip_code'].") DEFAULT '' NOT NULL,
		city varchar(".$mysql_column_length['city'].") DEFAULT '' NOT NULL,
		country varchar(".$mysql_column_length['country'].") DEFAULT '' NOT NULL,
		email varchar(".$mysql_column_length['mail'].") DEFAULT '' NOT NULL,
		phone varchar(".$mysql_column_length['phone'].") DEFAULT '' NOT NULL,
		time timestamp DEFAULT CURRENT_TIMESTAMP,
	  	text longtext NOT NULL,
	  	mail_approve int(1) DEFAULT 0 NOT NULL,
	  	mail_approval_key varchar(100) DEFAULT '' NOT NULL,
	  	webmaster_approve int(1) DEFAULT 0 NOT NULL,
	  	UNIQUE KEY id (id)
	)DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

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
		
		include_once __DIR__ . '/the_market.php';
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
	
	require_once __DIR__ . '/classes.php';
	$form_response = new SimpleMarketFormResponse();
	
	//first check nonce
	if(! wp_verify_nonce($_POST['sm_nonce'], 'sm_nonce'))
		die();
	$bla = "";
	
	if(isset($_POST['sm_submit_id']) && isset($_SESSION['sm_submit_id'])) {		
		if(strcmp($_POST['sm_submit_id'], $_SESSION['sm_submit_id']) != 0)
			die();
	} else {
		
		require_once __DIR__ . '/recaptcha-1.11/recaptchalib.php';
		
		$challange_field = $_POST['recaptcha_challenge_field'];
		$response_field = $_POST['recaptcha_response_field'];
		
		$resp = recaptcha_check_answer('6LdPfdwSAAAAAA_wdOwQLNf5ILdwXbAHL17C_s5g', $_SERVER['REMOTE_ADDR'], $challange_field, $response_field);
		if( $resp->is_valid !== true ) {
			$form_response->captcha_error = true;
			echo $form_response->get_json_response();
			exit();
		}
	}

	$sm_submit_item = new SimpleMarketItem($_POST['sm_first_name'], $_POST['sm_last_name'],
			$_POST['sm_zip_code'], $_POST['sm_city'], $_POST['sm_country'],
			$_POST['sm_text'], new DateTime('now'));
	
	//TODO: validation of submited user data goes here
	
	if(isset($_SESSION['sm_submit_id']) === false) {
		$_SESSION['sm_submit_id'] = uniqid(); //by default 13 chararcters
	}
	
	$form_response->preview_mode = true;
	$form_response->submit_id = $_SESSION['sm_submit_id'];
	$form_response->market_item = $sm_submit_item;
	
	echo $form_response->get_json_response();
	exit();	
	
}


?>