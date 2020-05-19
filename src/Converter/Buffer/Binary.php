<?php

namespace Converter\Buffer;

use Exception;
#--[ BinaryBuffer Class ]------------------------------------
/**
 * 
 */
class Binary
{
	protected $bigEndian = false;
    protected $buffer    = [];
	public    $length    = 0;


    protected function shl($a, $b)
    {
		for( ; $b--; $a = ( ( $a %= 0x7fffffff + 1 ) & 0x40000000 ) == 0x40000000 ? $a * 2 : ( $a - 0x40000000 ) * 2 + 0x7fffffff + 1 );
		return $a;
	}


    public function __construct($bigEndian = false, $buffer = '')
    {
		$this->bigEndian = $bigEndian;
		$this->setBuffer($buffer);
	}

    public function setBuffer($data)
    {
		if( gettype( $data ) == 'string' && strlen( $data ) ){
			for( $this->buffer = array(), $i = strlen( $data ); $i; $this->buffer[] = ord( $data[--$i] ) );
			if( $this->bigEndian )
				$this->buffer = array_reverse( $this->buffer );
			$this->length = count( $this->buffer );
		}
	}

    public function getBuffer()
    {
		return $this->buffer;
	}

    public function hasNeededBits( $neededBits )
    {
		return count( $this->buffer ) >= -( -$neededBits >> 3 );
	}

    public function checkBuffer( $neededBits )
    {
		if( !$this->hasNeededBits( $neededBits ) )
			throw new Exception( __METHOD__ . ": missing bytes" );
	}

    public function byte( $i )
    {
		return $this->buffer[ $i ];
	}

    public function setEndian( $bigEndian )
    {
		if( $this->$bigEndian != $bigEndian )
			$this->buffer = array_reverse( $this->buffer );
	}

    public function readBits( $start, $length )
    {
		if( $start < 0 || $length <= 0 )
			return 0;
		$this->checkBuffer( $start + $length );
		$offsetRight = $start % 8;
		$curByte = count( $this->buffer ) - ( $start >> 3 ) - 1;
		$lastByte = count( $this->buffer ) + ( -( $start + $length ) >> 3 );
		$diff = $curByte - $lastByte;
		$sum = ( ( $this->buffer[ $curByte ] >> $offsetRight ) & ( ( 1 << ( $diff ? 8 - $offsetRight : $length ) ) - 1 ) ) + ( $diff && ( $offsetLeft = ( $start + $length ) % 8 ) ? ( $this->buffer[ $lastByte++ ] & ( ( 1 << $offsetLeft ) - 1 ) ) << ( $diff-- << 3 ) - $offsetRight : 0 );
		for( ; $diff; $sum += self::shl( $this->buffer[ $lastByte++ ], ( $diff-- << 3 ) - $offsetRight ) );
		return $sum;
	}
}