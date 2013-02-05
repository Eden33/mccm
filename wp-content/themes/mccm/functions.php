<?php

$the_guestbook_page_id = 793;
$the_market_page_id = 49;

function wp_head_event() {
	global $the_guestbook_page_id;
	global $the_market_page_id;
	
	if(  is_page('rennergebnisse') || is_page($the_guestbook_page_id) || is_page($the_market_page_id)) {
?>
	<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/js/jQuery-extends.js"></script>
<?php
	}
}

/*
 * Inject page specific javascript
 * http://azoomer.com/adding-javascript-to-a-thematic-child-theme/
 */
add_action('wp_head', 'wp_head_event');

/* REGISTER COUNTDOWN SECTION ------------------------------------------------------------------ */
$_registration_start_date = new DateTime('2013-05-09 00:00');
$_registration_ctr_enabled = false;

function get_registration_counter() {
	global $_registration_ctr_enabled;
	global $_registration_start_date;
	if( $_registration_ctr_enabled === true ) {
		$height = 35;
		$width = 250;
		return '<script type="text/javascript" src="'.get_stylesheet_directory_uri().'/js/swfobject.js"></script>
		<script type="text/javascript">
		
				var flashvars = {
					registrationStartDate: "'.$_registration_start_date->format("Y/m/d H:i").'"
				};
				
	            // For version detection, set to min. required Flash Player version, or 0 (or 0.0.0), for no version detection. 
	            var swfVersionStr = "9.0.124";
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
	                "'.get_stylesheet_directory_uri().'/flash/mccm_countdown.swf", "reg-counter-span", 
	                "'.$width.'", "'.$height.'", 
	                swfVersionStr, xiSwfUrlStr, 
	                flashvars, params, attributes);
	            // JavaScript enabled so display the flashContent div in case it is not replaced with a swf object.
	            // swfobject.createCSS("#reg-counter-div", "position: absolute;right: 2%; top: 290px;");
	            
	            function register() {
		        	window.location.href = "'.get_bloginfo('url').'/rennen/rennfahreranmeldung";
				}
	        </script>
	        <div style="min-width:'.$width.'px ; min-height='.$height.'px;">
	         	<span id="reg-counter-span"></span>	
	        </div>';
	}
}

function head_menu_inject_registration_countdown($items) {
	return $items.'<li>'.get_registration_counter().'</li>';
}

add_filter('wp_nav_menu_items', 'head_menu_inject_registration_countdown');

function is_registration_enabled() {
	global $_registration_ctr_enabled;
	global $_registration_start_date;
	$now = new DateTime("now");
	//if now is greater or equal startdate then registration is enabled
	if( $_registration_start_date <= $now 
			|| $_SERVER['REMOTE_ADDR'] == '194.208.180.31'
			|| $_SERVER['REMOTE_ADDR'] == '194.208.180.31') {
		return true;
	}
	return false;
}

/* REGISTER COUNTDOWN SECTION END -------------------------------------------------------*/


/* Filter SECTION ------------------------------------------------------------------ */

add_filter('the_title', 'filter_the_title', 10, 2);

function filter_the_title ( $title, $id ) {
	global $the_guestbook_page_id;
	global $the_market_page_id;
	if( $id == $the_guestbook_page_id ) { //guestbook, add quick "Leave entry link" to title (scroll down to formular)
		$title = $title.'&nbsp;&nbsp;<a class="mccm-coloring" style="font-size:13px;" onClick="jQuery(function ($) { $(\'a[name=guestbookform]\').scrollTo(2000); }); return false;" href="#guestbookform">Eintrag hinterlassen</a>';
	} else if( $id == $the_market_page_id) {
		$title = $title.'&nbsp;&nbsp;<a id="sm-head-anchor" class="mccm-coloring" style="font-size:13px;" onClick="jQuery(function ($) { $(\'#sm-form-a\').scrollTo(2000); }); return false;" href="#simplemarketform">Eigenes Inserat aufgeben</a>';
	}
	return $title;
}

add_filter('the_content', 'filter_the_content');

function filter_the_content( $content ) {
	if(is_page( 'omc-oldtimer-online-anmeldung' ) 
	|| is_page( 'clubsport-online-anmeldung' )
	|| is_page( 'sam-quad-online-anmeldung' )
	|| is_page( 'inter-sam-online-anmeldung' )
	|| is_page( 'sjmcc-meisterschaftslauf-online-anmeldung' ) ) {
		if( is_registration_enabled() === false )
			return "Zur Zeit sind keine Rennanmeldungen m&ouml;glich.";
	}
	return $content;
}

/* Filter SECTION END------------------------------------------------------------------ */

if(false) {
	ob_start();
	phpinfo();
	$info = ob_get_contents();
	ob_end_clean();
	 
	$fp = fopen("phpinfo.txt", "w+");
	fwrite($fp, $info);
	fclose($fp);
}
?>