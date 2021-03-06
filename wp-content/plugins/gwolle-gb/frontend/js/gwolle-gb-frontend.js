/*
 * JavaScript for Gwolle Guestbook Frontend.
 */


/*
 * Event for clicking the button, and getting the form visible.
 */
jQuery(document).ready(function($) {
	jQuery( "#gwolle_gb_write_button input" ).click(function() {
		jQuery("#gwolle_gb_write_button").slideUp(1000);
		jQuery("#gwolle_gb_new_entry").slideDown(1000);
		return false;
	});
});
// And close it again.
jQuery(document).ready(function($) {
	jQuery( "button.gb-notice-dismiss" ).click(function() {
		jQuery("#gwolle_gb_write_button").slideDown(1000);
		jQuery("#gwolle_gb_new_entry").slideUp(1000);
		return false;
	});
});


/*
 * Event for clicking the readmore, and getting the full content of that entry visible.
 */
jQuery(document).ready(function($) {
	gwolle_gb_readmore();
	gwolle_gb_scroll_callback.add( gwolle_gb_readmore );
	gwolle_gb_ajax_callback.add( gwolle_gb_readmore );
});
function gwolle_gb_readmore() {
	jQuery(".gb-entry-content .gwolle-gb-readmore").off('click');
	jQuery(".gb-entry-content .gwolle-gb-readmore").on('click', function() {
		var content_div = jQuery(this).parent().parent();
		jQuery( content_div ).find('.gb-entry-excerpt').css( 'display', 'none' );
		jQuery( content_div ).find('.gb-entry-full_content').slideDown(500);
		return false;
	});

	jQuery(".gb-entry-admin_reply .gwolle-gb-readmore").off('click');
	jQuery(".gb-entry-admin_reply .gwolle-gb-readmore").on('click', function() {
		var content_div = jQuery(this).parent().parent();
		jQuery( content_div ).find('.gb-admin_reply-excerpt').css( 'display', 'none' );
		jQuery( content_div ).find('.gb-admin_reply-full-content').slideDown(500);
		return false;
	});
}


/*
 * Event for the metabox, toggle on and off.
 */
jQuery(document).ready(function($) {
	gwolle_gb_metabox_handle();
	gwolle_gb_scroll_callback.add( gwolle_gb_metabox_handle );
	gwolle_gb_ajax_callback.add( gwolle_gb_metabox_handle );
});
function gwolle_gb_metabox_handle() {
	jQuery('div.gb-metabox-handle').off('click');
	jQuery('div.gb-metabox-handle').on('click', function() {
		var metabox_parent = jQuery(this).parent();
		jQuery( metabox_parent ).find('div.gb-metabox').toggleClass( 'gwolle_gb_invisible' );
		return false;
	});
	jQuery("div.gb-metabox-handle").keypress(function(e) {
		if (e.keyCode == 13) {
			var metabox_parent = jQuery(this).parent();
			jQuery( metabox_parent ).find('div.gb-metabox').toggleClass( 'gwolle_gb_invisible' );
			return false;
		}
	});
	return false;
}
jQuery(document).ready(function($) {
	jQuery('body').on('click', function( el ) {
		jQuery('div.gb-metabox').addClass( 'gwolle_gb_invisible' );
	});
});


/*
 * Event for Infinite Scroll. Get more pages when you are at the bottom.
 */
var gwolle_gb_scroll_on = true; // The end has not been reached yet. We still get entries back.
var gwolle_gb_scroll_busy = false; // Handle async well. Only one request at a time.
var gwolle_gb_scroll_callback = jQuery.Callbacks(); // Callback function to be fired after AJAX request.

jQuery(document).ready(function($) {
	if ( jQuery( "#gwolle_gb_entries" ).hasClass( 'gwolle-gb-infinite' ) ) {
		var gwolle_gb_scroll_count = 2; // We already have page 1 listed.

		var gwolle_gb_load_message = '<div class="gb-entry gwolle_gb_load_message">' + gwolle_gb_frontend_script.load_message + '</div>' ;
		jQuery( "#gwolle_gb_entries" ).append( gwolle_gb_load_message );

		jQuery(window).scroll(function() {
			// have 10px diff for sensitivity.
			if ( ( jQuery(window).scrollTop() > jQuery(document).height() - jQuery(window).height() -10 ) && gwolle_gb_scroll_on == true && gwolle_gb_scroll_busy == false) {
				gwolle_gb_scroll_busy = true;
				gwolle_gb_load_page(gwolle_gb_scroll_count);
				gwolle_gb_scroll_count++;
			}
		});
	}

	function gwolle_gb_load_page( page ) {

		jQuery('.gwolle_gb_load_message').toggle();

		var gwolle_gb_end_message = '<div class="gb-entry gwolle_gb_end_message">' + gwolle_gb_frontend_script.end_message + '</div>' ;

		var data = {
			action:  'gwolle_gb_infinite_scroll',
			pageNum: page,
			permalink: window.location.href,
			book_id: jQuery( "#gwolle_gb_entries" ).attr( "data-book_id" )
		};

		jQuery.post( gwolle_gb_frontend_script.ajax_url, data, function(response) {

			jQuery('.gwolle_gb_load_message').toggle();
			if ( response == 'false' ) {
				jQuery( "#gwolle_gb_entries" ).append( gwolle_gb_end_message );
				gwolle_gb_scroll_on = false;
			} else {
				jQuery( "#gwolle_gb_entries" ).append( response );
			}

			/*
			 * Add callback for after infinite scroll event. Used for metabox-handle for new entries.
			 *
			 * @since 2.3.0
			 *
			 * Example code for using the callback:
			 *
			 * jQuery(document).ready(function($) {
			 *     gwolle_gb_scroll_callback.add( my_callback_function );
			 * });
			 *
			 * function my_callback_function() {
			 *     console.log('This is the callback');
			 *     return false;
			 * }
			 *
			 */
			gwolle_gb_scroll_callback.fire();

			gwolle_gb_scroll_busy = false;

		});

		return true;
	}
});


/*
 * Mangle data for the honeypot.
 */
jQuery(document).ready(function($) {
	var honeypot  = gwolle_gb_frontend_script.honeypot;
	var honeypot2 = gwolle_gb_frontend_script.honeypot2;
	var val = jQuery( '#' + honeypot ).val();
	if ( val > 0 ) {
		jQuery( '#' + honeypot2 ).val( val );
		jQuery( '#' + honeypot ).val( '' );
	}
});


/*
 * Mangle data for the form timeout.
 */
jQuery(document).ready(function($) {
	setInterval('gwolle_gb_timout_clock()', 1000 );
});
function gwolle_gb_timout_clock() {
	var timeout  = gwolle_gb_frontend_script.timeout;
	var timeout2 = gwolle_gb_frontend_script.timeout2;

	var timer  = new Number( jQuery( '#' + timeout ).val() );
	var timer2 = new Number( jQuery( '#' + timeout2 ).val() );

	var timer  = timer - 1
	var timer2 = timer2 + 1

	jQuery( '#' + timeout ).val( timer );
	jQuery( '#' + timeout2 ).val( timer2 );
}


/*
 * AJAX Submit for Gwolle Guestbook Frontend.
 */
var gwolle_gb_ajax_callback = jQuery.Callbacks(); // Callback function to be fired after AJAX request.
// Use an object, arrays are only indexed by integers. This var is kept for compatibility with add-on 1.0.0 till 1.1.1.
var gwolle_gb_ajax_data = {
	permalink: window.location.href,
	action: 'gwolle_gb_form_ajax'
};

jQuery(document).ready(function($) {
	jQuery( '.gwolle_gb_form_ajax #gwolle_gb_submit' ).click( function( submit_button ) {

		jQuery( '#gwolle_gb .gwolle_gb_submit_ajax_icon' ).css( 'display', 'inline' );

		// Use an object, arrays are only indexed by integers.
		var gwolle_gb_ajax_data = {
			permalink: window.location.href,
			action: 'gwolle_gb_form_ajax'
		};

		jQuery('.gwolle_gb_form_ajax input').each(function( index, value ) {
			var val = jQuery( this ).prop('value'); // For some reason, some hosts do not see any value here, and data does not get sent.
			var id = jQuery( this ).attr('id');
			if ( id == 'gwolle_gb_privacy' ) {
				var checked = jQuery('.gwolle_gb_form_ajax input#gwolle_gb_privacy').prop('checked');
				if ( checked == true ) {
					gwolle_gb_ajax_data[id] = 'on';
				}
			} else {
				gwolle_gb_ajax_data[id] = val;
				/*if ( typeof console != 'undefined' ) {
					console.log( id + ': ' + val );
					console.table( val );
				} */
			}
		});
		jQuery('.gwolle_gb_form_ajax textarea').each(function( index, value ) {
			var val = jQuery( this ).val();
			var id = jQuery( this ).attr('id');
			gwolle_gb_ajax_data[id] = val;
		});

		jQuery.post( gwolle_gb_frontend_script.ajax_url, gwolle_gb_ajax_data, function( response ) {

			if ( gwolle_gb_is_json( response ) ) {
				data = JSON.parse( response );

				if ( ( typeof data['saved'] == 'boolean' || typeof data['saved'] == 'number' )
					&& typeof data['gwolle_gb_messages'] == 'string'
					&& typeof data['gwolle_gb_errors'] == 'boolean'
					&& typeof data['gwolle_gb_error_fields'] == 'object' ) { // Too strict in testing?

					var saved                  = data['saved'];
					var gwolle_gb_messages     = data['gwolle_gb_messages'];
					var gwolle_gb_errors       = data['gwolle_gb_errors'];
					var gwolle_gb_error_fields = data['gwolle_gb_error_fields'];

					// we have all the data we expect.
					if ( typeof data['saved'] == 'number' ) {

						// Show returned messages.
						document.getElementById( 'gwolle_gb_messages_bottom_container' ).innerHTML = '';
						document.getElementById( 'gwolle_gb_messages_top_container' ).innerHTML = '<div id="gwolle_gb_messages">' + data['gwolle_gb_messages'] + '</div>';
						jQuery( '#gwolle_gb_messages' ).removeClass( 'error' );

						// Remove error class from input fields.
						jQuery( '#gwolle_gb_new_entry input' ).removeClass( 'error' );
						jQuery( '#gwolle_gb_new_entry textarea' ).removeClass( 'error' );

						// Remove form from view.
						jQuery( '#gwolle_gb_new_entry' ).css( 'display', 'none' );
						jQuery( '#gwolle_gb_write_button' ).css( 'display', 'block' );

						// Prepend entry to the entry list if desired.
						if ( typeof data['entry'] == 'string' ) {
							jQuery( '#gwolle_gb_entries' ).prepend( data['entry'] );
						}

						// Scroll to messages div. Add 80px to offset for themes with fixed headers.
						var offset = jQuery( '#gwolle_gb_messages_top_container' ).offset().top - 80;
						jQuery('html, body').animate({
							scrollTop: offset
						}, 200, function() {
							// Animation complete.
						});

						// Reset content textarea.
						jQuery( '.gwolle_gb_form_ajax textarea' ).val('');

						jQuery( '#gwolle_gb .gwolle_gb_submit_ajax_icon' ).css( 'display', 'none' );

						/*
						 * Add callback for after AJAX request. Used for metabox-handle for new entries.
						 *
						 * @since 2.3.0
						 *
						 * Example code for using the callback:
						 *
						 * jQuery(document).ready(function($) {
						 *     gwolle_gb_ajax_callback.add( my_callback_function );
						 * });
						 *
						 * function my_callback_function() {
						 *     console.log('This is the callback');
						 *     return false;
						 * }
						 *
						 */
						gwolle_gb_ajax_callback.fire();

					} else {
						// Not saved...

						// Show returned messages.
						document.getElementById( 'gwolle_gb_messages_top_container' ).innerHTML = '';
						document.getElementById( 'gwolle_gb_messages_bottom_container' ).innerHTML = '<div id="gwolle_gb_messages" class="error">' + data['gwolle_gb_messages'] + '</div>';

						// Add error class to failed input fields.
						jQuery( '.gwolle_gb_form_ajax input' ).removeClass( 'error' );
						jQuery( '.gwolle_gb_form_ajax textarea' ).removeClass( 'error' );
						jQuery.each( gwolle_gb_error_fields, function( index, value ) {
							jQuery( '#' + value ).addClass( 'error' );
						});

						jQuery( '#gwolle_gb .gwolle_gb_submit_ajax_icon' ).css( 'display', 'none' );

					}
				} else if (typeof console != "undefined") {
					console.log( 'Gwolle Error: Something unexpected happened. (not the data that is expected)' );
				}
			} else {
				if (typeof console != "undefined") {
					console.log( 'Gwolle Error: Something unexpected happened. (not json data)' );
				}
			}
		});
		return false;
	});
});


function gwolle_gb_is_json( string ) {
	try {
		JSON.parse( string );
	} catch (e) {
		return false;
	}
	return true;
}
