<?php
/**
 * 
 * JS里用到的一些常量或者数据
 * @author welefen
 *
 */
class Fl_Js_Static {

	/**
	 * 
	 * 特殊的一些Token
	 * @var array
	 */
	public static $specialTokens = array (
		array (
			'/*@cc_on', 
			'*/', 
			FL_TOKEN_JS_IE_CC 
		) 
	);

	/**
	 * 
	 * prefix and suffix in inline style
	 * @var array
	 */
	public static $scriptPrefixAndSuffix = array (
		array (
			"/*<![CDATA[*/", 
			"/*<![CDATA[*/" 
		), 
		array (
			"<!--", 
			"//-->" 
		) 
	);

	/**
	 * 
	 * 关键字
	 * @var array
	 */
	public static $keywords = array (
		"break" => 1, 
		"case" => 1, 
		"catch" => 1, 
		"const" => 1, 
		"continue" => 1, 
		"debugger" => 1, 
		"default" => 1, 
		"delete" => 1, 
		"do" => 1, 
		"else" => 1, 
		"finally" => 1, 
		"for" => 1, 
		"function" => 1, 
		"if" => 1, 
		"in" => 1, 
		"instanceof" => 1, 
		"new" => 1, 
		"return" => 1, 
		"switch" => 1, 
		"throw" => 1, 
		"try" => 1, 
		"typeof" => 1, 
		"var" => 1, 
		"void" => 1, 
		"while" => 1, 
		"with" => 1 
	);

	/**
	 * 
	 * 保留关键字
	 * @var array
	 */
	public static $reservedKeywords = array (
		"abstract", 
		"boolean", 
		"byte", 
		"char", 
		"class", 
		"debugger", 
		"double", 
		"enum", 
		"export", 
		"extends", 
		"final", 
		"float", 
		"goto", 
		"implements", 
		"import", 
		"int", 
		"interface", 
		"long", 
		"native", 
		"package", 
		"private", 
		"public", 
		"public", 
		"short", 
		"static", 
		"super", 
		"synchronized", 
		"throws", 
		"transient", 
		"volatile" 
	);

	/**
	 * 
	 * 表达式前面的关键字
	 * @var array
	 */
	public static $keywordsBeforeExpression = array (
		"return" => 1, 
		"new" => 1, 
		"delete" => 1, 
		"throw" => 1, 
		"else" => 1, 
		"case" => 1 
	);

	/**
	 * 
	 * 单一关键字
	 * @var array
	 */
	public static $keywordsAtom = array (
		"false" => 1, 
		"null" => 1, 
		"true" => 1, 
		"undefined" => 1 
	);

	/**
	 * 
	 * 十六进制
	 * @var RegexIterator
	 */
	public static $hexNumber = '/^0x[0-9a-f]+$/i';

	/**
	 * 
	 * 八进制
	 * @var RegexIterator
	 */
	public static $octNumber = '/^0[0-7]+$/';

	/**
	 * 
	 * 十进制
	 * @var RegexIterator
	 */
	public static $decNumber = '/^\d*\.?\d*(?:e[+-]?\d*(?:\d?\.?|\.?\d?)\d*)?$/i';

	/**
	 * 
	 * prefix number pattern
	 * @var regexp
	 */
	public static $prefixNumber = '/^0x?$/i';

	/**
	 * 
	 * 单个操作符
	 * @var array
	 */
	public static $operatorChars = array (
		"+" => 1, 
		"-" => 1, 
		"*" => 1, 
		"&" => 1, 
		"%" => 1, 
		"=" => 1, 
		"<" => 1, 
		">" => 1, 
		"!" => 1, 
		"?" => 1, 
		"|" => 1, 
		"~" => 1, 
		"^" => 1 
	);

	/**
	 * 
	 * 操作符号
	 * @var array
	 */
	public static $operators = array (
		"in" => 1, 
		"instanceof" => 1, 
		"typeof" => 1, 
		"new" => 1, 
		"void" => 1, 
		"delete" => 1, 
		"++" => 1, 
		"--" => 1, 
		"+" => 1, 
		"-" => 1, 
		"!" => 1, 
		"~" => 1, 
		"&" => 1, 
		"|" => 1, 
		"^" => 1, 
		"*" => 1, 
		"/" => 1, 
		"%" => 1, 
		">>" => 1, 
		"<<" => 1, 
		">>>" => 1, 
		"<" => 1, 
		">" => 1, 
		"<=" => 1, 
		">=" => 1, 
		"==" => 1, 
		"===" => 1, 
		"!=" => 1, 
		"!==" => 1, 
		"?" => 1, 
		"=" => 1, 
		"+=" => 1, 
		"-=" => 1, 
		"/=" => 1, 
		"*=" => 1, 
		"%=" => 1, 
		">>=" => 1, 
		"<<=" => 1, 
		">>>=" => 1, 
		"|=" => 1, 
		"^=" => 1, 
		"&=" => 1, 
		"&&" => 1, 
		"||" => 1 
	);

	/**
	 * 
	 * 表达式前面的标点
	 * @var array
	 */
	public static $puncBeforeExpression = array (
		"[" => 1, 
		"{" => 1, 
		"(" => 1, 
		")" => 1, 
		"]" => 1, 
		"}" => 1, 
		"," => 1, 
		"." => 1, 
		";" => 1, 
		":" => 1 
	);

	/**
	 * 
	 * 标点
	 * @var array
	 */
	public static $puncChars = array (
		"[" => 1, 
		"]" => 1, 
		"{" => 1, 
		"}" => 1, 
		"(" => 1, 
		")" => 1, 
		"," => 1, 
		";" => 1, 
		":" => 1 
	);

	/**
	 * 
	 * 正则表达式修饰符
	 * @var array
	 */
	public static $regexpModifiers = array (
		"g" => 1, 
		"m" => 1, 
		"s" => 1, 
		"i" => 1, 
		"y" => 1 
	);

	// regexps adapted from http://xregexp.com/plugins/#unicode
	public static $unicode = array (
		'letter' => '/[\p{L}]/u', 
		'non_spacing_mark' => '/[\p{Mn}]/u', 
		'space_combining_mark' => '/[\p{Mc}]/u', 
		'connector_punctuation' => '/[\p{Pc}]/u' 
	);

	/**
	 * 
	 * 前缀一元运算符
	 * @var array
	 */
	public static $unaryPrefix = array (
		"typeof" => 1, 
		"void" => 1, 
		"delete" => 1, 
		"--" => 1, 
		"++" => 1, 
		"!" => 1, 
		"~" => 1, 
		"-" => 1, 
		"+" => 1 
	);

	/**
	 * 
	 * 后缀一元运算符
	 * @var array
	 */
	public static $unarySuffix = array (
		"--" => 1, 
		"++" => 1 
	);

	/**
	 * 
	 * 赋值运算符
	 * @var array
	 */
	public static $assignment = array (
		"+=" => "+", 
		"-=" => "-", 
		"/=" => "/", 
		"*=" => "*", 
		"%=" => "%", 
		">>=" => ">>", 
		"<<=" => "<<", 
		">>>=" => ">>>", 
		"|=" => "|", 
		"^=" => "^", 
		"&=" => "&" 
	);

	/**
	 * 
	 * 优先权
	 * @var array
	 */
	public static $precedence = array (
		"!=" => 6, 
		"!==" => 6, 
		"%" => 10, 
		"&" => 5, 
		"&&" => 2, 
		"*" => 10, 
		"+" => 9, 
		"-" => 9, 
		"/" => 10, 
		"<" => 7, 
		"<<" => 8, 
		"<=" => 7, 
		"==" => 6, 
		"===" => 6, 
		">" => 7, 
		">=" => 7, 
		">>" => 8, 
		">>>" => 8, 
		"^" => 4, 
		"in" => 7, 
		"instanceof" => 7, 
		"|" => 3, 
		"||" => 1 
	);

	/**
	 * 
	 * 会出现标签的语法结构
	 * @var array
	 */
	public static $labelStatement = array (
		"for" => 1, 
		"do" => 1, 
		"while" => 1, 
		"switch" => 1 
	);

	public static $atomStartType = array (
		FL_TOKEN_JS_ATOM => 1, 
		FL_TOKEN_JS_NUMBER => 1, 
		FL_TOKEN_JS_STRING => 1, 
		FL_TOKEN_JS_REGEXP => 1, 
		FL_TOKEN_JS_NAME => 1 
	);

	/**
	 * 
	 * 判断接下来是否允许正则
	 * @param string $type
	 * @param string $value
	 */
	public static function isRegexpAllowed($type, $value) {
		if ($type === FL_TOKEN_JS_OPERATOR && ! isset ( self::$unarySuffix [$value] )) {
			return true;
		}
		if ($type === FL_TOKEN_JS_KEYWORD && isset ( self::$keywordsBeforeExpression [$value] )) {
			return true;
		}
		if ($type === FL_TOKEN_JS_PUNC && isset ( self::$puncBeforeExpression [$value] )) {
			return true;
		}
		return false;
	}

	/**
	 * 
	 * 单一关键字
	 * @param string $keyword
	 */
	public static function isKeywordAtom($keyword) {
		return isset ( self::$keywordsAtom [$keyword] );
	}

	/**
	 * 
	 * 是否是正常的字符
	 * @param string $char
	 */
	public static function isLetter($char) {
		return preg_match ( self::$unicode ['letter'], $char );
	}

	/**
	 * 
	 * 标记符号前缀
	 * @param string $char
	 */
	public static function isIdentifierStart($char) {
		return self::isLetter ( $char ) || $char === '$' || $char === '_';
	}

	/**
	 * 
	 * 
	 * @param string $char
	 */
	public static function isIdentifierChar($char) {
		return self::isIdentifierStart ( $char ) || self::isDigit ( $char ) || preg_match ( self::$unicode ['space_combining_mark'], $char ) || preg_match ( self::$unicode ['connector_punctuation'], $char ) || preg_match ( self::$unicode ['non_spacing_mark'], $char ) || $char === "\x{u200c}" || $char === "\x{u200d}";
	}

	/**
	 * 
	 * 判断当前符号是否单一操作符
	 * @param string $char
	 */
	public static function isOperatorChar($char) {
		return isset ( self::$operatorChars [$char] );
	}

	/**
	 * 
	 * 判断是否是操作符
	 * @param string $chars
	 */
	public static function isOperator($chars = '') {
		return isset ( self::$operators [$chars] );
	}

	/**
	 * 
	 * 判断是否是关键字
	 * @param string $word
	 */
	public static function isKeyword($word) {
		return isset ( self::$keywords [$word] );
	}

	/**
	 * 
	 * 标点符号
	 * @param string $char
	 */
	public static function isPuncChar($char) {
		return isset ( self::$puncChars [$char] );
	}

	/**
	 * 
	 * 判断是否是个数字
	 * @param string $char
	 */
	public static function isDigit($char) {
		$ord = ord ( $char );
		return $ord >= 48 && $ord <= 57;
	}

	/**
	 * 
	 * 判断是否是十六进制
	 * @param number $number
	 */
	public static function isHexNumber($number) {
		return preg_match ( self::$hexNumber, $number );
	}

	/**
	 * 
	 * 判断是否是八进制
	 * @param number $number
	 */
	public static function isOctNumber($number) {
		return preg_match ( self::$octNumber, $number );
	}

	/**
	 * 
	 * 判断是否是十进制
	 * @param number $number
	 */
	public static function isDecNumber($number) {
		return preg_match ( self::$decNumber, $number );
	}

	/**
	 * 
	 * 是数值的前缀
	 * @param string $number
	 */
	public static function isNumberPrefix($number) {
		return preg_match ( self::$prefixNumber, $number );
	}

	/**
	 * 
	 * 判断是否是数字
	 * @param string $number
	 */
	public static function isNumber($number) {
		return preg_match ( self::$hexNumber, $number ) || preg_match ( self::$octNumber, $number ) || preg_match ( self::$decNumber, $number );
	}

	/**
	 * 
	 * 检测正则的修饰符
	 * @param string $mods
	 */
	public static function checkRegexpModifiers($mods = '') {
		$len = strlen ( $mods );
		for($i = 0; $i < $len; $i ++) {
			$char = $mods {$i};
			if (! isset ( self::$regexpModifiers [$char] )) {
				return false;
			}
		}
		return true;
	}

	/**
	 * 
	 * 是否是个赋值运算符
	 * @param string $value
	 */
	public static function isAssignment($value) {
		return isset ( self::$assignment [$value] );
	}

	/**
	 * 
	 * 获取赋值运算符的值
	 * @param string $key
	 */
	public static function getAssignmentValue($key) {
		return self::$assignment [$key];
	}

	/**
	 * 
	 * 获取优先权
	 */
	public static function getPrecedenceValue($key) {
		return self::$precedence [$key];
	}

	/**
	 * 
	 * 是否是一元操作符前缀
	 * @param string $key
	 */
	public static function isUnaryPrefix($key) {
		return isset ( self::$unaryPrefix [$key] );
	}

	/**
	 * 
	 * 是否是一元操作符后缀
	 * @param string $key
	 */
	public static function isUnarySuffix($key) {
		return isset ( self::$unarySuffix [$key] );
	}

	/**
	 * 
	 * 是否标签语法结构
	 * @param string $keyword
	 */
	public static function isLabelStatement($keyword) {
		return isset ( self::$labelStatement [$keyword] );
	}

	public static function isAtomStartType($type) {
		return isset ( self::$atomStartType [$type] );
	}

	/**
	 * 
	 * get prefix and suffix in script
	 * @param string $value
	 */
	public static function getScriptDetail($value = '') {
		$value = trim ( $value );
		$prefix = $suffix = $text = '';
		foreach ( self::$scriptPrefixAndSuffix as $item ) {
			if (strpos ( $value, $item [0] ) === 0) {
				$pos = strrpos ( $value, $item [1] );
				if ($pos == (strlen ( $value ) - strlen ( $item [1] ))) {
					$prefix = $item [0];
					$suffix = $item [1];
					$value = trim ( substr ( $value, strlen ( $item [0] ), (strlen ( $value ) - strlen ( $item [0] ) - strlen ( $item [1] )) ) );
					break;
				}
			}
		}
		return array (
			"prefix" => $prefix, 
			"suffix" => $suffix, 
			"value" => $value 
		);
	}
}