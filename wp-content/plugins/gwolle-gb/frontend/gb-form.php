<?php

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * Build up a form for the user, including possible error_fields
 * Called by shortcode or template function
 *
 * @param array $shortcode_atts shortcode attributes
 * @param string $shortcode the shortcode that was used
 * @return string html with the form and messages
 */
function gwolle_gb_frontend_write( $shortcode_atts, $shortcode ) {

	/* Get the messages and formdata from the form handling in posthandling.php. */
	$gwolle_gb_messages     = gwolle_gb_get_messages();
	$gwolle_gb_errors       = gwolle_gb_get_errors();
	$gwolle_gb_error_fields = gwolle_gb_get_error_fields();
	$gwolle_gb_formdata     = gwolle_gb_get_formdata();

	$html5  = current_theme_supports( 'html5' );
	$output = '';
	$button_class = (string) apply_filters( 'gwolle_gb_button_class', '' );

	// Set data up for prefilling an already submitted form that had errors.
	$name = '';
	$origin = '';
	$email = '';
	$website = '';
	$antispam = '';
	$content = '';

	// Auto-fill the form if the user is already logged in.
	$user_id = get_current_user_id(); // returns 0 if no current user
	if ( $user_id > 0 ) {
		$userdata = get_userdata( $user_id );
		if ( is_object( $userdata ) ) {
			if ( isset( $userdata->display_name ) ) {
				$name = $userdata->display_name;
			} else {
				$name = $userdata->user_login;
			}
			$email = $userdata->user_email;
			$website = $userdata->user_url;

			$name = apply_filters( 'gwolle_gb_author_name_prefill', $name);
			$email = apply_filters( 'gwolle_gb_author_email_prefill', $email);
			$website = apply_filters( 'gwolle_gb_author_website_prefill', $website);
		}
	}

	// Only show old data when there are errors.
	if ( $gwolle_gb_errors ) {
		if ( is_array($gwolle_gb_formdata) && ! empty($gwolle_gb_formdata) ) {
			if (isset($gwolle_gb_formdata['author_name'])) {
				$name = stripslashes($gwolle_gb_formdata['author_name']);
			}
			if (isset($gwolle_gb_formdata['author_origin'])) {
				$origin = stripslashes($gwolle_gb_formdata['author_origin']);
			}
			if (isset($gwolle_gb_formdata['author_email'])) {
				$email = stripslashes($gwolle_gb_formdata['author_email']);
			}
			if (isset($gwolle_gb_formdata['author_website'])) {
				$website = stripslashes($gwolle_gb_formdata['author_website']);
			}
			if (isset($gwolle_gb_formdata['antispam_answer'])) {
				$antispam = stripslashes($gwolle_gb_formdata['antispam_answer']);
			}
			if (isset($gwolle_gb_formdata['content'])) {
				$content = stripslashes($gwolle_gb_formdata['content']);
			}
		}
	}


	/*
	 * Handle Messaging to the user.
	 */
	$messageclass = '';
	if ( $gwolle_gb_errors ) {
		$messageclass = 'error';
	}
	$output .= '<div id="gwolle_gb_messages_top_container">';
	if ( isset($gwolle_gb_messages) && $gwolle_gb_messages != '') {
		$output .= '<div id="gwolle_gb_messages" class="' . $messageclass . '">';
		$output .= $gwolle_gb_messages;
		$output .= '</div>';
	}
	$output .= '</div>';


	// Option to allow only logged-in users to post. Don't show the form if not logged-in. We still see the messages above.
	if ( !is_user_logged_in() && get_option('gwolle_gb-require_login', 'false') == 'true' ) {
		$output .= '
			<div id="gwolle_gb_new_entry">
				<h3>' . esc_html__('Log in to post an entry', 'gwolle-gb') . '</h3>';

		$args = array(
			'echo'     => false,
			'redirect' => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
		);
		$output .= wp_login_form( $args );

		$output .= wp_register('', '', false);

		$output .= '</div>';

		return $output;
	}


	/*
	 * Button 'write a new entry.'
	 * Only show when shortcode [gwolle_gb] is used and there are no errors.
	 */
	$formclass = '';
	if ( ( $shortcode_atts['button'] == 'true' ) && ( ! $gwolle_gb_errors ) ) {
		$button = '
			<div id="gwolle_gb_write_button">
				<input type="button" class="button btn btn-default ' . $button_class . '" value="&raquo; ' . /* translators: Button text */ esc_attr__('Write a new entry.', 'gwolle-gb') . '" />
			</div>';
		$output .= apply_filters( 'gwolle_gb_button', $button);

		$formclass .= 'gwolle_gb_hide';
	}


	/*
	 * Build up Form including possible error_fields.
	 */
	$form_setting = gwolle_gb_get_setting( 'form' );
	$autofocus = ' autofocus="autofocus"';
	if ( get_option( 'gwolle_gb-labels_float', 'true' ) === 'true' ) {
		$formclass .= ' gwolle_gb_float gwolle-gb-float';
	}
	if ( get_option( 'gwolle_gb-form_ajax', 'true' ) === 'true' ) {
		$formclass .= ' gwolle_gb_form_ajax gwolle-gb-form-ajax';
	}

	$header = gwolle_gb_sanitize_output( get_option('gwolle_gb-header', false) );
	if ( $header == false ) {
		$header = esc_html__('Write a new entry for the Guestbook', 'gwolle-gb');
	}

	$hidebutton = '';
	if ( ( $shortcode_atts['button'] == 'true' ) ) {
		$hidebutton = '<button type="button" class="gb-notice-dismiss"><span class="screen-reader-text">' . esc_html__('Hide this form.', 'gwolle-gb') . '</span></button>
			';
	}
	$output .= '
			<form id="gwolle_gb_new_entry" action="#" method="POST" class="' . $formclass . '">
				<h3>' . $header . '</h3>
				' . $hidebutton . '
				<input type="hidden" name="gwolle_gb_function" id="gwolle_gb_function" value="add_entry" />';

	// The book_id from the shortcode, to be used by the posthandling function again.
	$output .= '<input type="hidden" name="gwolle_gb_book_id" id="gwolle_gb_book_id" value="' . $shortcode_atts['book_id'] . '" />';

	// Use this filter to just add something
	$output .= apply_filters( 'gwolle_gb_write_add_before', '' );


	/* Name */
	if ( isset($form_setting['form_name_enabled']) && $form_setting['form_name_enabled']  === 'true' ) {
		$field_name = gwolle_gb_get_field_name( 'name' );
		$label = apply_filters( 'gwolle_gb_author_name_label', esc_html__('Name', 'gwolle-gb') );
		$output .= '<div class="' . $field_name . '">
				<div class="label"><label for="' . $field_name . '" class="text-info">' . $label . ':';
		if ( isset($form_setting['form_name_mandatory']) && $form_setting['form_name_mandatory']  === 'true' ) { $output .= ' *';}
		$output .= '</label></div>
				<div class="input"><input class="wp-exclude-emoji ';
		if (in_array($field_name, $gwolle_gb_error_fields)) {
			$output .= ' error';
		}
		$output .= '" value="' . $name . '" type="text" name="' . $field_name . '" id="' . $field_name . '" placeholder="' . $label . '"';
		if ( in_array($field_name, $gwolle_gb_error_fields) && isset($autofocus) ) {
			$output .= $autofocus;
			$autofocus = false; // disable it for the next error.
		}
		if ( isset($form_setting['form_name_mandatory']) && $form_setting['form_name_mandatory']  === 'true' ) {
			$output .= ' required';
		}
		$output .= ' /></div>
			</div>
			<div class="clearBoth">&nbsp;</div>';
	}
	$output .= apply_filters( 'gwolle_gb_write_add_after_name', '' );


	/* City / Origin */
	if ( isset($form_setting['form_city_enabled']) && $form_setting['form_city_enabled']  === 'true' ) {
		$field_name = gwolle_gb_get_field_name( 'city' );
		$label = apply_filters( 'gwolle_gb_author_origin_label', esc_html__('City', 'gwolle-gb') );
		$output .= '<div class="' . $field_name . '">
					<div class="label"><label for="' . $field_name . '" class="text-info">' . $label . ':';
		if ( isset($form_setting['form_city_mandatory']) && $form_setting['form_city_mandatory']  === 'true' ) { $output .= ' *';}
		$output .= '</label></div>
					<div class="input"><input class="wp-exclude-emoji ';
		if (in_array($field_name, $gwolle_gb_error_fields)) {
			$output .= ' error';
		}
		$output .= '" value="' . $origin . '" type="text" name="' . $field_name . '" id="' . $field_name . '" placeholder="' . $label . '"';
		if ( in_array($field_name, $gwolle_gb_error_fields) && isset($autofocus) ) {
			$output .= $autofocus;
			$autofocus = false; // disable it for the next error.
		}
		if ( isset($form_setting['form_city_mandatory']) && $form_setting['form_city_mandatory']  === 'true' ) {
			$output .= ' required';
		}
		$output .= ' /></div>
				</div>
				<div class="clearBoth">&nbsp;</div>';
	}
	$output .= apply_filters( 'gwolle_gb_write_add_after_origin', '' );

	/* Email */
	if ( isset($form_setting['form_email_enabled']) && $form_setting['form_email_enabled']  === 'true' ) {
		$field_name = gwolle_gb_get_field_name( 'email' );
		$label = apply_filters( 'gwolle_gb_author_email_label', esc_html__('Email', 'gwolle-gb') );
		$output .= '<div class="' . $field_name . '">
				<div class="label"><label for="' . $field_name . '" class="text-info">' . $label . ':';
		if ( isset($form_setting['form_email_mandatory']) && $form_setting['form_email_mandatory']  === 'true' ) { $output .= ' *';}
		$output .= '</label></div>
				<div class="input"><input class="';
		if (in_array($field_name, $gwolle_gb_error_fields)) {
			$output .= ' error';
		}
		$output .= '" value="' . $email . '" ' . ($html5 ? 'type="email"' : 'type="text"') . ' name="' . $field_name . '" id="' . $field_name . '" placeholder="' . $label . '" ';
		if ( in_array($field_name, $gwolle_gb_error_fields) && isset($autofocus) ) {
			$output .= $autofocus;
			$autofocus = false; // disable it for the next error.
		}
		if ( isset($form_setting['form_email_mandatory']) && $form_setting['form_email_mandatory']  === 'true' ) {
			$output .= ' required';
		}
		$output .= ' /></div>
			</div>
			<div class="clearBoth">&nbsp;</div>';
	} else {
		if ( isset($email) && strlen($email) > 0 ) {
			// For logged in users, just save the email anyway.
			$output .= '<input class="" value="' . $email . '" type="hidden" name="gwolle_gb_author_email" id="gwolle_gb_author_email" />';
		}
	}
	$output .= apply_filters( 'gwolle_gb_write_add_after_email', '' );

	/* Website / Homepage */
	if ( isset($form_setting['form_homepage_enabled']) && $form_setting['form_homepage_enabled']  === 'true' ) {
		$field_name = gwolle_gb_get_field_name( 'website' );
		$label = apply_filters( 'gwolle_gb_author_website_label', esc_html__('Website', 'gwolle-gb') );
		$output .= '<div class="' . $field_name . '">
				<div class="label"><label for="' . $field_name . '" class="text-info">' . $label . ':';
		if ( isset($form_setting['form_homepage_mandatory']) && $form_setting['form_homepage_mandatory']  === 'true' ) { $output .= ' *';}
		$output .= '</label></div>
				<div class="input"><input class="';
		if (in_array($field_name, $gwolle_gb_error_fields)) {
			$output .= ' error';
		}
		$output .= '" value="' . $website . '" ' . ($html5 ? 'type="url"' : 'type="text"') . ' name="' . $field_name . '" id="' . $field_name . '" placeholder="' . $label . '" ';
		if ( in_array($field_name, $gwolle_gb_error_fields) && isset($autofocus) ) {
			$output .= $autofocus;
			$autofocus = false; // disable it for the next error.
		}
		if ( isset($form_setting['form_homepage_mandatory']) && $form_setting['form_homepage_mandatory']  === 'true' ) {
			$output .= ' required';
		}
		// $output .= ' pattern="[a-z0-9]+\.[a-z]$"'; // try to relax validation to work without http://
		$output .= ' /></div>
			</div>
			<div class="clearBoth">&nbsp;</div>';
	}
	$output .= apply_filters( 'gwolle_gb_write_add_after_website', '' );

	/* Honeypot */
	if ( get_option( 'gwolle_gb-honeypot', 'true') == 'true' ) {
		$field_name = gwolle_gb_get_field_name( 'honeypot' );
		$field_name2 = gwolle_gb_get_field_name( 'honeypot2' );
		$honeypot_value = (int) get_option( 'gwolle_gb-honeypot_value', 15 );
		$output .= '
			<div class="' . $field_name . '" style="display:none;">
				<div class="label">
					<label for="' . $field_name . '" class="text-primary">' . esc_html__('Do not touch this', 'gwolle-gb') . ':</label>
					<label for="' . $field_name2 . '" class="text-primary">' . esc_html__('Do not touch this', 'gwolle-gb') . ':</label>
				</div>
				<div class="input">
					<input value="' . $honeypot_value . '" type="text" name="' . $field_name . '" id="' . $field_name . '" placeholder="" />
					<input value="" type="text" name="' . $field_name2 . '" id="' . $field_name2 . '" placeholder="" />
				</div>
			</div>
			<div class="clearBoth"></div>';
	}

	/* Form Timeout */
	if ( get_option( 'gwolle_gb-timeout', 'true') == 'true' ) {
		$field_name = gwolle_gb_get_field_name( 'timeout' );
		$field_name2 = gwolle_gb_get_field_name( 'timeout2' );
		$random = rand( 100, 100000 );
		$output .= '
			<div class="' . $field_name . '" style="display:none;">
				<div class="label">
					<label for="' . $field_name . '" class="text-primary">' . esc_html__('Do not touch this', 'gwolle-gb') . ':</label>
					<label for="' . $field_name2 . '" class="text-primary">' . esc_html__('Do not touch this', 'gwolle-gb') . ':</label>
				</div>
				<div class="input">
					<input value="' . $random . '" type="text" name="' . $field_name . '" id="' . $field_name . '" placeholder="" />
					<input value="' . $random . '" type="text" name="' . $field_name2 . '" id="' . $field_name2 . '" placeholder="" />
				</div>
			</div>
			<div class="clearBoth"></div>';
	}

	/* Content */
	if ( isset($form_setting['form_message_enabled']) && $form_setting['form_message_enabled']  === 'true' ) {
		$field_name = gwolle_gb_get_field_name( 'content' );
		$label = apply_filters( 'gwolle_gb_content_label', esc_html__('Guestbook entry', 'gwolle-gb') );
		$output .= '<div class="' . $field_name . '">
				<div class="label"><label for="' . $field_name . '" class="text-info">' . $label . ':';
		if ( isset($form_setting['form_message_mandatory']) && $form_setting['form_message_mandatory']  === 'true' ) { $output .= ' *';}
		$output .= '</label></div>
				<div class="input"><textarea name="' . $field_name . '" id="' . $field_name . '" class="wp-exclude-emoji ';
		if (in_array($field_name, $gwolle_gb_error_fields)) {
			$output .= ' error';
		}
		$output .= '" placeholder="' . $label . '" ';
		if ( in_array('content', $gwolle_gb_error_fields) && isset($autofocus) ) {
			$output .= $autofocus;
			$autofocus = false; // disable it for the next error.
		}
		if ( isset($form_setting['form_message_mandatory']) && $form_setting['form_message_mandatory']  === 'true' ) {
			$output .= ' required';
		}
		$output .= ' >' . $content . '</textarea>';

		if ( isset($form_setting['form_bbcode_enabled']) && $form_setting['form_bbcode_enabled']  === 'true' ) {
			// BBcode and MarkItUp
			gwolle_gb_enqueue_markitup();

			// Emoji symbols
			$output .= '<div class="gwolle_gb_emoji gwolle_gb_hide">';
			$output .= gwolle_gb_get_emoji();
			$output .= '</div>';
		}

		$output .= '</div>'; // .input

		$output .= '
				</div>
			<div class="clearBoth">&nbsp;</div>';
	}
	$output .= apply_filters( 'gwolle_gb_write_add_after_content', '' );

	/* Custom Anti-Spam */
	if ( isset($form_setting['form_antispam_enabled']) && $form_setting['form_antispam_enabled']  === 'true' ) {
		$field_name = gwolle_gb_get_field_name( 'custom' );
		$label = apply_filters( 'gwolle_gb_antispam_label', esc_html__('Anti-spam', 'gwolle-gb') );
		$antispam_question = gwolle_gb_sanitize_output( get_option('gwolle_gb-antispam-question') );
		$antispam_answer   = gwolle_gb_sanitize_output( get_option('gwolle_gb-antispam-answer') );

		if ( isset($antispam_question) && strlen($antispam_question) > 0 && isset($antispam_answer) && strlen($antispam_answer) > 0 ) {
			$output .= '
				<div class="gwolle_gb_antispam">
					<div class="label">
						<label for="' . $field_name . '" class="text-info">' . $label . ': *<br />
						' . esc_html__('Question:', 'gwolle-gb') . ' ' .  $antispam_question . '</label>
					</div>
					<div class="input"><input class="';
			if (in_array( $field_name, $gwolle_gb_error_fields)) {
				$output .= ' error';
			}
			$output .= '" value="' . $antispam . '" type="text" name="' . $field_name . '" id="' . $field_name . '" placeholder="' . esc_attr__('Answer', 'gwolle-gb') . '" ';
			if ( in_array( $field_name, $gwolle_gb_error_fields) && isset($autofocus) ) {
				$output .= $autofocus;
				$autofocus = false; // disable it for the next error.
			}
			$output .= ' required'; // always required.
			$output .= ' />
						</div>
					</div>
					<div class="clearBoth">&nbsp;</div>';
		}
	}
	$output .= apply_filters( 'gwolle_gb_write_add_after_antispam', '' );

	/* Privacy checkbox for GDPR compliance. */
	if ( isset($form_setting['form_privacy_enabled']) && $form_setting['form_privacy_enabled']  === 'true' ) {
		$a_open  = '';
		$a_close = '';
		if ( function_exists( 'get_privacy_policy_url' ) ) {
			$privacy_policy_page = get_privacy_policy_url(); // Since WP 4.9.6
			if ( ! empty( $privacy_policy_page ) ) {
				$a_open  = '<a href="' . $privacy_policy_page . '" title="' . esc_attr__('Read the Privacy Policy', 'gwolle-gb') . '" target="_blank">';
				$a_close = '</a>';
			}
		}
		/* translators: %s is a link to the privacy policy page. */
		$label = apply_filters( 'gwolle_gb_privacy_label', sprintf( esc_html__( 'Accept %sPrivacy Policy%s', 'gwolle-gb' ), $a_open, $a_close ) );
		$output .= '
				<div class="gwolle_gb_privacy">
					<div class="label"><label for="gwolle_gb_privacy" class="text-info">' . $label . ': *</label></div>
					<div class="input"><input type="checkbox" name="gwolle_gb_privacy" id="gwolle_gb_privacy" required /></div>
				</div>
				<div class="clearBoth">&nbsp;</div>';
	}

	/* Nonce */
	if (get_option( 'gwolle_gb-nonce', 'true') == 'true') {
		$field_name = gwolle_gb_get_field_name( 'nonce' );
		$nonce = wp_create_nonce( 'gwolle_gb_add_entry' );
		$output .= '<input type="hidden" id="' . $field_name . '" name="' . $field_name . '" value="' . $nonce . '" />';
	}

	/* Use this filter to just add something */
	$output .= apply_filters( 'gwolle_gb_write_add_form', '' );

	$output .= '
			<div id="gwolle_gb_messages_bottom_container"></div>

			<noscript><div class="no-js">' . esc_html__( 'Warning: This form can only be used if JavaScript is enabled in your browser.', 'gwolle-gb' ) . '</div></noscript>

			<div class="gwolle_gb_submit">
				<div class="label gwolle_gb_invisible text-muted">&nbsp;</div>
				<div class="input">
					<input type="submit" name="gwolle_gb_submit" id="gwolle_gb_submit" class="button btn btn-primary ' . $button_class . '" value="' . esc_attr__('Submit', 'gwolle-gb') . '" />
					<span class="gwolle_gb_submit_ajax_icon"></span>
			';

	$output .= apply_filters( 'gwolle_gb_write_add_after_submit', '' );

	$output .= '
				</div>
			</div>
			<div class="clearBoth">&nbsp;</div>

			<div class="gwolle_gb_notice">
				';

	$notice = gwolle_gb_sanitize_output( get_option('gwolle_gb-notice', false), 'setting_textarea' );
	if ( $notice == false ) { // No text set by the user. Use the default text.
		$notice = esc_html__("
Fields marked with * are required.
Your E-mail address won't be published.
It's possible that your entry will only be visible in the guestbook after we reviewed it.
We reserve the right to edit, delete, or not publish entries.
"
, 'gwolle-gb');
	}

	$notice = nl2br($notice);
	$output .= str_replace('%ip%', $_SERVER['REMOTE_ADDR'], $notice);

	$output .= '
			</div>';

	// Use this filter to just add something
	$output .= apply_filters( 'gwolle_gb_write_add_after', '' );

	$output .= '</form>';


	// Add filter for the form, so devs can manipulate it.
	$output = apply_filters( 'gwolle_gb_write', $output);

	return $output;
}
