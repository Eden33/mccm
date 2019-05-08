<?php

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * gwolle_gb_pagination_admin
 * Pagination of the entries for the page-entries.php
 *
 * @param int $pageNum the number of the requested page.
 * @param int $pages_total the total number of pages.
 * @param int $count total number of entries. Relative to the $show variable.
 * @param string $show the tab of the page that is shown.
 * @return string $pagination the html of the pagination.
 */
function gwolle_gb_pagination_admin( $pageNum, $pages_total, $count, $show ) {

	$num_entries = (int) get_option('gwolle_gb-entries_per_page', 20);

	$book_id = 0;
	if ( isset( $_GET['book_id'] ) ) {
		$book_id = (int) $_GET['book_id'];
	}

	// Calculate written text with info "Showing 1 – 25 of 54"
	if ($count == 0) {
		$firstentry = 0;
		$lastentry = 0;
	} else {
		$firstentry = ($pageNum - 1) * $num_entries + 1;
		$total_on_this_page = $count - ( ($pageNum - 1) * $num_entries );
		if ( $total_on_this_page > $num_entries ) {
			$total_on_this_page = $num_entries;
		}
		$lastentry = $firstentry + $total_on_this_page -1;
	}

	$pagination = '
				<h2 class="screen-reader-text">' . esc_html__('Guestbook list navigation', 'gwolle-gb') . '</h2>
				<div class="tablenav-pages">';

	$highDotsMade = false;
	$pages_done = array();

	$pagination .= '<span class="displaying-num">' . esc_html__('Showing:', 'gwolle-gb') .
		' ' . $firstentry . ' &#8211; ' . $lastentry . ' ' . esc_html__('of', 'gwolle-gb') . ' ' . $count . '</span>
		';


	if ($pageNum > 1) {
		$link = admin_url( 'admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&show=' . $show . '&pageNum=' . round($pageNum - 1) . '&book_id=' . $book_id );
		$pagination .= '<a class="first page-numbers button" href="' . $link . '" rel="prev">&larr;</a>';
	}

	if ($pageNum < 5) {
		$showRange = 5;
		if ($pages_total < 6) {
			$showRange = $pages_total;
			$highDotsMade = true; // no need for highdots.
		}
		for ($i = 1; $i < ($showRange + 1); $i++) {
			if ($i == $pageNum) {
				if ( in_array( $i, $pages_done ) ) { continue; }
				$pagination .= '<span class="page-numbers current">' . $i . '</span>';
				$pages_done[] = $i;
			} else {
				if ( in_array( $i, $pages_done ) ) { continue; }
				$link = admin_url( 'admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&show=' . $show . '&pageNum=' . $i . '&book_id=' . $book_id );
				$pagination .= '<a class="page-numbers button" href="' . $link . '">' . $i . '</a>';
				$pages_done[] = $i;
				if ( $i == $pages_total ) { break; }
			}
		}

		if ( ($pageNum + 4 < $pages_total) && ( ! $highDotsMade) ) {
			$pagination .= '<span class="page-numbers dots">...</span>';
			$highDotsMade = true;
		}
	} elseif ($pageNum > 4) {
		$link = admin_url( 'admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&show=' . $show . '&pageNum=1&book_id=' . $book_id );
		$pagination .= '<a class="page-numbers button" href="' . $link . '">1</a>';
		if ($pages_total > 4) {
			$pagination .= '<span class="page-numbers dots">...</span>';
		}
		if ($pageNum + 2 < $pages_total) {
			$minRange = $pageNum - 2;
			$showRange = $pageNum + 2;
		} else {
			$minRange = $pageNum - 3;
			$showRange = $pages_total - 1;
		}
		for ($i = $minRange; $i <= $showRange; $i++) {
			if ($i == $pageNum) {
				$pagination .= '<span class="page-numbers button current">' . $i . '</span>';
			} else {
				$link = admin_url( 'admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&show=' . $show . '&pageNum=' . $i . '&book_id=' . $book_id );
				$pagination .= '<a class="page-numbers button" href="' . $link . '">' . $i . '</a>';
			}
		}
		if ($pageNum == $pages_total) {
			$pagination .= '<span class="page-numbers button current">' . $pageNum . '</span>';
		}
	}

	if ($pageNum < $pages_total) {
		if ( ($pageNum + 3 < $pages_total) && ( ! $highDotsMade) ) {
			$pagination .= '<span class="page-numbers dots">...</span>';
			$highDotsMade = true;
		}
		if ( ! in_array( $pages_total, $pages_done ) ) {
			$link = admin_url( 'admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&show=' . $show . '&pageNum=' . $pages_total . '&book_id=' . $book_id );
			$pagination .= '<a class="page-numbers button" href="' . $link . '">' . $pages_total . '</a>';
		}
		$link = admin_url( 'admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&show=' . $show . '&pageNum=' . round($pageNum + 1) . '&book_id=' . $book_id );
		$pagination .= '<a class="last page-numbers button" href="' . $link . '" rel="next">&rarr;</a>';
	}

	$pagination .= '</div>';

	return $pagination;

}
