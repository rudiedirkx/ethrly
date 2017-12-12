<?php

use rdx\ethrly\Ethrly20;
use rdx\ethrly\Ethrly8;

require 'vendor/autoload.php';
require 'env.php';

header('Content-type: text/plain; charset=utf-8');

testToggle(Ethrly8::class, ETHRLY_TEST_IP, ETHRLY_TEST_PORT8, ETHRLY_TEST_PASS);
testToggle(Ethrly20::class, ETHRLY_TEST_IP, ETHRLY_TEST_PORT20, ETHRLY_TEST_PASS);

function testToggle($class, $ip, $port, $pass = null) {
	if ( $port === 0 ) return;

	$refl = new ReflectionClass($class);

	echo $refl->getShortName() . " - $ip:$port...\n";
	$eth = new $class($ip, $port, null, $pass);
	$eth->socket();

	$status = $eth->status();
	echo implode($status) . "\n";

	$relay = rand(1, 8);
	$on = rand(0, 1);

	echo str_repeat(' ', $relay-1) . ($on ? '1' : '0') . "\n";
	$eth->relay($relay, $on);

	$status = $eth->status();
	echo implode($status) . "\n";

	echo "\n\n";
}
