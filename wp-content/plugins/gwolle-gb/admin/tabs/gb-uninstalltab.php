<?php
/*
 * Settings page for the guestbook
 */

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}

/*
 * $uninstalled is a bool, if the install has been done already. In that case, show messages.
 */

function gwolle_gb_page_settingstab_uninstall( $uninstalled ) {

	if ( function_exists('current_user_can') && ! current_user_can('manage_options') ) {
		die(__('Cheatin&#8217; uh?', 'gwolle-gb'));
	}
	if ( function_exists('is_multisite') && is_multisite() ) {
		return;
	} ?>

	<input type="hidden" id="gwolle_gb_tab" name="gwolle_gb_tab" value="gwolle_gb_uninstall" />
	<?php
	settings_fields( 'gwolle_gb_options' );
	do_settings_sections( 'gwolle_gb_options' );

	/* Nonce */
	$nonce = wp_create_nonce( 'gwolle_gb_page_settings_uninstalltab' );
	echo '<input type="hidden" id="gwolle_gb_page_settings_uninstalltab" name="gwolle_gb_page_settings_uninstalltab" value="' . $nonce . '" />';
	?>
	<table class="form-table">
		<tbody>

		<?php
		if ( $uninstalled == true ) { ?>
			<tr valign="top">
				<th scope="row"><?php _e('Message', 'gwolle-gb'); ?></th>
				<td>
					<div id="message" class="updated error fade">
						<p><?php _e('The entries and settings have been removed.', 'gwolle-gb'); ?></p>
						<p><?php _e('The plugin is deactivated.', 'gwolle-gb'); ?></p>
						<p><?php echo __('You can now go to your', 'gwolle-gb') . ' <a href="' . admin_url( '/index.php' ) . '">' . __('Dashboard', 'gwolle-gb') . '</a>. ' . __('(We will go there in 10 seconds)', 'gwolle-gb') ; ?>
					</div>

					<?php // Redirect to main admin page after 10 seconds. wp_redirect() does not work, headers were already sent. ?>
					<script>
						 setTimeout(
							function() {
								window.location.href = '<?php echo admin_url( '/index.php' ); ?>'
							}, 10000 );
					</script>
				</td>
			</tr>
			<?php
		}
		?>

		<tr valign="top">
			<th scope="row" style="color:#FF0000;"><label for="blogdescription"><?php _e('Uninstall', 'gwolle-gb'); ?></label></th>
			<td>
				<?php _e('Uninstalling means that all database entries are removed (settings and entries).', 'gwolle-gb');
				echo '<br />';
				_e('This can <strong>not</strong> be undone.', 'gwolle-gb');
				?>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" style="color:#FF0000;"><label for="gwolle_gb_uninstall_confirmed"><?php _e('Confirm', 'gwolle-gb'); ?></label></th>
			<td>
				<input type="checkbox" name="gwolle_gb_uninstall_confirmed" id="gwolle_gb_uninstall_confirmed">
				<label for="gwolle_gb_uninstall_confirmed"><?php _e("Yes, I'm absolutely sure of this. Proceed!", 'gwolle-gb'); ?></label>
			</td>
		</tr>

		<tr>
			<th colspan="2">
				<p class="submit">
					<input type="submit" name="gwolle_gb_uninstall" id="gwolle_gb_uninstall" class="button" disabled value="<?php esc_attr_e('Uninstall &raquo;', 'gwolle-gb'); ?>" />
				</p>
			</th>
		</tr>

		</tbody>
	</table>

	<?php
}
