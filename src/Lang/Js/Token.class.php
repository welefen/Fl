<?php
Fl::loadClass ( "Fl_Token" );
Fl::loadClass ( "Fl_Js_Static" );
/**
 * 
 * JS Tokenizar
 * @author welefen
 *
 */
class Fl_Js_Token extends Fl_Token {

	/**
	 * 
	 * 是否允许是正则， 主要是正则和除法（/）比较像，要对他们进行区别
	 * @var boolean
	 */
	public $regexpAllowed = true;

	/**
	 * 
	 * 是否进行简单的数据校验
	 * @var booelean
	 */
	public $validate = true;

	/**
	 * 
	 * validate data, check number
	 * @var array
	 */
	public $validateData = array (
		'(' => 0, 
		')' => 0, 
		'{' => 0, 
		'}' => 0, 
		'[' => 0, 
		']' => 0 
	);

	/**
	 * get next token
	 * @see Fl_Token::getNextToken()
	 */
	public function getNextToken() {
		$token = parent::getNextToken ();
		if ($token || $token === false) {
			return $token;
		}
		//获取特殊Token
		$token = $this->getSpecialToken ();
		if ($token) {
			return $token;
		}
		$char = $this->text {$this->pos};
		//字符串
		$result = $this->getQuoteText ( $char, true, true );
		if ($result) {
			$this->pendingNextChar = false;
			return $this->getTokenInfo ( FL_TOKEN_JS_STRING, $result );
		}
		//数值
		if (Fl_Js_Static::isDigit ( $char ) || $char === '.' && preg_match ( "/\d/", $this->text {$this->pos + 1} )) {
			$result = $this->getNumberToken ( $char );
			return $this->getTokenInfo ( FL_TOKEN_JS_NUMBER, $result );
		}
		//符号
		if (Fl_Js_Static::isPuncChar ( $char ) || $char === '.') {
			$this->getNextChar ();
			if (isset ( $this->validateData [$char] )) {
				$this->validateData [$char] ++;
			}
			return $this->getTokenInfo ( FL_TOKEN_JS_PUNC, $char );
		}
		if ($char === '/' && $this->regexpAllowed) {
			$result = $this->getRegexpToken ( $char );
			return $this->getTokenInfo ( FL_TOKEN_JS_REGEXP, $result );
		}
		//操作符号
		if (Fl_Js_Static::isOperatorChar ( $char ) || $char === '/') {
			$result = $this->getOperatorToken ( $char );
			return $this->getTokenInfo ( FL_TOKEN_JS_OPERATOR, $result );
		}
		//单词
		if (Fl_Js_Static::isIdentifierStart ( $char )) {
			$result = $this->readWhile ( 'getWordToken' );
			$type = FL_TOKEN_JS_NAME;
			if (Fl_Js_Static::isKeywordAtom ( $result )) {
				$type = FL_TOKEN_JS_ATOM;
			} else if (Fl_Js_Static::isOperator ( $result )) {
				$type = FL_TOKEN_JS_OPERATOR;
			} else if (Fl_Js_Static::isKeyword ( $result )) {
				$type = FL_TOKEN_JS_KEYWORD;
			}
			return $this->getTokenInfo ( $type, $result );
		}
		$this->throwException ( 'Unexpected character ' . $char );
	}

	/**
	 * get token info, add regexp allowed item
	 * @see Fl_Token::getTokenInfo()
	 */
	public function getTokenInfo($type = '', $value = '', $isComment = false) {
		$result = parent::getTokenInfo ( $type, $value, $isComment );
		$this->regexpAllowed = Fl_Js_Static::isRegexpAllowed ( $type, $value );
		return $result;
	}

	/**
	 * 
	 * get regexp token
	 * @param string $char
	 */
	public function getRegexpToken($char) {
		$result = $char;
		$escape = false;
		$inCharClass = false;
		$this->getNextChar ();
		while ( ($char = $this->getNextChar ()) !== false ) {
			if ($escape) {
				$result .= "\\" . $char;
				$escape = false;
			} else if ($char === '[') {
				$inCharClass = true;
				$result .= $char;
			} else if ($char === ']' && $inCharClass) {
				$inCharClass = false;
				$result .= $char;
			} else if ($char === '/' && ! $inCharClass) {
				break;
			} else if ($char === "\\") {
				$escape = true;
			} else {
				$result .= $char;
			}
		}
		$result .= '/';
		$char = $this->text [$this->pos];
		if (Fl_Js_Static::isIdentifierStart ( $char )) {
			$mods = $this->readWhile ( 'getWordToken' );
			//check modifier is valid
			if ($this->validate && ! Fl_Js_Static::checkRegexpModifiers ( $mods )) {
				$this->throwException ( 'Invalid flags supplied to RegExp constructor "' . $mods . '"' );
			}
		}
		return array (
			$result, 
			$mods 
		);
	}

	/**
	 * 
	 * 获取单词token
	 * @param string $char
	 */
	public function getWordToken($char) {
		if (! Fl_Js_Static::isIdentifierChar ( $char )) {
			$this->pendingNextChar = true;
			return true;
		}
	}

	/**
	 * 
	 * 获取操作符Token
	 * @param string $char
	 */
	public function getOperatorToken($char) {
		$result = $this->getNextChar ();
		while ( $this->pos < $this->length ) {
			$tmp = $result . $this->text {$this->pos};
			if (! Fl_Js_Static::isOperator ( $tmp )) {
				break;
			}
			$result .= $this->getNextChar ();
		}
		return $result;
	}

	/**
	 * 
	 * 获取数值Token
	 */
	public function getNumberToken($char) {
		$result = $this->getNextChar ();
		while ( $this->pos < $this->length ) {
			$tmp = $result . $this->text {$this->pos};
			if (! Fl_Js_Static::isNumberPrefix ( $tmp ) && ! Fl_Js_Static::isNumber ( $tmp )) {
				break;
			}
			$result .= $this->getNextChar ();
		}
		return $result;
	}

	/**
	 * 
	 * 获取一些特殊的token，如: IE的条件编译
	 */
	public function getSpecialToken() {
		foreach ( Fl_Js_Static::$specialTokens as $item ) {
			$result = $this->getMatched ( $item [0], $item [1], false, false, false );
			if ($result) {
				return $this->getTokenInfo ( $item [2], $result );
			}
		}
		return false;
	}

	/**
	 * @see Fl_Token::skipComment()
	 */
	public function skipComment() {
		while ( $this->text {$this->pos} === '/' ) {
			$mulComment = $this->getComment ( 'multi' );
			if ($mulComment) {
				$this->commentBefore [] = $mulComment;
				continue;
			}
			$inlineComment = $this->getComment ( 'inline' );
			if ($inlineComment) {
				$this->commentBefore [] = $inlineComment;
				continue;
			}
			//处理最后一个行内注释，但没有换行符，就不能getComment方法匹配到了。
			if ($this->text [$this->pos] === '/' && $this->text [$this->pos + 1] === '/') {
				$comment = substr ( $this->text, $this->pos );
				$this->commentBefore [] = $comment;
				$this->pos = $this->length;
			} else {
				break;
			}
		}
	}

	/**
	 * @see Fl_Token::getLastToken()
	 */
	public function getLastToken() {
		if ($this->validate) {
			$length = count ( $this->validateData );
			for($i = 0; $i < $length; $i += 2) {
				if ($this->validateData [$i] != $this->validateData [$i + 1]) {
					$this->throwException ( '"' . $item [0] . '"(' . $this->validateData [$item [0]] . ') & "' . $item [1] . '"(' . $this->validateData [$item [1]] . ') count not equal' );
				}
			}
		}
		return parent::getLastToken ();
	}
}