<?php

function wp_head_event()
{
    global $_registration_ctr_enabled;
    global $_registration_start_date;
    $stylesheet_directory_uri = get_stylesheet_directory_uri();
    
    if( $_registration_ctr_enabled)
    {
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
	if(  is_page('rennergebnisse')) 
    {
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
// russmedia server setting is UTC+0 
// hour and minute configuration
$_registration_start_date = new DateTime('2022-04-20 00:00');
$_registration_ctr_enabled = false;
$_registration_ip_whitelist = array(
);

function head_menu_inject_registration_countdown($items) 
{
	return $items.'<li id="counter-li"></li>';
}

add_filter('wp_nav_menu_items', 'head_menu_inject_registration_countdown');

function is_registration_enabled() 
{
	global $_registration_start_date;
    global $_registration_ip_whitelist;

	$now = new DateTime("now");

	//if now is greater or equal startdate then registration is enabled
	if($_registration_start_date <= $now 
        || in_array($_SERVER['REMOTE_ADDR'], $_registration_ip_whitelist)) 
    {
		return true;
	}
	return false;
}

/* REGISTER COUNTDOWN SECTION END -------------------------------------------------------*/

/* Filter SECTION ------------------------------------------------------------------ */

add_filter('the_content', 'filter_the_content');

function filter_the_content( $content ) 
{
    $registration_prohibited_msg = 'Zur Zeit sind keine Rennanmeldungen m&ouml;glich.';
    
	if((is_page( 'clubsport-online-anmeldung' )
	|| is_page( 'sjmcc-online-anmeldung')
	|| is_page( 'oldtimer-seitenwagen-online-anmeldung' )
	|| is_page( 'oldtimer-solo-online-anmeldung' ))
	&& is_registration_enabled() === false) 
    {
        return $registration_prohibited_msg;
	}
	
	// not available in 2020
	if(is_page( 'inter-sam-online-anmeldung' )
	    || is_page( 'sam-masters-online-anmeldung')
	    || is_page( 'sam-junioren-open-online-anmeldung' ))
	{
	    return $registration_prohibited_msg;
	}

	if(is_page('rennfahreranmeldung') && is_registration_enabled() === false) 
    {
        return "Alle M&ouml;glichkeiten zur Rennfahreranmeldungen sind derzeit deaktiviert.";

    }
    
	return $content;
}

add_filter('do_shortcode_tag', 'filter_guestbook_shortcode', 10, 2);

function filter_guestbook_shortcode( $content, $tag )
{
    if( $tag === 'gwolle_gb' )
    {        
        $content = preg_replace('/Datenschutzerklärung/', '<a href="'
                    .esc_url(get_permalink(get_page_by_title('Datenschutzerklärung'))).'"' 
                    . ' target="_blank">Datenschutzerklärung</a>', $content);      
    }
    
    return $content;
}

add_filter('pre_get_posts', 'filter_home');

function filter_home( $query )
{
    // exclude all categories: "Rennberichte"
    if ( $query->is_home() && $query->is_main_query() ) 
    {
        $query->set( 'cat', array('-5', '-6', '-7'));
        
    }
}

/* Filter SECTION END------------------------------------------------------------------ */

if(false) 
{
    ob_start();
    phpinfo();
    $info = ob_get_contents();
    ob_end_clean();
    
    $fp = fopen("phpinfo.html", "w+");
    fwrite($fp, $info);
    fclose($fp);
}
?>