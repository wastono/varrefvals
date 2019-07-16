<?php

$variable1 = 0;
$variable2 = 1000;
$variable3[] = 'array';

class ClassName
{
	//	int property1
	//	var string property2
	
	//	private float property3, property4 = 0.0
	//	protected int property5 = 10 000 000, property6 = 1 000 00
	
	public static $property7;
	public $property8 = [ 'one' => 1, 'two' => 2 ];
	public const Constant1 = 'SALAAM';
	
	public function method1 ()
	{
		echo 'this is method 1.', "\n";
		self::method2();
	}
	
	public static function method2 () :string
	{
		echo 'this is content of property 7: ', self::$property7, "\n";
		return '';
	}
	
	public function method3 ()
	{
		global $variable3;
		echo $variable3[0], "\n";
	}
}

ClassName::$property7 = 'just string';

$variable4 = new ClassName;
$variable4->method1();
$variable4->method3();
$variable4->{'method3'}();

echo ClassName::Constant1, "\n", ClassName::class, "\n";
var_dump($variable4->property8);

$variable5 = new stdClass;
$variable5->newProperty1 = 0x1A2B3C4D;		//	0x1A2B3C4D
$variable5->newProperty2 = '';

var_dump($variable5);

try
{
	//	variable6 = fn (a) : return a * a
	$variable7 = function ($b) :int { return $b * $b; };
	echo $variable7(30), "\n";
}
catch (Exception $e)
{
	echo $e->message(), "\n";
}

$variable3[] = 'string';
foreach ($variable3 as $key => $value)
{
	echo $key, ' : ', $value, "\n";
}

list($d, $e) = ['d', 'e'];
[$f, $g, [$h, $i]] = ['f', 'g', ['h', 'i']];

echo $d, $e, $f, $g, $h, $i, "\n";

$variable8 = function (& $x) use ($d) :int
{
	//	yield x : d
	echo $d, "\n";
	return $x + $x;
};
echo $variable8($variable5->newProperty1), "\n";

if (!isset($_POST['none'])) echo "none\n";