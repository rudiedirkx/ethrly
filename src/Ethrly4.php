<?php

// http://www.robot-electronics.co.uk/files/dS2824.pdf
// Software DS 3.x/4.x

namespace rdx\ethrly;

class Ethrly4 extends Ethrly3 {

	protected function extractStatusBytes( array $bytes ) : array {
		// Skip 1, then take 4, no padding necessary
		return array_slice($bytes, 1, 4);
	}

}
