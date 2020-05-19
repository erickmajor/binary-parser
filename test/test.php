<?php
/*
 * Binary Parser: serializes and unserializes binary data.
 * Jonas Raoni Soares da Silva <http://raoni.org>
 * https://github.com/jonasraoni/binary-parser
 */

require_once "BinaryParser.php";

$floatValues = array(
	array("+0", "00000000", "0.0" ),
	array("-0", "80000000", "-0.0" ),
	array("1", "3f800000", "1.0" ),
	array("2", "40000000", "2.0" ),
	array("maximum normal number", "7f7fffff", "3.40282347e+38" ),
	array("minimum positive normal number", "00800000", "1.17549435e-38" ),
	array("maximum subnormal number", "007fffff", "1.17549421e-38" ),
	array("minimum positive subnormal number", "00000001", "1.40129846e-45" ),
	array("&infin;+", "7f800000", "+Infinity" ),
	array("&infin;-", "ff800000", "-Infinity" ),
	array("Not-a-Number", "7fc00000", "NaN" )
);

$doubleValues = array(
	array("+0", "0000000000000000", "0.0" ),
	array("-0", "8000000000000000", "-0.0" ),
	array("1", "3ff0000000000000", "1.0" ),
	array("2", "4000000000000000", "2.0" ),
	array("max normal number", "7fefffffffffffff", "1.7976931348623157e+308" ),
	array("min positive normal number", "0010000000000000", "2.2250738585072014e-308" ),
	array("max subnormal number", "000fffffffffffff", "2.2250738585072009e-308" ),
	array("min positive subnormal number", "0000000000000001", "4.9406564584124654e-324" ),
	array("&infin;+", "7ff0000000000000", "Infinity" ),
	array("&infin;-", "fff0000000000000", "-Infinity" ),
	array("Not-a-Number", "7ff8000000000000", "NaN" )
);

function test( $title, $values, $callback ){
	$parser = new BinaryParser;
	$to = "to$callback";
	$from = "from$callback";

print <<<END
	<table border="1">
		<tr><th colspan="6">$title</th></tr>
		<tr class="header">
			<td>Nome</td><td>Hexadecimal</td><td>Valor</td><td>[Valor => Bin]</td><td>[Bin => Value]</td>
		</tr>
END;
	foreach( $values as $test ){
print <<<END
		<tr>
			<td>$test[0]</td><td>$test[1]</td><td>$test[2]</td>
END;
		echo "<td>[" . ( $data = $parser->$from( $test[2] ) ) . "]</td>";
		echo "<td>[" . $parser->$to( $data ) . "]</td>";
		echo "</tr>";
	}
	echo "</table><br />";
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

<?php

test( "Float Test", $floatValues, "Float" );
test( "Double Test", $doubleValues, 'Double' );

?>
</body></html>