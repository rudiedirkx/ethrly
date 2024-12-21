<?php

namespace rdx\ethrly\commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use rdx\ethrly\Ethrly1;

abstract class EthrlyCommand extends Command {

	protected function configure() {
		$this->addArgument('version', InputArgument::REQUIRED);
		$this->addArgument('ip', InputArgument::REQUIRED);
		$this->addArgument('port', InputArgument::REQUIRED);
		$this->addOption('password', 'p', InputOption::VALUE_OPTIONAL);
		$this->addOption('relays', 'r', InputOption::VALUE_OPTIONAL, '', 8);
		$this->addOption('timeout', 't', InputOption::VALUE_OPTIONAL, '', 3);
	}

	protected function api(InputInterface $input) : Ethrly1 {
		$ip = $input->getArgument('ip');
		$port = $input->getArgument('port');
		$password = $input->getOption('password');
		$relays = $input->getOption('relays');
		$timeout = $input->getOption('timeout');

		$class = 'rdx\ethrly\Ethrly' . $input->getArgument('version');
		return new $class($ip, $port, $relays, $timeout, $password);
	}

}
