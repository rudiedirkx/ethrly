<?php

// http://www.robot-electronics.co.uk/files/dS2824.pdf

namespace rdx\ethrly;

class Ethrly3 extends Ethrly1 {

	public function relay( $relay, $on ) {
		return $this->write(array(49, $relay, $on ? 1 : 0, 0, 0, 0, 0));
	}

	public function verifyVersion() {
		$version = $this->version();
		return count($version) == 8;
	}

	public function getVersionString() {
		$version = $this->version();
		if ( !$version ) {
			return '[DS?] Unknown';
		}

		$V = number_format($version[5] / 10, 1);
		return "[DS] Module {$version[0]}; System {$version[1]}.{$version[2]}; Application {$version[3]}.{$version[4]}; {$V}V";
	}

	public function VERSION_CODE() {
		return 48;
	}

	public function STATUS_CODE() {
		return 51;
	}

	public function STATUS_OFFSET() {
		return 1;
	}

	public function READ_BYTES() {
		return 16;
	}

}
