<?php

namespace rdx\ethrly\commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VersionCommand extends EthrlyCommand {

	protected function configure() {
		parent::configure();

		$this->setName('version');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$api = $this->api($input);

		$version = $api->getVersionString();

		echo "$version\n";
	}

}
