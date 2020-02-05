<?php

$variable1 = 0;
$variable2 = 1000;
$variable3[] = 'array';

$x = [1, 2, 3];
$y = [0, &$x, 4];

var_dump($y);

class ClassName
{
	
	var int $tes = 0xDFFDDD; 
	
	var string $property2;
	var int $property1;
	
	private float $property3, $property4 = 0.0;
	protected int $property5 = 10000000, $property6 = 100000;
	
	public static $property7;
	public $property8 = [ 'one' => 1, 'two' => 2 ];
	public const Constant1 = 'SALAAM';
	
	public function method1 ()
	{
		echo 'this is method 1.' . "\n";
		self::method2();
	}
	
	
	
	var int $dd = 0;
	public static function method2 () :string
	{
		echo 'this is content of property 7: ' . self::$property7 . "\n";
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
//	, variable4.class, "\n"	//	next on php 8

var_dump($variable4->property8);

$variable5 = new stdClass;
$variable5->newProperty1 = 0x1A2B3C4D;		//	0x1A2B3C4D
$variable5->newProperty2 = '';
$variable5->newProperty3 = new ClassName;

var_dump($variable5);
echo $variable5
	->newProperty3
	::Constant1
	, "\n";

try
{
	$variable6 = fn ($a) => $a * $a;
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

$variable8 = function (
	&$x,
	$y = 0.123456,
	$z = [1.2, 3.4, 5.6]
) use ($d) :int
{
	//	yield x : d
	echo $d, "\n";
	return $x + $x;
};
echo $variable8($variable5->newProperty1), "\n";

if (!isset($_POST['none'])) echo "none\n";

for ($i = 0; $i < 12; $i++)
{
	echo $i;
}
echo "\n";
for ($j = 10; $j > 0; $j -= 2)
{
	echo $j;
}
echo "\n";
$y = 10;
for ($j = 1; $j < 8; $j += 2, $y++)
{
	echo $j + $y;
}

$f = fn ($xx) :int => $xx * 8;
$f = fn ($xx) => 8 * 8;
$f = fn ($xx) => $xx * 8;

$f = preg_replace_callback('~.~', function ($match)
{
	var_dump($match);
}, 'testing');

$f = preg_replace_callback('~.~', fn ($match) => var_dump($match), 'testing');
$f = preg_replace_callback('~.~', fn ($match) :string => $match[0], 'testing');
$f = preg_replace_callback('~.~', fn ($match) :?string => $match[0], 'testing');

/*
int|string ss = 'as'
int/string ss = 'as'
//	next on php 8
//*/

switch ($x)
{
	case 1:
		echo 1;
		break;
	
	case 2:
	case 3:
		echo 2;
		break;
	
	case 4:
	case 5:
	case 6:
		echo 4;
		break;
	
	case 6:
	case 7:
	case 8:
	case 9:
	case 10:
	case 11:
	case 12:
		echo 4;
		break;
	
	default:
		echo 'akhir';
}


