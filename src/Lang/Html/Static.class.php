<?php
/**
 * 
 * HTML Static methods
 *
 */
class Fl_Html_Static {

	/**
	 * 
	 * left than sign
	 * @var string
	 */
	const LEFT = '<';

	/**
	 * 
	 * greater than sign
	 * @var string
	 */
	const RIGHT = '>';

	/**
	 * 
	 * xml的前缀
	 * @var string
	 */
	const XML_PREFIX = '<?xml ';

	/**
	 * 
	 * cdata前缀
	 * @var string
	 */
	const CDATA_PREFIX = '<![CDATA[';

	/**
	 * 
	 * cdata后缀
	 * @var string
	 */
	const CDATA_SUFFIX = ']]>';

	/**
	 * 
	 * simple doctype
	 * @var string
	 */
	const SIMPLE_DOCTYPE = '<!Doctype html>';

	/**
	 * 
	 * html里特殊的注释, 解析的时候不能当注释来解析
	 * @var array
	 */
	public static $specialCommentPrefix = array (
		'<!--[if', 
		'<!--status', 
		'<!--<![endif' 
	);

	/**
	 * 
	 * 特殊的token
	 * @var array
	 */
	public static $specialTokens = array (
		array (
			'<script', 
			'</script>', 
			FL_TOKEN_HTML_SCRIPT_TAG 
		), 
		array (
			'<style', 
			'</style>', 
			FL_TOKEN_HTML_STYLE_TAG 
		), 
		array (
			'<textarea', 
			'</textarea>', 
			FL_TOKEN_HTML_TEXTAREA_TAG 
		), 
		array (
			'<!--[if', 
			']>', 
			FL_TOKEN_HTML_IE_HACK, 
			']>-->' 
		), 
		array (
			'<![endif', 
			']-->', 
			FL_TOKEN_HTML_IE_HACK 
		), 
		array (
			'<!--<![endif', 
			']-->', 
			FL_TOKEN_HTML_IE_HACK 
		), 
		array (
			'<!Doctype', 
			'>', 
			FL_TOKEN_HTML_DOCTYPE 
		), 
		array (
			'<!--status', 
			'-->', 
			FL_TOKEN_HTML_STATUS 
		), 
		array (
			'<pre', 
			'</pre>', 
			FL_TOKEN_HTML_PRE_TAG 
		) 
	);

	/**
	 * 
	 * 标签首个字符正则
	 * 首个字符：26个大小写字符, ?用于xml, !用于注释, /用于闭合标签
	 * @var RegexIterator
	 */
	public static $tagFirstCharPattern = '/[A-Za-z\?\!\/]{1}/';

	/**
	 * 
	 * tag名的正则
	 * @var RegexIterator
	 */
	public static $tagNamePattern = '/^([A-Za-z\!][A-Za-z0-9\!]*)/';

	/**
	 * 
	 * 结束标签的正则
	 * @var RegexIterator
	 */
	public static $getTagNamePattern = '/^\<\/?([A-Za-z][A-Za-z0-9]*)/';

	/**
	 * 
	 * 属性值可以没有引号的正则
	 * 详情见：http://www.w3.org/TR/html5/syntax.html#attributes-0
	 * @var string
	 */
	public static $attrValueNoQuotePattern = '/^[^\s\"\'\=\<\>\`]+$/';

	/**
	 * 
	 * 空白字符
	 * @var array
	 */
	public static $whiteSpace = array (
		"\n", 
		"\t", 
		"\f" 
	);

	/**
	 * 
	 * 单一标签
	 * @var array
	 */
	public static $singleTag = array (
		"br", 
		"input", 
		"link", 
		"meta", 
		"!doctype", 
		"basefont", 
		"base", 
		"area", 
		"hr", 
		"wbr", 
		"param", 
		"img", 
		"isindex", 
		"?xml", 
		"embed" 
	);

	/**
	 * 
	 * 可选的闭合标签
	 * @var array
	 */
	public static $optionalEndTag = array (
		"html" => 1, 
		"body" => 1, 
		"colgroup" => 1, 
		"thead" => 1, 
		"tr" => 1, 
		"tbody" => 1, 
		"td" => 1, 
		"th" => 1, 
		"p" => 1, 
		"dt" => 1, 
		"dd" => 1, 
		"li" => 1, 
		"option" => 1, 
		"tfoot" => 1, 
		"rt" => 1, 
		"rp" => 1, 
		"optgroup" => 1 
	);

	/**
	 * 
	 * 块级标签
	 * @var array
	 */
	public static $blockTag = array (
		'html' => 1, 
		'meta' => 1, 
		'style' => 1, 
		'script' => 1, 
		'head' => 1, 
		'link' => 1, 
		'title' => 1, 
		'body' => 1, 
		'address' => 2, 
		'blockquote' => 2, 
		'center' => 2, 
		'dir' => 2, 
		'div' => 2, 
		'dl' => 2, 
		'fieldset' => 2, 
		'form' => 2, 
		'h1' => 2, 
		'h2' => 2, 
		'h3' => 2, 
		'h4' => 2, 
		'h5' => 2, 
		'h6' => 2, 
		'hr' => 2, 
		'menu' => 2, 
		'noframes' => 2, 
		'noscript' => 2, 
		'ol' => 2, 
		'p' => 2, 
		'pre' => 2, 
		'table' => 2, 
		'ul' => 2 
	);

	/**
	 * 
	 * 标签属性的默认值
	 * @var array
	 */
	public static $tagAttrDefaultValue = array (
		'*' => array (
			'class' => '', 
			'alt' => '', 
			'title' => '', 
			'style' => '' 
		), 
		'link' => array (
			'media' => 'screen' 
		), 
		'input' => array (
			'type' => 'text' 
		), 
		'form' => array (
			'method' => 'get' 
		), 
		'style' => array (
			'type' => 'text/css', 
			'rel' => 'stylesheet' 
		), 
		'script' => array (
			'type' => 'text/javascript', 
			'language' => 'javascript' 
		) 
	);

	/**
	 * 
	 * 只需要属性名就可以
	 * @var array
	 */
	public static $tagAttrOnlyName = array (
		'disabled' => 1, 
		'selected' => 1, 
		'checked' => 1, 
		'readonly' => 1, 
		'multiple' => 1 
	);

	/**
	 * 
	 * rel的属性值
	 * 通常出现在a, link中
	 * 参见：http://www.dreamdu.com/xhtml/attribute_rel/
	 * @var array
	 */
	public static $relValues = array (
		'alternate' => 1, 
		'appendix' => 1, 
		'bookmark' => 1, 
		'canonical' => 1, 
		'chapter' => 1, 
		'contents' => 1, 
		'copyright' => 1, 
		'glossary' => 1, 
		'help' => 1, 
		'index' => 1, 
		'next' => 1, 
		'nofollow' => 1, 
		'prev' => 1, 
		'section' => 1, 
		'start' => 1, 
		'stylesheet' => 1, 
		'subsection' => 1 
	);

	/**
	 * 
	 * no newline for token
	 * @var array
	 */
	public static $noNewlineTokens = array (
		FL_TOKEN_HTML_STATUS => 1, 
		FL_TOKEN_HTML_SCRIPT_TAG => 1, 
		FL_TOKEN_HTML_STYLE_TAG => 1, 
		FL_TOKEN_HTML_PRE_TAG => 1, 
		FL_TOKEN_HTML_IE_HACK => 1 
	);

	/**
	 * 
	 * 检测是否是各合法的标签第一个字符
	 * @param string $char
	 */
	public static function isTagFirstChar($char) {
		return preg_match ( self::$tagFirstCharPattern, $char );
	}

	/**
	 * 
	 * 获取开始或者结束标签的名字
	 * @param string $tag
	 */
	public static function getTagName($tag = '', Fl_Base $instance) {
		preg_match ( self::$getTagNamePattern, $tag, $matches );
		if (is_array ( $matches ) && $matches [1]) {
			return $matches [1];
		}
		$instance->throwException ( 'tag tag error' );
	}

	/**
	 * 
	 * 检测是否是个tag，开始或者闭合标签都可以
	 * @param array $token
	 */
	public static function isTag($token = array()) {
		return $token ['type'] === FL_TOKEN_HTML_TAG_START || $token ['type'] === FL_TOKEN_HTML_TAG_END;
	}

	/**
	 * 
	 * 是否是块级tag
	 * @param string $tag
	 * @param array $blackList
	 */
	public static function isBlockTag($tag, $blackList = array()) {
		return isset ( self::$blockTag [$tag] ) && ! in_array ( $tag, $blackList );
	}

	/**
	 * 
	 * 是否是可删除的闭合标签
	 * @param string $tag
	 * @param array $blackList
	 */
	public static function isOptionalEndTag($tag, $blackList = array()) {
		return isset ( self::$optionalEndTag [$tag] ) && ! in_array ( $tag, $blackList );
	}

	/**
	 * 
	 * 获取去除引号的文本内容
	 * @param string $text
	 */
	public static function getUnquoteText($text = '') {
		$startQuote = substr ( $text, 0, 1 );
		$endQuote = substr ( $text, strlen ( $text ) - 1 );
		if (($startQuote === '"' || $startQuote === "'") && $startQuote === $endQuote) {
			return array (
				'quote' => $startQuote, 
				'text' => substr ( $text, 1, strlen ( $text ) - 2 ) 
			);
		}
		return array (
			'quote' => '', 
			'text' => $text 
		);
	}

	/**
	 * 
	 * 检测是否tag的属性的默认值
	 * @param string $name
	 * @param string $value
	 * @param string $tag
	 */
	public static function isTagAttrDefaultValue($name, $value, $tag) {
		foreach ( self::$tagAttrDefaultValue as $key => $attrs ) {
			if ($key === '*' || $tag === $key) {
				foreach ( $attrs as $attrName => $attrValue ) {
					if ($name === $attrName && strtolower ( $value ) === $attrValue) {
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * 
	 * 属性值是否可以没有引号
	 * @param string $value
	 * @param Fl_Base $instance
	 */
	public static function isAttrValueNoQuote($value = '', Fl_Base $instance) {
		if ($instance->containTpl ( $value )) {
			return false;
		}
		return preg_match ( self::$attrValueNoQuotePattern, $value );
	}

	/**
	 * 
	 * 是否是单一标签
	 * @param string $tag
	 */
	public static function isSingleTag($tag) {
		return in_array ( $tag, self::$singleTag );
	}

	/**
	 * 
	 * 是否是只要属性名
	 * @param string $name
	 */
	public static function isTagOnlyNameAttr($name) {
		return isset ( self::$tagAttrOnlyName [$name] );
	}

	/**
	 * 
	 * 检测rel的值是否合法
	 * @param string $value
	 * @return 返回不符合规范的部分
	 */
	public static function checkRelValue($value) {
		$values = split ( FL_SPACE_PATTERN, $value );
		$return = array ();
		foreach ( $values as $item ) {
			if (! isset ( self::$relValues [$item] )) {
				$return [] = $item;
			}
		}
		return $return;
	}

	/**
	 * 
	 * 
	 */
	public static function splitSpecialValue($value, $type = 'script', $instance) {
		$value = trim ( $value );
		$pos = 0;
		$i = 0;
		$v = '';
		while ( true ) {
			$pos = strpos ( $value, ">", $pos );
			$v = substr ( $value, 0, $pos + 1 );
			try {
				$instance->getInstance ( "Fl_Html_TagToken", $v )->run ();
				break;
			} catch ( Fl_Exception $e ) {
				$i ++;
				if ($i > 5) {
					throw new Fl_Exception ( "split special value error `" . $value . "`", $code );
				}
			}
		}
		$prefix = $v;
		$suffix = "</" . $type . ">";
		$content = substr ( $value, $pos + 1, strlen ( $value ) - $pos - 1 - strlen ( $suffix ) );
		return array (
			"tag_start" => $prefix, 
			"content" => $content, 
			"tag_end" => $suffix 
		);
	}

	public static function isNoNewlineToken($type) {
		return isset ( self::$noNewlineTokens [$type] );
	}

	/**
	 * 
	 * 将tokens还原成html片段
	 * @param array $tokens
	 */
	public static function tokens2Text($tokens = array()) {
	}
}