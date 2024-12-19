<?php

namespace rdx\ethrly\commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ToggleCommand extends EthrlyCommand {

	protected function configure() {
		parent::configure();

		$this->setName('toggle');
		$this->addOption('relay', null, InputOption::VALUE_REQUIRED);
		$this->addOption('on', null, InputOption::VALUE_NONE);
		$this->addOption('off', null, InputOption::VALUE_NONE);
	}

	protected function execute(InputInterface $input, OutputInterface $output) : int {
		$relay = $input->getOption('relay');
		if ( !$relay ) {
			echo "Need --relay option\n";
			return 1;
		}

		$on = $input->getOption('on');
		$off = $input->getOption('off');
		if ( !($on XOR $off) ) {
			echo "Need --on OR --off option\n";
			return 1;
		}

		$api = $this->api($input);

		$on = $on || !$off;

		$status = $api->status();
		echo "before:\n" . implode($status) . "\n\n";

		$status = $api->relay($relay, $on);

		$status = $api->status();
		echo "after:\n" . implode($status) . "\n";

		if ($output->isVerbose()) dump($api);

		return 0;
	}

}
