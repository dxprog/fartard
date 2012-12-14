<?php

$genStart = microtime(true);

$conn = new Mongo('mongodb:///tmp/mongodb-27017.sock');

date_default_timezone_set('America/Chicago');

$db = $conn->gnm;
$analytics = $db->analytics;

$start = strtotime('-5 min');
$query = [ 'date' => [ '$gte' => $start ]];

$cursor = $analytics->find($query);
echo 'Visitors in the last 5 minutes: ', $cursor->count(), '<br />';
$query = [ 'distinct' => 'analytics', 'key' => 'person_ident' ];
echo 'Unique users: ', count($db->command($query)), '<br />';

echo '<br />', '--- Browsers ---', '<br />';
$query = [ 'distinct' => 'analytics', 'key' => 'browser' ];
$browsers = $db->command($query);
$temp = [];

foreach ($browsers['values'] as $browser) {

	$query = $analytics->find([ 'browser' => $browser, 'date' => [ '$gte' => $start ] ]);
	$count = $query->count();
	if ($count > 0) {
		$browser = split(' ', $browser)[0];
		if (!isset($temp[$browser])) {
			$temp[$browser] = 0;
		}
		$temp[$browser] += $count;
	}

}

arsort($temp);
foreach ($temp as $key => $val) {
	echo $key, ' = ', $val, '<br />';
}

echo '<br />', '--- Pages ---', '<br />';
$query = [ 'distinct' => 'analytics', 'key' => 'page' ];
$pages = $db->command($query);
$temp = [];

foreach ($pages['values'] as $page) {

	$query = $analytics->find([ 'page' => $page, 'date' => [ '$gte' => $start ] ]);
	$count = $query->count();
	if ($count > 0) {
		$temp[$page] = $count;
	}

}

arsort($temp);
foreach ($temp as $key => $val) {
	echo $key, ' = ', $val, '<br />';
}

echo '<br/><br/>gathered in ', microtime(true) - $genStart, 's';