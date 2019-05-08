<?php

/*
 * WordPress Actions and Filters.
 * See the Plugin API in the Codex:
 * http://codex.wordpress.org/Plugin_API
 */


// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * Add a menu in the WordPress backend.
 * Load JavaSCript and CSS for Admin.
 */
function gwolle_gb_adminmenu() {
	/*
	 * How to add new menu-entries:
	 * add_menu_page( $page_title, $menu_title, $access_level, $file, $function = '', $icon_url = '' )
	 */

	// Counter
	$count_unchecked = get_transient( 'gwolle_gb_menu_counter' );
	if ( false === $count_unchecked ) {
		$count_unchecked = (int) gwolle_gb_get_entry_count(
			array(
				'checked' => 'unchecked',
				'trash'   => 'notrash',
				'spam'    => 'nospam'
			)
		);
		set_transient( 'gwolle_gb_menu_counter', $count_unchecked, DAY_IN_SECONDS );
	}

	// Main navigation entry
	// Admin page: admin/welcome.php
	$menu_text = esc_html__('Guestbook', 'gwolle-gb') . '<span class="awaiting-mod count-' . $count_unchecked . '"><span>' . $count_unchecked . '</span></span>';
	add_menu_page(
		esc_html__('Guestbook', 'gwolle-gb'), /* translators: Menu entry */
		$menu_text,
		'moderate_comments',
		GWOLLE_GB_FOLDER . '/gwolle-gb.php',
		'gwolle_gb_welcome',
		'dashicons-testimonial'
	);

	// Admin page: admin/entries.php
	$menu_text = esc_html__('Entries', 'gwolle-gb') . '<span class="awaiting-mod count-' . $count_unchecked . '"><span>' . $count_unchecked . '</span></span>';
	add_submenu_page(
		GWOLLE_GB_FOLDER . '/gwolle-gb.php',
		esc_html__('Entries', 'gwolle-gb'), /* translators: Menu entry */
		$menu_text,
		'moderate_comments',
		GWOLLE_GB_FOLDER . '/entries.php',
		'gwolle_gb_page_entries'
	);

	// Admin page: admin/editor.php
	add_submenu_page( GWOLLE_GB_FOLDER . '/gwolle-gb.php', esc_html__('Entry editor', 'gwolle-gb'), /* translators: Menu entry */ esc_html__('Add/Edit entry', 'gwolle-gb'), 'moderate_comments', GWOLLE_GB_FOLDER . '/editor.php', 'gwolle_gb_page_editor' );

	// Admin page: admin/settings.php
	add_submenu_page( GWOLLE_GB_FOLDER . '/gwolle-gb.php', esc_html__('Settings', 'gwolle-gb'), /* translators: Menu entry */ esc_html__('Settings', 'gwolle-gb'), 'manage_options', GWOLLE_GB_FOLDER . '/settings.php', 'gwolle_gb_page_settings' );

	// Admin page: admin/import.php
	add_submenu_page( GWOLLE_GB_FOLDER . '/gwolle-gb.php', esc_html__('Import', 'gwolle-gb'), /* translators: Menu entry */ esc_html__('Import', 'gwolle-gb'), 'manage_options', GWOLLE_GB_FOLDER . '/import.php', 'gwolle_gb_page_import' );

	// Admin page: admin/export.php
	add_submenu_page( GWOLLE_GB_FOLDER . '/gwolle-gb.php', esc_html__('Export', 'gwolle-gb'), /* translators: Menu entry */ esc_html__('Export', 'gwolle-gb'), 'manage_options', GWOLLE_GB_FOLDER . '/export.php', 'gwolle_gb_page_export' );
}
add_action('admin_menu', 'gwolle_gb_adminmenu');


/*
 * Load CSS for admin.
 */
function gwolle_gb_admin_enqueue_style() {
	wp_enqueue_style( 'gwolle-gb-admin-css', GWOLLE_GB_URL . 'admin/css/gwolle-gb-admin.css', false, GWOLLE_GB_VER, 'all' );
}
add_action( 'admin_enqueue_scripts', 'gwolle_gb_admin_enqueue_style' );


/*
 * Load JavaScript for admin.
 * It's called directly on the adminpages, it's not being used as a hook.
 */
function gwolle_gb_admin_enqueue() {
	wp_enqueue_script( 'gwolle-gb-admin-js', GWOLLE_GB_URL . 'admin/js/gwolle-gb-admin.js', 'jquery', GWOLLE_GB_VER, true );
}
//add_action( 'admin_enqueue_scripts', 'gwolle_gb_admin_enqueue' );


/*
 * Add Settings link to the main plugin page
 */
function gwolle_gb_links( $links, $file ) {
	if ( $file == plugin_basename( dirname(__FILE__).'/gwolle-gb.php' ) ) {
		$links[] = '<a href="' . admin_url( 'admin.php?page=gwolle-gb/settings.php' ) . '">'.__( 'Settings', 'gwolle-gb' ).'</a>';
	}
	return $links;
}
add_filter( 'plugin_action_links', 'gwolle_gb_links', 10, 2 );


/*
 * Support uninstall for MultiSite through a filter.
 * Take Note: This will do an uninstall on all sites.
 * Only run on admin_init, no need for the frontend.
 *
 * @since 2.1.0
 */
function gwolle_gb_multisite_uninstall() {
	global $wpdb;

	if ( is_admin() ) {
		if ( function_exists('is_multisite') && is_multisite() ) {
			$do_uninstall = apply_filters( 'gwolle_gb_multisite_uninstall', false );
			if ( $do_uninstall ) {
				$blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					gwolle_gb_uninstall();
					restore_current_blog();
				}
				// Avoid database errors and PHP notices, don't run these actions anymore.
				remove_action( 'admin_menu', 'gwolle_gb_adminmenu', 10 );
				remove_action( 'wp_dashboard_setup', 'gwolle_gb_dashboard_setup', 10 );
				remove_action( 'admin_bar_menu', 'gwolle_gb_admin_bar_menu', 61 );
			}
		}
	}
}
add_action('admin_init', 'gwolle_gb_multisite_uninstall', 99);
