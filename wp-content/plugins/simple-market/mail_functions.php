<?php
function sm_mail_activate_ad($key) {
	if(sm_check_key($key) === true) {
		global $wpdb;
		global $sm_table_name;
		
		$prepared_stmt = $wpdb->prepare("SELECT * FROM $sm_table_name WHERE mail_approval_key = %s", $key);
		
		$row = $wpdb->get_row($prepared_stmt, ARRAY_A);
		$sm_item = new SimpleMarketItem($row);
		
		if($sm_item->is_approved_by_mail() === false) {
				
			$affected = $wpdb->update($sm_table_name, 
					array('mail_approve' => 1), 
					array('mail_approval_key' => $key),
					array('%d'));
			
			if($affected === 1) {
				
				//TODO: move images from tmp folder to primary
				
				$msg_ad_activated = "Ihr Inserat wurde aktiviert.<br/>";
				if($sm_item->is_approved_by_webmaster() === false) {
					$msg_ad_activated .= "Eines unserer Mitglieder wird Ihr Inserat nach einem kurzen Review freischalten."
										."<br/>Wir bitten um ein wenig Geduld da dies alles auf ehrenamtlicher Basis geschieht.";
				}
				return $msg_ad_activated;
			}
			
		} else {
			return "Dieses Inserat wurde bereits aktiviert.";
		}
	}
	return get_sm_mail_ups_message();
}
function sm_mail_reactivate_ad($key) {
	
	die('Check sm_mail_reactivate_ad() function ....');
	
	if(sm_check_key($key) === true) {
		global $wpdb;
		global $sm_table_name;
		global $sm_options;
		
		$prepared_stmt = $wpdb->prepare("SELECT * FROM $sm_table_name WHERE mail_approval_key = %s", $key);
		
		$row = $wpdb->get_row($prepared_stmt, ARRAY_A);
		$sm_item = new SimpleMarketItem($row);

		if(!isset($sm_options))
			$sm_options = get_site_option('simple_market');
			
		//both are considered to be days
		$ad_max_active_in_days = $sm_options['ad_max_active_in_days'];
		$reactivation_threshold_in_days = $sm_options['ad_reactivation_treshold_in_days'];
		
		if($sm_item->is_approved_by_mail() === true && $sm_item->is_approved_by_webmaster()) {
			$current_ad_keep_alive_date_gmt = $sm_item->get_keep_alive_date_time();
			$now_gmt = current_time('mysql', 1);

			//protect future users
			if($ad_max_active_in_days < $reactivation_threshold_in_days)
				$reactivation_threshold_in_days = $ad_max_active_in_days;
			
			$reactivation_threshold_in_seconds = $reactivation_threshold_in_days * 24 * 60 * 60;
			$ad_max_active_in_seconds = $ad_max_active_in_days * 24 * 60 * 60;
			$seconds_passed_since_ad_online = (mysql2date('G', $now_gmt) - mysql2date('G', $current_ad_keep_alive_date_gmt));
			
			if($seconds_passed_since_ad_online >= ($ad_max_active_in_seconds - $reactivation_threshold_in_seconds))  {
				
				//TODO: check function again
				//TODO: reaktivate keep_alive_date
				
				return "Inserat wurde reaktiviert und ist nun weiter $ad_max_active_in_days Tage g&uuml;ltig.";
			} else {

				$seconds_to_wait_till_reactivation_possible = ($ad_max_active_in_seconds - $reactivation_threshold_in_seconds)
																- $seconds_passed_since_ad_online;
				$days_to_wait = ceil($seconds_to_wait_till_reactivation_possible / (60 * 60 * 24));
				
				if($days_to_wait == 1) {
					$reactivation_day = "Tag";
				} else {
					$reactivation_day = "Tagen";
				}
				return "Reaktivierung von Inseraten ist $reactivation_threshold_in_days Tage vor dessen Ablauf m&ouml;lich.<br/>"
						."Sie k&oumlnnen Ihr Inserat in fr&uuml;hestens $days_to_wait $reactivation_day reaktivieren.";
			}
		}
		if($sm_item->is_approved_by_webmaster() === false) {
			return "Reaktivierung eines Inserates ist erst m&ouml;gliche nachdem das Inserat von uns online geschalted wurde.<br/>"
					."Dieses Inserat wurde von uns noch nicht freigegeben und kann somit noch nicht reaktiviert werden.";
		}
		if($sm_item->is_approved_by_mail() === false) {
			return "Dieses Inserat ist nicht aktiviert. <br/>Nur aktivierte Inserate k&ouml;nnen mit dem Reaktivierungs-Link verl&auml;ngert werden."
					."<br/>Nach einer erfolgreichen Verl&auml;ngerung, wird das Inserat weitere $ad_max_active_in_days Tage in unserem Online-Markt aufscheinen.";
		}
	}
	return get_sm_mail_ups_message();
	
}
function sm_mail_deactivate_ad($key) {
	if(sm_check_key($key) === true) {
		global $wpdb;
		global $sm_table_name;
		
		$prepared_stmt = $wpdb->prepare("SELECT * FROM $sm_table_name WHERE mail_approval_key = %s", $key);
		
		$row = $wpdb->get_row($prepared_stmt, ARRAY_A);
		$sm_item = new SimpleMarketItem($row);
		
		if($sm_item->is_approved_by_mail() === true) {
			
			$updated = $wpdb->update($sm_table_name,
					array('mail_approve' => 0),
					array('mail_approval_key' => $key),
					array('%d'));
			
			if($updated === 1) {
				return "Ihr Inserat wurde deaktiviert.<br/>Im deaktivierten Zustand scheint Ihr Inserat nicht mehr in unserem Online-Markt auf.";
			}
		} else {
			return "Dieses Inserat ist bereits deaktiviert.";	
		}
	}
	return get_sm_mail_ups_message();
}

/**
 * ----------------------------------------- some helpers
 */
function sm_check_key($key) {
	global $sm_mysql_column_length;
	
	if(isset($sm_mysql_column_length) === false) {
		return false;	
	}

	$key = str_replace(" ", "", $key);
	$key_length = strlen($key);
	if($key_length === $sm_mysql_column_length['mail_approval_key']) {
		return true;
	}
	return false;
}
function get_sm_mail_ups_message() {
	global $sm_options;
	if(!isset($sm_options))
		$sm_options = get_site_option('simple_market');
	return "Ups, da is wohl etwas nicht wie es sein soll.<br/>Wenn du glaubst, dies ist ein Fehler, dann wende dich doch bitte an unseren "
			.'<a style="font-weight: bold;"href="mailto:'.$sm_options['webmaster_mail'].'">Webmaster</a>.';
}
?>