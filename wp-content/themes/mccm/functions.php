<?php
function wp_head_event() {
	if(  is_page('rennergebnisse') ) {
?>
	<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/js/page-rennergebnisse.js"></script>
<?php 
	}
}
//http://azoomer.com/adding-javascript-to-a-thematic-child-theme/
/*
 * Inject page specific javascript
 * 
 */
add_action('wp_head', 'wp_head_event');

?>