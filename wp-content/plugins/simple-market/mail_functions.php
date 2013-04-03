<?php

/*
 * USER mail control functions ----------------------------------------------------------------------------------
 */

function sm_mail_activate_ad($key) {
	if(sm_check_key($key) === true) {
		global $wpdb;
		global $sm_table_name;
		global $sm_options;
		
		$prepared_stmt = $wpdb->prepare("SELECT * FROM $sm_table_name WHERE mail_approval_key = %s", $key);
		$row = $wpdb->get_row($prepared_stmt, ARRAY_A);
		
		if($row) {
			$sm_item = new SimpleMarketItem($row);
			
			if($sm_item->is_approved_by_mail() === false) {
				
				//on first activation we send the mail review request(s) to the admin(s)
				$keep_alive_date_time_gmt = $sm_item->get_keep_alive_date_time();
				if($keep_alive_date_time_gmt == '0000-00-00 00:00:00') {
					$keep_alive_date_time_gmt = $sm_item->get_submit_date_time();
					if(!sm_send_admin_review_mail_request($sm_item)) {
						return get_sm_mail_ups_message();
					}
				}
			
				$affected = $wpdb->update($sm_table_name,
						array('mail_approve' => 1,
							  'keep_alive_date_time' => $keep_alive_date_time_gmt),
						array('id' => $sm_item->get_id()),
						array('%d', '%s'), array('%d'));
				
				if($affected === 1) {
			
					$options = array('move_to_main_dir' => true, 'check_for_malicious' => true);
					perform_action_on_uploaded_images($sm_item, $options);
			
					$msg_ad_activated = "Ihr Inserat wurde aktiviert.<br/>";
					if($sm_item->is_approved_by_webmaster() === false) {
						$msg_ad_activated .= "Eines unserer Mitglieder wird Ihr Inserat nach einem kurzen Review freischalten."
								."<br/>Wir bitten um ein wenig Geduld, da dies auf ehrenamtlicher Basis geschieht.";
					}
					return $msg_ad_activated;
				}
					
			} else {
				$return_msg = "Dieses Inserat wurde bereits aktiviert";
				if($sm_item->is_approved_by_webmaster() === false) {
					$return_msg .= ", jedoch von uns noch nicht freigeschalten.<br/>Wir bitten um ein wenig Geduld, da dies auf ehrenamtlicher Basis geschieht.<br/><br/>"
						."Sollten Sie dennoch eine Frage habe, wenden Sie sich bitte an unseren ".'<a style="font-weight: bold;"href="mailto:'.$sm_options['webmaster_mail'].'">Webmaster</a>';
				} else {
					$return_msg .=" und von uns freigeschaltet";
				}
				$return_msg .= ".";
				return $return_msg;
			}	
		}
	}
	return get_sm_mail_ups_message();
}
function sm_mail_reactivate_ad($key) {
	
	if(sm_check_key($key) === true) {
		global $wpdb;
		global $sm_table_name;
		global $sm_options;
		
		$prepared_stmt = $wpdb->prepare("SELECT * FROM $sm_table_name WHERE mail_approval_key = %s", $key);
		
		$row = $wpdb->get_row($prepared_stmt, ARRAY_A);
		
		if($row) {
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

					$affected = $wpdb->update($sm_table_name,
												array('keep_alive_date_time' => $now_gmt),
												array('mail_approval_key' => $key) );
					if($affected === 1) {
						return "Inserat wurde reaktiviert und ist nun weitere $ad_max_active_in_days Tage g&uuml;ltig.";	
					}
				} else {
	
					$seconds_to_wait_till_reactivation_possible = ($ad_max_active_in_seconds - $reactivation_threshold_in_seconds)
																	- $seconds_passed_since_ad_online;
					$days_to_wait = ceil($seconds_to_wait_till_reactivation_possible / (60 * 60 * 24));
					
					if($days_to_wait == 1) {
						$reactivation_day = "Tag";
					} else {
						$reactivation_day = "Tagen";
					}
					return "Reaktivierung von Inseraten ist $reactivation_threshold_in_days Tage vor deren Ablauf m&ouml;glich.<br/>"
							."Sie k&oumlnnen Ihr Inserat in fr&uuml;hestens $days_to_wait $reactivation_day reaktivieren.";
				}
			}
			if($sm_item->is_approved_by_mail() === false) {
				return "Dieses Inserat ist nicht aktiviert. <br/>Nur aktivierte Inserate k&ouml;nnen mit dem Reaktivierungslink verl&auml;ngert werden."
						."<br/>Nach einer erfolgreichen Verl&auml;ngerung wird Ihr Inserat weitere $ad_max_active_in_days Tage in unserem Online-Markt aufscheinen.";
			}
			if($sm_item->is_approved_by_webmaster() === false) {
				return "Reaktivierung eines Inserates ist erst m&ouml;glich, nachdem das Inserat von uns frei geschaltet wurde.<br/>"
						."Dieses Inserat wurde von uns noch nicht freigegeben und kann somit noch nicht reaktiviert werden.";
			}
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

/*
 * ADMIN mail control functions ----------------------------------------------------------------------------------
*/

function sm_admin_mail_activate_ad($admin_key_received) {
	if(sm_check_key($admin_key_received) === true) {
		global $wpdb;
		global $sm_table_name;
			
		$affected = $wpdb->update($sm_table_name,
				array('webmaster_approve' => 1),
				array('webmaster_approval_key' => $admin_key_received),
				array('%d'), array('%s'));
		
		if($affected === 1) {
			
			$prepared_stmt = $wpdb->prepare("SELECT * FROM $sm_table_name WHERE webmaster_approval_key = %s", $admin_key_received);
			
			$row = $wpdb->get_row($prepared_stmt, ARRAY_A);
			$sm_item = new SimpleMarketItem($row);
			
			$deactivated_success_msg = "Das Inserat wurde aktiviert.<br>";
			
			if($sm_item->is_approved_by_mail() === false) {
				$deactivated_success_msg .= "Es scheint zur Zeit jedoch nicht im Online-Markt auf, da der Inserent dieses Inserates nach Aktivierung wieder deaktiviert hat.";
			}
			
			return $deactivated_success_msg;
		}	
	}
	return get_sm_mail_ups_message();
}

function sm_admin_mail_deactivate_ad($admin_key_received) {
	if(sm_check_key($admin_key_received) === true) {
		global $wpdb;
		global $sm_table_name;
		
		$affected = $wpdb->update($sm_table_name,
				array('webmaster_approve' => 0),
				array('webmaster_approval_key' => $admin_key_received),
				array('%d'), array('%s'));
		
		if($affected === 1) {
			return "Das Inserat wurde deaktiviert.";
		}
		
	}
	return get_sm_mail_ups_message();
}

/*
 * ----------------------------------------- some helpers
 */

/**
 * Mainly checks that the passed key has exactly the lenght uniqid returns with more entropy -> 23 
 * 
 * @param unknown $key the
 * @return boolean true if passed key seems to be in valid format else false
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
	return "Ups, da ist wohl etwas nicht so, wie es sein sollte.<br/>Wenn du glaubst, dies ist ein Fehler, dann wende dich doch bitte an unseren "
			.'<a style="font-weight: bold;"href="mailto:'.$sm_options['webmaster_mail'].'">Webmaster</a>.';
}

/**
 * Sends the admin review requests to the addresses mentioned in 'reviewer_mail_addresses' simple_market options.
 * 
 * @return boolean true if wpmail returns with success otherwise false.
 */
function sm_send_admin_review_mail_request(SimpleMarketItem $market_item) {
	global $options;
	
	if(!isset($sm_options))
		$sm_options = get_site_option('simple_market');
	
	$market_permalink = get_market_permalink();

	$ADMIN_preview_link = $market_permalink . '?admin-action=preview&key='.$market_item->get_webmaster_approval_key();
	$ADMIN_activation_link = $market_permalink . '?admin-action=activate&key='.$market_item->get_webmaster_approval_key();
	$ADMIN_deactivate_link = $market_permalink . '?admin-action=deactivate&key='.$market_item->get_webmaster_approval_key();
	
	$admin_name_info = 	$market_item->get_last_name().' '.$market_item->get_first_name();
	$admin_message =
	'<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>
	<body>
		Herr/Frau '.$admin_name_info.' w&uuml;rde gerne folgendes Inserat zu schalten:
		<br/>
		'.$ADMIN_preview_link.'
		<br/>
		<br/>
		<span style="font-weight: bold; color: #33CC33;">Inserat aktivieren:</span><br/>
		'.$ADMIN_activation_link.'
		<br/>
		<br/>
		<span style="font-weight: bold; color: #990000;">&Uuml;ber diesen Link, kann das Inserat wieder deaktiviert werden:</span><br/>
		'.$ADMIN_deactivate_link.'
	</body>
	</html>
	';
	
	$admin_headers[] = 'From: Review Request MCCM Simple-Market Inserent <'.$market_item->get_mail().'>';
	$admin_headers[] = 'MIME-Version: 1.0';
	$admin_headers[] = 'Content-type: text/html; charset=UTF-8';
	
	if(!wp_mail($sm_options['reviewer_mail_adresses'], "Inserat von $admin_name_info", $admin_message, $admin_headers)) {
		return false;	
	}
	return true;
}
?>