<?php

namespace rdx\ethrly\commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends EthrlyCommand {

	protected function configure() {
		parent::configure();

		$this->setName('status');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$api = $this->api($input);

		$status = $api->status();

		echo implode($status) . "\n";
	}

}
