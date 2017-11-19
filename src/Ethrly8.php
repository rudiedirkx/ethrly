<?php

// https://www.robot-electronics.co.uk/htm/eth_rly02tech.htm
// https://www.robot-electronics.co.uk/htm/eth_rly16tech.htm
// https://www.robot-electronics.co.uk/htm/eth_rly16tech1.htm

namespace rdx\ethrly;

class Ethrly8 {

	const DEFAULT_PORT = 17494;

	// Necessary
	public $ip = '';
	public $port = 0;

	// Runtime
	public $timeout = 1;
	/** @var resource */
	public $socket;
	public $error = '';
	public $errno = 0;

	public function __construct( $ip, $port = null, $timeout = 5 ) {
		$this->ip = $ip;
		$this->port = $port ?: DEFAULT_PORT;

		$this->timeout = $timeout;
	}

	public function socket() {
		if ( $this->socket === null ) {
			$this->socket = @fsockopen($this->ip, $this->port, $this->errno, $this->error, $this->timeout) ?: false;
			if ( $this->socket ) {
				stream_set_timeout($this->socket, $this->timeout);
			}
		}

		return $this->socket;
	}

	public function close() {
		if ( $this->socket ) {
			@fclose($this->socket);
			$this->socket = null;
		}
	}



	protected function write( $code ) {
		$bytes = array_map('chr', (array)$code);

		@fwrite($this->socket(), implode($bytes));

		return $this->read();
	}

	protected function read() {
		$bytes = @fread($this->socket(), $this->READ_BYTES());
		if ( $bytes === false || $bytes === '' ) {
			return array();
		}
		$decimals = array_map('ord', str_split($bytes));
		return $decimals;
	}



	// @overridable
	public function status() {
		$bytes = $this->write($this->STATUS_CODE());
		if ( !$bytes ) {
			return array();
		}

		$bits = array();
		foreach ($bytes as $byte) {
			$bits = array_merge($bits, $this->dec201($byte));
		}

		$bits = array_slice($bits, 0, $this->RELAYS());
		return array_combine(range(1, count($bits)), $bits);
	}

	// @overridable
	public function relay( $relay, $on ) {
		$code = 100 + $relay + ( $on ? 0 : 10 );

		return $this->write($code);
	}

	// @overridable
	public function on( $relays = null ) {
		// Turn all ON
		if ( $relays === null ) {
			return $this->write(100);
		}

		// Turn some ON
		foreach ( (array)$relays AS $n ) {
			$this->relay($n, true);
		}
	}

	// @overridable
	public function off( $relays = null ) {
		// Turn all OFF
		if ( $relays === null ) {
			return $this->write(110);
		}

		// Turn some OFF
		foreach ( (array)$relays AS $n ) {
			$this->relay($n, false);
		}
	}

	// @overridable
	protected function READ_BYTES() {
		return 1;
	}

	// @overridable
	protected function RELAYS() {
		return 8;
	}

	// @overridable
	protected function STATUS_CODE() {
		return 91;
	}



	protected function dec201( $dec ) {
		$bin = array();
		for ( $i=7; $i>=0; $i-- ) {
			$on = 0 < ($dec & pow(2, $i));
			$bin[$i+1] = (int) $on;
		}

		ksort($bin, SORT_NUMERIC);
		return $bin;
	}

}
