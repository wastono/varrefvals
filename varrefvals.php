<?php

//	varrefvals.php

/*
	
    Varrefvals. Simple code compiler for PHP code written without $ => :: -> ; signs.
    Copyright (C) 2016 - 2019 Wastono
	
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

//	Varrefvals version 2.0

class ResultPackage
{
	private $counter = 0;
	public $alias = [];
	public $part = [];
	public $variable = [];
	public $falseVariable = [];
	
	//	generate alias
	public function generateAlias (&$part)
	{
		$alias = 'A_' . $this->counter++ . '_';
		array_unshift($this->alias, $alias);
		array_unshift($this->part, $part);
		return $alias;
	}
	
	//	isolate text
	public function isolateText (&$text)
	{
		return ($text === '') ? '' : ('X_' . $text . '_');
	}
	
	//	isolate part
	public function isolatePart (&$part)
	{
		if (strpos($part, '\\') !== false) return $this->generateAlias($part);
		return $this->isolateText($part);
	}
}

class Varrefvals
{
	public $package;
	
	public function __construct ()
	{
		ob_implicit_flush(1);
		$this->message("Varrefvals (c) 2016 - 2019 Wastono.\n");
	}
	
	private function message ($s) { echo "\n\t", $s; }
	private function message2 ($s)
	{
		$now = DateTime::createFromFormat('U.u', microtime(true));
		echo "\n\t", $now->format('Y-m-d H:i:s.u'), " : ", $s;
	}
	
	public function execute ($file = '')
	{
		try
		{
			//	check file
			//	skip varrefvals.php
			if( $file == 'varrefvals.php' )
			{
				$this->message("skip varrefvals.php file.\n\n");
				return;
			}
			
			//	execute .php file
			if (preg_match('/.*?\.php$/i', $file))
			{
				$this->message('Executing ' . $file . "...\n\n");
				include $file;
				return;
			}
			
			//	empty argument: find all .var file, recursively
			if ($file == '')
			{
				$dir = getcwd();
				foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $path)
				{
					if (preg_match('/.*?\.var$/i', $path) && !is_dir($path))
					{
						// create .php file from .var file
						$this->var2php($path);
					}
				}
				$this->message("That's all in " . $dir . "\n");
				return;
			}
			
			//	non empty argument: check .var file extension
			if(!preg_match('/.*?\.var$/i', $file))
			{
				$this->message($file);
				$this->message("Please provide .var or .php file only.\n");
				return;
			}
			
			//	create a .php file from a .var file
			$this->var2php($file);
		}
		catch (Exception $e)
		{
			$this->message2($e->getMessage());
		}
	}
	
	//	convert .var file to php file
	public function var2php ($path)
	{
		$filename = basename($path);
		$this->message2('start ' . $filename);
		
		//	check file
		if (!file_exists($path))
		{
			$this->message2("file is not found!\n\t" . $path . "\n");
			return;
		}
		
		//	result package
		$this->package = new ResultPackage;
		
		//	add special variable
		$this->package->variable[] = 'this';
		
		//	read file content
		$file = file_get_contents($path);
		
		//	1. html
		$this->isolateHtmlPart($file);
		
		//	2. heredoc
		//	3. nowdoc
		$this->isolateDocString($file);
		
		//	4. single quoted string
		//	5. double quoted string
		//	6. backtick operator
		$this->isolateQuotedText($file);
		
		//	7. comment
		$this->isolateComment($file);
		
		//	8. interface
		//	9. trait
		$this->isolateInterfaceAndTraitName($file);
		
		//	10. namespace
		$this->isolateNamespace($file);
	}
	
	//	isolate namespace
	public function isolateNamespace (&$file)
	{
		$file = preg_replace_callback(
		[
			//	namespace
			str_replace('\s*', self::CommentEscape,
			'~\bnamespace\b\s\s*[^;{]+|[^\\\\]\K\bnamespace\b\\\\[^\[(;\s#/]+~'),
		],
		function ($match)
		{
			$match[0] = str_replace('.', '::', $match[0]);
			return $this->package->generateAlias($match[0]);
		}
		, $file);
	}
	
	//	isolate interface and trait name
	public function isolateInterfaceAndTraitName (&$file)
	{
		$file = preg_replace_callback(
		[
			//	interface, trait
			'~\s\b(?:interface|trait)\s\K(?>[^{]+)~',
		],
		function ($match)
		{
			return $this->package->generateAlias($match[0]);
		}
		, $file);
	}
	
	//	isolate comment
	public function isolateComment (&$file)
	{
		$file = preg_replace_callback(
		[
			//	comment
			'~(?|(#|//)([^\r\n]*)|(/\*)(.*?)\*/)~s',
			//		1		2	 |	1	 2
		],
		function ($match)
		{
			return $match[1] . $this->package->generateAlias($match[2]) . (($match[1] == '/*') ? '*/' : '');
		}
		, $file);
	}
	
	//	isolate quoted string and backtick
	public function isolateQuotedText (&$file)
	{
		$file = preg_replace_callback(
		[
			//	single n double quoted string, backtick operator
			'~(?|(?>([\'"])(.*?(?<!\\\\)(\\\\\\\\)*)\1)|(`)([^`]*)`)~s',
			//			1					2			 1	  2
		],
		function ($match)
		{
			if ($match[2] === '') return $match[0];
			return $match[1] . $this->package->generateAlias($match[2]) . $match[1];
		}
		, $file);
	}
	
	//	isolate heredoc and nowdoc
	public function isolateDocString (&$file)
	{
		$file = preg_replace_callback(
		[
			//	heredoc n nowdoc
			str_replace('\s*', self::CommentEscape,
			'~(<<<[\'"]?(\b\w+\b).+?[\r\n][ \t]*\b\g{-1}\b)()(\s*)([,);\]]?)~s'),
			//  1           2                              3   4      5
		],
		function ($match)
		{
			return $this->package->generateAlias($match[1]) . ($match[5] ? $match[5] : ';') . $match[4];
		}
		, $file);
	}
	
	//	isolate html part
	public function isolateHtmlPart (&$file)
	{
		$file = preg_replace_callback(
		[
			//	html
			'~^(?!<\?).+?(?=<\?)|(?<=\?>).+?(?=<\?)|(?<=\?>).+?$~s',
		],
		function ($match)
		{
			return $this->package->generateAlias($match[0]);
		}
		, $file);
	}
}


$varrefvals = new Varrefvals;
$varrefvals->execute($argv[1]);



function echoMessage($text)
{
	$now = DateTime::createFromFormat('U.u', microtime(true));
	echo "\n	", $now->format('Y-m-d H:i:s.u'), " : $text";
}

function var2php($path)
{
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
	
	echoMessage("write $name.\n");
}
