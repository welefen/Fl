<?php
Fl::loadClass ( 'Fl_Base' );
Fl::loadClass ( 'Fl_Html_Static' );
Fl::loadClass ( 'Fl_Tpl' );
/**
 * 
 * HTML Compress support tpl
 * @author welefen
 *
 */
class Fl_Html_Compress extends Fl_Base {

	/**
	 * 
	 * 压缩选项
	 * @var array
	 */
	public $options = array (
		"remove_comment" => true, 
		"simple_doctype" => true, 
		"newline_to_space" => true, 
		"tag_to_lower" => true, 
		"remove_inter_tag_space" => false,  //not safe
		"remove_inter_block_tag_space" => true,  //safe
		"replace_multi_space" => FL_SPACE, 
		"remove_script_attrs" => true, 
		"remove_style_attrs" => true, 
		"remove_optional_attrs" => true, 
		"remove_attrs_quote" => true, 
		"remove_attrs_optional_value" => true, 
		"remove_http_protocal" => true, 
		"remove_https_protocal" => true, 
		"remove_optional_end_tag" => true, 
		"remove_optional_end_tag_list" => array (), 
		"chars_line_break" => 8000, 
		"compress_tag" => true 
	);

	/**
	 * 
	 * is a xml
	 * @var boolean
	 */
	public $isXML = false;

	/**
	 * prev token
	 * @var array
	 */
	protected $preToken = array ();

	/**
	 * 
	 * next token
	 * @var array
	 */
	protected $nextToken = array ();

	/**
	 * 
	 * output
	 * @var array
	 */
	protected $output = array ();

	/**
	 * 
	 * prev output text
	 * @var string
	 */
	protected $preOutputText = '';

	/**
	 * 
	 * token instance
	 * @var object
	 */
	protected $tokenInstance = null;

	/**
	 * 
	 * 压缩HTML
	 * @param string $text
	 * @param array $options
	 */
	public function run($options = array()) {
		$this->options = array_merge ( $this->options, $options );
		if ($this->isXML) {
			$this->options ['tag_to_lower'] = false;
		}
		$this->tokenInstance = $this->getInstance ( 'Fl_Html_Token' );
		$token = array ();
		$this->nextToken = $this->getNextToken ();
		$end = false;
		$num = 0;
		$charsLineBreak = intval ( $this->options ['chars_line_break'] );
		while ( true ) {
			$this->preToken = $token;
			$token = $this->nextToken;
			$this->nextToken = $this->getNextToken ();
			if (! $this->nextToken) {
				$end = true;
				$this->nextToken = array ();
			}
			if ($token ['type'] === FL_TOKEN_XML_HEAD) {
				$this->isXML = true;
				$this->options ['tag_to_lower'] = false;
			}
			$this->preOutputText = $this->compressToken ( $token );
			$len = strlen ( $this->preOutputText );
			if ($charsLineBreak && ($num + $len) > $charsLineBreak) {
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
		if ($this->options ['compress_tag']) {
			if ($nextToken ['type'] === FL_TOKEN_HTML_TAG_START) {
				$result = $this->getInstance ( "Fl_Html_TagToken", $nextToken ['value'] )->run ();
				$nextToken ['tag'] = $result ['tag'];
				$nextToken ['attrs'] = $result ['attrs'];
			} elseif ($nextToken ['type'] === FL_TOKEN_HTML_TAG_END) {
				$nextToken ['tag'] = Fl_Html_Static::getTagName ( $nextToken ['value'], $this );
			}
		} else {
			if (Fl_Html_Static::isTag ( $nextToken )) {
				$nextToken ['tag'] = Fl_Html_Static::getTagName ( $nextToken ['value'], $this );
			}
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
		$result = $this->compressCommon ( $token );
		switch ($token ['type']) {
			case FL_TOKEN_HTML_DOCTYPE :
				if ($this->options ['simple_doctype']) {
					return Fl_Html_Static::SIMPLE_DOCTYPE;
				} else {
					$result .= $token ['value'];
					break;
				}
			case FL_TOKEN_HTML_TAG_START :
				$result .= $this->compressStartTag ( $token );
				break;
			case FL_TOKEN_HTML_TEXT :
				$result .= $this->compressText ( $token );
				break;
			case FL_TOKEN_TPL :
				$result .= $this->compressTpl ( $token );
				break;
			case FL_TOKEN_HTML_TAG_END :
				$result .= $this->compressEndTag ( $token );
				break;
			default :
				$result .= $this->compressDefault ( $token );
		}
		return $result;
	}

	/**
	 * 
	 * 压缩一些通用的，如：注释，换行等
	 * @param array $token
	 */
	public function compressCommon($token) {
		$return = $comment = $newline = '';
		foreach ( $token ['commentBefore'] as $item ) {
			//如果注释内容第一个字符是!，则保留该注释
			if (preg_match ( '/^\<\!\-\-\!/', $item ['text'] ) || ! $this->options ['remove_comment']) {
				$comment .= $item ['text'];
			}
		}
		if (! $token ['newlineBefore']) {
			return $comment;
		}
		//如果是模版语法且不会输出，则不添加空白字符
		if ($token ['type'] === FL_TOKEN_TPL && ! $this->checkTplHasOutput ( $token ['value'] )) {
			return $comment;
		}
		if ($this->options ['newline_to_space']) {
			$newline = FL_SPACE;
		} else {
			$newline = FL_NEWLINE;
		}
		$preText = $this->preOutputText;
		if (! $this->isXML && $preText && substr ( $preText, strlen ( $preText ) - 1, 1 ) == $newline) {
			return $comment;
		}
		if (Fl_Html_Static::isTag ( $token )) {
			if ($this->options ['remove_inter_tag_space']) {
				return $comment;
			}
			if ($this->options ['remove_inter_block_tag_space'] && Fl_Html_Static::isBlockTag ( $token ['tag'] )) {
				return $comment;
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
			return $value;
		}
		if ($this->options ['newline_to_space']) {
			$value = str_replace ( FL_NEWLINE, FL_SPACE, $value );
		}
		if ($this->options ['replace_multi_space'] !== false) {
			$value = preg_replace ( FL_SPACE_PATTERN, $this->options ['replace_multi_space'], $value );
		}
		if ($this->options ['remove_inter_tag_space']) {
			$value = rtrim ( $value );
		} elseif ($this->options ['remove_inter_block_tag_space'] && Fl_Html_Static::isTag ( $this->nextToken ) && Fl_Html_Static::isBlockTag ( $this->nextToken ['lowerTag'] )) {
			$value = rtrim ( $value );
		}
		return $value;
	}

	/**
	 * 
	 * 压缩开始标签
	 */
	public function compressStartTag($token) {
		if ($this->isXML || ! $this->options ['compress_tag']) {
			return $token ['value'];
		}
		$tag = $token ['tag'];
		$lowerTag = $token ['lowerTag'];
		$attrs = $token ['attrs'];
		$resultAttrs = array ();
		foreach ( $attrs as $item ) {
			if ($item [1] === '=') {
				$valueDetail = Fl_Html_Static::getUnquoteText ( $item [2] );
				if ($this->options ['remove_optional_attrs'] && Fl_Html_Static::isTagAttrDefaultValue ( $item [0], $valueDetail ['text'], $lowerTag )) {
					continue;
				}
				if ($this->options ['remove_attrs_optional_value'] && Fl_Html_Static::isTagOnlyNameAttr ( $item [0] )) {
					$item = array (
						$item [0] 
					);
					continue;
				} else if ($this->options ['remove_attrs_quote'] && Fl_Html_Static::isAttrValueNoQuote ( $valueDetail ['text'], $this )) {
					$item [2] = $valueDetail ['text'];
				}
				if ($this->options ['remove_http_protocal'] || $this->options ['remove_https_protocal']) {
					$nameLower = strtolower ( $item [0] );
					if ($nameLower === 'href' || $nameLower === "src") {
						$valueDetail = Fl_Html_Static::getUnquoteText ( $item [2] );
						$value = $valueDetail ['text'];
						if ($this->options ['remove_http_protocal'] && strpos ( $value, "http://" ) === 0) {
							$value = substr ( $value, 5 );
							$item [2] = $valueDetail ['quote'] . $value . $valueDetail ['quote'];
						} elseif ($this->options ['remove_https_protocal'] && strpos ( $value, "https://" ) === 0) {
							$value = substr ( $value, 6 );
							$item [2] = $valueDetail ['quote'] . $value . $valueDetail ['quote'];
						}
					}
				}
			}
			$resultAttrs [] = $item;
		}
		$return = Fl_Html_Static::LEFT;
		if ($this->options ['tag_to_lower']) {
			$return .= $lowerTag;
		} else {
			$return .= $tag;
		}
		$blankChar = FL_SPACE;
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
			} else {
				$last2Char = substr ( $return, strlen ( $return ) - 2 );
				if ($last2Char === '\\"' || $last2Char === "\\'") {
					$return .= $blankChar;
				}
			}
			$return .= $itemText;
		}
		$return = rtrim ( $return ) . Fl_Html_Static::RIGHT;
		return $return;
	}

	/**
	 * 
	 * 压缩闭合标签
	 * @param array $token
	 */
	public function compressEndTag($token) {
		if ($this->options ['remove_optional_end_tag']) {
			if (Fl_Html_Static::isOptionalEndTag ( $token ['lowerTag'], $this->options ['remove_optional_end_tag_list'] )) {
				return '';
			}
		}
		$tag = $this->options ['tag_to_lower'] ? $token ['lowerTag'] : $token ['tag'];
		return '</' . $tag . '>';
	}

	/**
	 * 
	 * 压缩模版语法
	 * @param array $token
	 */
	public function compressTpl($token) {
		return Fl_Tpl::factory ( $this )->compress ( $token ['value'], $this );
	}
}