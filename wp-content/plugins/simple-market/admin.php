<?php
/*
Plugin Name: Simple Market
Plugin URI: http://www.mccm-feldkirch.at/
Description: Simple market plugin initially coded for Motocross Club Montfort.
Version: 1.0.0
Author: Eduard Gopp
Author URI: http://www.mccm-feldkirch.at/
*/
$sm_table_name = $wpdb->prefix . 'simple_market';
$sm_options = array(
	'plugin_name' 	 => 'simple_market',
	'plugin_version' => '1.0.0',
	'another_option' => 3
);

function on_plugin_activate() {
	global $wpdb;
	global $sm_table_name;
	global $sm_options;

	$sql = "CREATE TABLE $sm_table_name (
		id INT NOT NULL AUTO_INCREMENT,
		name varchar(50) DEFAULT '' NOT NULL,
		email varchar(50) DEFAULT '' NOT NULL,
		phone varchar(50) DEFAULT '' NOT NULL,
		time timestamp DEFAULT CURRENT_TIMESTAMP,
	  	message longtext NOT NULL,
	  	flag int(1) NOT NULL,
	  	UNIQUE KEY id (id)
	)DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql); //create or compute differences and alter
	update_option('simple_market', $sm_options);
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
	global $sm_options;
	$options = get_site_option('simple_market');
	if($options !== FALSE && $options['plugin_version'] != $sm_options['plugin_version']) {
		on_plugin_activate();
		update_option('simple_market', $sm_options);
	}
}
add_action('plugins_loaded', 'on_plugin_update');

function drop_table_and_clean_opt_version() {
	global $wpdb;
	global $sm_table_name;
	$wpdb->query("DROP TABLE IF EXISTS $sm_table_name");
	delete_option('simple_market');
}
?>