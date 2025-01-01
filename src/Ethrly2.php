<?php

// https://www.robot-electronics.co.uk/htm/eth002tech.htm
// https://www.robot-electronics.co.uk/htm/eth008tech.htm
// https://www.robot-electronics.co.uk/htm/eth044tech.htm
// https://www.robot-electronics.co.uk/htm/eth0621tech.htm
// https://www.robot-electronics.co.uk/htm/eth484tech.htm
// https://www.robot-electronics.co.uk/htm/eth8020tech.htm

namespace rdx\ethrly;

class Ethrly2 extends Ethrly1 {

	protected function unlock() : void {
		if ( $this->unlocked ) {
			return;
		}

		if ( !$this->password ) {
			return;
		}

		$bytes = str_split($this->password);
		$bytes = array_map(ord(...), $bytes);
		array_unshift($bytes, 121);

		$rsp = $this->write($bytes);
		if ( $rsp === [1] ) {
			$this->unlocked = true;
		}
	}

	public function relay( int $relay, int|bool $pulse ) : bool {
		$on = $pulse !== false && $pulse !== 0;
		$pulse = $on && is_int($pulse) && $pulse >= 100 ? round($pulse/100) : 0;

		$rsp = $this->write([$on ? 32 : 33, $relay, $pulse]);

		return $this->isACK($rsp);
	}

	public function getVersionString() : string {
		$version = $this->version();
		if ( !$version ) {
			return '[ETH2?] Unknown';
		}

		$module = self::MODULES[ $version[0] ] ?? 'ETH ' . $version[0] . '?';

		return "[$module] Hardware {$version[1]}; Software {$version[2]}";
	}

	protected function READ_BYTES() : int {
		return 6;
	}

	protected function STATUS_CODE() : int {
		return 36;
	}

	protected function VERSION_CODE() : int {
		return 16;
	}

}
