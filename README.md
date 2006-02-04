# Binary Parser

Serializes and unserializes binary data in PHP.

## Constants

- NOT_A_NUMBER: Defines a kind of float error-flag, which isn't a number at all
- POSITIVE_INFINITY: Defines a positive number higher than the maximum allowed
- NEGATIVE_INFINITY: Defines a negative number lower than the minimum allowed

## Classes

### BinaryParser: The binary encoder/decoder

#### Properties

- public bigEndian: bool = false => specify which byte order the class will use the decode/encode
- public constructor( bigEndian: bool = false ) => it may receive the endian kind


#### Methods

- public function toFloat( data: string ): float => receives string containing 4 chars/bytes and returns its respective float representation or a special value (NOT_A_NUMBER, POSITIVE_INFINITY, NEGATIVE_INFINITY)
- public function fromFloat( data: float ): string => receives a floating point number and returns its binary representation in a 4 chars string, it may rise an exceptions if the number is a special value (NOT_A_NUMBER, POSITIVE_INFINITY, NEGATIVE_INFINITY) or if the number can't be represented (overflow, underflow)
- public function toDouble( data: string ): float => receives string containing 8 chars/bytes and returns its respective double representation or a special value (NOT_A_NUMBER, POSITIVE_INFINITY, NEGATIVE_INFINITY)
- public function fromDouble( data: float ): string => receives a floating point number and returns its binary representation in a 8 chars string, it may rise an exceptions if the number is a special value (NOT_A_NUMBER, POSITIVE_INFINITY, NEGATIVE_INFINITY) or if the number can't be represented (overflow, underflow)
- public function toSmall( data: string ): int => receives string containing 1 char/byte and returns your respective numeric representation with signal
- public function fromSmall( data: int ): string => receives a number and returns its binary representation in a 1 char string, it may rise an exception if the number exceeds the limit
- public function toByte( data: string ): int => receives string containing 1 char/byte and returns its respective numeric representation without signal
- public function fromByte( data: int ): string => receives a number and returns its binary representation in a 1 char string, it may rise an exception if the number exceeds the limit and negative numbers are converted
- public function toShort( data: string ): int => receives string containing 2 chars/bytes and returns its respective numeric representation with signal
- public function fromShort( data: int ): string => receives a number and returns its binary representation in a 2 chars string, it may rise an exception if the number exceeds the limit
- public function toWord( data: string ): int => receives string containing 2 chars/bytes and returns its respective numeric representation without signal
- public function fromWord( data: int ): string => receives a number and returns its binary representation in a 2 chars string, it may rise an exception if the number exceeds the limit and negative numbers are converted
- public function toInt( data: string ): int => receives string containing 4 chars/bytes and returns its respective numeric representation with signal
- public function fromInt( data: int ): string => receives a number and returns its binary representation in a 4 chars string, it may rise an exception if the number exceeds the limit
- public function toDWord( data: string ): int => receives string containing 4 chars/bytes and returns its respective numeric representation without signal
- public function fromDWord( data: int ): string => receives a number and returns its binary representation in a 4 chars string, it may rise an exception if the number exceeds the limit and negative numbers are converted

- protected function decodeFloat( data: string, precisionBits: int, exponentBits: int ): float => internal function for decoding the standard IEEE-754, since PHP isn't able to handle float types using more than 64bits, I decided to not support the 80 and 128 bits formats.  
data: a string buffer containing the binary representation of the number (must have at least "ceil( ( exponentBits + precisionBits + 1 ) / 8 )" bytes)  
precisionBits: the amount of bits that specify the precision/significand  
exponentBits: the amount of bits that specify the exponent that will multiply the significand to obtain the number and returns the number or the following special values: NaN, +Infinity, -Infinity

- protected function decodeInt( data: string, bits: int, signed: bool ): int => internal function for decoding standard integer types, since PHP isn't able to handle integers higher than 32 bits, i decided to not support the 64 bits format.  
data: a string buffer containing the binary representation of the number (must have at least "ceil( bits / 8 )" bytes)
bits: the amount of bits that specify the number max length and min length
signed: if the number must be decoded as signed or unsigned
and returns the number

- protected function encodeFloat( data: float, precisionBits: int, exponentBits: int ): string => internal function for encoding a number into the standard IEEE-754 format, since PHP isn't able to handle float types using more than 64bits, i decided to not support the 80 and 128 bits formats.  
data: the number which will be converted  
precisionBits: the amount of bits that specify the precision/significand  
exponentBits: the amount of bits that specify the exponent that will multiply the significand to obtain the number and returns a binary string representation of the number containing "ceil( ( exponentBits + precisionBits + 1 ) / 8 )" bytes

- protected function encodeInt( data: string, bits: int, signed: bool ): int => internal function for decoding standard integer types, since PHP isn't able to handle integers higher than 32 bits, i decided to not support the 64 bits format.  
data: the number which will be converted  
bits: the amount of bits that specify the number max length and min length  
signed: if the number must be decoded as signed or unsigned and returns a binary string representation of the number containing "ceil( bits / 8 )" bytes


### BinaryBuffer

Simple class to hold some bytes while decoding the data into a numerical format

#### Properties

- public  length: int = 0 => the amount of bytes stored in the class
- private bigEndian: bool = false => keeps safe the internal endian-format that was chosed
- private buffer: array = [] => buffer storage, hold the bytes


#### Methods

- public constructor( bigEndian: bool = false, buffer: string = '' ): void => it may receive the ordering type and a starting string buffer
- public function setBuffer( data: string ): void => set new content into the buffer array
- public function getBuffer(): array => returns the buffer array
- public function hasNeededBits( neededBits: int ): bool => returns if the buffer has "neededBits" bits avaiable for reading
- public function checkBuffer( $neededBits ): void => the same as BinaryBuffer::hasNeededBits, but it rises an exception when there isn't enough data available
- public function byte( $i ): int => returns the byte value in the buffer at the specified "i" offset
- public function setEndian( $bigEndian ): void => sets up the endian
- public function readBits( $start, $length ): int => read the bits interval and returns the corresponding integer value
- private private function shl( a: Int, b: Int ): int => rotates "a" bits "b" times to the left
