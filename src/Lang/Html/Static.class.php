<?php
/**
 * 
 * HTML静态类
 * 定义一些静态变量，方便在其他地方使用
 * @author welefen
 * @version 2011.12.05
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
	 * html里特殊的注释, 解析的时候不能当注释来解析
	 * @var array
	 */
	public static $specialCommentPrefix = array ('<!--[if', '<!--status', '<!--<![endif' );
	/**
	 * 
	 * 特殊的token
	 * @var array
	 */
	public static $specialTokens = array (array ('<script', '</script>', FL_TOKEN_HTML_SCRIPT_TAG ), array ('<style', '</style>', FL_TOKEN_HTML_STYLE_TAG ), array ('<textarea', '</textarea>', FL_TOKEN_HTML_TEXTAREA_TAG ), array ('<!--[if', ']>', FL_TOKEN_HTML_IE_HACK, ']>-->' ), array ('<![endif', ']-->', FL_TOKEN_HTML_IE_HACK ), array ('<!--<![endif', ']-->', FL_TOKEN_HTML_IE_HACK ), array ('<!Doctype', '>', FL_TOKEN_HTML_DOCTYPE ), array ('<!--status', '-->', FL_TOKEN_HTML_STATUS ), array ('<pre', '</pre>', FL_TOKEN_HTML_PRE_TAG ) );
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
	public static $tagNamePattern = '/^([A-Za-z\!]{1}[A-Za-z0-9\!]*)/';
	/**
	 * 
	 * 结束标签的正则
	 * @var RegexIterator
	 */
	public static $endTagNamePattern = '/^\<\/([A-Za-z]{1}[A-Za-z0-9]*)/';
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
	public static $whiteSpace = array ("\n", "\t", "\f" );
	/**
	 * 
	 * 单一标签
	 * @var array
	 */
	public static $singleTag = array ("br", "input", "link", "meta", "!doctype", "basefont", "base", "area", "hr", "wbr", "param", "img", "isindex", "?xml", "embed" );
	/**
	 * 
	 * 可选的闭合标签
	 * @var array
	 */
	public static $optionalEndTag = array ("html", "body", "colgroup", "thead", "tr", "tbody", "td", "th", "p", "dt", "dd", "li", "option", "tfoot", "rt", "rp", "optgroup" );
	/**
	 * 
	 * 块级标签
	 * @var array
	 */
	public static $blockTag = array ('address', 'blockquote', 'center', 'dir', 'div', 'dl', 'fieldset', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'menu', 'noframes', 'noscript', 'ol', 'p', 'pre', 'table', 'ul' );
	/**
	 * 
	 * 标签属性的默认值
	 * @var array
	 */
	public static $tagAttrDefaultValue = array ('*' => array ('class' => '', 'alt' => '', 'title' => '', 'style' => '' ), 'link' => array ('media' => 'screen' ), 'input' => array ('type' => 'text' ), 'form' => array ('method' => 'get' ), 'script' => array ('type' => 'text/javascript' ) );
	/**
	 * 
	 * 只需要属性名就可以
	 * @var array
	 */
	public static $tagAttrOnlyName = array ('disabled', 'selected', 'checked', 'readonly', 'multiple' );
	/**
	 * 
	 * rel的属性值
	 * 通常出现在a, link中
	 * 参见：http://www.dreamdu.com/xhtml/attribute_rel/
	 * @var array
	 */
	public static $relValues = array ('alternate', 'appendix', 'bookmark', 'canonical', 'chapter', 'contents', 'copyright', 'glossary', 'help', 'index', 'next', 'nofollow', 'prev', 'section', 'start', 'stylesheet', 'subsection' );
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
	 * 获取结束标签的名字
	 * @param string $endTag
	 */
	public static function getEndTagName($endTag = '', Fl_Base $instance) {
		preg_match ( self::$endTagNamePattern, $endTag, $matches );
		if (is_array ( $matches ) && $matches [1]) {
			return $matches [1];
		}
		$instance->throwException ( 'analytic end tag error' );
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
	 * 获取tag的属性
	 * @param string $tag
	 */
	public static function getTagAttrs($tag = '', Fl_Base $obj) {
		Fl::loadClass ( 'Fl_Html_TagToken' );
		$instance = new Fl_Html_TagToken ();
		$instance->tpl = $obj->tpl;
		$instance->ld = $obj->ld;
		$instance->rd = $obj->rd;
		return $instance->getAttrs ( $tag );
	}
	/**
	 * 
	 * 是否是块级tag
	 * @param string $tag
	 * @param array $blackList
	 */
	public static function isBlockTag($tag, $blackList = array()) {
		return in_array ( $tag, self::$blockTag ) && ! in_array ( $tag, $blackList );
	}
	/**
	 * 
	 * 是否是可删除的闭合标签
	 * @param string $tag
	 * @param array $blackList
	 */
	public static function isOptionalEndTag($tag, $blackList = array()) {
		return in_array ( $tag, self::$optionalEndTag ) && ! in_array ( $tag, $blackList );
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
			return array ('quote' => $startQuote, 'text' => substr ( $text, 1, strlen ( $text ) - 2 ) );
		}
		return array ('quote' => '', 'text' => $text );
	}
	/**
	 * 
	 * 检测文本是否包含模版语法
	 * @param string $text
	 * @param Fl_Base $instance
	 */
	public static function containTpl($text, Fl_Base $instance) {
		$ld = $instance->ld;
		$rd = $instance->rd;
		if ($instance->tpl && $ld && $rd) {
			return strpos ( $text, $ld ) !== false && strpos ( $text, $rd ) !== false;
		}
		return false;
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
					if ($name === $attrName && $value === $attrValue) {
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
		if (self::containTpl ( $value, $instance )) {
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
		return in_array ( $name, self::$tagAttrOnlyName );
	}
	/**
	 * 
	 * 检测rel的值是否合法
	 * @param string $value
	 * @return 返回不符合规范的部分
	 */
	public static function checkRelValue($value) {
		$values = split ( "/\s+/", $value );
		$return = array ();
		foreach ( $values as $item ) {
			if (! in_array ( $item, self::$relValues )) {
				$return [] = $item;
			}
		}
		return $return;
	}
	/**
	 * 
	 * 将tokens还原成html片段
	 * @param array $tokens
	 */
	public static function tokensToHtml($tokens = array()){
		
	}
}