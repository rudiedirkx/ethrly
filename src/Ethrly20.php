<?php

// https://www.robot-electronics.co.uk/htm/eth002tech.htm
// https://www.robot-electronics.co.uk/htm/eth008tech.htm
// https://www.robot-electronics.co.uk/htm/eth044tech.htm
// https://www.robot-electronics.co.uk/htm/eth0621tech.htm
// https://www.robot-electronics.co.uk/htm/eth484tech.htm
// https://www.robot-electronics.co.uk/htm/eth8020tech.htm

namespace rdx\ethrly;

class Ethrly20 extends Ethrly {

	function relay( $relay, $on ) {
		return $this->write(array($on ? 32 : 33, $relay, 0));
	}

	function on( $relays = null ) {
		// Turn all ON
		if ( $relays === null ) {
			return $this->write(array(35, 255, 255, 255));
		}

		// Turn some ON
		foreach ( (array)$relays AS $n ) {
			$this->relay($n, true);
		}
	}

	function off( $relays = null ) {
		// Turn all OFF
		if ( $relays === null ) {
			return $this->write(array(35, 0, 0, 0));
		}

		// Turn some OFF
		foreach ( (array)$relays AS $n ) {
			$this->relay($n, false);
		}
	}

	function READ_BYTES() {
		return 6;
	}

	function RELAYS() {
		return 20;
	}

	function STATUS_CODE() {
		return 36;
	}

}
