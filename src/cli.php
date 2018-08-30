<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use rdx\ethrly\commands\StatusCommand;
use rdx\ethrly\commands\ToggleCommand;
use rdx\ethrly\commands\VersionCommand;

require __DIR__ . '/../vendor/autoload.php';

$app = new Application;

$app->add(new VersionCommand);
$app->add(new StatusCommand);
$app->add(new ToggleCommand);

$app->run();
