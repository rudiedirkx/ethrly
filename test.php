<?php

use rdx\ethrly\Ethrly1;
use rdx\ethrly\Ethrly2;

require 'vendor/autoload.php';
require 'env.php';

header('Content-type: text/plain; charset=utf-8');

testToggle(Ethrly1::class, ETHRLY_TEST_IP, ETHRLY_TEST_PORT1, ETHRLY_TEST_PASS);
testToggle(Ethrly2::class, ETHRLY_TEST_IP, ETHRLY_TEST_PORT2, ETHRLY_TEST_PASS);

function testToggle($class, $ip, $port, $pass = null) {
	if ( $port === 0 ) return;

	$relays = 8;

	$refl = new ReflectionClass($class);

	echo $refl->getShortName() . " - $ip:$port...\n";
	$eth = new $class($ip, $port, $relays, null, $pass);
	$eth->socket();

	$status = $eth->status();
	echo implode($status) . "\n";

	$relay = rand(1, $relays);
	$on = rand(0, 1);

	echo str_repeat(' ', $relay-1) . ($on ? '1' : '0') . "\n";
	$eth->relay($relay, $on);

	$status = $eth->status();
	echo implode($status) . "\n";

	echo "\n\n";
}
