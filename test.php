<?php

use rdx\ethrly\Ethrly20;
use rdx\ethrly\Ethrly8;

require 'vendor/autoload.php';
require 'env.php';

header('Content-type: text/plain; charset=utf-8');

testToggle(Ethrly8::class, ETHRLY_TEST_IP, ETHRLY_TEST_PORT8);
testToggle(Ethrly20::class, ETHRLY_TEST_IP, ETHRLY_TEST_PORT20);

function testToggle($class, $ip, $port) {
	$refl = new ReflectionClass($class);

	echo $refl->getShortName() . " - $ip:$port...\n";
	$eth8 = new $class($ip, $port);
	$eth8->socket();

	$status = $eth8->status();
	echo implode($status) . "\n";

	$relay = rand(1, 8);
	$on = rand(0, 1);

	echo str_repeat(' ', $relay-1) . ($on ? '1' : '0') . "\n";
	$eth8->relay($relay, $on);

	$status = $eth8->status();
	echo implode($status) . "\n";

	echo "\n\n";
}
