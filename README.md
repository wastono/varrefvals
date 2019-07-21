# Varrefvals

I know, it's too *LOL* wanting PHP coding without `$` sign on its variable name. But, writing code with `$` sign on every variable is adding typing complexity, for me. I wish I could do coding using simple syntax only. So I decided to design and write simple program that can be used for compiling no `$` sign code into normal PHP code.

Now, I add the *LOL* level by adding feature: coding without `=>` `::` `->` `;` signs too.

**Varrefvals**, that's the name that I use for this program. It comes from 3 selected keywords: `var`, `ref`, and `vals`. `var` keyword is used as direct variable declaration. `ref` keyword is used for setting variable accessed by reference. `vals` keyword is used for setting variables accessed by value.

Varrefvals is a simple helper to write PHP codes. All PHP rules are apply to Varrefvals codes with some exceptions due to Features and Needs stated below.

## Features

1. Variable name can be written without `$` sign.
2. The end of statement line can be written without `;` sign.
3. Methods and properties can be accessed using `.` sign.
4. Associative pairing can be written using `:` sign.
5. Empty object variable can be declared using `{}` signs.
6. Digit separator can be written using *single space* character.
7. Return type of `function` / `fn` can be written without `:` sign.
8. Arrow on `fn` can be replaced by `:` sign.
9. Variable can be declared directly using `var` keyword.
10. Variable names can be detected from  *properties declaration*, *assignment*, and *keywords*: `global`, `foreach` `as`, `list`, `catch`, `function`, `fn`, `use`.
11. Keyword `ref` can be used for declaring *expression* accessed by reference.
12. Keyword `refs` can be used for declaring *expressions* accessed by reference.
13. Keyword `vals` can be used for declaring *expressions* accessed by value.
14. Some *predefined variables* and *magic constants* are aliased.

## Needs

1. Constant names need to be started by *upper cased* alphabet.
2. Variable, property, and method names need to be started by *lower cased* alphabet.
3. Method needs to be followed by `()` signs.
4. Variable name needs to be different from keywords and function names.
5. Dot operator for string concatenation needs to be separated from *expressions* by *space* characters.
6. Dot operator for *object* (instance, special variable (`this`, `self`, `static`, `parent`), and class name) needs to be without space next to the property or method name. *Object* can be separated from dot operator on the next line of statement.
7. Accessing *object*, property, or method name within variable should not be used. Please use `{ expression }` instead.

## Aliases

| No. | Varrefvals code | PHP code |
|:---:|---|---|
| 1 | `ref` | `&` |
| 2 | `refs` | `&...` |
| 3 | `vals` | `...` |
| 4 | `dserver` | `$_SERVER` |
| 5 | `the.server` | `$_SERVER` |
| 6 | `dget` | `$_GET` |
| 7 | `the.get` | `$_GET` |
| 8 | `dpost` | `$_POST` |
| 9 | `the.post` | `$_POST` |
| 10 | `drequest` | `$_REQUEST` |
| 11 | `the.request` | `$_REQUEST` |
| 12 | `dcookie` | `$_COOKIE` |
| 13 | `the.cookie` | `$_COOKIE` |
| 14 | `dfiles` | `$_FILES` |
| 15 | `the.files` | `$_FILES` |
| 16 | `denv` | `$_ENV` |
| 17 | `the.env` | `$_ENV` |
| 18 | `dsession` | `$_SESSION` |
| 19 | `the.session` | `$_SESSION` |
| 20 | `dglobals` | `$GLOBALS` |
| 21 | `the.globals` | `$GLOBALS` |
| 22 | `dhttpResponseHeader` | `$http_response_header` |
| 23 | `the.httpResponseHeader` | `$http_response_header` |
| 24 | `dargc` | `$argc` |
| 25 | `the.argc` | `$argc` |
| 26 | `dargv` | `$argv` |
| 27 | `the.argv` | `$argv` |
| 28 | `dline` | `__LINE__` |
| 29 | `the.line` | `__LINE__` |
| 30 | `dfile` | `__FILE__` |
| 31 | `the.file` | `__FILE__` |
| 32 | `ddir` | `__DIR__` |
| 33 | `the.dir` | `__DIR__` |
| 34 | `dfunction` | `__FUNCTION__` |
| 35 | `the.function` | `__FUNCTION__` |
| 36 | `dclass` | `__CLASS__` |
| 37 | `the.class` | `__CLASS__` |
| 38 | `dtrait` | `__TRAIT__` |
| 39 | `the.trait` | `__TRAIT__` |
| 40 | `dmethod` | `__METHOD__` |
| 41 | `the.method` | `__METHOD__` |
| 42 | `dnamespace` | `__NAMESPACE__` |
| 43 | `the.namespace` | `__NAMESPACE__` |

## Conversion

**I am not update this section**. Please see *sample.var* or *simple.var* for simple code sample. Compile it to see the conversion result.

| No. | Varrefvals code | PHP code |
|:---:|---|---|
| 1 | `var counter; counter = 1;` | `; $counter = 1;` |
| 2 | `var counter = 0;` | `$counter = 0;` |
| 3 | `var counter, limit, total; counter = 1; limit = 10; total = 0;` | `; $counter = 1; $limit = 10; $total = 0;` |
| 4 | `global var1; global var2, var3;` | `global $var1; global $var2, $var3;` |
| 5 | `public prop1; private prop2; protected prop3; static prop4;` | `public $prop1; private $prop2; protected $prop3; static $prop4;` |
| 6 | `public static prop1; private static prop2; protected static prop3;` | `public static $prop1; private static $prop2; protected static $prop3;` |
| 7 | `public prop1 = 1; private prop2 = 2; protected prop3 = 3; static prop4 = 4;` | `public $prop1 = 1; private $prop2 = 2; protected $prop3 = 3; static $prop4 = 4;` |
| 8 | `public static prop1 = 1; private static prop2 = 2; protected static prop3 = 3;` | `public static $prop1 = 1; private static $prop2 = 2; protected static $prop3 = 3;` |
| 9 | `for (i = 0; i < 10; i++) {     echo i; }` | `for ($i = 0; $i < 10; $i++) {     echo $i; }` |
| 10 | `for (i = 0, x = 1; i < 10, x > i; i++, x++) {     echo i + x; }` | `for ($i = 0, $x = 1; $i < 10, $x > $i; $i++, $x++) {     echo $i + $x; }` |
| 11 | `var ar = array(1, 2, 3, 4); foreach (ar as key : value) {     ar[key] = key.'-'.value; }` | `$ar = array(1, 2, 3, 4); foreach ($ar as $key => $value) {     $ar[$key] = $key.'-'.$value; }` |
| 12 | `var arr = array(1, 2, 3); list(one, two, three) = arr; list(var1, , var2) = arr;` | `$arr = array(1, 2, 3); list($one, $two, $three) = $arr; list($var1, , $var2) = $arr;` |
| 13 | `list(x, list(y, z)) = array(1, array(2, 3));` | `list($x, list($y, $z)) = array(1, array(2, 3));` |
| 14 | `var message = ''; try {     message = 'ok'; } catch (Exception e) {     message = e.getMessage(); }` | `$message = ''; try {     $message = 'ok'; } catch (Exception $e) {     $message = $e->getMessage(); }` |
| 15 | `function add (a, b) {     return a + b; }` | `function add ($a, $b) {     return $a + $b; }` |
| 16 | `function add (ref a, b = 1) {     return a + b; }` | `function add (&$a, $b = 1) {     return $a + $b; }` |
| 17 | `function add (a = [1, 2], b = array(3, 4)) {     return a[0] + a[1] + b[0] + b[1]; }` | `function add ($a = [1, 2], $b = array(3, 4)) {     return $a[0] + $a[1] + $b[0] + $b[1]; }` |
| 18 | `function doIt (refs parameter) {}` | `function doIt (&...$parameter) {}` |
| 19 | `function doIt (vals parameter) {}` | `function doIt (...$parameter) {}` |
| 20 | `function doIt (array a, callable cb) {     echo cb(a); }` | `function doIt (array $a, callable $cb) {     echo $cb($a); }` |
| 21 | `this.doIt([1, 2], function (a) {     var x = a[0] + a[1];     return x; });` | `$this->doIt([1, 2], function ($a) {     $x = $a[0] + $a[1];     return $x; });` |
| 22 | `function ref doIt (ref a) {     return a; } var b = [1, 2, 3]; var x = ref doIt(b);` | `function &doIt (&$a) {     return $a; } $b = [1, 2, 3]; $x = &doIt($b);` |
| 23 | `var a = dserver[' PHP_SELF']; var b = dglobals['id']; var c = dget['id']; var d = dpost['id']; var e = dfiles['image']; var f = dcookie['id']; var g = dsession['id']; var h = drequest['id']; var i = denv['HOSTNAME'];` | `$a = $_SERVER[' PHP_SELF']; $b = $GLOBALS['id']; $c = $_GET['id']; $d = $_POST['id']; $e = $_FILES['image']; $f = $_COOKIE['id']; $g = $_SESSION['id']; $h = $_REQUEST['id']; $i = $_ENV['HOSTNAME'];` |
| 24 | `var a = array(1 : 'o', 2 : 'two'); var a = [1 : 'one', 2 : 'two'];` | `$a = array(1 => 'o', 2 => 'two'); $a = [1 => 'one', 2 => 'two'];` |
| 25 | `var c = this.db.isConnected; this.method(); var r = this.property;` | `$c = $this->db->isConnected; $this->method(); $r = $this->property;` |
| 26 | `var method = 'method'; this.{method}(); this.{'method'}();` | `$method = 'method'; $this->{$method}(); $this->{'method'}();` |
| 27 | `var prop = 'property'; this.{prop} = 1; this.{'property'} = 2;` | `$prop = 'property'; $this->{$prop} = 1; $this->{'property'} = 2;` |
| 28 | `var objectName = (object)[]; objectName.method(); objectName.property = 1;` | `$objectName = (object)[]; $objectName->method(); $objectName->property = 1;` |
| 29 | `var objectName = (object)[]; var method = 'method'; objectName.{method}(); objectName.{'method'}();` | `$objectName = (object)[]; $method = 'method'; $objectName->{$method}(); $objectName->{'method'}();` |
| 30 | `var objectName = (object)[]; var property = 'property'; objectName.{property} = 1; objectName.{'property'} = 2;` | `$objectName = (object)[]; $property = 'property'; $objectName->{$property} = 1; $objectName->{'property'} = 2;` |
| 31 | `className.method(); var p = className.property; var cc = className.Constant;` | `className::method(); $p = className::$property; $cc = className::Constant;` |
| 32 | `self.method(); self.property = 1; var a = self.Constant;` | `self::method(); self::$property = 1; $a = self::Constant;` |
| 33 | `parent.method(); parent.property = 2; var b = parent.Constant;` | `parent::method(); parent::$property = 2; $b = parent::Constant;` |
| 34 | `static.method(); static.property = 3; var c = static.Constant;` | `static::method(); static::$property = 3; $c = static::Constant;` |
| 35 | `var z = 1; var a = array(z : 'o'); var a = [z : 'one'];` | `$z = 1; $a = array($z => 'o'); $a = [$z => 'one'];` |
| 36 | `var a = array(className.Constant : 'o'); var a = [className.Constant : 'one'];` | `$a = array(className::Constant => 'o'); $a = [className::Constant => 'one'];` |
| 37 | `// filename.var is compiled into filename.php` | `// filename.var is compiled into filename.php` |

## Implementation

Variable detection does not consider the variable scope rule. If the same name is used as variable, it will be just replaced.

In class declaration, capitalize the first character of constant name. Do not capitalize the first character of property name. Use round brackets for method detection. Do not use keyword for variable name. Any other structures, operators, names, or keywords are written and used as normal PHP code.

Code is written and saved on file with **.var** extension. Varrefvals code compiler will create or overwrite **.php** file with the same name.

Varrefvals code compiler is written in PHP code. It is saved as **varrefvals.php**. It is executed from command line. It needs file name argument. If left empty, all **.var** files inside folder will be compiled into **.php**, recursively.

There is no error handling when compiling. There are two possibilities: syntax error or false detection. Please try to write simple testing code and see what is resulted. You still need to understand PHP code after all. You may send me error code for debugging.

## Usage

Execute **varrefvals.php** with optional file name parameter. Do something like this:

```
	> cd "path/to/var/file"
	> "path/to/binary/of/php" -f "path/to/varrefvals.php" -- "file.var"
```

It will create *file.php* in *path/to/var/file* directory.

Fill empty file name parameter for compiling all **.var** files inside current directory, recursively:

```
	> cd "path/to/var/file"
	> "path/to/binary/of/php" -f "path/to/varrefvals.php" -- ""
```

You can bind the execution with `ctrl + s` or else on your favorite code editor.

If you add an argument *path/to/binary/of/php*, the syntax of generated .php file will be checked too.

```
	> cd "path/to/var/file"
	> "path/to/binary/of/php" -f "path/to/varrefvals.php" -- "file.var" "path/to/binary/of/php"
```

## License

```
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
```
