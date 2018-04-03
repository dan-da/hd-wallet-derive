<?php
namespace CashAddress;


use BitWasp\Bitcoin\Bech32;


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

		$payloadConverted = Bech32::convertBits($data, $inLen, 8,5, true);
		if ($realNet) {
			$arr = array_merge(self::getExpandPrefix(), $payloadConverted, [0, 0, 0, 0, 0, 0, 0, 0]);
			$ret = "bitcoincash:";
		} else {
			$arr = array_merge(self::getExpandPrefixTestnet(), $payloadConverted, [0, 0, 0, 0, 0, 0, 0, 0]);
			$ret = "bchtest:";
		}

		$mod          = Bech32::polymod($arr, sizeof($arr));
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