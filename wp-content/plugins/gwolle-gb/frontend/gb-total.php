<?php
/*
 *
 */


// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * Display the total number of entries.
 *
 * @param string $html html content of the filter.
 *        array $args the parameters of the query for visible entries
 * @return array
 *
 * @since 2.3.2
 */
function gwolle_gb_addon_get_total_entries( $html, $args ) {
	$key = 'gwolle_gb_frontend_pagination_book_' . $args['book_id'];
	$entries_total = get_transient( $key );
	if ( false === $entries_total ) {
		$entries_total = gwolle_gb_get_entry_count(
			array(
				'checked' => 'checked',
				'trash'   => 'notrash',
				'spam'    => 'nospam',
				'book_id' => $args['book_id']
			)
		);
		set_transient( $key, $entries_total, DAY_IN_SECONDS );
	}
	$html .= '<div id="gwolle-gb-total">' .
		sprintf( _n( '%d entry.', '%d entries.', $entries_total, 'gwolle-gb' ), $entries_total )
		. '</div>';
	return $html;
}
add_filter( 'gwolle_gb_entries_list_before', 'gwolle_gb_addon_get_total_entries', 8, 2 );
