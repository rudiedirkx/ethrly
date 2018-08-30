<?php

use rdx\ethrly\Ethrly1;

require 'vendor/autoload.php';
$instances = require 'env.php';

header('Content-type: text/plain; charset=utf-8');

foreach ( $instances as $eth ) {
	$eth->socket();

	var_dump($eth->getVersionString());
	echo "\n";

	// Show relays
	$status = $eth->status();
	echo implode(' ', str_split(implode($status), 4)) . "\n";

	// // Turn all OFF
	for ( $relay = 1; $relay <= $eth->relays; $relay++ ) {
		$eth->relay($relay, false);
	}

	sleep(1);

	// Show relays
	$status = $eth->status();
	echo implode(' ', str_split(implode($status), 4)) . "\n";

	sleep(1);

	// Turn all ON
	for ( $relay = 1; $relay <= $eth->relays; $relay++ ) {
		$eth->relay($relay, true);
	}

	sleep(1);

	// Show relays
	$status = $eth->status();
	echo implode(' ', str_split(implode($status), 4)) . "\n";

	echo "\n\n\n";
}
