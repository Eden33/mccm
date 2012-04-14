<?php
function wp_head_event() {
	if(  is_page('rennergebnisse') ) {
?>
	<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/js/page-rennergebnisse.js"></script>
<?php 
	}
}

/*
 * Inject page specific javascript
 * http://azoomer.com/adding-javascript-to-a-thematic-child-theme/
 */
add_action('wp_head', 'wp_head_event');

/* REGISTER COUNTDOWN SECTION ------------------------------------------------------------------ */
$_registration_start_date = new DateTime('2012-04-14 19:00');
$_registration_ctr_enabled = true;

function get_registration_counter() {
	global $_registration_ctr_enabled;
	global $_registration_start_date;
	if( $_registration_ctr_enabled === true ) {
		echo '<script type="text/javascript" src="'.get_stylesheet_directory_uri().'/js/swfobject.js"></script>
		<script type="text/javascript">
		
				var flashvars = {
					registrationStartDate: "'.$_registration_start_date->format("Y/m/d H:i").'"
				};
				
	            // For version detection, set to min. required Flash Player version, or 0 (or 0.0.0), for no version detection. 
	            var swfVersionStr = "9.0.0";
	            // To use express install, set to playerProductInstall.swf, otherwise the empty string. 
	            //var xiSwfUrlStr = "playerProductInstall.swf";
	            var xiSwfUrlStr = "";
	            var params = {};
	            params.quality = "high";
	            params.bgcolor = "#00FF00";
	            params.allowscriptaccess = "always";
	            params.allowfullscreen = "true";
	            params.wmode = "transparent";
	            var attributes = {};
	            attributes.id = "mccm_countdown";
	            attributes.name = "mccm_countdown";
	            attributes.align = "middle";
	            swfobject.embedSWF(
	                "'.get_stylesheet_directory_uri().'/flash/mccm_countdown.swf", "reg-counter-div", 
	                "280", "43", 
	                swfVersionStr, xiSwfUrlStr, 
	                flashvars, params, attributes);
	            // JavaScript enabled so display the flashContent div in case it is not replaced with a swf object.
	            swfobject.createCSS("#reg-counter-div", "position: absolute;right: 2%; top: 290px;");
	            
	            function register() {
		        	window.location.href = "'.get_bloginfo('url').'/rennen/rennfahreranmeldung";
				}
	        </script>
	        <div id="reg-counter-div"></div>';	
	}
}

function is_registration_enabled() {
	global $_registration_ctr_enabled;
	global $_registration_start_date;
	$now = new DateTime("now");
	//if now is greater or equal startdate then registration is enabled
	if( $_registration_start_date <= $now ) {
		return true;
	}
	return false;
}

function filter_for_content_of_online_registration_pages( $content ) {
	if(is_page( 'omc-oldtimer-online-anmeldung' ) 
	|| is_page( 'clubsport-online-anmeldung' )
	|| is_page( 'sam-quad-online-anmeldung' )
	|| is_page( 'inter-sam-online-anmeldung' )
	|| is_page( 'sjmcc-meisterschaftslauf-online-anmeldung' ) ) {
		if( is_registration_enabled() === false )
			return "Online Rennanmeldungen sind ab dem 9 Mai m&ouml;glich.";
	}
	return $content;
}

add_filter('the_content', 'filter_for_content_of_online_registration_pages');

/* REGISTER COUNTDOWN SECTION END -------------------------------------------------------*/
?>