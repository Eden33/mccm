<?php

$the_guestbook_page_id = 793;
$the_market_page_id = 49;

function wp_head_event() {
	global $the_guestbook_page_id;
	global $the_market_page_id;
        global $_registration_ctr_enabled;
        global $_registration_start_date;
        $stylesheet_directory_uri = get_stylesheet_directory_uri();
        
        if( $_registration_ctr_enabled) {
?>
        <script type="text/javascript" src="<?= $stylesheet_directory_uri ?>/js/countdown.min.js"></script>
        <script type="text/javascript">
            var registrationStartDate = '<?= $_registration_start_date->format("Y/m/d H:i") ?> UTC';
            function register() {
                window.location.href = "<?= get_bloginfo('url') ?>/rennen/rennfahreranmeldung";
            }
        </script> 
        <script type="text/javascript" src="<?= $stylesheet_directory_uri ?>/js/countdown-init.js"></script>
<?php
        }
	if(  is_page('rennergebnisse') || is_page($the_guestbook_page_id) || is_page($the_market_page_id)) {
?>
	<script type="text/javascript" src="<?= $stylesheet_directory_uri ?>/js/jQuery-extends.js"></script>
<?php
	}
}

/*
 * Inject page specific javascript
 * http://azoomer.com/adding-javascript-to-a-thematic-child-theme/
 */
add_action('wp_head', 'wp_head_event');

/* REGISTER COUNTDOWN SECTION ------------------------------------------------------------------ */
// russmedia server setting is UTC
// to enable registration at UTC+1 (Austria) you have to set 23:00 as 
// hour and minute configuration
$_registration_start_date = new DateTime('2015-05-01 23:00');
$_registration_ctr_enabled = true;
$_registration_ip_whitelist = array(
    '77.101.135.76'
);

function head_menu_inject_registration_countdown($items) {
	return $items.'<li id="counter-li"></li>';
}

add_filter('wp_nav_menu_items', 'head_menu_inject_registration_countdown');

function is_registration_enabled() {
	global $_registration_start_date;
        global $_registration_ip_whitelist;
        
	$now = new DateTime("now");
	//if now is greater or equal startdate then registration is enabled
	if($_registration_start_date <= $now 
            || in_array($_SERVER['REMOTE_ADDR'], $_registration_ip_whitelist)) {
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
		if( is_registration_enabled() === false ) {
			return "Zur Zeit sind keine Rennanmeldungen m&ouml;glich.";
                }
	}
        if(is_page('rennfahreranmeldung')) {
            if(is_registration_enabled() === false) {
                $now = new DateTime('now');
                return "Rennfahreranmeldungen für ".$now->format('Y')." sind noch nicht m&ouml;glich.";
            }
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