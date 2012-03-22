<?php
Fl::loadClass ( 'Fl_Html_Token' );
Fl::loadClass ( 'Fl_Html_Static' );
/**
 * 
 * HTML压缩类，支持模版语法的压缩
 * @author welefen
 *
 */
class Fl_Html_Compress extends Fl_Base {
	/**
	 * 
	 * token类名
	 * @var string
	 */
	public $tokenClass = 'Fl_Html_Token';
	/**
	 * 
	 * token类实例
	 * @var object
	 */
	public $tokenInstance = null;
	/**
	 * 
	 * 压缩选项
	 * @var array
	 */
	public $options = array ();
	/**
	 * 
	 * 前一个token
	 * @var array
	 */
	public $preToken = array ();
	/**
	 * 
	 * 下一个token
	 * @var array
	 */
	public $nextToken = array ();
	/**
	 * 
	 * 最终输出结果
	 * @var array
	 */
	public $output = array ();
	/**
	 * 
	 * 上一个输出的文本
	 * @var string
	 */
	public $preOutputText = '';
	
	/**
	 * 
	 * 是否是XML
	 * @var boolean
	 */
	public $isXML = false;
	/**
	 * 
	 * 保留换行符
	 * @var boolean
	 */
	public $retentionNewline = false;
	
	public function __construct($text = '') {
		parent::__construct ( $text );
		$this->setDefaultOptions ();
	}
	/**
	 * 
	 * 设置默认配置项
	 */
	public function setDefaultOptions() {
		$options = array ();
		$options ['removeComment'] = true; //去除注释
		$options ['removeTextExtBlank'] = true; //去除文本节点里多余的空白字符
		$options ['removeBlockBlank'] = true; //去除2个block元素之间的空白字符，如：</div> <div>
		$options ['blockBlankList'] = array (); //block元素白名单，命中白名单则不去除
		$options ['removeAttrDefaultValue'] = true; //去除一些元素的默认值
		$options ['removeOptionalTag'] = true; //去除一些可以去除的闭合标签
		$options ['removeAttrQuote'] = true; //去除属性值的引号，如果可以去除的话
		$options ['removeAttrExtBlank'] = true; //去除属性值里多余的空白字符
		$options ['optionalTagList'] = array ();
		$options ['tagToLower'] = true; //将tag名小写,如果是XML，则不变为小写
		$options ['newline'] = ''; //换行符改变，默认删除
		$options ['endSingleTag'] = false; //闭合单一标签，如： <input type="text" />; 加上"/"
		$options ['attrOnlyName'] = true; //只要属性名，如disabled="true"
		$options ['charsLineBreak'] = 8000; //一行放多少个字符
		$this->options = $options;
	}
	/**
	 * 
	 * 压缩HTML
	 * @param string $text
	 * @param array $options
	 */
	public function run($text = '', $options = array()) {
		$this->setText ( $text );
		$this->options = array_merge ( $this->options, $options );
		$this->tokenInstance = $this->getTokenInstance ();
		$token = array ();
		$this->nextToken = $this->getNextToken ();
		$end = false;
		$num = 0;
		while ( true ) {
			$this->preToken = $token;
			$token = $this->nextToken;
			$this->nextToken = $this->getNextToken ();
			if (! $this->nextToken) {
				$end = true;
				$this->nextToken = array ();
			}
			if (! $this->isXML && $token ['type'] === FL_TOKEN_XML_HEAD) {
				$this->isXML = true;
				//如果是XML,是区分标签名大小写的
				$this->options ['tagToLower'] = false;
			}
			$this->preOutputText = $this->compressToken ( $token );
			$len = strlen ( $this->preOutputText );
			if (($num + $len) > $this->options ['charsLineBreak']) {
				$this->output [] = FL_NEWLINE;
				$num = $len;
			} else {
				$num += $len;
			}
			$this->output [] = $this->preOutputText;
			if ($end) {
				break;
			}
		}
		return join ( '', $this->output );
	}
	/**
	 * 
	 * 获取下一个token
	 */
	public function getNextToken() {
		$nextToken = $this->tokenInstance->getNextToken ();
		if (! $nextToken) {
			return false;
		}
		if ($nextToken ['type'] === FL_TOKEN_HTML_TAG_START) {
			$detail = Fl_Html_Static::getTagAttrs ( $nextToken ['value'], $this );
			$nextToken = array_merge ( $nextToken, $detail );
		} else if ($nextToken ['type'] === FL_TOKEN_HTML_TAG_END) {
			$tag = Fl_Html_Static::getEndTagName ( $nextToken ['value'], $this );
			$nextToken = array_merge ( $nextToken, array ('tag' => $tag ) );
		}
		if ($nextToken ['tag']) {
			$nextToken ['lowerTag'] = strtolower ( $nextToken ['tag'] );
		}
		return $nextToken;
	}
	/**
	 * 
	 * 压缩每个token
	 * @param array $token
	 */
	public function compressToken($token) {
		$return = $this->compressCommon ( $token );
		switch ($token ['type']) {
			case FL_TOKEN_HTML_TAG_START :
				$return .= $this->compressStartTag ( $token );
				break;
			case FL_TOKEN_HTML_TEXT :
				$return .= $this->compressText ( $token );
				break;
			case FL_TOKEN_TPL :
				$return .= $this->compressTpl ( $token );
				break;
			case FL_TOKEN_HTML_TAG_END :
				$return .= $this->compressEndTag ( $token );
				break;
			default :
				$return .= $this->compressDefault ( $token );
				break;
		}
		return $return;
	}
	/**
	 * 
	 * 压缩一些通用的，如：注释，换行等
	 * @param array $token
	 */
	public function compressCommon($token) {
		$return = '';
		$comment = '';
		$newline = '';
		//是否去掉注释
		if (count ( $token ['commentBefore'] )) {
			foreach ( $token ['commentBefore'] as $item ) {
				//如果注释内容第一个字符是!，则保留该注释
				if (preg_match ( '/^\<\!\-\-\s*\!/', $item ) || ! $this->options ['removeComment']) {
					$comment .= $item;
				}
			}
		}
		
		//是否去除换行符
		if ($token ['newlineBefore']) {
			if ($this->retentionNewline) {
				$newline = "\n";
				$this->retentionNewline = false;
			} else {
				//如果是模版语法且不会输出，则不添加空白字符
				if ($token ['type'] === FL_TOKEN_TPL && ! $this->checkTplHasOutput ( $token ['value'] )) {
					//$newline = '';
				} else {
					$preText = $this->preOutputText;
					if (! $this->isXML && $preText && substr ( $preText, strlen ( $preText ) - 1, 1 ) !== $this->options ['newline']) {
						$newline = $this->options ['newline'];
						if ($this->textCanRemove ( $newline, $this->preToken, $token )) {
							$newline = '';
						}
					}
				}
			}
		}
		if ($token ['col'] == 0) {
			$return .= $comment . $newline;
		} else {
			$return .= $newline . $comment;
		}
		return $return;
	}
	/**
	 * 
	 * 压缩默认的token
	 * @param array $token
	 */
	public function compressDefault($token) {
		return $token ['value'];
	}
	/**
	 * 
	 * 压缩文本
	 */
	public function compressText($token) {
		$value = $token ['value'];
		//如果文本中含有//，则不去除换行等，主要是一些异步接口（JS环境）会被识别成HTML环境，如果有JS的//注释就要注意了
		if (strpos ( $value, '//' ) !== false) {
			$this->retentionNewline = true;
			return $value;
		}
		//将多个空白字符合并为一个
		if ($this->options ['removeTextExtBlank']) {
			$value = str_replace ( Fl_Html_Static::$whiteSpace, "", $value );
			$value = preg_replace ( "/\s+/", " ", $value );
		}
		//如果下一个是块级元素，则文本右侧的空白字符可以去除
		if ($this->textCanRemove ( " ", $this->preToken, $this->nextToken )) {
			$value = rtrim ( $value );
		}
		//如果下一个是块级元素，且文本是空白字符，则完全去除
		if ($this->textCanRemove ( $value, $this->preToken, $this->nextToken )) {
			$value = '';
		}
		return $value;
	}
	/**
	 * 
	 * 压缩开始标签
	 */
	public function compressStartTag($token) {
		if ($this->isXML) {
			return $token ['value'];
		}
		$tag = $token ['tag'];
		$lowerTag = $token ['lowerTag'];
		$attrs = $token ['attrs'];
		$resultAttrs = array ();
		foreach ( $attrs as $item ) {
			if ($item [1] === '=') {
				$valueDetail = Fl_Html_Static::getUnquoteText ( $item [2] );
				if ($this->options ['removeAttrDefaultValue'] && Fl_Html_Static::isTagAttrDefaultValue ( $item [0], $valueDetail ['text'], $lowerTag )) {
					continue;
				}
				if ($this->options ['attrOnlyName'] && Fl_Html_Static::isTagOnlyNameAttr ( $item [0] )) {
					$item = array ($item [0] );
				} else if ($this->options ['removeAttrQuote'] && Fl_Html_Static::isAttrValueNoQuote ( $valueDetail ['text'], $this )) {
					$item [2] = $valueDetail ['text'];
				}
			}
			$resultAttrs [] = $item;
		}
		$return = Fl_Html_Static::LEFT;
		if ($this->options ['tagToLower']) {
			$return .= $lowerTag;
		} else {
			$return .= $tag;
		}
		$blankChar = ' ';
		$return .= $blankChar;
		foreach ( $resultAttrs as $item ) {
			$itemText = join ( '', $item );
			$lastChar = substr ( $return, strlen ( $return ) - 1 );
			if ($lastChar !== '"' && $lastChar !== "'" && $lastChar !== $blankChar) {
				if ($item [1] !== '=' && $this->isTpl ( $item [0] ) && ! $this->checkTplHasOutput ( $item [0] )) {
					//do nothing
				} else {
					$return .= $blankChar;
				}
			}
			$return .= $itemText;
		}
		if ($this->options ['endSingleTag'] && Fl_Html_Static::isSingleTag ( $lowerTag )) {
			$lastChar = substr ( $return, strlen ( $return ) - 1 );
			if ($lastChar !== '"' && $lastChar !== "'" && $lastChar !== $blankChar) {
				$return .= $blankChar;
			}
			$return .= '/';
		}
		$return = rtrim ( $return );
		$return .= Fl_Html_Static::RIGHT;
		return $return;
	}
	/**
	 * 
	 * 压缩闭合标签
	 * @param array $token
	 */
	public function compressEndTag($token) {
		if ($this->options ['removeOptionalTag']) {
			if (Fl_Html_Static::isOptionalEndTag ( $token ['lowerTag'], $this->options ['optionalTagList'] )) {
				return '';
			}
		}
		$tag = $this->options ['tagToLower'] ? $token ['lowerTag'] : $token ['tag'];
		return '</' . $tag . '>';
	}
	/**
	 * 
	 * 压缩模版语法
	 * @param array $token
	 */
	public function compressTpl($token) {
		Fl::loadClass ( 'Fl_Tpl' );
		return Fl_Tpl::factory ( $this )->compress ( $token ['value'], $this );
	}
	/**
	 * 
	 * 判断当前的text是否可删除
	 * @param string $text
	 * @param array $nextToken
	 */
	public function textCanRemove($text, $preToken = array(), $nextToken = array()) {
		if ($this->options ['removeBlockBlank'] && preg_match ( '/^\s+$/', $text )) {
			$pregTag = $pregToken ['lowerTag'];
			$nextTag = $nextToken ['lowerTag'];
			if ($pregTag && Fl_Html_Static::isBlockTag ( $pregTag, $this->options ['blockBlankList'] )) {
				return true;
			}
			if ($nextTag && Fl_Html_Static::isBlockTag ( $nextTag, $this->options ['blockBlankList'] )) {
				return true;
			}
		}
		return false;
	}
}