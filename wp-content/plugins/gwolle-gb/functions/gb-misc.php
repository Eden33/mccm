<?php


// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * Uses intermittent meta_key to determine the post ID. See functions/gb-post-meta.php and gwolle_gb_set_meta_keys().
 *
 * @param int book_id integer of the guestbook ID. Not required for backwards compatibility, but suggested to use the parameter.
 *
 * @return (int) postid if found, else 0.
 */
function gwolle_gb_get_postid( $book_id = 1 ) {

	$the_query = new WP_Query( array(
		'post_type'           => 'any',
		'ignore_sticky_posts' => true, // do not use sticky posts.
		'meta_query'          => array(
			array(
				'key'   => 'gwolle_gb_read',
				'value' => 'true',
			),
			array(
				'key'   => 'gwolle_gb_book_id',
				'value' => $book_id,
			),
		),
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false
	));

	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) : $the_query->the_post();
			$postid = get_the_ID();
			return $postid;
			break; // only one postid is needed.
		endwhile;
		wp_reset_postdata();
	}
	return 0;

}


/*
 * Uses intermittent meta_key to determine the post ID. See functions/gb-post-meta.php and gwolle_gb_set_meta_keys().
 *
 * @return int postid if found, else 0.
 *
 * @since 2.4.0
 */
function gwolle_gb_get_postid_biggest_book() {

	$postids = gwolle_gb_get_books();
	if ( is_array($postids) && ! empty($postids) ) {

		if ( count( $postids ) == 1 ) {
			return $postids[0]; // just one guestbook, return it.
		}

		$books = array();
		$totals = array();
		foreach ( $postids as $postid ) {
			$bookid = (int) get_post_meta( $postid, 'gwolle_gb_book_id', true );
			if ( empty( $bookid ) ) {
				continue;
			}
			$key = 'gwolle_gb_frontend_pagination_book_' . $bookid;
			$entries_total = (int) get_transient( $key );
			if ( false === $entries_total ) {
				$entries_total = gwolle_gb_get_entry_count(
					array(
						'checked' => 'checked',
						'trash'   => 'notrash',
						'spam'    => 'nospam',
						'book_id' => $bookid
					)
				);
				set_transient( $key, $entries_total, DAY_IN_SECONDS );
			}
			$book = array();
			$book['postid'] = $postid;
			$book['bookid'] = $bookid;
			$book['entries_total'] = $entries_total;
			$books[] = $book;
			$totals[] = $entries_total;
		}

		// First check what the biggest total is, then find the post_id that belongs to it.
		rsort( $totals );

		foreach ( $books as $book ) {
			if ( $book['entries_total'] == $totals[0] ) {
				return $book['postid'];
			}
		}
	}

	return 0;

}


/*
 * Uses intermittent meta_key to determine the post IDs. See functions/gb-post-meta.php and gwolle_gb_set_meta_keys().
 *
 * @return array with post IDs that contain a guestbook.
 *
 * @since 2.4.0
 */
function gwolle_gb_get_books() {

	$the_query = new WP_Query( array(
		'post_type'           => 'any',
		'ignore_sticky_posts' => true, // do not use sticky posts.
		'meta_query'          => array(
			array(
				'key'   => 'gwolle_gb_read',
				'value' => 'true',
			),
		),
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false
	));

	$postids = array();
	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) : $the_query->the_post();
			$postids[] = get_the_ID();
		endwhile;
		wp_reset_postdata();
	}

	return $postids;

}


/*
 * Taken from wp-admin/includes/template.php touch_time()
 * Adapted for simplicity.
 */
function gwolle_gb_touch_time( $entry ) {
	global $wp_locale;

	$date = $entry->get_datetime();
	if ( !$date ) {
		$date = current_time('timestamp');
	}

	$dd = date( 'd', $date );
	$mm = date( 'm', $date );
	$yy = date( 'Y', $date );
	$hh = date( 'H', $date );
	$mn = date( 'i', $date );

	// Day
	echo '<label><span class="screen-reader-text">' . esc_html__( 'Day', 'gwolle-gb' ) . '</span><input type="text" id="dd" name="dd" value="' . $dd . '" size="2" maxlength="2" autocomplete="off" /></label>';

	// Month
	echo '<label for="mm"><span class="screen-reader-text">' . esc_html__( 'Month', 'gwolle-gb' ) . '</span><select id="mm" name="mm">\n';
	for ( $i = 1; $i < 13; $i = $i +1 ) {
		$monthnum = zeroise($i, 2);
		echo "\t\t\t" . '<option value="' . $monthnum . '" ' . selected( $monthnum, $mm, false ) . '>';
		/* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
		echo sprintf( esc_html__( '%1$s-%2$s', 'gwolle-gb' ), $monthnum, $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) ) . "</option>\n";
	}
	echo '</select></label>';

	// Year
	echo '<label for="yy"><span class="screen-reader-text">' . esc_html__( 'Year', 'gwolle-gb' ) . '</span><input type="text" id="yy" name="yy" value="' . $yy . '" size="4" maxlength="4" autocomplete="off" /></label>';
	echo '<br />';
	// Hour
	echo '<label for="hh"><span class="screen-reader-text">' . esc_html__( 'Hour', 'gwolle-gb' ) . '</span><input type="text" id="hh" name="hh" value="' . $hh . '" size="2" maxlength="2" autocomplete="off" /></label>:';
	// Minute
	echo '<label for="mn"><span class="screen-reader-text">' . esc_html__( 'Minute', 'gwolle-gb' ) . '</span><input type="text" id="mn" name="mn" value="' . $mn . '" size="2" maxlength="2" autocomplete="off" /></label>';
	?>

	<div class="gwolle_gb_timestamp">
		<!-- Clicking OK will place a timestamp here. -->
		<input type="hidden" id="gwolle_gb_timestamp" name="gwolle_gb_timestamp" value="" />
	</div>

	<p>
		<a href="#" class="gwolle_gb_save_timestamp hide-if-no-js button" title="<?php esc_attr_e('Save the date and time', 'gwolle-gb'); ?>">
			<?php esc_html_e('Save Date', 'gwolle-gb'); ?>
		</a>
		<a href="#" class="gwolle_gb_cancel_timestamp hide-if-no-js button-cancel" title="<?php esc_attr_e('Cancel saving date and time', 'gwolle-gb'); ?>">
			<?php esc_html_e('Cancel', 'gwolle-gb'); ?>
		</a>
	</p>
	<?php
}


/*
 * Use a custom field name for the form fields that are different for each website.
 *
 * @param string field name of the requested field.
 *
 * @return hashed fieldname or fieldname, prepended with gwolle_gb.
 *
 * @since 2.4.1
 */
function gwolle_gb_get_field_name( $field ) {

	if ( ! in_array( $field, array('name', 'city', 'email', 'website', 'honeypot', 'nonce') ) ) {
		return 'gwolle_gb_' . $field;
	}

	$blog_url = get_bloginfo('wpurl');
	$key = 'gwolle_gb_' . $field . '_field_name_' . $blog_url;
	$field_name = get_transient( $key );
	if ( false === $field_name ) {
		$field_name = wp_hash( $key, 'auth' );
		set_transient( $key, $field_name, DAY_IN_SECONDS );
	}
	$field_name = 'gwolle_gb_' . $field_name;

	return $field_name;
}
