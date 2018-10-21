<?php

require 'vendor/autoload.php';
$instances = require 'env.php';

header('Content-type: text/plain; charset=utf-8');

$on = !empty($_GET['on']);
$relays = [24, 23, 22, 21];

foreach ( $instances as $ethrly ) {
	// $ethrly->version();

	foreach ( $relays as $relay ) {
		echo "$relay: \n";
		var_dump($ethrly->relay($relay, $on));
		echo "\n";
		usleep(200000);
	}
}

echo "\n\nstatus: \n";
echo implode(' ', str_split(implode($ethrly->status()), 4)) . "\n";
