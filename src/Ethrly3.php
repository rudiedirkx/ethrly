<?php

// http://www.robot-electronics.co.uk/files/dS2824.pdf

namespace rdx\ethrly;

class Ethrly3 extends Ethrly1 {

	// @overridable
	public function status() {
		// Ask relay 1, get all relays
		$bytes = $this->write([$this->STATUS_CODE(), 1]);
		if ( !$bytes ) {
			return [];
		}

		// First byte is for relay 1
		$bytes = array_slice($bytes, 1);
		$bytes = array_values(array_reverse($bytes));

		$bits = [];
		foreach ( $bytes as $byte ) {
			$bits = array_merge($bits, $this->dec201($byte));
		}

		$bits = array_slice($bits, 0, $this->relays);
		if ( $bits ) {
			return array_combine(range(1, count($bits)), $bits);
		}

		return [];
	}

	public function relay( $relay, $pulse ) {
		$on = $pulse !== false && $pulse !== 0;
		$pulse = $on && is_int($pulse) && $pulse >= 100 ? array_reverse(unpack('C*', pack('L', $pulse))) : [0, 0, 0, 0];
		return $this->write(array_merge([49, $relay, $on ? 1 : 0], $pulse));
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

	public function READ_BYTES() {
		return 16;
	}

}
