<?php
/**
 * 继承自Fl_Token类
 */
Fl::loadClass ( 'Fl_Token' );
Fl::loadClass ( 'Fl_Css_Static' );
/**
 * 
 * CSS的词法分析
 * @author welefen
 *
 */
class Fl_Css_Token extends Fl_Token {
	/**
	 * 
	 * 上一个token的类型
	 * @var string
	 */
	public $preTokenType = '';
	/**
	 * 
	 * 花括号的个数
	 * @var array
	 */
	public $bracesNum = array (0, 0 );
	
	/**
	 * 获取下一个TOKEN
	 * @see Fl_Token::getNextToken()
	 */
	public function getNextToken() {
		$token = parent::getNextToken ();
		if ($token || $token === false) {
			return $token;
		}
		$char = $this->getCurrentChar ();
		if ($char === false) {
			return $this->getLastToken ();
		}
		if ($char === '@') {
			$result = $this->readWhile ( 'getAtToken' );
			$type = $this->getAtDetailType ( $result );
			return $this->getTokenInfo ( $type, $result );
		} else if ($char === '{') {
			$type = $this->getStartBracesType ();
			$this->getNextChar ();
			return $this->getTokenInfo ( $type, $char );
		} else if ($char === '}') {
			$type = $this->getEndBracesType ();
			$this->getNextChar ();
			return $this->getTokenInfo ( $type, $char );
		} else if ($char === ':') {
			if ($this->preTokenType != FL_TOKEN_CSS_PROPERTY && $this->preTokenType != FL_TOKEN_TPL) {
				$this->throwException ( 'prev value is not property or tpl' );
			}
			$this->getNextChar ();
			$this->preTokenType = FL_TOKEN_CSS_COLON;
			return $this->getTokenInfo ( FL_TOKEN_CSS_COLON, $char );
		} else if ($char === ';') {
			$this->getNextChar ();
			$this->preTokenType = FL_TOKEN_CSS_SEMICOLON;
			return $this->getTokenInfo ( FL_TOKEN_CSS_SEMICOLON, $char );
		}
		$token = $this->getSpecialToken ();
		if ($token) {
			return $token;
		}
		switch ($this->preTokenType) {
			case FL_TOKEN_CSS_BRACES_ONE_START :
			case FL_TOKEN_CSS_SEMICOLON :
				$result = $this->readWhile ( 'getPropertyToken' );
				$this->preTokenType = FL_TOKEN_CSS_PROPERTY;
				return $this->getTokenInfo ( FL_TOKEN_CSS_PROPERTY, $result );
			case FL_TOKEN_CSS_COLON :
			case FL_TOKEN_CSS_PROPERTY :
				$result = $this->getValueToken ();
				$this->preTokenType = FL_TOKEN_CSS_VALUE;
				return $this->getTokenInfo ( FL_TOKEN_CSS_VALUE, $result );
			default :
				$result = $this->readWhile ( 'getSelectorToken' );
				if ($this->getPosChar ( $this->pos ) !== '{') {
					$this->throwException ( 'get Selector error' );
				}
				$this->preTokenType = FL_TOKEN_CSS_SELECTOR;
				return $this->getTokenInfo ( FL_TOKEN_CSS_SELECTOR, $result );
		}
		$this->throwException ( 'uncaught error' );
	}
	/**
	 * 
	 * 获取@开头的token
	 * @param string $char
	 */
	public function getAtToken($char = '') {
		$comment = $this->getComment ( 'multi', false );
		if ($comment) {
			$this->pendingNextChar = true;
			return $comment;
		}
		if ($return = $this->getQuoteText ( $char, true )) {
			return $return;
		}
		if ($char === ';') {
			return false;
		}
		if ($this->getPosChar ( $this->pos + 1 ) === '{') {
			return false;
		}
	}
	/**
	 * 
	 * 获取@的详细类型
	 */
	public function getAtDetailType($result = '') {
		$type = Fl_Css_Static::getAtDetailType ( $result, $this );
		$this->preTokenType = $type;
		return $type;
	}
	/**
	 * 跳过评论
	 * (non-PHPdoc)
	 * @see Fl_Token::skipComment()
	 */
	public function skipComment() {
		while ( $comment = $this->getComment ( 'multi' ) ) {
			$this->commentBefore [] = $comment;
		}
	}
	/**
	 * 
	 * 获取左花括号的类型
	 */
	public function getStartBracesType() {
		switch ($this->preTokenType) {
			case FL_TOKEN_CSS_AT_MEDIA :
			case FL_TOKEN_CSS_AT_KEYFRAMES :
				$type = FL_TOKEN_CSS_BRACES_TWO_START;
				break;
			case FL_TOKEN_CSS_SELECTOR :
			case FL_TOKEN_CSS_AT_PAGE :
			case FL_TOKEN_CSS_AT_OTHER :
				$type = FL_TOKEN_CSS_BRACES_ONE_START;
				break;
		}
		if (! $type) {
			$this->throwException ( 'get { error' );
		}
		$this->bracesNum [0] ++;
		$this->preTokenType = $type;
		return $type;
	}
	/**
	 * 
	 * 获取右花括号的类型
	 */
	public function getEndBracesType() {
		switch ($this->preTokenType) {
			case FL_TOKEN_CSS_VALUE :
			case FL_TOKEN_CSS_SEMICOLON :
			case FL_TOKEN_CSS_COLON :
			case FL_TOKEN_CSS_PROPERTY :
			case FL_TOKEN_CSS_BRACES_ONE_START :
				$type = FL_TOKEN_CSS_BRACES_ONE_END;
				break;
			default :
				$type = FL_TOKEN_CSS_BRACES_TWO_END;
		}
		$this->bracesNum [1] ++;
		$this->preTokenType = $type;
		return $type;
	}
	/**
	 * 
	 * 获取属性名
	 */
	public function getPropertyToken($char = '') {
		$nextChar = $this->getPosChar ( $this->pos + 1 );
		if ($nextChar === ':' || $nextChar === '{' || $nextChar === ';' || $nextChar === '}') {
			return false;
		}
	}
	/**
	 * 
	 * 获取属性值
	 */
	public function getValueToken() {
		$return = '';
		while ( ($char = $this->getNextChar ()) !== false ) {
			$return .= $char;
			//expression和background（dataURI）的值可能含有:和;，这会导致后面的解析出问题，所以要特殊处理
			if ($char === '(') {
				if (preg_match ( "/expression\s*/ies", $return )) {
					$value = $this->getJsText ();
					$return .= $value;
				} else {
					$matched = $this->getMatched ( $this->getCurrentChar (), ')', false, false, false );
					if ($matched) {
						$return .= $matched;
					}
				}
			}
			$nextChar = $this->getPosChar ( $this->pos );
			if ($nextChar === ';' || $nextChar === '}') {
				break;
			}
		}
		return $return;
	}
	/**
	 * 
	 * 获取expression里js部分的值
	 */
	public function getJsText() {
		$result = '';
		Fl::loadClass ( 'Fl_Js_Token' );
		while ( ($char = $this->getNextChar ()) !== false ) {
			$result .= $char;
			if ($char === ')') {
				$instance = new Fl_Js_Token ( $result );
				$instance->validate = false;
				$output = $instance->getAllTokens ();
				if ($instance->validateData ['('] === $instance->validateData [')']) {
					break;
				}
			}
		}
		return $result;
	}
	/**
	 * 
	 * 获取selector
	 */
	public function getSelectorToken($char = '') {
		$comment = $this->getComment ( 'multi', false );
		if ($comment) {
			$this->pendingNextChar = true;
			return $comment;
		}
		if ($return = $this->getQuoteText ( $char, true )) {
			return $return;
		}
		if ($this->getPosChar ( $this->pos + 1 ) === '{') {
			return false;
		}
	}
	/**
	 * 
	 * 获取一些特殊的token，如: chrome下的hack
	 */
	public function getSpecialToken() {
		foreach ( Fl_Css_Static::$specialTokens as $item ) {
			$result = $this->getMatched ( $item [0], $item [1], false, false, false );
			if ($result) {
				return $this->getTokenInfo ( $item [2], $result );
			}
		}
		return false;
	}
	/**
	 * 在获取最后一个token的时候，检测{和}个数是否相等
	 * @see Fl_Token::getLastToken()
	 */
	public function getLastToken() {
		if ($this->bracesNum [0] != $this->bracesNum [1]) {
			$this->throwException ( '{ & } num not equal' );
		}
		return parent::getLastToken ();
	}
}