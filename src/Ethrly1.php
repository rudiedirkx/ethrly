<?php

// https://www.robot-electronics.co.uk/htm/eth_rly02tech.htm
// https://www.robot-electronics.co.uk/htm/eth_rly16tech.htm
// https://www.robot-electronics.co.uk/htm/eth_rly16tech1.htm

namespace rdx\ethrly;

class Ethrly1 {

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

				if ( $this->socketTimedOut() ) {
					$this->error = 'Timeout after socket open';
					$this->socket = false;
				}
				else {
					$this->unlock();

					if ( $this->socketTimedOut() ) {
						$this->error = 'Timeout after unlock';
						$this->socket = false;
					}
				}
			}
		}

		return $this->socket;
	}

	protected function socketTimedOut() {
		if ( $this->socket ) {
			$properties = stream_get_meta_data($this->socket);
			if ( isset($properties['timed_out']) ) {
				return $properties['timed_out'];
			}
		}

		return false;
	}

	protected function unlock() {
		// This version doesn't have password protection
	}

	public function close() {
		if ( $this->socket ) {
			@fclose($this->socket);
			$this->socket = null;
		}
	}


	protected function encryptBytes( $bytes ) {
		return $bytes;
	}

	protected function decryptBytes( $bytes ) {
		return $bytes;
	}

	protected function write( $code, $convert = true ) {
		$bytes = (array) $code;
		if ( $convert ) {
			$bytes = array_map('chr', $bytes);
		}

		$write = $this->encryptBytes(implode($bytes));
		@fwrite($this->socket(), $write);

		return $this->read();
	}

	protected function read() {
		$bytes = @fread($this->socket(), $this->READ_BYTES());
		if ( $bytes === false || $bytes === '' ) {
			return [];
		}

		$read = $this->decryptBytes($bytes);
		$decimals = array_map('ord', str_split($read));
		return $decimals;
	}


	public function version( $force = false ) {
		if ( $this->version === null || $force ) {
			$this->version = $this->write($this->VERSION_CODE()) ?: [];
		}

		return $this->version;
	}



	public function getVersionString() {
		$version = $this->version();
		if ( !$version ) {
			return '[ETH1?] Unknown';
		}

		return "[ETH1] Software {$version[0]}";
	}

	public function status() {
		$bytes = $this->write($this->STATUS_CODE());
		if ( !$bytes ) {
			return [];
		}

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

	public function relay( $relay, $on ) {
		$code = 100 + $relay + ( $on ? 0 : 10 );

		$rsp = $this->write($code);

		return $this->isACK($rsp);
	}

	protected function isACK( $bytes ) {
		return isset($bytes[0]) && $bytes[0] === 0;
	}

	protected function READ_BYTES() {
		return 1;
	}

	protected function STATUS_CODE() {
		return 91;
	}

	protected function VERSION_CODE() {
		return 90;
	}



	protected function dec201( $dec ) {
		$bin = [];
		for ( $i=7; $i>=0; $i-- ) {
			$on = 0 < ($dec & pow(2, $i));
			$bin[$i+1] = (int) $on;
		}

		ksort($bin, SORT_NUMERIC);
		return $bin;
	}

}
