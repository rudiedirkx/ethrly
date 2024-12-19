<?php

// https://www.robot-electronics.co.uk/htm/eth_rly02tech.htm
// https://www.robot-electronics.co.uk/htm/eth_rly16tech.htm
// https://www.robot-electronics.co.uk/htm/eth_rly16tech1.htm

namespace rdx\ethrly;

class Ethrly1 {

	public const DEFAULT_PORT = 17494;
	public const DEFAULT_RELAYS = 8;
	public const DEFAULT_TIMEOUT = 5;

	// Necessary
	public int $port;
	public int $relays;
	public float $timeout;

	// Runtime
	/** @var resource */
	public $socket;
	/** @var resource */
	public $origSocket;
	public ?bool $unlocked = null;
	public string $error = '';
	public int $errno = 0;
	public ?array $version = null;

	// For logging
	public $id = 0;
	public $name = '';
	public $active = true;

	public function __construct(
		public string $ip,
		?int $port = null,
		?int $relays = null,
		?float $timeout = null,
		public ?string $password = null,
	) {
		$this->port = $port ?? self::DEFAULT_PORT;
		$this->relays = $relays ?? self::DEFAULT_RELAYS;
		$this->timeout = $timeout ?? self::DEFAULT_TIMEOUT;
	}

	public function socket() {
		if ( $this->socket === null ) {
			$this->socket = @fsockopen($this->ip, $this->port, $this->errno, $this->error, $this->timeout) ?: false;
			if ( $this->socket ) {
				stream_set_timeout($this->socket, $this->timeout);

				if ( $this->socketTimedOut() ) {
					$this->error = 'Timeout after socket open';
					$this->origSocket = $this->socket;
					$this->socket = false;
				}
				else {
					$this->unlock();

					if ( $this->socketTimedOut() ) {
						$this->error = 'Timeout after unlock';
						$this->origSocket = $this->socket;
						$this->socket = false;
					}
				}
			}
		}

		return $this->socket;
	}

	protected function socketTimedOut() : bool {
		if ( $this->socket ) {
			$properties = stream_get_meta_data($this->socket);
			if ( isset($properties['timed_out']) ) {
				return (bool) $properties['timed_out'];
			}
		}

		return false;
	}

	protected function unlock() : void {
		// This version doesn't have password protection
	}

	public function close() : void {
		if ( $this->socket ) {
			@fclose($this->socket);
			$this->socket = null;
		}
		if ( $this->origSocket ) {
			@fclose($this->origSocket);
			$this->origSocket = null;
		}
	}


	protected function encryptBytes( string $bytes ) : string {
		return $bytes;
	}

	protected function decryptBytes( string $bytes ) : string {
		return $bytes;
	}

	/**
	 * @param int|list<int> $code
	 * @return int|list<int>
	 */
	protected function write( int|array $code, bool $convert = true ) : array {
		$bytes = (array) $code;
		if ( $convert ) {
			$bytes = array_map('chr', $bytes);
		}

		if ( $this->socket() ) {
			$write = $this->encryptBytes(implode($bytes));
			@fwrite($this->socket(), $write);
		}

		return $this->read();
	}

	/**
	 * @return int|list<int>
	 */
	protected function read() : array {
		if (!$this->socket()) {
			return [];
		}

		$bytes = @fread($this->socket(), $this->READ_BYTES());
		if ( $bytes === false || $bytes === '' ) {
			return [];
		}

		$read = $this->decryptBytes($bytes);
		$decimals = array_map('ord', str_split($read));
		return $decimals;
	}


	/**
	 * @return int|list<int>
	 */
	public function version( bool $force = false ) : array {
		if ( $this->version === null || $force ) {
			$this->version = $this->write($this->VERSION_CODE()) ?: [];
		}

		return $this->version;
	}



	public function getVersionString() : string {
		$version = $this->version();
		if ( !$version ) {
			return '[ETH1?] Unknown';
		}

		return "[ETH1] Software {$version[0]}";
	}

	/**
	 * @return list<0|1>
	 */
	public function status() : array {
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

	public function relay( int $relay, int|bool $on ) : bool {
		$code = 100 + $relay + ( $on ? 0 : 10 );

		$rsp = $this->write($code);

		return $this->isACK($rsp);
	}

	protected function isACK( string $bytes ) : bool {
		return isset($bytes[0]) && $bytes[0] === 0;
	}

	protected function READ_BYTES() : int {
		return 1;
	}

	protected function STATUS_CODE() : int {
		return 91;
	}

	protected function VERSION_CODE() : int {
		return 90;
	}



	/**
	 * @return list<0|1>
	 */
	protected function dec201( int $dec ) : array {
		$bin = [];
		for ( $i=7; $i>=0; $i-- ) {
			$on = 0 < ($dec & pow(2, $i));
			$bin[$i+1] = (int) $on;
		}

		ksort($bin, SORT_NUMERIC);
		return $bin;
	}

}
