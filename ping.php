<?php

/**
 * Fucking-Awesome-Real-Time-Analytic-Display
 */

require('phpUserAgentStringParser.php');
 
date_default_timezone_set('America/Chicago');

$conn = new Mongo('mongodb:///tmp/mongodb-27017.sock');
$db = $conn->gnm;

$analytics = $db->analytics;

$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;

if ($ua && $referer) {
	$parser = new phpUserAgentStringParser();
	$ident = isset($_COOKIE['GnmTracker']) ? $_COOKIE['GnmTracker'] : null;
	if (!$ident) {
		$ident = uniqid();
		setcookie('GnmTracker', $ident, time() + 2592000);
	}
	$ua = $parser->parse($ua);
	$os = $ua['operating_system'];
	$ua = $ua['browser_name'] . ' ' . $ua['browser_version'];
	$insert = [
		'browser' => $ua,
		'os' => $os,
		'page' => strtolower(parse_url($referer, PHP_URL_PATH)),
		'date' => time(),
		'site' => strtolower(parse_url($referer, PHP_URL_HOST)),
		'person_ident' => $ident
	];
	$analytics->insert($insert);
}

header('Content-Type: image/gif');