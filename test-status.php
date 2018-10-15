<?php

use rdx\ethrly\Ethrly1;

require 'vendor/autoload.php';
$instances = require 'env.php';

header('Content-type: text/plain; charset=utf-8');

foreach ( $instances as $eth ) {
	testToggle($eth);
}

function testToggle(Ethrly1 $eth) {
	$eth->socket();

	var_dump($eth->getVersionString());

	$status = $eth->status();
	echo implode(' ', str_split(implode($status), 4)) . "\n";

	echo "\n\n";
}
