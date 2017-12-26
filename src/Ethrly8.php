<?php

// https://www.robot-electronics.co.uk/htm/eth_rly02tech.htm
// https://www.robot-electronics.co.uk/htm/eth_rly16tech.htm
// https://www.robot-electronics.co.uk/htm/eth_rly16tech1.htm

namespace rdx\ethrly;

class Ethrly8 {

	const DEFAULT_PORT = 17494;
	const DEFAULT_RELAYS = 8;
	const DEFAULT_TIMEOUT = 5;

	// Necessary
	public $ip = '';
	public $port = 0;
	public $relays = 0;
	public $password = '';

	// Runtime
	public $timeout = 0;
	/** @var resource */
	public $socket;
	public $unlocked = null;
	public $error = '';
	public $errno = 0;
	public $version = null;

	// For logging
	public $id = 0;
	public $name = '';
	public $active = true;

	/**
	 * @param string $ip
	 * @param int $port
	 * @param int $timeout
	 * @param string $password
	 */
	public function __construct( $ip, $port = null, $relays = null, $timeout = null, $password = null ) {
		$this->ip = $ip;
		$this->port = $port ?: self::DEFAULT_PORT;
		$this->relays = $relays ?: self::DEFAULT_RELAYS;
		$this->password = $password;

		$this->timeout = $timeout ?: self::DEFAULT_TIMEOUT;
	}

	public function socket() {
		if ( $this->socket === null ) {
			$this->socket = @fsockopen($this->ip, $this->port, $this->errno, $this->error, $this->timeout) ?: false;
			if ( $this->socket ) {
				stream_set_timeout($this->socket, $this->timeout);

				$this->unlock();
			}
		}

		return $this->socket;
	}

	public function unlock() {
		// This version doesn't have password protection
	}

	public function close() {
		if ( $this->socket ) {
			@fclose($this->socket);
			$this->socket = null;
		}
	}



	public function write( $code, $binary = false ) {
		$bytes = (array) $code;
		if ( !$binary ) {
			$bytes = array_map('chr', $bytes);
		}

		@fwrite($this->socket(), implode($bytes));

		return $this->read();
	}

	public function read() {
		$bytes = @fread($this->socket(), $this->READ_BYTES());
		if ( $bytes === false || $bytes === '' ) {
			return array();
		}
		$decimals = array_map('ord', str_split($bytes));
		return $decimals;
	}


	public function version( $force = false ) {
		if ( $this->version === null || $force ) {
			$this->version = $this->write($this->VERSION_CODE()) ?: array();
		}

		return $this->version;
	}



	// @overridable
	public function getVersionString() {
		$version = $this->version();
		if ( !$version ) {
			return 'Unknown';
		}

		return "Software {$version[0]}";
	}

	// @overridable
	public function verifyVersion() {
		$version = $this->version();
		return count($version) == 1;
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

		$bits = array_slice($bits, 0, $this->relays);
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
	public function isPasswordProtected() {
		return null;
	}

	// @overridable
	public function READ_BYTES() {
		return 1;
	}

	// @overridable
	public function STATUS_CODE() {
		return 91;
	}

	// @overridable
	public function VERSION_CODE() {
		return 90;
	}



	public function dec201( $dec ) {
		$bin = array();
		for ( $i=7; $i>=0; $i-- ) {
			$on = 0 < ($dec & pow(2, $i));
			$bin[$i+1] = (int) $on;
		}

		ksort($bin, SORT_NUMERIC);
		return $bin;
	}

}
