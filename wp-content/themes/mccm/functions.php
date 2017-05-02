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
?>
        <script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

            ga('create', 'UA-53925452-1', 'auto');
            ga('set', 'forceSSL', true);
            ga('set', 'anonymizeIp', true);
            ga('send', 'pageview');
        </script>
<?php
}

/*
 * Inject page specific javascript
 * http://azoomer.com/adding-javascript-to-a-thematic-child-theme/
 */

add_action('wp_head', 'wp_head_event');

/* REGISTER COUNTDOWN SECTION ------------------------------------------------------------------ */
// russmedia server setting is UTC+0 
// hour and minute configuration
$_registration_start_date = new DateTime('2020-09-01 22:00');
$_registration_ctr_enabled = false;
$_registration_ip_whitelist = array(
    /*
    '81.189.26.58',
    '188.21.52.126'
     */
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
	if(is_page( 'omc-oldtimer-online-anmeldung' ) 
	|| is_page( 'clubsport-online-anmeldung' )
	|| is_page( 'inter-sam-online-anmeldung' )
        || is_page( 'sam-masters-online-anmeldung')
	|| is_page( 'sam-junioren-open-online-anmeldung' ) ) 
        {
		if( is_registration_enabled() === false ) 
                {
                    return "Zur Zeit sind keine Rennanmeldungen m&ouml;glich.";
                }
	}

        if(is_page('rennfahreranmeldung')) 
        {
            if(is_registration_enabled() === false) 
            {
                $now = new DateTime('now');
                return "Alle M&ouml;glichkeiten zur Rennfahreranmeldungen sind derzeit deaktiviert.";
            }

        }

        if(is_page('mitglieder')) {
            require_once dirname(__FILE__).'/util/MemberPageUtil.php';
            $memberPageUtil = new MemberPageUtil($content);
            $content = $memberPageUtil->getMemberSummaryMarkup();
            $content .= $memberPageUtil->getMemberListMarkup();
        }

	return $content;
}



/* Filter SECTION END------------------------------------------------------------------ */

if(false) 
{
    ob_start();
    phpinfo();
    $info = ob_get_contents();
    ob_end_clean();
    
    $fp = fopen("phpinfo.txt", "w+");
    fwrite($fp, $info);
    fclose($fp);
}
?>