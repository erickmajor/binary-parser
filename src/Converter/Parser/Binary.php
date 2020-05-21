<?php

namespace Converter\Parser;

use Exception;
use Converter\Buffer\Binary as BinaryBuffer;

/*
 * Binary Parser: serializes and unserializes binary data.
 * Jonas Raoni Soares da Silva <http://raoni.org>
 * https://github.com/jonasraoni/binary-parser
 */

#--[ BinaryParser Class ]------------------------------
class Binary
{
    public $bigEndian = false;


    public function __construct($bigEndian = false)
    {
        $this->bigEndian = $bigEndian;
    }

    protected function decodeFloat($data, $precisionBits, $exponentBits)
    {
        $bias        = pow(2, $exponentBits - 1) - 1;
        $buffer      = new BinaryBuffer($this->bigEndian, $data);
        $buffer->checkBuffer($precisionBits + $exponentBits + 1);
        $signal      = $buffer->readBits($precisionBits + $exponentBits, 1);
        $exponent    = $buffer->readBits($precisionBits, $exponentBits);
        $significand = 0;
        $divisor     = 2;
        $curByte     = $buffer->length + (-$precisionBits >> 3) - 1;
        do {
            for (
                $byteValue = $buffer->byte(++$curByte)
                    , $startBit = ($startBit = $precisionBits % 8) ? $startBit : 8
                    , $mask = 1 << $startBit
                ; $mask >>= 1
                ; $divisor *= 2
            ) {
                if ($byteValue & $mask) {
                    $significand += 1 / $divisor;
                }
            }
        } while ($precisionBits -= $startBit);

        $result = null;
        if ($exponent == ($bias << 1) + 1) {
            // Not a number                
            if ($significand) {
                $result = acos(1.01);
            // Negative infinity
            } elseif ($signal) {
                $result = log(0);
            // Positive infinity
            } else {
                $result = -log(0);
            }
        } else {
            if ($exponent || $significand) {
                if (!$exponent) {
                    $result = pow(2, -$bias + 1) * $significand;
                } else {
                    $result = pow(2, $exponent - $bias) * (1 + $significand);
                }
            } else {
                $result = 0;
            }

            $result *= (1 + $signal * -2);
        }
        
        return $result;
    }

    protected function decodeInt($data, $bits, $signed)
    {
        $buffer = new BinaryBuffer($this->bigEndian, $data);
        $x      = $buffer->readBits(0, $bits);

        return $signed && $x >= ($max = pow( 2, $bits)) / 2 ? $x - $max : $x;
    }

    protected function encodeFloat($data, $precisionBits, $exponentBits)
    {
        $negative_infinity = -log(0);
        $positive_infinity =  log(0);
        $bias         = pow(2, $exponentBits - 1) - 1;
        $minExp       = -$bias + 1;
        $maxExp       = $bias;
        $minUnnormExp = $minExp - $precisionBits;
        $status       = is_nan($n = (float) $data) || $n == $negative_infinity || $n == $positive_infinity ? $n : 0;
        $exp          = 0;
        $len          = 2 * $bias + 1 + $precisionBits + 3;
        $bin          = array_pad([], $len, 0);
        $signal       = ($n = $status !== 0 ? 0 : $n) < 0;
        $n            = abs($n);
        $intPart      = floor($n);
        $floatPart    = $n - $intPart;
        for (
            $i = $bias + 2
            ; $intPart && $i
            ; $bin[--$i] = ($intPart % 2) & 1
                , $intPart = floor($intPart / 2)
        );
        for ($i = $bias + 1; $floatPart > 0 && $i;) {
            if ($bin[++$i] = (($floatPart *= 2) >= 1) - 0) {
                --$floatPart;
            }
        }

        for ($i = -1; ++$i < $len && !$bin[$i];);
        $i = ($exp = $bias + 1 - $i) >= $minExp && $exp <= $maxExp ? $i + 1 : $bias + 1 - ($exp = $minExp - 1);
        if ($bin[($lastBit = $precisionBits - 1 + $i) + 1]) {
            if (!($rounded = $bin[$lastBit])) {
                for (
                    $j = $lastBit + 2
                    ; !$rounded && $j < $len
                    ; $rounded = $bin[$j++]
                );
            }

            for ($j = $lastBit + 1; $rounded && --$j >= 0;) {
                if ($bin[$j] = !$bin[$j] - 0) {
                    $rounded = 0;
                }
            }
        }

        for ($i = $i - 2 < 0 ? -1 : $i - 3; ++$i < $len && !$bin[$i];);
        if (($exp = $bias + 1 - $i) >= $minExp && $exp <= $maxExp) {
            ++$i;
        } elseif ($exp < $minExp) {
            if ($exp != $bias + 1 - $len && $exp < $minUnnormExp) {
                throw new Exception(__METHOD__ . ': underflow');
                $i = $bias + 1 - ($exp = $minExp - 1);
            }
        }

        if ($intPart || $status !== 0) {
            throw new Exception(__METHOD__ . ': ' . ($intPart ? 'overflow' : $status));
            $exp = $maxExp + 1;
            $i = $bias + 2;
            if ($status == $negative_infinity) {
                $signal = 1;
            } elseif (is_nan($status)) {
                $bin[$i] = 1;
            }
        }

        for (
            $n = abs($exp + $bias)
                , $j = $exponentBits + 1
                , $result = ''
            ; --$j
            ; $result = ($n % 2) . $result
                , $n = $n >>= 1
        );
        $result = ($signal ? "1" : "0") . $result . implode('', array_slice($bin, $i, $precisionBits));
        for ($n = 0, $j = 0, $i = strlen($result), $r = array(); $i; $j = ($j + 1) % 8) {
            $n += ( 1 << $j) * $result[--$i];
            if ($j == 7) {
                $r[] = chr($n);
                $n = 0;
            }
        }

        $r[] = ($n ? chr($n) : '');

        return implode('', ($this->bigEndian ? array_reverse($r) : $r));
    }

    protected function encodeInt($data, $bits, $signed)
    {
        if ($data >= ($max = pow( 2, $bits)) || $data < -($max >> 1)) {
            throw new Exception( __METHOD__ . ": overflow");
        }

        if ($data < 0) {
            $data += $max;
        }

        for (
            $r = array()
            ; $data
            ; $r[] = chr($data % 256)
                , $data = floor($data / 256)
        );
        for (
            $bits = -(-$bits >> 3) - count($r)
            ; $bits--
            ; $r[] = "\0"
        );

        return implode('', ($this->bigEndian ? array_reverse($r) : $r));
    }

    public function toSmall($data)
    {
        return $this->decodeInt($data,  8, true);
    }

    public function fromSmall($data)
    {
        return $this->encodeInt($data,  8, true);
    }

    public function toByte($data)
    {
        return $this->decodeInt($data,  8, false);
    }

    public function fromByte($data)
    {
        return $this->encodeInt($data,  8, false);
    }

    public function toShort($data)
    {
        return $this->decodeInt($data, 16, true);
    }

    public function fromShort($data)
    {
        return $this->encodeInt($data, 16, true);
    }

    public function toWord($data)
    {
        return $this->decodeInt($data, 16, false);
    }

    public function fromWord($data)
    {
        return $this->encodeInt($data, 16, false);
    }

    public function toInt($data)
    {
        return $this->decodeInt($data, 32, true);
    }

    public function fromInt($data)
    {
        return $this->encodeInt($data, 32, true);
    }

    public function toDWord($data)
    {
        return $this->decodeInt($data, 32, false);
    }

    public function fromDWord($data)
    {
        return $this->encodeInt($data, 32, false);
    }

    public function toFloat($data)
    {
        return $this->decodeFloat($data, 23, 8);
    }

    public function fromFloat($data)
    {
        return $this->encodeFloat($data, 23, 8);
    }

    public function toDouble($data)
    {
        return $this->decodeFloat($data, 52, 11);
    }

    public function fromDouble($data)
    {
        return $this->encodeFloat($data, 52, 11);
    }
}
