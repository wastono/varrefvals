<?php

/*

    Varrefvals. Simple code compiler of no $ sign for PHP.
    Copyright (C) 2016 - 2018 Wastono

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published
    by the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
	
*/

ob_implicit_flush(true);
echo "\n	Varrefvals (c) 2016 - 2018 Wastono.\n";

//	check file extension
if( $argv[1] == 'varrefvals.php' ) return;	//	skip varrefvals.php

if(preg_match('/.*?\.php$/i', $argv[1]))		//	execute .php file
{
	echo "\n	Executing $argv[1]...\n\n";
	include $argv[1];
	return;
}

if ($argv[1] == '')	//	empty argument: find all .var file, recursively
{
	$dir = getcwd();
	foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file)
	{
		if (preg_match('/.*?\.var$/i', $file) && !is_dir($file))
		{
			var2php($file);		// create .php file from .var file
		}
	}
	echo "\n", '	That\'s all in ', $dir, "\n";
	return;
}

if(!preg_match('/.*\.var/i', $argv[1]))	//	non empty argument: check .var file extension
{
	echo "\n	", $argv[1], "\n";
	echo '	please provide .var or .php file only. ' , "\n";
	return;
}

var2php($argv[1]);	// create a .php file from a .var file

function echoMessage($text)
{
	$now = DateTime::createFromFormat('U.u', microtime(true));
	echo "\n	", $now->format('Y-m-d H:i:s.u'), " : $text";
}

function maxi ($carry, $item)
{
	if (!isset($carry) || $carry == '') $carry = 0;
	
	$count = substr_count($item, '|');
	if ($count > $carry) $carry = $count;
	return $carry;
}

function mapping ($item)
{
	global $max;
	$delta = $max - substr_count($item, '|');
	$ad = '';
	$default = explode('|', $item)[0];
	
	for ($i = $delta - 1; $i >= 0; $i--) { $ad .= ',' . $default; }
	return "\t\t[". str_replace('|', $item[0] . ',' . $item[0], $item) . $ad . "],\r\n";
}

function klas ($item)
{
	global $rslt1;
	return $item . $rslt1;
}

function kode1 ($item) { return 't' . $item; }
function kode2 (&$item, $key)
{
	global $unik2;
	$unik2[] = '$this->t_r('. $key .')';
}

function var2php($path)
{
	echoMessage('start ' . $path);
	
	//	check file
	if (!file_exists($path))
	{
		echoMessage("file is not found!\n	$path\n");
		return;
	}
	
	//	read file content
	$file = file_get_contents($path);
	
	//	find all html parts
	$part = array();
	preg_match_all('/^(?!<\?).+?(?=<\?)|(?<=\?>).*?(?=<\?)|(?<=\?>).+?$/s', $file, $html);
	
	//var_dump($html); return;
	
	if ((bool)$html[0])
	{
		$html = array_values(array_unique($html[0]));
		array_multisort(array_map('strlen', $html), SORT_DESC, $html);
		$len = count($html);
		for ($i = $len - 1; $i > -1; $i-- )
		{
			$part[] = '<_'.$i.'_T__o__N_>';
		}
		//	replace html parts temporarily
		$file = str_replace($html, $part, $file);
	}
	
	//	------------------------------------------------------------------------
	//	Translator
	//	------------------------------------------------------------------------
	
	//	t'What is this?|Apakah ini?'
	$rslt = '';
	preg_match_all('/(?<=\bt)(["\'])(?:\\\\.|[^"\'])+?\1/', $file, $rslt);
	
	//var_dump($file);
	//echo "\n\n"; var_dump($rslt); return;
	
	global $rslt1;
	$rslt1 = '';
	$unik = [];
	
	if ((bool)$rslt[0])
	{
		$rslt1 = array_values(array_unique($rslt[0]));
		$unik = $rslt1;
		
		global $max;
		$max = 0;
		if ((bool)$rslt1)
		{
			$max = array_reduce($rslt1, "maxi");
			
			$rslt1 = "\r\n\tprivate \$t_l;\r\n\tprivate \$t_c;\r\n\tprivate \$t_d = [\r\n" .
				implode('', array_map("mapping", $rslt1)) .
				"\t];\r\n\tprivate function t_r (\$k) { return \$this->t_d[\$k][\$this->t_c]; }\r\n";
		}
	}
	
	//	pasang sisipan di dalam class
	$rslt2 = '';
	preg_match_all('/\bclass\b\s+[^{]+\{/', $file, $rslt2);
	
	if ((bool)$rslt2[0] && $rslt1 != '')
	{
		$rslt3 = array_map("klas", $rslt2[0]);
		$file = str_replace($rslt2[0], $rslt3, $file);
	}
	
	global $unik2;
	if ((bool)$unik)
	{
		$unik1 = array_map("kode1", $unik);
		$unik2 = [];
		
		array_walk($unik1, 'kode2');
		$file = str_replace($unik1, $unik2, $file);
	}
	
	//	tr(['en', 'id'], language); tl()
	
	$file = preg_replace([
		'/\btr\s*\(\s*(\[[^]]+])\s*,\s*([^;]+)\s*\)\s*;/',
		'/\btl\s*\(\s*\)/',
	],[
		"\$this->t_l = \${1};\r\n\t\t\$t_c = array_search(strtolower(\${2}), \$this->t_l);\r\n\t\t\$this->t_c = (\$t_c === false) ? 0 : \$t_c;\r\n\t\t\$this->t_l = \$this->t_l[\$this->t_c];",
		'$this->t_l',
	],
	$file);
	
	//	------------------------------------------------------------------------
	//	String Literal & Comments
	//	------------------------------------------------------------------------
	
	//	find all string literals & comments
	$coarr = array();
	preg_match_all('~\'\'|""|(\'|").*?(?:[^\\\\]|(?<=[^\\\\])(?:\\\\\\\\)+)\1|(?:#|//)[^\r\n]*|/\*[\s\S]*?\*/~', $file, $comment);
	if ((bool)$comment[0])
	{
		$comment = array_values(array_unique($comment[0]));
		array_multisort(array_map('strlen', $comment), SORT_DESC, $comment);
		$len = count($comment);
		for ($i = $len - 1; $i > -1; $i-- )
		{
			$coarr[] = '<_'.$i.'_W__a__S_>';
		}
		//	replace string literals & comments temporarily
		$file = str_replace($comment, $coarr, $file);
	}
	
	//	replace all casting temporaily
	$casting = 'string|int|integer|bool|boolean|float|double|real|array|object|unset|binary';
	$file = preg_replace('/(?<=[^\s\w])\s*\(\s*('.$casting.')\s*\)/', ' ${1}_C__t__G_ ', $file);
	
	$match = '';
	
	//	find variable by var keyword
	preg_match_all('/(?<=\bvar\s)\s*[^;=]+(?=[;=])/', $file, $match2);
	if ((bool)$match2[0])
	{
		$match .= implode(',' , $match2[0]) . ',';
	}
	
	//	find variable by global keyword
	preg_match_all('/(?<=\bglobal\s)\s*[^;]+(?=[;])/', $file, $match2);
	if ((bool)$match2[0])
	{
		$match .= implode(',' , $match2[0]) . ',';
	}
	
	//	find variable by static, public, private, protected keywords
	$find = array('static','public','private','protected');
	foreach( $find as $value )
	{
		preg_match_all('/(?<=\b'. $value .'\s)\s*\w+\s*(?=[;=])/', $file, $match2);
		if ((bool)$match2[0]) $match .= implode(',' , $match2[0]) . ',';
	}
	
	//	find variable by for keyword
	preg_match_all('/(?<=\bfor)\s*\(\s*([^;]+)(?=;)/', $file, $match2);
	if ((bool)$match2[1])
	{
		$match2 = ','.implode(',' , $match2[1]);
		preg_match_all('/(?<=,)[^=]+(?==)/', $match2, $match2);
		if ((bool)$match2[0]) $match .= implode(',' , $match2[0]) . ',';
	}
	
	//	find variable by foreach keyword
	preg_match_all('/(?<=\bas\s)\s*[^\)]+(?=\))/', $file, $match2);
	if ((bool)$match2[0])
	{
		$match2 = implode(',' , $match2[0]);
		$match .= preg_replace(array('/\bref\s+/','/\s*=>\s*/','/\s*:\s*/'), array('',',',','), $match2) . ',';
	}
	
	//	find variable by list keyword
	preg_match_all('/(?<=\blist)\s*\(\s*([^=]+)(?==)/', $file, $match2);
	if ((bool)$match2[1])
	{
		$match2 = implode(',' , $match2[1]);
		$match .= preg_replace(array('/\s*\[[^\]]+\]\s*/', '/\s*\)\s*/','/\s*list\s*\(\s*/'), '', $match2) . ',';
	}
	
	//	replace var, ref, refs, vals, this, supergobal alias
	$file = preg_replace(
		array(
		'/\bref\s+/',		'/\brefs\s+/',
		'/\bvals\s+/',		'/\bthis\./',
		'/\bvar\s+[^=;]+;/',
		'/\bvar\s+/',		'/\bdserver\b/',
		'/\bdget\b/',		'/\bdpost\b/',
		'/\bdrequest\b/',	'/\bdcookie\b/',
		'/\bdfiles\b/',		'/\bdenv\b/',
		'/\bdsession\b/',	'/\bdglobals\b/'
		),
		array(
		'&',				'&...',
		'...',				'$this->',
		'',
		'',					'$_SERVER',
		'$_GET',			'$_POST',
		'$_REQUEST',		'$_COOKIE',
		'$_FILES', 			'$_ENV',
		'$_SESSION',		'$GLOBALS'
		),
		$file);
	
	//	find variable by catch keyword
	preg_match_all('/(?<=\bcatch)\s*\(\s*[\w\\\\]+\s+(\w+)/', $file, $match2);
	if ((bool)$match2[1]) $match .= implode(',' , $match2[1]) . ',';
	
	//	find variable by function keyword
	preg_match_all('/(?<=\bfunction\b)[^\(]*\(\s*([^\{]+)(?=\{)/', $file, $match2);
	if ((bool)$match2[1])
	{
		$match2 = implode(',' , $match2[1]) . ',';
		$match2 = preg_replace(array(
			'/\s*=\s*array\s*\([^\)]+\)/',
			'/\s*=\s*\[[^\]]+\]/',
			'/\s*=[^,]+/',
			'/\s*\)\s*use\s*\(/',
			'/\s*\)\s*[^,]+/'), array('','','',',',''), $match2);
		preg_match_all('/\b\w+\s*(?=,)/', $match2, $match2);
		if ((bool)$match2[0]) $match .= implode(',' , $match2[0]);
	}
	
	//	put $ to variable
	$match = trim(preg_replace(array('/\s+/', '/,,/'), array('', ','), $match), ',');
	if ( $match != '' )
	{
		$match2 = '$'.str_replace(',',',$', $match);
		$match2 = explode(',', $match2);
		$match = '/(?<![\$>:\\\\])\b'.str_replace(',', '(?!\\\\)\b/,/(?<![\$>:\\\\])(?!\\\\)\b', $match) . '\b/';
		$match = explode(',', $match);
		$file = preg_replace($match, $match2, $file);
		
		//	fix error on class, interface, extends, implements, trait, & function names
		$file = preg_replace(
			['/\b(class|interface|extends|implements|trait)\s+\$/', '/\b(function)\s+(|&)\s*\$/'],
			['${1} ', '${1} ${2}'], $file);
		
		//	fix error on type declaration/hint & return type of function argument
		preg_match_all('/(?<=\bfunction\b)[^\{]*([,\(]\s*\$\w+\s+[&\$\.]|\)\s*:\s*\$\w+)[^\{]*(?=\{)/', $file, $match2);
		if ((bool)$match2[0])
		{
			foreach( $match2[0] as $value )
			{
				preg_match_all('/[,\(]\s*\$\w+\s+[&\$\.]|\)\s*:\s*\$\w+/', $value, $match3);
				if ((bool)$match3[0])
				{
					$match3 = $match3[0];
					$match4 = preg_replace('/\$/', '', $match3, 1);
					$match3 = str_replace($match3, $match4, $value);
					$file = str_replace($value, $match3, $file);
				}
			}
		}
	}
	
	//	replace dot for method / property
	$match4 = array(
		'/\$\w+\.[{\$\w]/',
		'/\w\.\w+\.[{\$\w]/',
		'/\w->\w+\.[{\$\w]/',
		'/\w->\w+\.[{\$\w]/',
		'/(?<!\$)\b\w+\.[\$\w]+\s*\(/',
		'/(?<=[^\$>])\b\w+\.\$?[A-Z]\w*/',
		'/(?<=[^\$>])\b\w+\.\$?[_a-z]\w*/'
		);
	$match5 = array(
		'->',
		'->',
		'->',
		'->',
		'::',
		'::',
		'::$'
		);
	foreach( $match4 as $key => $value )
	{
		preg_match_all($value, $file, $match2);
		if ((bool)$match2[0])
		{
			$match2 = $match2[0];
			$match3 = str_replace(array('.$','.'), $match5[$key], $match2);
			$file = str_replace($match2, $match3, $file);
		}
	}
	
	//	replace shortcut of empty object variable declaration: {} into (object)[]
	//$file = preg_replace('/=\s*{}/', '= (object)[]', $file);
	$file = preg_replace('/=\s*{}/', '= new stdClass', $file);
	
	//	restore casting
	$file = preg_replace('/('.$casting.')_C__t__G_/', '($1)', $file);
	
	//	replace colon for associative arrays
	preg_match_all('/[,\[\(]\s*([-]?\s*\d+|<\w+>|[$]?\w+(|[\[\(][^\?]*[\]\)])|([$]?\w+(::|->)[$]?\w+(|[\[\(][^\?]*[\]\)]))+)\s*:(?!:)|as\s+\$\w+\s*:/', $file, $match);
	if ((bool)$match[0])
	{
		$match = array_values(array_unique($match[0]));
		$match2 = str_replace([':','=>=>'], ['=>','::'], $match);
		$file = str_replace($match, $match2, $file);
	}
	
	//	restore string literals & comments
	if ((bool)$comment) $file = str_replace($coarr, $comment, $file);
	
	//	restore html parts
	if ((bool)$html) $file = str_replace($part, $html, $file);
	
	//	write .php file
	$name = str_replace('.var', '.php', $path);
	file_put_contents( $name , $file);
	
	echoMessage("write $name\n");
}
