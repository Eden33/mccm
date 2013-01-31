<?php

function get_rand_string($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789') {
	$str = '';
	$count = strlen($charset);
	while ($length-- > 0) {
		$str .= $charset[mt_rand(0, $count-1)];
	}
	return $str;
}
?>