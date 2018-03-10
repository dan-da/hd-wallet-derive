<?php
namespace CashAddress;
// (c) uMCCCS
// with some minor additions from Har01d @ blockchair.com
// This script uses some of the code and ideas from the following repositories:
// https://github.com/deadalnix/cashaddressed
// https://github.com/cryptocoinjs/base-x/blob/master/index.js - base-x encoding
// Forked from https://github.com/cryptocoinjs/bs58
// Originally written by Mike Hearn for BitcoinJ
// Copyright (c) 2011 Google Inc
// Ported to JavaScript by Stefan Thomas
// Merged Buffer refactorings from base58-native by Stephen Pair
// Copyright (c) 2013 BitPay Inc
// The MIT License (MIT)
// Copyright base-x contributors (c) 2016
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
// Copyright (c) 2017 Pieter Wuille
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
// ISC License
//
// Copyright (c) 2013-2016 The btcsuite developers
//
// Permission to use, copy, modify, and distribute this software for any
// purpose with or without fee is hereby granted, provided that the above
// copyright notice and this permission notice appear in all copies.
//
// THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
// WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
// MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
// ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
// WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
// ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
// OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
// https://github.com/Bit-Wasp/bitcoin-php/blob/master/src/Bech32.php
// This is free and unencumbered software released into the public domain.
//
// Anyone is free to copy, modify, publish, use, compile, sell, or
// distribute this software, either in source code form or as a compiled
// binary, for any purpose, commercial or non-commercial, and by any
// means.
//
// In jurisdictions that recognize copyright laws, the author or authors
// of this software dedicate any and all copyright interest in the
// software to the public domain. We make this dedication for the benefit
// of the public at large and to the detriment of our heirs and
// successors. We intend this dedication to be an overt act of
// relinquishment in perpetuity of all present and future rights to this
// software under copyright law.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
// EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
// IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
// OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
// ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
// OTHER DEALINGS IN THE SOFTWARE.
//
// For more information, please refer to <http://unlicense.org/>
class CashAddressException extends \Exception {
}
class CashAddress {
	const ALPHABET = "123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";
	const CHARSET = "qpzry9x8gf2tvdw0s3jn54khce6mua7l";
	const ALPHABET_MAP = ["1" => 0, "2" => 1, "3" => 2, "4" => 3, "5" => 4, "6" => 5, "7" => 6,
  					  "8" => 7, "9" => 8, "A" => 9, "B" => 10, "C" => 11, "D" => 12, "E" => 13, "F" => 14, "G" => 15,
  					  "H" => 16, "J" => 17, "K" => 18, "L" => 19, "M" => 20, "N" => 21, "P" => 22, "Q" => 23, "R" => 24,
  					  "S" => 25, "T" => 26, "U" => 27, "V" => 28, "W" => 29, "X" => 30, "Y" => 31, "Z" => 32, "a" => 33,
  					  "b" => 34, "c" => 35, "d" => 36, "e" => 37, "f" => 38, "g" => 39, "h" => 40, "i" => 41, "j" => 42,
  					  "k" => 43, "m" => 44, "n" => 45, "o" => 46, "p" => 47, "q" => 48, "r" => 49, "s" => 50, "t" => 51,
						  "u" => 52, "v" => 53, "w" => 54, "x" => 55, "y" => 56, "z" => 57];
	const BECH_ALPHABET = ["q" => 0, "p" => 1,
						  "z" => 2, "r" => 3, "y" => 4, "9" => 5, "x" => 6, "8" => 7,
						  "g" => 8, "f" => 9, "2" => 10, "t" => 11, "v" => 12, "d" => 13,
						  "w" => 14, "0" => 15, "s" => 16, "3" => 17, "j" => 18, "n" => 19,
						  "5" => 20, "4" => 21, "k" => 22, "h" => 23, "c" => 24, "e" => 25,
						  "6" => 26, "m" => 27, "u" => 28, "a" => 29, "7" => 30, "l" => 31];
	const EXPAND_PREFIX = [2, 9, 20, 3, 15, 9, 14, 3, 1, 19, 8, 0];
	const EXPAND_PREFIX_TESTNET = [2, 3, 8, 20, 5, 19, 20, 0];
	const BASE16 = ["0" => 0, "1" => 1, "2" => 2, "3" => 3,
						  "4" => 4, "5" => 5, "6" => 6, "7" => 7,
							"8" => 8, "9" => 9, "a" => 10, "b" => 11,
							"c" => 12, "d" => 13, "e" => 14, "f" => 15];
	public function __construct()
	{
		if (PHP_INT_SIZE < 5) {
			// Requires x64 system and PHP!
			throw new CashAddressException('Run it on a x64 system (+ 64 bit PHP)');
		}
	}
	/**
	 * convertBits is the internal function to convert 256-based bytes
	 * to base-32 grouped bit arrays and vice versa.
	 * @param  array $data Data whose bits to be re-grouped
	 * @param  integer $fromBits Bits per input group of the $data
	 * @param  integer $toBits Bits to be put to each output group
	 * @param  boolean $pad Whether to add extra zeroes
	 * @return array $ret
	 * @throws CashAddressException
	 */
	static private function convertBits(array $data, $fromBits, $toBits, $pad = true)
	{
		$acc    = 0;
		$bits   = 0;
		$ret    = [];
		$maxv   = (1 << $toBits) - 1;
		$maxacc = (1 << ($fromBits + $toBits - 1)) - 1;
		for ($i = 0; $i < sizeof($data); $i++)
		{
			$value = $data[$i];
			if ($value < 0 || $value >> $fromBits != 0)
			{
				throw new CashAddressException("Error!");
			}
			$acc  = (($acc << $fromBits) | $value) & $maxacc;
			$bits += $fromBits;
			while ($bits >= $toBits)
			{
				$bits  -= $toBits;
				$ret[] = (($acc >> $bits) & $maxv);
			}
		}
		if ($pad)
		{
			if ($bits)
			{
				$ret[] = ($acc << $toBits - $bits) & $maxv;
			}
		}
		else if ($bits >= $fromBits || ((($acc << ($toBits - $bits))) & $maxv))
		{
			throw new CashAddressException("Error!");
		}
		return $ret;
	}
	/**
	* polyMod is the internal function create BCH codes.
	* @param  array $var 5-bit grouped data array whose polyMod to be calculated.
	* @return integer $polymodValue polymod result
	*/
	static private function polyMod($var)
	{
		$c = 1;
		for ($i = 0; $i < sizeof($var); $i++)
		{
			$c0 = $c >> 35;
			$c = (($c & 0x07ffffffff) << 5) ^ $var[$i];
			if ($c0 & 1)
			{
				$c ^= 0x98f2bc8e61;
			}
			if ($c0 & 2)
			{
				$c ^= 0x79b76d99e2;
			}
			if ($c0 & 4)
			{
				$c ^= 0xf33e5fb3c4;
			}
			if ($c0 & 8)
			{
				$c ^= 0xae2eabe2a8;
			}
			if ($c0 & 16)
			{
				$c ^= 0x1e4f43e470;
			}
		}
		return $c ^ 1;
	}
	/**
	* rebuildAddress is the internal function to recreate error
	* corrected addresses.
	* @param  array $addressBytes
	* @return string $correctedAddress
	*/
	static private function rebuildAddress($addressBytes)
	{
		$ret = "";
		$i   = 0;
		while ($addressBytes[$i] != 0)
		{
			// 96 = ord('a') & 0xe0
			$ret .= chr(96 + $addressBytes[$i]);
			$i++;
		}
		$ret .= ':';
		for ($i++; $i < sizeof($addressBytes); $i++)
		{
			$ret .= self::CHARSET[$addressBytes[$i]];
		}
		return $ret;
	}
	/**
	* old2new converts an address in old format to the new Cash Address format.
	* @param  string $oldAddress (either Mainnet or Testnet)
	* @return string $newAddress Cash Address result
	* @throws CashAddressException
	*/
	static public function old2new($oldAddress)
	{
		$bytes = [0];
		for ($x = 0; $x < strlen($oldAddress); $x++)
		{
			if (!array_key_exists($oldAddress[$x], self::ALPHABET_MAP))
			{
				throw new CashAddressException('Unexpected character in address!');
			}
			$value = self::ALPHABET_MAP[$oldAddress[$x]];
			$carry = $value;
			for ($j = 0; $j < sizeof($bytes); $j++)
			{
				$carry     += $bytes[$j] * 58;
				$bytes[$j] = $carry & 0xff;
				$carry     >>= 8;
			}
			while ($carry > 0)
			{
				array_push($bytes, $carry & 0xff);
				$carry >>= 8;
			}
		}
		for ($numZeros = 0; $numZeros < strlen($oldAddress) && $oldAddress[$numZeros] === "1"; $numZeros++)
		{
			array_push($bytes, 0);
		}
		// reverse array
		$answer = [];
		for ($i = sizeof($bytes) - 1; $i >= 0; $i--)
		{
			array_push($answer, $bytes[$i]);
		}
		$version = $answer[0];
		$payload = array_slice($answer, 1, sizeof($answer) - 5);
		if (sizeof($payload) % 4 != 0)
		{
			throw new CashAddressException('Unexpected address length!');
		}
		// Assume the checksum of the old address is right
		// Here, the Cash Address conversion starts
		if ($version == 0x00)
		{
			// P2PKH
			$addressType = 0;
			$realNet = true;
		}
		else if ($version == 0x05)
		{
			// P2SH
			$addressType = 1;
			$realNet = true;
		}
		else if ($version == 0x6f)
		{
			// Testnet P2PKH
			$addressType = 0;
			$realNet = false;
		}
		else if ($version == 0xc4)
		{
			// Testnet P2SH
			$addressType = 1;
			$realNet = false;
		}
		else if ($version == 0x1c)
		{
			// BitPay P2PKH
			$addressType = 0;
			$realNet = true;
		}
		else if ($version == 0x28)
		{
			// BitPay P2SH
			$addressType = 1;
			$realNet = true;
		}
		else
		{
			throw new CashAddressException('Unknown address type!');
		}
		$encodedSize = (sizeof($payload) - 20) / 4;
		$versionByte      = ($addressType << 3) | $encodedSize;
		$data             = array_merge([$versionByte], $payload);
		$payloadConverted = self::convertBits($data, 8, 5, true);
		if ($realNet) {
			$arr = array_merge(self::EXPAND_PREFIX, $payloadConverted, [0, 0, 0, 0, 0, 0, 0, 0]);
			$ret = "bitcoincash:";
		} else {
			$arr = array_merge(self::EXPAND_PREFIX_TESTNET, $payloadConverted, [0, 0, 0, 0, 0, 0, 0, 0]);
			$ret = "bchtest:";
		}
		$mod          = self::polymod($arr);
		$checksum     = [0, 0, 0, 0, 0, 0, 0, 0];
		for ($i = 0; $i < 8; $i++)
		{
			// Convert the 5-bit groups in mod to checksum values.
			// $checksum[$i] = ($mod >> 5*(7-$i)) & 0x1f;
			$checksum[$i] = ($mod >> (5 * (7 - $i))) & 0x1f;
		}
		$combined = array_merge($payloadConverted, $checksum);
		for ($i = 0; $i < sizeof($combined); $i++)
		{
			$ret .= self::CHARSET[$combined[$i]];
		}
		return $ret;
	}
	/**
	 * Decodes Cash Address.
	 * @param  string $inputNew New address to be decoded.
	 * @param  boolean $shouldFixErrors Whether to fix typing errors.
	 * @param  boolean &$isTestnetAddressResult Is pointer, set to whether it's
	 * a testnet address.
	 * @return array $decoded Returns decoded byte array if it can be decoded.
	 * @return string $correctedAddress Returns the corrected address if there's
	 * a typing error.
	 * @throws CashAddressException
	 */
	static public function decodeNewAddr($inputNew, $shouldFixErrors, &$isTestnetAddressResult) {
		$inputNew = strtolower($inputNew);
		if (strpos($inputNew, ":") === false) {
			$afterPrefix = 0;
			$data = self::EXPAND_PREFIX;
			$isTestnetAddressResult = false;
		}
		else if (substr($inputNew, 0, 12) === "bitcoincash:")
		{
			$afterPrefix = 12;
			$data = self::EXPAND_PREFIX;
			$isTestnetAddressResult = false;
		}
		else if (substr($inputNew, 0, 8) === "bchtest:")
		{
			$afterPrefix = 8;
			$data = self::EXPAND_PREFIX_TESTNET;
			$isTestnetAddressResult = true;
		}
		else
		{
			throw new CashAddressException('Unknown address type');
		}
		for ($values = []; $afterPrefix < strlen($inputNew); $afterPrefix++)
		{
			if (!array_key_exists($inputNew[$afterPrefix], self::BECH_ALPHABET))
			{
				throw new CashAddressException('Unexpected character in address!');
			}
			array_push($values, self::BECH_ALPHABET[$inputNew[$afterPrefix]]);
		}
		$data     = array_merge($data, $values);
		$checksum = self::polyMod($data);
		if ($checksum != 0)
		{
			// Checksum is wrong!
			// Try to fix up to two errors
			if ($shouldFixErrors) {
				$syndromes = Array();
				for ($p = 0; $p < sizeof($data); $p++)
				{
					for ($e = 1; $e < 32; $e++)
					{
						$data[$p] ^= $e;
						$c        = self::polyMod($data);
						if ($c == 0)
						{
							return self::rebuildAddress($data);
						}
						$syndromes[$c ^ $checksum] = $p * 32 + $e;
						$data[$p]                  ^= $e;
					}
				}
				foreach ($syndromes as $s0 => $pe)
				{
					if (array_key_exists($s0 ^ $checksum, $syndromes))
					{
						$data[$pe >> 5]                         ^= $pe % 32;
						$data[$syndromes[$s0 ^ $checksum] >> 5] ^= $syndromes[$s0 ^ $checksum] % 32;
						return self::rebuildAddress($data);
					}
				}
				// Can't correct errors!
				throw new CashAddressException('Can\'t correct typing errors!');
			}
		}
		return $values;
	}
	/**
	 * Corrects Cash Address typing errors.
	 * @param  string $inputNew Cash Address to be corrected.
	 * @return string $correctedAddress Error corrected address, or the input itself
	 * if there are no errors.
	 * @throws CashAddressException
	 */
	static public function fixCashAddrErrors($inputNew) {
		try {
			$corrected = self::decodeNewAddr($inputNew, true, $isTestnet);
			if (gettype($corrected) === "array") {
				return $inputNew;
			} else {
				return $corrected;
			}
		}
		catch(CashAddressException $e) {
			throw $e;
		}
	}
	/**
	* new2old converts an address in the Cash Address format to the old format.
	* @param  string $inputNew Cash Address (either mainnet or testnet)
	* @param  boolean $shouldFixErrors Whether to fix typing errors.
	* @return string $oldAddress Old style 1... or 3... address
	* @throws CashAddressException
	*/
	static public function new2old($inputNew, $shouldFixErrors)
	{
		try {
			$corrected = self::decodeNewAddr($inputNew, $shouldFixErrors, $isTestnet);
			if (gettype($corrected) === "array") {
				$values = $corrected;
			} else {
				$values = self::decodeNewAddr($corrected, false, $isTestnet);
			}
		}
		catch(Exception $e) {
			throw new CashAddressException('Error');
		}
		$values      = self::convertBits(array_slice($values, 0, sizeof($values) - 8), 5, 8, false);
		$addressType = $values[0] >> 3;
		$addressHash = array_slice($values, 1, 21);
		// Encode Address
		if ($isTestnet) {
			if ($addressType) {
				$bytes = [0xc4];
			} else {
				$bytes = [0x6f];
			}
		} else {
			if ($addressType) {
				$bytes = [0x05];
			} else {
				$bytes = [0x00];
			}
		}
		$bytes  = array_merge($bytes, $addressHash);
		$merged = array_merge($bytes, self::doubleSha256ByteArray($bytes));
		$digits = [0];
		for ($i = 0; $i < sizeof($merged); $i++)
		{
			$carry = $merged[$i];
			for ($j = 0; $j < sizeof($digits); $j++)
			{
				$carry      += $digits[$j] << 8;
				$digits[$j] = $carry % 58;
				$carry      = intdiv($carry, 58);
			}
			while ($carry > 0)
			{
				array_push($digits, $carry % 58);
				$carry = intdiv($carry, 58);
			}
		}
		// leading zero bytes
		for ($i = 0; $i < sizeof($merged) && $merged[$i] === 0; $i++)
		{
			array_push($digits, 0);
		}
		// reverse
		$converted = "";
		for ($i = sizeof($digits) - 1; $i >= 0; $i--)
		{
			if ($digits[$i] > strlen(self::ALPHABET))
			{
				throw new CashAddressException('Error!');
			}
			$converted .= self::ALPHABET[$digits[$i]];
		}
		return $converted;
	}
	/**
	 * internal function to calculate sha256
	 * @param  array $byteArray Byte array of data to be hashed
	 * @return array $hashResult First four bytes of sha256 result
	 */
	private static function doubleSha256ByteArray($byteArray) {
		$stringToBeHashed = "";
		for ($i = 0; $i < sizeof($byteArray); $i++)
		{
			$stringToBeHashed .= chr($byteArray[$i]);
		}
		$hash = hash("sha256", $stringToBeHashed);
		$hashArray = [];
		for ($i = 0; $i < 32; $i++)
		{
			array_push($hashArray, self::BASE16[$hash[2 * $i]] * 16 + self::BASE16[$hash[2 * $i + 1]]);
		}
		$stringToBeHashed = "";
		for ($i = 0; $i < sizeof($hashArray); $i++)
		{
			$stringToBeHashed .= chr($hashArray[$i]);
		}
		$hashArray = [];
		$hash      = hash("sha256", $stringToBeHashed);
		for ($i = 0; $i < 4; $i++)
		{
			array_push($hashArray, self::BASE16[$hash[2 * $i]] * 16 + self::BASE16[$hash[2 * $i + 1]]);
		}
		return $hashArray;
	}
}
?>