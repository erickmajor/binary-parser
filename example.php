<?php
/*
 * Binary Parser: serializes and unserializes binary data.
 * Jonas Raoni Soares da Silva <http://raoni.org>
 * https://github.com/jonasraoni/binary-parser
 */

/*******************************************************************************
this example will read data from a file generated using Turbo C,
bellow you can see the C code and also the data that was written with it
on the file data.bin

#include <stdio.h>

int main(int argc, char* argv[]){

	FILE *fp;
	int data = -1234;
	long int data2 = 4321;
	float data3 = 1234.1234;
	double data4 = 4321.4321;

	fp = fopen( "data.bin", "wb" );

	fwrite( &data, sizeof( data ), 1, fp );
	fwrite( &data2, sizeof( data2 ), 1, fp );
	fwrite( &data3, sizeof( data3 ), 1, fp );
	fwrite( &data4, sizeof( data4 ), 1, fp );
	fclose( fp );

	getch();

	return 0;
}



if you dont have a C compiler, you can create the file "data.bin" using this:

$f = fopen( "data.bin", "wb" );
fwrite( $f, "\x2e\xfb\xe1\x10\x0\x0\xf3\x43\x9a\x44\x9a\x8\x1b\x9e\x6e\xe1\xb0\x40" );
fclose( $f );


*******************************************************************************/


require_once "BinaryParser.php";

function char2hex( $s ){
	for( $i = -1, $l = strlen( $s ), $r = array(); ++$i < $l; array_push( $r, dechex( ord( $s[$i] ) ) ) );
	return implode( ":", $r );
}

?>

<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pt">
<head><title>Parser de dados binários</title>
<style type="text/css">
body { font: 1em monospace; }
table { border: 3px solid Grey; border-collapse: collapse; }
td { padding: 10px; }
tr.header { background: silver; font-weight: bold; }
th { font-size: 1.5em; color: white; background: black; }
</style>
</head>
<body>

<table border="1">
	<tr><th colspan="4	">Reading from a binary file</th></tr>
	<tr class="header">
		<td>Expected Number</td><td>Bytes Read</td><td>Bytes => Number</td><td>Number => Bytes</td>
	</tr>
<?php

if( !file_exists( "data.bin" ) ) {
	$f = fopen( "data.bin", "wb" );
	fwrite( $f, "\x2e\xfb\xe1\x10\x0\x0\xf3\x43\x9a\x44\x9a\x8\x1b\x9e\x6e\xe1\xb0\x40" );
	fclose( $f );
}

$f = fopen( "data.bin", "rb" );

$parser = new BinaryParser;
echo "<tr><td>-1234    </td><td>" . char2hex( $bytes = fread( $f, 2 ) )
	. "</td><td>[" . $parser->toShort( $bytes ) . "]</td><td>["
	. char2hex( $parser->fromShort( -1234 ) ) . "]</td></tr>";
echo "<tr><td>4321     </td><td>" . char2hex( $bytes = fread( $f, 4 ) )
	. "</td><td>[" . $parser->toInt( $bytes ) . "]</td><td>["
	. char2hex( $parser->fromInt( 4321 ) ) . "]</td></tr>";
echo "<tr><td>1234.1234</td><td>" . char2hex( $bytes = fread( $f, 4 ) )
	. "</td><td>[" . $parser->toFloat( $bytes ) . "]</td><td>["
	. char2hex( $parser->fromFloat( 1234.1234 ) ) . "]</td></tr>";
echo "<tr><td>4321.4321</td><td>" . char2hex( $bytes = fread( $f, 8 ) )
	. "</td><td>[" . $parser->toDouble( $bytes ) . "]</td><td>["
	. char2hex( $parser->fromDouble( 4321.4321 ) ) . "]</td></tr>";

fclose( $f );

?>
</table>

</body></html>