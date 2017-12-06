<?php

// https://www.robot-electronics.co.uk/htm/eth002tech.htm
// https://www.robot-electronics.co.uk/htm/eth008tech.htm
// https://www.robot-electronics.co.uk/htm/eth044tech.htm
// https://www.robot-electronics.co.uk/htm/eth0621tech.htm
// https://www.robot-electronics.co.uk/htm/eth484tech.htm
// https://www.robot-electronics.co.uk/htm/eth8020tech.htm

namespace rdx\ethrly;

class Ethrly20 extends Ethrly8 {

	public function relay( $relay, $on ) {
		return $this->write(array($on ? 32 : 33, $relay, 0));
	}

	public function on( $relays = null ) {
		// Turn all ON
		if ( $relays === null ) {
			return $this->write(array(35, 255, 255, 255));
		}

		// Turn some ON
		foreach ( (array)$relays AS $n ) {
			$this->relay($n, true);
		}
	}

	public function off( $relays = null ) {
		// Turn all OFF
		if ( $relays === null ) {
			return $this->write(array(35, 0, 0, 0));
		}

		// Turn some OFF
		foreach ( (array)$relays AS $n ) {
			$this->relay($n, false);
		}
	}

	public function verifyVersion() {
		$version = $this->version();
		return count($version) == 3;
	}

	// @overridable
	public function getVersionString() {
		$version = $this->version();
		if ( !$version ) {
			return 'Unknown';
		}

		return "Module {$version[0]}; Hardware {$version[1]}; Software {$version[2]}";
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

	public function RELAYS() {
		return 20;
	}

	public function STATUS_CODE() {
		return 36;
	}

	public function VERSION_CODE() {
		return 16;
	}

}
