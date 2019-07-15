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
		
		//	11. catch
		$this->processCatchPart($file);
		
		//	12. class (extends, implements)
		//	13. use for namespace
		//	14. use for trait
		$this->processClassAndUsePart($file);
		
		//	15. function
		//	16. fn
		$this->processFunctionAndFnPart($file);
		
		//	17. digit space (binary, octal, decimal, float, hexadecimal)
		$this->removeDigitSpace($file);
		
		//	18. properties declaration
		//	19. variable declaration
		//	20. global variable
		$this->processVariableIntro($file);
		
		//	21. list
		$this->processListPart($file);
		
		//	22. foreach as
		$this->processForeachAsPart($file);
		
		//	23. yield
		$this->processYieldPart($file);
		
		//	24. pairing notation
		$this->replacePairingNotation($file);
		
		//	25. assignment
		$this->processAssignmentPart($file);
		
		//	26. aliases
		$this->replaceAliases($file);
		
		//	27. variable
		$this->variablilize($file);
	}
	
	//	variablilize
	public function variablilize (&$file)
	{
		$this->package->variable = array_filter(preg_replace_callback('~^(?i:' . self::Reserved . ')$~',
		function ($match)
		{
			$this->package->falseVariable[] = $match[0];
			return '';
		}
		, array_unique($this->package->variable)));
		
		if ($this->package->falseVariable) $this->message2("false variable detected:\n\t" . implode(', ', $this->package->falseVariable) . "\n");
		
		$file = preg_replace_callback(
		[
			str_replace(
			[
				'\s*',
				'\c*',
			],
			[
				self::CommentEscape,
				self::CodeEscape,
			],
			'~(?:[\w})]\s*\.|(..))\K(?<!\$|::|->|\\\)(?!\b(?i:' . self::SemiReserved . ')\b(?:\\\|\s*\())(?=\b(?:' . implode('|', $this->package->variable) . ')\b(?!\\\))~s'),
		],
		function ($match)
		{
			return isset($match[1]) ? '$' : '';
		}
		, $file);
	}
	
	//	replace aliases
	public function replaceAliases (&$file)
	{
		$file = preg_replace(
		[
			'~\bref\b(?:\s+|(?=\())~',		//	2
			'~\brefs\s+~',		//	3
			'~\bvals\s+~',		//	4
			'~\b\s*=\s*\{\}~',	//	5
			'~\bdserver\b|\bthe\.server\b~',	//	6
			'~\bdget\b|\bthe\.get\b~',			//	7
			'~\bdpost\b|\bthe\.post\b~',		//	8
			'~\bdrequest\b|\bthe\.request\b~',	//	9
			'~\bdcookie\b|\bthe\.cookie\b~',	//	10
			'~\bdfiles\b|\bthe\.files\b~',		//	11
			'~\bdenv\b|\bthe\.env\b~',			//	12
			'~\bdsession\b|\bthe\.session\b~',	//	13
			'~\bdglobals\b|\bthe\.globals\b~',	//	14
			'~\bdhttpresponseheader\b|\bthe\.httpResponseHeader\b~',	//	15
			'~\bargc\b|\bthe\.argc\b~',		//	16
			'~\bargv\b|\bthe\.argv\b~',		//	17
			'~\bdline\b|\bthe\.line\b~',		//	18
			'~\bdfile\b|\bthe\.file\b~',		//	19
			'~\bddir\b|\bthe\.dir\b~',			//	20
			'~\bdfunction\b|\bthe\.function\b~',	//	21
			'~\bdclass\b|\bthe\.class\b~',		//	22
			'~\bdtrait\b|\bthe\.trait\b~',		//	23
			'~\bdmethod\b|\bthe\.method\b~',	//	24
			'~\bdnamespace\b|\bthe\.namespace\b~',	//	25
		],
		[
			'&',				//	2
			'&...',				//	3
			'...',				//	4
			' = new stdClass',	//	5
			'$_SERVER',			//	6
			'$_GET',			//	7
			'$_POST',			//	8
			'$_REQUEST',		//	9
			'$_COOKIE',			//	10
			'$_FILES',			//	11
			'$_ENV',			//	12
			'$_SESSION',		//	13
			'$_GLOBALS',		//	14
			'$http_response_header',		//	15
			'$argc',			//	16
			'$argv',			//	17
			'__LINE__',			//	18
			'__FILE__',			//	19
			'__DIR__',			//	20
			'__FUNCTION__',		//	21
			'__CLASS__',		//	22
			'__TRAIT__',		//	23
			'__METHOD__',		//	24
			'__NAMESPACE__',	//	25
		]
		, $file);
	}
	
	//	process assignment part
	public function processAssignmentPart (&$file)
	{
		$file = preg_replace_callback(
		[
			str_replace(
			[
				'\s*',
				'\c*',
			],
			[
				self::CommentEscape,
				self::CodeEscape,
			],
			'~((?:\b(?:A_\d+_|X_\w+_)\b[ \t]+|\bconst\b|\bdeclare\b\s*\(\s*)?)(\s*)((?<!->|\.|::)\$?)(\c*\b\w+\b)([ \t]*(\[(?:(?>[^\[\]]+)|(?-1))*\])?)(\s*(?:[-%|+/&.^]|\*\*?|>>|<<|\?\?)?[=])(?![=>])~'),
			//                  1                                                 2          3               4       5                6                              7              
		],
		function ($match)
		{
			if ($match[1]) return $match[0];
			$match[3] = '$';
			$this->package->variable[] = $match[4];
			$match[6] = '';
			$match[0] = '';
			return implode($match);
		}
		, $file);
	}
	
	//	replace pairing notation
	public function replacePairingNotation (&$file)
	{
		//	pairing:	key : value
		$file = preg_replace(
		[
			'~[(\[,]\s*[-]?\s*[\'"]?\$?\w+[\'"]?(?:(?:\.|::|->)\$?\w+)*\s*(?:\[[^\]]*\]\s*|\([^)]*\)\s*)*\s*\K:(?![:?])(?=\s*\S)~',
			//                1                      2      3       4       5    6         7                       
		], '=>', $file);
	}
	
	//	process yield part
	public function processYieldPart (&$file)
	{
		$file = preg_replace_callback(
		[
			'~\byield\b\s*\K(\$?)((?!\d+|\bfrom\b)\b\w+\b)(\s*)((?:=>|:)?)(\s*)(\$?)((?:\b\w+\b)?)(?=\s*(?:\)|;))~',
			//                1                      2      3       4       5    6         7                       
		],
		function ($match)
		{
			$match[1] = '$';
			$this->package->variable[] = $match[2];
			if ($match[4]) $match[4] = '=>';
			if ($match[7])
			{
				$match[6] = '$';
				$this->package->variable[] = $match[7];
			}
			$match[0] = '';
			return implode($match);
		}
		, $file);
	}
	
	//	process foreach as part
	public function processForeachAsPart (&$file)
	{
		$file = preg_replace_callback(
		[
			str_replace('\s*', self::CommentEscape,
			'~\bas\b\s*\K((?:\bref\b|&)?)(\s*)(\$?)(\b\w+\b)(\s*)((?:=>|:)?)(\s*)((?:\bref\b|&)?)(\s*)(\$?)((?:\b\w+\b)?)(\s*\))~'),
			//                    1        2    3      4      5       6       7         8          9    10        11        12                             |                       1              
		],
		function ($match)
		{
			if ($match[1]) $match[1] = '&';
			if ($match[8]) $match[8] = '&';
			
			$match[3] = '$';
			$this->package->variable[] = $match[4];
			if ($match[6]) $match[6] = '=>';
			if ($match[11])
			{
				$this->package->variable[] = $match[11];
				$match[10] = '$';
			}
			$match[0] = '';
			return implode($match);
		}
		, $file);
	}
	
	//	process list part
	public function processListPart (&$file)
	{
		$file = preg_replace_callback(
		[
			str_replace('\s*', self::CommentEscape,
			'~\blist\b\s*\K(\((?:(?>[^()]+)|(?-1))*\))|\bas\b\s*\K(\[(?:(?>[^\[\]]+)|(?-1))*\])|(\[(?:(?>[^\[\]]+)|(?-1))*\])(?=\s*=)~'),
			//                         1              |               1                        |                       1              
		],
		function ($match)
		{
			$match[0] = str_replace(':', '=>', $match[0]);
			return preg_replace_callback(
			[
				str_replace('\s*', self::CommentEscape,
				'~([\[(,>]\s*&?\s*)((?:\bref\b\s*)?)(\$?)((?!\blist\b|\d+)\b\w+\b)(?=\s*[\[\],)])~'),
				//        1                2           3                  4                       
			],
			function ($found)
			{
				if ($found[2]) $found[2] = '&';
				
				$found[3] = '$';
				$this->package->variable[] = $found[4];
				
				$found[0] = '';
				return implode($found);
			}
			, $match[0]);
		}
		, $file);
	}
	
	//	process variable intro
	public function processVariableIntro (&$file)
	{
		$file = preg_replace_callback(
		[
			str_replace(
			[
				'\s*',
				'\c*',
			],
			[
				self::CommentEscape,
				self::CodeEscape2,
			],
			//	global
			'~(?|()(\bglobal\b[ \t]+)()()()(\$?\b\w+\b(?:\s*,\s*\b\w+\b)*)((?:\s*;)?)'
			//   1       2           3 4 5   6                                   7   
			//        keyword                $name         , buntut              ;   
			
			//	properties, variable declaration
			. '|()(\b(?:var|public|private|protected|static)\b[ \t]+(?:\b(?:public|private|protected|static)\b[ \t]+)?)(\??[ \t]*)((?:[\\\\]?\c*\b[\w\\\\]+\b)?)([ \t]*)(\$?\b\c*\w+\b(?:\s*[=,]\s*[^,;\r\n]*)*)((?:\s*;)?)'
			//  1        2                                                                                                3                         4              5      6                                            7   
			//        keyword                                                                                             ?                       type                    $name          , buntut                      ;
			
			//	bare properties
			. '|((?:(?:\S\s*)?[\r\n]+|[\r\n{};])[ \t]*)()(\??[ \t]*)([\\\\]?\c*\b[\w\\\\]+\b)([ \t]+)(\$?\b\c*\w+\b(?:\s*[=,]\s*[^,;\r\n]*)*)((?:\s*;)?))~'
			//          1                              2    3                      4             5     6                                            7     
			//         cek                                  ?                    type                  $name   , buntut                             ;      
			),	
		],
		function ($match)
		{
			if (preg_match('~^[,\[(]~', $match[1])) return $match[0];
			if (substr($match[2], 0, 3) == 'var' && $match[4] === '') $match[2] = ltrim(substr($match[2], 3));
			$match[4] = $this->package->isolatePart($match[4]);
			
			//	meletakkan tanda ;
			$match[6] = preg_replace('~('. self::CommentEscape . ')_REPLACED$~', ';$1', $match[6] . '_REPLACED');
			
			//	+ bagian $nama + buntut setelah awal property declaration
			$match[6] = preg_replace_callback(
			[
				str_replace('\s*', self::CommentEscape,
				'~(^|\s*,\s*)(\$?)(\b\w+\b)((?:\s*=\s*(\[(?:(?>[^\[\]]+)|(?-1))*\])?(?>(?:\b\w+\b\s*)+(\((?:(?>[^()]+)|(?-1))*\)))?[^,\r\n]*)?)~'),
				//	    1      2      3        4                       5                                            6
				//      ,      $    nama     buntut
			],
			function ($found)
			{
				$found[2] = '$';
				$this->package->variable[] = $found[3];
				$found[5] = '';
				$found[6] = '';
				$found[0] = '';
				return implode($found);
			}
			, $match[6]);
			
			$match[7] = str_replace(';', '', $match[7]);
			$match[0] = '';
			return implode($match);
		}
		, $file);
	}
	
	//	remove digit space
	public function removeDigitSpace (&$file)
	{
		$file = preg_replace_callback(
		[
			'~\d\K (?=\d)|0x[a-f\d][a-f \d]+[a-f\d]~i',
		],
		function ($match)
		{
			return str_replace(' ', '', $match[0]);
		}
		, $file);
	}
	
	//	process function and fn part
	public function processFunctionAndFnPart (&$file)
	{
		$file = preg_replace_callback(
		[
			str_replace(
			[
				'\s*',
				'\c*',
			],
			[
				self::CommentEscape,
				self::CodeEscape2,
			]
			, '~(?>(\bf(?:unctio)?n\b)(\s*)((?:\bref\b|&)?)(\s*)((?:\c*\b(?>\w+)\b)?)(\s*)(\((?:(?>[^()]+)|\)\s*\buse\b\s*\(|(?-1))*\))([ \t]*)(:?)([ \t]*)(\??)([ \t]*)((?:\c*(?>[\w\\\\]+))?)(\s*)([{;:=]?))~'),
			//			   1            2          3         4               5         6                7                                8      9      10    11    12             13           14     15
			//			   fn                      &                        name                     argument                                   :            ?                   type               buntut
		],
		function ($match)
		{
			//	arguments		
			$match[7] = preg_replace_callback(
			[
				str_replace(
				[
					'\s*',
					'\c*',
				],
				[
					self::CommentEscape,
					self::CodeEscape,
				]
				, '~(?>([\(,]\s*\??\s*)((?>\c*[\w\\\\]+)?)(\s*&?\s*)((?:\bvals\b|\brefs?\b)?)(\s*[.]*\s*)(\$?)(\c*\b\w+\b)(\s*=?\s*(\[(?:(?>[^\[\]]+)|(?-1))*\])?(?>(?:\b\w+\b\s*)+(\((?:(?>[^()]+)|(?-1))*\)))?(?>[^,\)]+)?))(?=$|[,\)])~'),
				//             1                2              3                4                  5       6        7          8                  9                                           10
				//       (,    ?              type             &               ref                ...      $       name      buntut
			],
			function ($found)
			{
				//	variable type
				$found[2] = $this->package->isolatePart($found[2]);
				
				//	reference
				if ($found[4]) $found[4] = str_replace(['vals', 'refs', 'ref'], ['...', '&...', '&'], $found[4]);
				
				$this->package->variable[] = $found[7];
				$found[6] = '$';
				$found[9] = '';
				$found[10] = '';
				$found[0] = '';
				return implode($found);
			}
			, $match[7]);
			
			//	function / fn name
			$match[5] = $this->package->isolateText($match[5]);
			
			//	return reference
			if ($match[3]) $match[3] = '&';
			
			$isolateReturnType = false;
			
			if ($match[1] == 'fn')
			{
				if ($match[13])	//	return type
				{
					if ($match[15] == ':')	//	buntut
					{
						$isolateReturnType = true;
						$match[15] = '=>';
						$match[9] = ':';
					}
					else if ($match[15] == '=')
					{
						$isolateReturnType = true;
					}
					else if ($match[9]) $match[9] = '=>';
				}
				else if ($match[9]) $match[9] = '=>';
			}
			else	//	bagian function
			{
				if ($match[15] == '')
				{
					if ($match[13] == '')
					{
						$match[9] = '';
						$match[11] = '';
						$match[8] = ';' . $match[8];
					}
					else $match[14] = ';' . $match[14];
				}
				if ($match[13])
				{
					$match[9] = ':';
					$isolateReturnType = true;
				}
			}
			
			if ($isolateReturnType) $match[13] = $this->package->isolatePart($match[13]);
			
			$match[0] = '';
			return implode($match);
		}
		, $file);
	}
	
	//	process class and use part
	public function processClassAndUsePart (&$file)
	{
		$file = preg_replace_callback(
		[
			//	class (extends, implements) n use for namespace
			str_replace('\s*', self::CommentEscape,
			'~(?|\s\K(\bclass\b)(\s\s*)([^{]+)(\{(?:(?>[^{}]+)|(?-1))*\})|\s\K(\buse\b)(\s*)((?:\s*,?\s*(?!\()[\w\\\\]+)+)(\s*)((?:\{[^}]+\})?)(\s*)(;?))~'),
			//            1        2      3              4               |        1      2               3                  4       5            6   7    
			//			class            name          body              |       use                    name                       item              ;    
		],
		function ($match)
		{
			if ($match[0][0] == 'c')	//	class
			{
				$match[4] = preg_replace_callback(
				[
					//	use in class for trait related
					str_replace('\s*', self::CommentEscape,
					'~(?>\s\buse\b\s*)\K((?>\s*,?\s*(?!\()\b\w+\b)+)(\s*)(;?)((?>\{[^}]+\})?)~'),
					//					              1               2   3          4
					//							     name                 ;         item
				],
				function ($found)
				{
					$found[1] = $this->package->generateAlias($found[1]);
					$found[3] = '';
					
					if ($found[4] == '') $found[2] = ';' . $found[2];
					else $found[4] = preg_replace_callback(
					[
						//	trait item part
						str_replace('\s*', self::CommentEscape,
						'~(?>\{?\s*)\K((?>\b[\w:.]+\b))(\s\s*)((?>\b\w+\b\s*,?\s*)+)(;?)~'),
						//					  1		          2           3                     4
						//					nama                        nama                    ;
					],
					function ($got)
					{
						$got[3] = str_replace('.', '::', $got[1]) . $got[2] . $got[3];
						$got[2] = '';
						$got[1] = '';
						$got[4] = ';';
						$got[3] = $this->package->generateAlias($got[3]);
						$got[0] = '';
						return implode($got);
					}
					, $found[4]);
					
					$found[0] = '';
					return implode($found);
				}
				, $match[4]);
			}
			else	//	use, namespace related
			{
				if ($match[5]) $match[6] = ';' . $match[6];
				else
				{
					$match[6] = ';' . $match[4] . $match[6];
					$match[4] = '';
				}
				
				$match[7] = '';
				$match[3] = $match[3] . $match[4] . $match[5];
				$match[4] = '';
				$match[5] = '';
			}
			
			$match[3] = $this->package->generateAlias($match[3]);
			$match[0] = '';
			return implode($match);
		}
		, $file);
	}
	
	//	process catch part
	public function processCatchPart (&$file)
	{
		$file = preg_replace_callback(
		[
			//	catch
			str_replace('\s*', self::CommentEscape,
			'~\bcatch\b\s*\(\K([^)]+?)(\s)(\$?)(\b\w+\b)(?=\s*\))~'),
			//				    1       2    3     4         
			//				  type           $    name
		],
		function ($match)
		{
			$this->package->variable[] = $match[4];
			$match[3] = '$';
			$match[1] = $this->package->generateAlias($match[1]);
			$match[0] = '';
			return implode($match);
		}
		, $file);
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
	
	//	restore string literals & comments
	if ((bool)$comment) $file = str_replace($coarr, $comment, $file);
	
	//	restore html parts
	if ((bool)$html) $file = str_replace($part, $html, $file);
	
	//	write .php file
	$name = str_replace('.var', '.php', $path);
	file_put_contents( $name , $file);
	
	echoMessage("write $name.\n");
}
