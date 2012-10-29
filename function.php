<?php
function get_fetcher_url() {
	$protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
	$url = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$len = strlen(strrchr($url, '/'));
	return substr($url, 0, count($url)-$len).'server-status-fetcher.php?server_index=';
}
?>