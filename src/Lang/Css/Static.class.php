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
	public static $atType = array ('@import ' => FL_TOKEN_CSS_AT_IMPORT, '@charset ' => FL_TOKEN_CSS_AT_CHARSET, '@media ' => FL_TOKEN_CSS_AT_MEDIA, '@font-face' => FL_TOKEN_CSS_AT_FONTFACE, '@page' => FL_TOKEN_CSS_AT_PAGE, '/^\@(?:\-(?:webkit|moz|o|ms)\-)?keyframes/' => FL_TOKEN_CSS_AT_KEYFRAMES );
	/**
	 * 
	 * special tokens
	 * @var array
	 */
	public static $specialTokens = array (array ('[;', ';]', FL_TOKEN_CSS_HACK ) );
	/**
	 * 
	 * css comment pattern
	 * @var RegexIterator
	 */
	public static $commentPattern = '/\/\*.*?\*\//';
	/**
	 * 
	 * 属性hack字符
	 * @var array
	 */
	public static $propertyHack = array ('*', '!', '$', '&', '*', '(', ')', '=', '%', '+', '@', ',', '.', '/', '`', '[', ']', '#', '~', '?', ':', '<', '>', '|', '_', '-', '£', '¬', '¦' );
	/**
	 * 
	 * selector token split char
	 * @var array
	 */
	public static $selectorCharUtil = array ('#', '.', ':', '[', '>', '+', '~', '*', ',', '/' );
	/**
	 * 
	 * namespace pattern
	 * foo|div
	 * @var RegexIterator
	 */
	public static $namespacePattern = "/^[\w\*]+\|/";
	/**
	 * 
	 * Enter description here ...
	 * @var unknown_type
	 */
	public static $selectorTokenPattern = array (FL_TOKEN_CSS_SELECTOR_ID => "/^\#[\w]+$/ies", FL_TOKEN_CSS_SELECTOR_CLASS => "/^\.[\w\-]+$/ies", FL_TOKEN_CSS_SELECTOR_TYPE => "/^[a-z][\w]+$/ies" );
	/**
	 * 
	 * get comment from text
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
		return in_array ( $char, self::$selectorCharUtil );
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
	 * get selector text tokens
	 * @param string $text
	 */
	public static function getSelectorTokens($text) {
		Fl::loadClass ( 'Fl_Css_SelectorToken' );
		$instance = new Fl_Css_SelectorToken ( $text );
		return $instace->run ();
	}
	/**
	 * 
	 * compare two selector specificity
	 * @param string $selector1
	 * @param string $selector2
	 */
	public static function compareSelectorSpecificity($selector1, $selector2) {
		$score1 = self::getSelectorSpecificity ( $selector1 );
		$score2 = self::getSelectorSpecificity ( $selector2 );
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
	 * see more: http://www.w3.org/TR/selectors/#specificity
	 * @param array $selectorTokens
	 */
	public static function getSelectorSpecificity($selectorTokens = array()) {
		if (! is_array ( $selectorTokens )) {
			$selectorTokens = self::getSelectorTokens ( $selectorTokens );
		}
		$score = array (0, 0, 0 );
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
		return $score;
	}
}