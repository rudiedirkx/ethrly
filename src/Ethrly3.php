<?php

// http://www.robot-electronics.co.uk/files/dS2824.pdf
// Software 2.x

namespace rdx\ethrly;

class Ethrly3 extends Ethrly1 {

	protected $lastNonceSent = 0;
	protected $lastNonceReceived = 0;

	protected function unlock() : void {
		if ( $this->unlocked ) {
			return;
		}

		// Get first nonce
		$this->version();
		$this->unlocked = true;
	}

	protected function encryptBytes( string $bytes ) : string {
		if ( strlen($this->password ?? '') != 32 ) {
			return $bytes;
		}

		$bytes = str_pad($bytes, 12, chr(0), STR_PAD_RIGHT);
		$this->lastNonceSent = $this->lastNonceReceived ?: rand();
		$bytes .= pack('L', $this->lastNonceSent);

		$cipher = 'aes-256-cbc';
		$ivlen = openssl_cipher_iv_length($cipher);
		$iv = openssl_random_pseudo_bytes($ivlen);
		$enc = openssl_encrypt($bytes, $cipher, $this->password, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
		$output = "$iv$enc";

		return $output;
	}

	protected function decryptBytes( string $bytes ) : string {
		if ( strlen($this->password ?? '') != 32 ) {
			return $bytes;
		}

		if ( strlen($bytes) != 32 ) {
			return $bytes;
		}

		$cipher = 'aes-256-cbc';
		$ivlen = openssl_cipher_iv_length($cipher);
		$iv = substr($bytes, 0, $ivlen);
		$enc = substr($bytes, $ivlen);
		$dec = openssl_decrypt($enc, $cipher, $this->password, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

		$nonce = array_slice(str_split($dec), -4);
		$nonce = unpack('L', implode($nonce));
		$this->lastNonceReceived = reset($nonce) ?: $this->lastNonceReceived;

		return $dec;
	}

	protected function extractStatusBytes( array $bytes ) : array {
		// Skip 1, then take however many bytes this setup needs
		$bytes = array_slice($bytes, 1, ceil($this->relays / 8));

		// Pad to 4
		while ( count($bytes) < 4 ) {
			array_unshift($bytes, 0);
		}

		return $bytes;
	}

	public function status() : array {
		// Ask relay 1, get all relays
		$bytes = $this->write([$this->STATUS_CODE(), 1]);
		if ( !$bytes ) {
			return [];
		}

		$bytes = $this->extractStatusBytes($bytes);

		$bytes = array_reverse($bytes);

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
		$on = $pulse !== false && $pulse !== 0;
		$pulse = $on && is_int($pulse) && $pulse >= 100 ? array_reverse(unpack('C*', pack('L', $pulse))) : [0, 0, 0, 0];

		$rsp = $this->write(array_merge([49, $relay, $on ? 1 : 0], $pulse));

		return $this->isACK($rsp);
	}

	public function getVersionString() : string {
		$version = $this->version();
		if ( !$version || !isset($version[5]) ) {
			return '[DS?] Unknown';
		}

		$volt = number_format($version[5] / 10, 1);

		$temp = isset($version[7]) ? ($version[6] * 256 + $version[7]) / 10 : null;
		$temp = $temp ? "; {$temp}C" : '';

		return "[DS] Module {$version[0]}; System {$version[1]}.{$version[2]}; Application {$version[3]}.{$version[4]}; {$volt}V{$temp}";
	}

	protected function READ_BYTES() : int {
		return $this->password ? 32 : 16;
	}

	protected function STATUS_CODE() : int {
		return 51;
	}

	protected function VERSION_CODE() : int {
		return 48;
	}

}
