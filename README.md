# Varrefvals

I know, it's too LOL wanting PHP coding without $ sign on its variable name. But, writing code with $ sign on every variable is adding typing complexity, for me. I wish I could do coding using simple syntax only. So I decided to design and write simple script that can be used for compiling no $ sign code into normal PHP code.

**Varrefvals**, that's the name that I use for this script. It comes from 3 selected keywords: `var`, `ref`, and `vals`. `var` keyword is used as direct variable declaration. `ref` keyword is used for setting variable accessed by reference. `vals` keyword is used for setting variables accessed by value.

## Coding Specification

1. Use `var` keyword for direct variable declaration.
2. Detect variable declaration from keywords: `global`, `static`, `public`, `private`, `protected`, `for`, `foreach`, `list`, `catch`, `function`
3. Use `ref` keyword to set variable accessed by reference.
4. Use `refs` keyword to set variables accessed by reference.
5. Use `vals` keyword to set variables accessed by value.
6. Use `this` keyword to access own object properties or methods.
7. Set alias variables to write superglobal variables.
8. Use `.` operator to access every method or property, static or not.
9. Use `:` for associative arrays.

## Conversion

| No. | Varrefvals code | PHP code |
|:---:|---|---|
| 1 | `var counter; counter = 1;` | `$counter = 1;` |
| 2 | `var counter = 0;` | `$counter = 0;` |
| 3 | `var counter, limit, total; counter = 1; limit = 10; total = 0;` | ` $counter = 1; $limit = 10; $total = 0;` |
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
| 35 | `// File is saved as filename.var` | `// File is saved as filename.php` |
