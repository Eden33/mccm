<?php
/*
 * These strings are not used for the main plugin, but for the Commercial Add-On at:
 * http://www.mojomarketplace.com/item/gwolle-gb-add-on
 */

function gwolle_gb_addon_translation_strings() {

	// description of readme
	/* translators: Commercial Add-On description */
	esc_html_e('Gwolle Guestbook: The Add-On is the add-on for Gwolle Guestbook that gives extra functionality for your guestbook. Meta Fields, Star ratings, Social Sharing and much more.', 'gwolle-gb');

	// function gwolle_gb_addon_page_settings() {
	/* translators: Commercial Add-On */
	esc_html_e('Add-On Settings', 'gwolle-gb');

	/* translators: Commercial Add-On: Settings page tab */
	esc_html_e('Form Fields', 'gwolle-gb');
	/* translators: Commercial Add-On: Settings page tab */
	esc_html_e('Reading Fields', 'gwolle-gb');
	/* translators: Commercial Add-On: Settings page tab */
	esc_html_e('Social Media', 'gwolle-gb');
	/* translators: Commercial Add-On: Settings page tab */
	esc_html_e('Star Rating', 'gwolle-gb');
	/* translators: Commercial Add-On: Settings page tab (typo in 1.0.4) */
	esc_html_e('Miscellanious', 'gwolle-gb');
	/* translators: Commercial Add-On: Settings page tab */
	esc_html_e('Miscellaneous', 'gwolle-gb');
	/* translators: Commercial Add-On: Settings page tab */
	esc_html_e('Strings', 'gwolle-gb');
	/* translators: Commercial Add-On: Settings page tab */
	esc_html_e('Ideas?', 'gwolle-gb');

	// function gwolle_gb_entry_metabox_lines_admin_reply( $gb_metabox, $entry ) {
	/* translators: Commercial Add-On */
	esc_attr__('Add admin reply', 'gwolle-gb');

	// function gwolle_gb_admin_reply_javascript() {

	// function gwolle_gb_entry_metabox_lines_delete_link( $gb_metabox, $entry ) {
	/* translators: Commercial Add-On */
	esc_attr__('Delete entry', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html__('Delete', 'gwolle-gb');

	// function gwolle_gb_entry_metabox_lines_social_media( $gb_metabox, $entry ) {
	/* translators: Commercial Add-On: Post it on Social Media */
	esc_attr__('Post on', 'gwolle-gb');

	// function gwolle_gb_entry_metabox_lines_email( $gb_metabox, $entry ) {
	/* translators: Commercial Add-On metabox line */
	esc_html__('Email author', 'gwolle-gb');

	// function gwolle_gb_addon_form_starrating( $output ) {
	/* translators: Commercial Add-On */
	esc_html__('Rating', 'gwolle-gb');

	// class GwolleGB_Widget_Av_Rating extends WP_Widget {
	/* translators: Commercial Add-On Widget */
	esc_html__('Displays the average star rating of a guestbook.','gwolle-gb');
	/* translators: Commercial Add-On Widget */
	esc_html__('Gwolle GB: Average Star Rating', 'gwolle-gb');
	/* translators: Commercial Add-On Widget */
	esc_html__('Average Star Rating', 'gwolle-gb');

	// function gwolle_gb_addon_deps_admin_notice() {
	/* translators: Commercial Add-On. %s is a link. */
	esc_html__( 'Gwolle Guestbook: The Add-On requires Gwolle Guestbook. Go to your %sPlugins%s page to install or activate Gwolle Guestbook.', 'gwolle-gb' );
	/* translators: Commercial Add-On. %s is a version number. */
	esc_html__( 'Gwolle Guestbook: The Add-On requires Gwolle Guestbook version %s. You have version %s. Go to your %sPlugins%s page to update Gwolle Guestbook.', 'gwolle-gb' );

	// function gwolle_gb_addon_page_settingstab_empty() {
	/* translators: Commercial Add-On. %s is a link. */
	esc_html__( 'Please place them on the %ssupport forum%s. I will see what I can do.', 'gwolle-gb' );

	// function gwolle_gb_addon_page_settingstab_form() {
	/* translators: Commercial Add-On */
	esc_html_e('Configure the extra fields that you want.', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('The slug of the field is where your data is attached to. Only change the slug if you know what you are doing.', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('The name of the field is what you will see in the label and placeholder in the form.', 'gwolle-gb');
	/* translators: Commercial Add-On. At the top of the form. */
	esc_html_e('Top', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('Slug:', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('Name:', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('+ Add new field.', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html__('Delete this meta field?', 'gwolle-gb' );
	/* translators: Commercial Add-On */
	esc_html__('Delete this string row?', 'gwolle-gb' );


	// function gwolle_gb_addon_page_settingstab_misc() {
	/* translators: Settings page, option for permalink */
	esc_html_e('Permalink', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('Show permalink in Metabox.', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('A link to the single entry will be added to the metabox.', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('Author Email', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('Show author email in Metabox.', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('The email address of the author will be added to the metabox.', 'gwolle-gb');
	/* translators: Settings page, option for delete link */
	esc_html_e('Delete link', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('Show delete link in Metabox.', 'gwolle-gb'); // deprecated since 1.0.3
	/* translators: Commercial Add-On */
	esc_html_e('Show delete link in Metabox for moderators.', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('Show delete link in Metabox for author.', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('A link to delete the entry will be added to the metabox. Only visible for moderators and the author.', 'gwolle-gb');

	// function gwolle_gb_addon_page_settingstab_reading() {
	/* translators: Commercial Add-On */
	esc_html_e('Configure where you want the extra fields displayed.', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('Above content.','gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('Under content.','gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('In metabox.','gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('None.','gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('There are no Meta Fields saved yet. Please go to the Form tab, enter a field and save it.','gwolle-gb');

	// function gwolle_gb_addon_page_settingstab_social() {
	/* translators: Commercial Add-On */
	esc_html_e('Share on Social Media', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('Show share icons for Social Media in the metabox. Below you can select which ones and their order.', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('Sharing Services', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('Select the Social Media services you want enabled for sharing.', 'gwolle-gb');
	/* translators: Commercial Add-On. Location of the display. */
	esc_html_e('Location for display', 'gwolle-gb');

	// function gwolle_gb_addon_page_settingstab_starrating() {
	/* translators: Commercial Add-On */
	esc_html_e('Use star rating so visitors can give a star rating for your website or post.', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('Show Average', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('Show Average Star Rating', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('The average will be shown above the list of entries.', 'gwolle-gb');

	// function gwolle_gb_addon_page_settingstab_strings() {
	/* translators: Commercial Add-On */
	esc_html_e('String Replacement', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('Here you can replace text strings throughout the frontend form, the list of entries, and the messages that get displayed for the form.', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('Old String', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('Example: Guestbook', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('New String', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('Example: Review', 'gwolle-gb');
	/* translators: Commercial Add-On */
	esc_html_e('+ Add new string.', 'gwolle-gb');

	// function gwolle_gb_addon_starrating_average_html()
	/* translators: Commercial Add-On. %s is the value/number of votes. */
	__( 'Average Rating: <strong>%s out of %s</strong> (%s votes)', 'gwolle-gb' );

}