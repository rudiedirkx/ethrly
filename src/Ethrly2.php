<?php

// https://www.robot-electronics.co.uk/htm/eth002tech.htm
// https://www.robot-electronics.co.uk/htm/eth008tech.htm
// https://www.robot-electronics.co.uk/htm/eth044tech.htm
// https://www.robot-electronics.co.uk/htm/eth0621tech.htm
// https://www.robot-electronics.co.uk/htm/eth484tech.htm
// https://www.robot-electronics.co.uk/htm/eth8020tech.htm

namespace rdx\ethrly;

class Ethrly2 extends Ethrly1 {

	public function unlock() {
		if ( $this->password !== null ) {
			$bytes = str_split($this->password);
			array_unshift($bytes, chr(121));

			$rsp = $this->write($bytes, false);
			if ( $rsp === array(1) ) {
				$this->isPasswordProtected();
				return $this->unlocked = true;
			}

			return $this->unlocked = false;
		}
	}

	public function relay( $relay, $on ) {
		return $this->write(array($on ? 32 : 33, $relay, 0));
	}

	public function verifyVersion() {
		$version = $this->version();
		return count($version) == 3;
	}

	public function getVersionString() {
		$version = $this->version();
		if ( !$version ) {
			return '[ETH2?] Unknown';
		}

		return "[ETH2] Module {$version[0]}; Hardware {$version[1]}; Software {$version[2]}";
	}

	public function isPasswordProtected() {
		$locked = $this->write(122);
		if ( !$locked ) {
			return null;
		}

		$locked = $locked[0];
		return $locked == 0;
	}

	public function READ_BYTES() {
		return 6;
	}

	public function STATUS_CODE() {
		return 36;
	}

	public function VERSION_CODE() {
		return 16;
	}

}
