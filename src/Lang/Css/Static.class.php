<?php
/**
 * 
 * CSS static class
 * @author welefen
 *
 */
class Fl_Css_Static {

	/**
	 * 
	 * @ detail type
	 * @var array
	 */
	public static $atType = array (
		'@import ' => FL_TOKEN_CSS_AT_IMPORT, 
		'@charset ' => FL_TOKEN_CSS_AT_CHARSET, 
		'@media ' => FL_TOKEN_CSS_AT_MEDIA, 
		'@namespace ' => FL_TOKEN_CSS_AT_NAMESPACE, 
		'@font-face' => FL_TOKEN_CSS_AT_FONTFACE, 
		'@page' => FL_TOKEN_CSS_AT_PAGE, 
		'/^\@(?:\-(?:webkit|moz|o|ms)\-)?keyframes/' => FL_TOKEN_CSS_AT_KEYFRAMES, 
		'@-moz' => FL_TOKEN_CSS_AT_MOZILLA 
	);

	/**
	 * 
	 * special tokens
	 * @var array
	 */
	public static $specialTokens = array (
		array (
			'[;', 
			';]', 
			FL_TOKEN_CSS_HACK 
		) 
	);

	/**
	 * 
	 * prefix and suffix in inline style
	 * @var array
	 */
	public static $stylePrefixAndSuffix = array (
		array (
			"<!--", 
			"-->" 
		) 
	);

	/**
	 * 
	 * css comment pattern
	 * @var RegexIterator
	 */
	public static $commentPattern = '/\/\*.*?\*\//';

	/**
	 * 
	 * hack chars in property
	 * @var array
	 */
	public static $propertyHack = array (
		'*', 
		'!', 
		'$', 
		'&', 
		'*', 
		'(', 
		')', 
		'=', 
		'%', 
		'+', 
		'@', 
		',', 
		'.', 
		'/', 
		'`', 
		'[', 
		']', 
		'#', 
		'~', 
		'?', 
		':', 
		'<', 
		'>', 
		'|', 
		'_', 
		'-', 
		'£', 
		'¬', 
		'¦' 
	);

	/**
	 * 
	 * selector token split char
	 * @var array
	 */
	public static $selectorCharUtil = array (
		'#' => 1, 
		'.' => 1, 
		':' => 1, 
		'[' => 1, 
		'>' => 1, 
		'+' => 1, 
		'~' => 1, 
		'*' => 1, 
		',' => 1, 
		'/' => 1, 
		" " => 1 
	);

	/**
	 * 
	 * namespace pattern
	 * foo|div
	 * @var RegexIterator
	 */
	public static $namespacePattern = "/^[\w\*]+\|/";

	/**
	 * 
	 * selector token check pattern
	 * @var array
	 */
	public static $selectorTokenPattern = array (
		FL_TOKEN_CSS_SELECTOR_ID => "/^\#[\w\-]+$/ies", 
		FL_TOKEN_CSS_SELECTOR_CLASS => "/^\.[\w\-]+$/ies", 
		FL_TOKEN_CSS_SELECTOR_TYPE => "/^(?:[a-z][\w]*)|(?:[\d\.]+\%)$/ies" 
	);

	/**
	 * 
	 * regular property pattern
	 * @var RegexIterator
	 */
	public static $propertyPattern = "/^[a-z\-]+$/";

	/**
	 * 
	 * css3 property prefix pattern
	 * -webkit, -moz, -o, -ms
	 * @var RegexIterator
	 */
	public static $css3PropertyPrefixPattern = "/^\-(webkit|moz|o|ms)\-/";

	/**
	 * 
	 * css hack char in property
	 * @var RegexIterator
	 */
	public static $propertyCharHackPattern = "/[^a-z\-]/i";

	/**
	 * 
	 * important in css value
	 * @var string
	 */
	public static $importantPattern = '/\!\s*important/i';

	/**
	 * 
	 * ie hack in value, eg: color: red\9;
	 * @var RegexIterator
	 */
	public static $ieValueHackPattern = "/\\\\\d+$/i";

	/**
	 * 
	 * multi same property pattern
	 * @var regexp
	 */
	public static $multiSamePropertyPattern = "/background/i";

	/**
	 * 
	 * multi same property in a selector
	 * @var array
	 */
	public static $multiSameProperty = array (
		"background" => 1, 
		"background-image" => 1, 
		"background-color" => 1 
	);

	/**
	 * 
	 * short colors
	 * @var array
	 */
	public static $shortColor = array (
		"black" => "#000", 
		"fuchsia" => "#F0F", 
		"white" => "#FFF", 
		"yellow" => "#FF0", 
		"#800000" => "maroon", 
		"#ffa500" => "orange", 
		"#808000" => "olive", 
		"#800080" => "purple", 
		"#008000" => "green", 
		"#000080" => "navy", 
		"#008080" => "teal", 
		"#c0c0c0" => "silver", 
		"#808080" => "gray", 
		"#f00" => "red", 
		"#ff0000" => "red" 
	);

	/**
	 * 
	 * short font-weight
	 * @var array
	 */
	public static $shortFontWeight = array (
		"normal" => "400", 
		"bold" => "700" 
	);

	/**
	 * 
	 * rgb pattern
	 * @var RegexIterator
	 */
	public static $rgbPattern = "/rgb\s*\(\s*(\d+)\s*\,\s*(\d+)\s*\,\s*(\d+)\s*\)/e";

	/**
	 * 
	 * @ type list
	 * @var array
	 */
	public static $atTypeList = array (
		FL_TOKEN_CSS_AT => 1, 
		FL_TOKEN_CSS_AT_CHARSET => 1, 
		FL_TOKEN_CSS_AT_FONTFACE => 1, 
		FL_TOKEN_CSS_AT_IMPORT => 1, 
		FL_TOKEN_CSS_AT_KEYFRAMES => 1, 
		FL_TOKEN_CSS_AT_MEDIA => 1, 
		FL_TOKEN_CSS_AT_MOZILLA => 1, 
		FL_TOKEN_CSS_AT_OTHER => 1, 
		FL_TOKEN_CSS_AT_PAGE => 1 
	);

	/**
	 * 
	 * padding 4 children
	 * @var array
	 */
	public static $paddingChildren = array (
		"padding-top", 
		"padding-right", 
		"padding-bottom", 
		"padding-left" 
	);

	/**
	 * 
	 * margin 4 children
	 * @var array
	 */
	public static $marginChildren = array (
		'margin-top', 
		'margin-right', 
		'margin-bottom', 
		'margin-left' 
	);

	/**
	 * 
	 * regular property list
	 * @var array
	 */
	public static $propertyList = array (
		"background-attachment" => 1, 
		"background-color" => 1, 
		"background-image" => 1, 
		"background-position" => 1, 
		"background-repeat" => 1, 
		"background", 
		"border-collapse", 
		"border-color", 
		"border-spacing", 
		"border-style", 
		"border-top", 
		"border-right", 
		"border-bottom", 
		"border-left", 
		"border-top-color", 
		"border-right-color", 
		"border-bottom-color", 
		"border-left-color", 
		"border-top-style", 
		"border-right-style", 
		"border-bottom-style", 
		"border-left-style", 
		"border-top-width", 
		"border-right-width", 
		"border-bottom-width", 
		"border-left-width", 
		"border-width", 
		"border", 
		"bottom", 
		"caption-side", 
		"clear", 
		"clip", 
		"color", 
		"content", 
		"counter-increment", 
		"counter-reset", 
		"cursor", 
		"direction", 
		"display", 
		"empty-cells", 
		"flot", 
		"font-family", 
		"font-size", 
		"font-style", 
		"font-variant", 
		"font-weight", 
		"font", 
		"height", 
		"left", 
		"letter-spacing", 
		"line-height", 
		"list-style-image", 
		"list-style-position", 
		"list-style-type", 
		"list-style", 
		"margin-right", 
		"margin-left", 
		"margin-top", 
		"margin-bottom", 
		"margin", 
		"max-height", 
		"max-width", 
		"min-height", 
		"min-width", 
		"opacity", 
		"orphans", 
		"outline-color", 
		"outline-style", 
		"outline-width", 
		"outline", 
		"overflow", 
		"padding-top", 
		"padding-right", 
		"padding-bottom", 
		"padding-left", 
		"padding", 
		"page-break-after", 
		"page-break-before", 
		"page-break-inside", 
		"position", 
		"quotes", 
		"right", 
		"table-layout", 
		"text-align", 
		"text-decoration", 
		"text-indent", 
		"text-transform", 
		"top", 
		"unicode-bidi", 
		"vertical-align", 
		"visibility", 
		"white-space", 
		"windows", 
		"width", 
		"word-spacing", 
		"z-index" 
	);

	/**
	 * 
	 * remove comment from text
	 * @param string $text
	 */
	public static function removeComment($text = '') {
		$text = preg_replace ( self::$commentPattern, '', $text );
		return $text;
	}

	/**
	 * 
	 * get @ detail type
	 * @param string $text
	 */
	public static function getAtDetailType($text = '', Fl_Base $instance) {
		$text = self::removeComment ( $text );
		foreach ( self::$atType as $key => $type ) {
			if ($key {0} === '/') {
				if (preg_match ( $key, $text )) {
					return $type;
				}
			} else {
				if (strpos ( $text, $key ) === 0) {
					return $type;
				}
			}
		}
		return FL_TOKEN_CSS_AT_OTHER;
	}

	/**
	 * 
	 * check has break for selector tokenizar
	 * @param char $char
	 */
	public static function isSelectorCharUtil($char = '') {
		return isset ( self::$selectorCharUtil [$char] );
	}

	/**
	 * 
	 * check namespace
	 * @param string $text
	 */
	public static function checkNamespace($text = '') {
		return preg_match ( self::$namespacePattern, $text );
	}

	/**
	 * 
	 * check selector token
	 * @param string $type
	 * @param string $value
	 */
	public static function checkSelectorToken($type, $value) {
		if (! isset ( self::$selectorTokenPattern [$type] )) {
			return true;
		}
		return preg_match ( self::$selectorTokenPattern [$type], $value );
	}

	/**
	 * 
	 * compare two selector specificity
	 * @param string $score1
	 * @param string $score2
	 */
	public static function compareSelectorSpecificity($score1 = array(), $score2 = array()) {
		if (is_string ( $score1 )) {
			$score1 = self::getSelectorSpecificity ( $score1 );
		}
		if (is_string ( $score2 )) {
			$score2 = self::getSelectorSpecificity ( $score2 );
		}
		if (! is_array ( $score1 ) && ! is_array ( $score2 )) {
			if ($score1 === $score2) {
				return 0;
			} else if ($score1 > $score2) {
				return 1;
			}
			return - 1;
		}
		for($i = 0, $count = count ( $score1 ); $i < $count; $i ++) {
			$item1 = $score1 [$i];
			$item2 = $score2 [$i];
			if ($item1 < $item2) {
				return - 1;
			}
			if ($item1 > $item2) {
				return 1;
			}
		}
		return 0;
	}

	/**
	 * 
	 * Calculating a selector's specificity
	 * see more at: http://www.w3.org/TR/selectors/#specificity
	 * @param array $selectorTokens
	 */
	public static function getSelectorSpecificity($selectorTokens = array(), $number = false) {
		if (! is_array ( $selectorTokens )) {
			throw new Fl_Exception ( "selectorTokens must be array", - 1 );
		}
		$score = array (
			0, 
			0, 
			0 
		);
		$notPattern = '/^\:not\(/ies';
		foreach ( $selectorTokens as $item ) {
			$type = $item ['type'];
			switch ($type) {
				case FL_TOKEN_CSS_SELECTOR_ID :
					$score [0] ++;
					break;
				case FL_TOKEN_CSS_SELECTOR_TYPE :
				case FL_TOKEN_CSS_SELECTOR_PSEUDO_ELEMENT :
					$score [2] ++;
					break;
				case FL_TOKEN_CSS_SELECTOR_CLASS :
				case FL_TOKEN_CSS_SELECTOR_ATTRIBUTES :
					$score [1] ++;
					break;
				case FL_TOKEN_CSS_SELECTOR_PSEUDO_CLASS :
					$value = $item ['value'];
					//:not(xxx)
					if (preg_match ( $notPattern, $value )) {
						$value = trim ( preg_replace ( $notPattern, "", $value ) );
						$value = substr ( $value, 0, strlen ( $value ) - 1 );
						Fl::loadClass ( 'Fl_Css_SelectorToken' );
						$instance = new Fl_Css_SelectorToken ( $value );
						$tokens = $instance->run ();
						$notScore = Fl_Css_Static::getSelectorSpecificity ( $tokens [0] );
						$score [0] += $notScore [0];
						$score [1] += $notScore [1];
						$score [2] += $notScore [2];
					} else {
						$score [1] ++;
					}
					break;
			}
		}
		if ($number) {
			return $score [0] * 10000 + $score [1] * 100 + $score [2];
		}
		return $score;
	}

	/**
	 * 
	 * check property valid
	 * @param string $property
	 */
	public static function checkPropertyPattern($property = '') {
		return preg_match ( self::$propertyPattern, $property );
	}

	/**
	 * 
	 * multi same property in a selector
	 * @param string $property
	 */
	public static function isMultiSameProperty($property = '') {
		return isset ( self::$multiSameProperty [$property] );
	}

	/**
	 * 
	 * css selector token to text
	 * @param array $tokens
	 * @param boolean $embedExtInfo
	 */
	public static function selectorTokenToText($tokens = array(), $embedExtInfo = true) {
		if (count ( $tokens ) === 0) {
			return '';
		}
		if ($embedExtInfo) {
			$line = $tokens [0] ['line'];
			$col = $tokens [0] ['col'];
			$pos = $tokens [0] ['pos'];
		}
		$result = array ();
		foreach ( $tokens as $token ) {
			if ($token ['spaceBefore']) {
				$result [] = FL_SPACE;
			}
			$result [] = $token ['value'];
		}
		$result = trim ( join ( '', $result ) );
		if ($embedExtInfo) {
			return array (
				'text' => $result, 
				'line' => $line, 
				'pos' => $pos, 
				'col' => $col 
			);
		}
		return $result;
	}

	/**
	 * 
	 * check token type is @ type
	 * @param string $type
	 */
	public static function isAtType($type = '') {
		return isset ( self::$atTypeList [$type] );
	}

	/**
	 * 
	 * get clean property, some has IE hack
	 * @param string $property
	 */
	public static function getPropertyDetail($property = '') {
		preg_match ( self::$css3PropertyPrefixPattern, $property, $matches );
		if ($matches) {
			$prefix = $matches [0];
			$value = substr ( $property, strlen ( $prefix ) );
		} else {
			$value = preg_replace ( self::$propertyCharHackPattern, "", $property );
			if ($property === $value) {
				$prefix = '';
			} else {
				$prefix = substr ( $property, 0, strpos ( $property, $value ) );
			}
		}
		return array (
			"prefix" => $prefix, 
			"property" => $value 
		);
	}

	/**
	 * 
	 * get css value detail info
	 * contain: ie value hack, !important in value
	 * @param string $value
	 */
	public static function getValueDetail($value = '') {
		$important = false;
		$suffix = '';
		$cleanValue = preg_replace ( self::$importantPattern, "", $value );
		if ($cleanValue !== $value) {
			$important = true;
		}
		$cleanValue1 = preg_replace ( self::$ieValueHackPattern, "", $cleanValue );
		if ($cleanValue1 !== $cleanValue) {
			$suffix = substr ( $cleanValue, strlen ( $cleanValue1 ) );
		}
		return array (
			'value' => $cleanValue1, 
			'important' => $important, 
			'suffix' => $suffix 
		);
	}

	/**
	 * 
	 * compress css value
	 * @param string $value
	 */
	public static function compressCommon($value = '') {
		//remove comment in value
		$value = self::removeComment ( $value );
		//remove newline in value
		$value = str_replace ( FL_NEWLINE, "", $value );
		//replace multi space to one
		$value = preg_replace ( FL_SPACE_PATTERN, " ", $value );
		//can't replace `, ` to `,`, see http://www.imququ.com/post/the_bug_of_ie-matrix-filter.html
		//$value = str_replace ( ", ", ",", $value );
		//$value = str_replace ( "0 0 0 0", "0", $value );
		//$value = str_replace ( "0 0 0", "0", $value );
		//$value = str_replace ( "0 0", "0", $value );
		//Replace 0(px,em,%) with 0.
		$value = preg_replace ( "/([\s:])(0)(px|em|%|in|cm|mm|pc|pt|ex)/is", "$1$2", $value );
		//Replace 0.6 to .6
		$value = trim ( preg_replace ( "/\s0\.(\d+)/is", " .$1", ' ' . $value ) );
		// Shorten colors from #AABBCC to #ABC. Note that we want to make sure
		// the color is not preceded by either ", " or =. Indeed, the property
		//     filter: chroma(color="#FFFFFF");
		// would become
		//     filter: chroma(color="#FFF");
		// which makes the filter break in IE.
		$value = preg_replace ( "/([^\"'=\s])(\s*)#([0-9a-fA-F])\\3([0-9a-fA-F])\\4([0-9a-fA-F])\\5/is", "$1$2#$3$4$5", $value );
		return $value;
	}

	/**
	 * 
	 * sort properties
	 * @param array $attrs
	 * @param array $b
	 */
	public static function sortProperties($attrs = array(), $b = null) {
		if ($b) {
			$ap = strtolower ( $attrs ['property'] );
			$bp = strtolower ( $b ['property'] );
			if ($ap === $bp || $ap === 'filter' || $bp === 'filter') {
				return $attrs ['pos'] > $b ['pos'] ? 1 : - 1;
			} else {
				return strcmp ( $ap, $bp ) > 0 ? 1 : - 1;
			}
		} else {
			uasort ( $attrs, "Fl_Css_Static::sortProperties" );
			return $attrs;
		}
	}

	/**
	 * 
	 * sort selectors
	 * @param array $selectors
	 * @param array or null $b
	 */
	public static function sortSelectors($selectors, $b = null) {
		if ($b) {
			if ($selectors ['score'] === $b ['score']) {
				return $selectors ['pos'] > $b ['pos'] ? 1 : - 1;
			} else if ($selectors ['score'] > $b ['score']) {
				return 1;
			}
			return - 1;
		} else {
			uasort ( $selectors, "Fl_Css_Static::sortSelectors" );
			return $selectors;
		}
	}

	/**
	 * 
	 * combine padding value, such as: padding & margin
	 * @param array $attrs
	 */
	public static function combineProperty($attrs = array(), $primary = '', $list = array()) {
		$properties = array (
			$primary => 0 
		);
		foreach ( $list as $item ) {
			$properties [$item] = 0;
		}
		foreach ( $attrs as $name => $item ) {
			if (isset ( $properties [$item ['property']] )) {
				if ($item ['important'] || $item ['prefix'] || $item ['suffix']) {
					return $attrs;
				} else {
					$properties [$name] = 1;
				}
			}
			//if css hack in attrs, can't combine it
			if ($item ['type'] === FL_TOKEN_CSS_HACK) {
				return $attrs;
			}
		}
		if ($properties [$primary]) {
			$value = $attrs [$primary] ['value'];
			$append = array ();
			foreach ( $list as $k => $item ) {
				if ($properties [$item]) {
					$append [$k] = $attrs [$item] ['value'];
					unset ( $attrs [$item] );
				}
			}
			$attrs [$primary] ['value'] = self::short4NumValue ( $value, $append );
			return $attrs;
		} else {
			$value = array ();
			$copyAttrs = $attrs;
			foreach ( $list as $k => $item ) {
				if (! $properties [$item]) {
					return $attrs;
				} else {
					$value [$k] = $copyAttrs [$item] ['value'];
					unset ( $copyAttrs [$item] );
				}
			}
			$attrs = $copyAttrs;
			$attrs [$primary] = array (
				'property' => $primary, 
				'value' => self::short4NumValue ( $value ) 
			);
		}
		return $attrs;
	}

	/**
	 * 
	 * short for padding,margin,border-color value
	 * @param string $value
	 */
	public static function short4NumValue($value = '', $append = array(), $returnArray = false) {
		$value = preg_split ( FL_SPACE_PATTERN, $value );
		$count = count ( $value );
		$v = array (
			"1" => array (
				0, 
				0, 
				0, 
				0 
			), 
			"2" => array (
				0, 
				1, 
				0, 
				1 
			), 
			"3" => array (
				0, 
				1, 
				2, 
				1 
			), 
			"4" => array (
				0, 
				1, 
				2, 
				3 
			) 
		);
		$sv = $v [strval ( $count )];
		$value = array (
			$value [$sv [0]], 
			$value [$sv [1]], 
			$value [$sv [2]], 
			$value [$sv [3]] 
		);
		foreach ( $append as $k => $v ) {
			$value [$k] = $v;
		}
		if ($value [1] === $value [3]) {
			unset ( $value [3] );
		}
		if (count ( $value ) === 3 && $value [0] === $value [2]) {
			unset ( $value [2] );
		}
		if (count ( $value ) === 2 && $value [0] === $value [1]) {
			unset ( $value [1] );
		}
		if ($returnArray) {
			return $value;
		}
		return trim ( join ( FL_SPACE, $value ) );
	}

	/**
	 * 
	 * get short value
	 * @param string $value
	 * @param string $property
	 */
	public static function getShortValue($value, $property) {
		//http://www.w3schools.com/cssref/pr_border-width.asp
		if ($property === 'border-color' || $property === 'border-style' || $property === 'border-width') {
			return self::short4NumValue ( $value );
		}
		$list = array (
			"color" => self::$shortColor, 
			"border-top-color" => self::$shortColor, 
			"border-left-color" => self::$shortColor, 
			"border-right-color" => self::$shortColor, 
			"border-bottom-color" => self::$shortColor, 
			"background-color" => self::$shortColor, 
			"font-weight" => self::$shortFontWeight 
		);
		// rgb(0,0,0) -> #000000 (or #000 in this case later)
		$value = self::rgb2Hex ( $value );
		if (isset ( $list [$property] )) {
			$mix = $list [$property];
			return isset ( $mix [$value] ) ? $mix [$value] : $value;
		}
		return $value;
	}

	/**
	 * 
	 * rgb to hex
	 * @param string $value
	 */
	public static function rgb2Hex($value, $r = 0, $g = 0, $b = 0) {
		if ($value === true) {
			$v = array (
				intval ( $r ), 
				intval ( $g ), 
				intval ( $b ) 
			);
			$result = '#';
			foreach ( $v as $item ) {
				if ($item < 16) {
					$result .= '0' . dechex ( $item );
				} else {
					$result .= dechex ( $item );
				}
			}
			return $result;
		}
		if (strpos ( $value, "rgb" ) === false) {
			return $value;
		}
		$replace = "self::rgb2hex(true, '\\1', '\\2', '\\3')";
		$value = preg_replace ( self::$rgbPattern, $replace, $value );
		return $value;
	}

	/**
	 * 
	 * merge property
	 * @param array $attrs1
	 * @param array $attrs2
	 */
	public static function mergeProperties($attrs1 = array(), $attrs2 = array()) {
		foreach ( $attrs2 as $name => $item ) {
			if (isset ( $attrs1 [$name] )) {
				if (! $attrs1 [$name] ['important'] || $item ['important']) {
					//can't not replace it
					unset ( $attrs1 [$name] );
					$attrs1 [$name] = $item;
				}
			} else {
				$attrs1 [$name] = $item;
			}
		}
		return $attrs1;
	}

	/**
	 * 
	 * check properties equal
	 * @param array $attrs1
	 * @param array $attrs2
	 */
	public static function checkPropertiesEqual($attrs1, $attrs2) {
		if ($attrs1 ['prefix'] || $attrs1 ['suffix'] || $attrs2 ['prefix'] || $attrs2 ['suffix']) {
			return - 1;
		}
		if ($attrs1 ['type'] === FL_TOKEN_CSS_HACK || $attrs2 ['type'] === FL_TOKEN_CSS_HACK) {
			return - 1;
		}
		unset ( $attrs1 ['pos'], $attrs2 ['pos'], $attrs1 ['equal'], $attrs2 ['equal'] );
		#return strcmp ( json_encode ( $attrs1 ), json_encode ( $attrs2 ) );
		//use serialize to compare it
		return strcmp ( serialize ( $attrs1 ), serialize ( $attrs2 ) );
	}

	/**
	 * 
	 * get properties intersect
	 * @param array $se1
	 * @param array $se2
	 */
	public static function getPropertiesIntersect($se1 = array(), $se2 = array()) {
		$attrs1 = $se1 ['attrs'];
		$attrs2 = $se2 ['attrs'];
		$assoc = array_uintersect_assoc ( $attrs1, $attrs2, "Fl_Css_Static::checkPropertiesEqual" );
		//if intersect attrs has hack attr, remove it
		foreach ( $assoc as $name => $item ) {
			if (preg_match ( self::$multiSamePropertyPattern, $name )) {
				unset ( $assoc [$name] );
				continue;
			}
			foreach ( $attrs1 as $n1 => $i1 ) {
				if ($i1 ['property'] == $item ['property'] && ($i1 ['prefix'] || $i1 ['suffix'])) {
					unset ( $assoc [$name] );
				}
				if ($i1 ['type'] === FL_TOKEN_CSS_HACK) {
					return false;
				}
			}
			foreach ( $attrs2 as $n1 => $i1 ) {
				if ($i1 ['property'] == $item ['property'] && ($i1 ['prefix'] || $i1 ['suffix'])) {
					unset ( $assoc [$name] );
				}
				if ($i1 ['type'] === FL_TOKEN_CSS_HACK) {
					return false;
				}
			}
		}
		if (empty ( $assoc )) {
			return false;
		}
		$assCount = count ( $assoc );
		if (count ( $attrs1 ) != $assCount && count ( $attrs2 ) !== $assCount) {
			// 3 chars is `, { }`
			$seLen = strlen ( $se1 ['selector'] ) + strlen ( $se2 ['selector'] ) + 3;
			$se1Equal = strlen ( join ( ',', $se1 ['equal'] ) );
			$se2Equal = strlen ( join ( ',', $se2 ['equal'] ) );
			if ($se1Equal) {
				$seLen += $se1Equal + 1;
			}
			if ($se2Equal) {
				$seLen += $se2Equal + 1;
			}
			$assLen = 0;
			foreach ( $assoc as $item ) {
				//2 chars is : and ;
				$assLen += strlen ( $item ['prefix'] . $item ['property'] . $item ['value'] . $item ['suffix'] ) + 2;
				//if have important in value, add `!important` length(10)
				if ($item ['important']) {
					$assLen += 10;
				}
			}
			$assLen --;
			//if combine selector length more than combine attrs, can't not combine them
			if ($seLen >= $assLen) {
				return false;
			}
		}
		return $assoc;
	}

	/**
	 * 
	 * combine same selector
	 * @param array $selectors
	 */
	public static function combineSameSelector($selectors = array()) {
		$result = array ();
		$preLongSelector = '';
		foreach ( $selectors as $item ) {
			$longSelector = trim ( $item ['selector'] . ',' . join ( ',', $item ['equal'] ), ',' );
			if ($longSelector === $preLongSelector) {
				$last = array_pop ( $result );
				$last ['attrs'] = self::mergeProperties ( $last ['attrs'], $item ['attrs'] );
				$result [] = $last;
			} else {
				$result [] = $item;
			}
			$preLongSelector = $longSelector;
		}
		return $result;
	}

	/**
	 * 
	 * get prefix and suffix in style
	 * @param string $value
	 */
	public static function getStyleDetail($value = '') {
		$value = trim ( $value );
		$prefix = $suffix = $text = '';
		foreach ( self::$stylePrefixAndSuffix as $item ) {
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