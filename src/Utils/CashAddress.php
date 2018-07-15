<?php


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

namespace App\Utils;

use BitWasp\Bech32;



class CashAddress
{

    const ALPHABET = "123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";
	const CHARSET = "qpzry9x8gf2tvdw0s3jn54khce6mua7l";


	static public function old2new($oldAddress)
	{
	    $alphabetMap = self::getAlphabetMap();
	    $bytes = [0];

	    for ($x = 0; $x < strlen($oldAddress); $x++)
		{
			if (!array_key_exists($oldAddress[$x], $alphabetMap))
			{
				throw new CashAddressException('Unexpected character in address!');
			}
			$value = $alphabetMap[$oldAddress[$x]];
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
			echo "Unexpected address length!\n";
			exit;
		}

		// Assume the checksum of the old address is right
		// Here, the Cash Address conversion starts
        switch($version)
        {
            // P2PKH
            case 0x00:
                $addressType = 0;
                $realNet = true;
                break;

            // P2SH
            case 0x05:
                $addressType = 1;
                $realNet = true;
                break;

            // Testnet P2PKH
            case 0x6f:
                $addressType = 0;
                $realNet = false;
                break;

            // Testnet P2SH
            case 0xc4:
                $addressType = 1;
                $realNet = false;
                break;

            // BitPay P2PKH
            case 0x1c:
                $addressType = 0;
                $realNet = true;
                break;

            // BitPay P2SH
            case 0x28:
                $addressType = 1;
                $realNet = true;
                break;

            default:
                echo "Unknown address type!\n";
                exit;
                break;

        }

        $encodedSize = (sizeof($payload) - 20) / 4;
		$versionByte = ($addressType << 3) | $encodedSize;
		$data        = array_merge([$versionByte], $payload);

        $inLen = sizeof($data);

		$payloadConverted = Bech32\convertBits($data, $inLen, 8,5, true);
		if ($realNet) {
			$arr = array_merge(self::getExpandPrefix(), $payloadConverted, [0, 0, 0, 0, 0, 0, 0, 0]);
			$ret = "bitcoincash:";
		} else {
			$arr = array_merge(self::getExpandPrefixTestnet(), $payloadConverted, [0, 0, 0, 0, 0, 0, 0, 0]);
			$ret = "bchtest:";
		}

		$mod          = Bech32\polyMod($arr, sizeof($arr));
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

	static private function getAlphabetMap()
    {
        $alphabetMap = ["1" => 0, "2" => 1, "3" => 2, "4" => 3, "5" => 4, "6" => 5, "7" => 6,
            "8" => 7, "9" => 8, "A" => 9, "B" => 10, "C" => 11, "D" => 12, "E" => 13, "F" => 14, "G" => 15,
            "H" => 16, "J" => 17, "K" => 18, "L" => 19, "M" => 20, "N" => 21, "P" => 22, "Q" => 23, "R" => 24,
            "S" => 25, "T" => 26, "U" => 27, "V" => 28, "W" => 29, "X" => 30, "Y" => 31, "Z" => 32, "a" => 33,
            "b" => 34, "c" => 35, "d" => 36, "e" => 37, "f" => 38, "g" => 39, "h" => 40, "i" => 41, "j" => 42,
            "k" => 43, "m" => 44, "n" => 45, "o" => 46, "p" => 47, "q" => 48, "r" => 49, "s" => 50, "t" => 51,
            "u" => 52, "v" => 53, "w" => 54, "x" => 55, "y" => 56, "z" => 57];

        return $alphabetMap;
    }

    static private function getExpandPrefix()
    {
        return [2, 9, 20, 3, 15, 9, 14, 3, 1, 19, 8, 0];
    }

    static private function getExpandPrefixTestnet()
    {
        return [2, 3, 8, 20, 5, 19, 20, 0];
    }
}


?>