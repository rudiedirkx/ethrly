<?php

// https://www.robot-electronics.co.uk/htm/eth002tech.htm
// https://www.robot-electronics.co.uk/htm/eth008tech.htm
// https://www.robot-electronics.co.uk/htm/eth044tech.htm
// https://www.robot-electronics.co.uk/htm/eth0621tech.htm
// https://www.robot-electronics.co.uk/htm/eth484tech.htm
// https://www.robot-electronics.co.uk/htm/eth8020tech.htm

namespace rdx\ethrly;

class Ethrly2 extends Ethrly1 {

	protected function unlock() {
		if ( $this->password !== null ) {
			$bytes = str_split($this->password);
			array_unshift($bytes, chr(121));

			$rsp = $this->write($bytes, false);
			if ( $rsp === [1] ) {
				$this->isPasswordProtected();
				return $this->unlocked = true;
			}

			return $this->unlocked = false;
		}
	}

	public function relay( $relay, $pulse ) {
		$on = $pulse !== false && $pulse !== 0;
		$pulse = $on && is_int($pulse) && $pulse >= 100 ? round($pulse/100) : 0;

		$rsp = $this->write([$on ? 32 : 33, $relay, $pulse]);

		return $this->isACK($rsp);
	}

	public function getVersionString() {
		$version = $this->version();
		if ( !$version ) {
			return '[ETH2?] Unknown';
		}

		return "[ETH2] Module {$version[0]}; Hardware {$version[1]}; Software {$version[2]}";
	}

	protected function READ_BYTES() {
		return 6;
	}

	protected function STATUS_CODE() {
		return 36;
	}

	protected function VERSION_CODE() {
		return 16;
	}

}
