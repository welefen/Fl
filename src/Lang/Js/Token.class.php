<?php
Fl::loadClass ( "Fl_Token" );
Fl::loadClass ( "Fl_Js_Static" );
/**
 * 
 * JS的词法分析
 * @author welefen
 * @version 1.0-2012.02.15
 *
 */
class Fl_Js_Token extends Fl_Token {
	/**
	 * 
	 * 临时存储变量
	 * @var string
	 */
	public $tmp = '';
	/**
	 * 
	 * 是否允许是正则， 主要是正则和除法（/）比较像，要对他们进行区别
	 * @var boolean
	 */
	public $regexpAllowed = false;
	/**
	 * 
	 * 是否进行简单的数据校验
	 * @var booelean
	 */
	public $validate = true;
	/**
	 * 
	 * 用于存放校验数据
	 * @var array
	 */
	public $validateData = array ('(' => 0, ')' => 0, '{' => 0, '}' => 0, '[' => 0, ']' => 0 );
	/**
	 * 获取下一个TOKEN
	 * (non-PHPdoc)
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
		$char = $this->getCurrentChar ();
		if ($char === false) {
			return $this->getLastToken ();
		}
		//字符串
		$result = $this->getQuoteText ( $char, true, true );
		if ($result) {
			$this->pendingNextChar = false;
			return $this->getTokenInfo ( FL_TOKEN_JS_STRING, $result );
		}
		//数值
		if (Fl_Js_Static::isDigit ( $char )) {
			$result = $this->readWhile ( 'getNumberToken' );
			return $this->getTokenInfo ( FL_TOKEN_JS_NUMBER, $result );
		}
		//符号
		if (Fl_Js_Static::isPuncChar ( $char ) || $char === '.') {
			$this->getNextChar ();
			if (array_key_exists ( $char, $this->validateData )) {
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
			//echo $char;
			$result = $this->readWhile ( 'getOperatorToken' );
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
	 * (non-PHPdoc)
	 * @see Fl_Token::getTokenInfo()
	 */
	public function getTokenInfo($type = '', $value = '', $isComment = false) {
		$result = parent::getTokenInfo ( $type, $value, $isComment );
		$this->regexpAllowed = Fl_Js_Static::isRegexpAllowed ( $type, $value );
		return $result;
	}
	/**
	 * 
	 * 获取正则的token
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
		$char = $this->getCurrentChar ();
		if (Fl_Js_Static::isIdentifierStart ( $char )) {
			$mods = $this->readWhile ( 'getWordToken' );
			//检测正则的修饰符是否合法
			if (! Fl_Js_Static::checkRegexpModifiers ( $mods )) {
				$this->throwException ( 'Invalid flags supplied to RegExp constructor "' . $mods . '"' );
			}
		}
		return array ($result, $mods );
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
		$this->tmp .= $char;
		if (! Fl_Js_Static::isOperator ( $this->tmp )) {
			$this->tmp = '';
			$this->pendingNextChar = true;
			return true;
		}
	}
	/**
	 * 
	 * 获取数值Token
	 */
	public function getNumberToken($char) {
		$this->tmp .= $char;
		if (! Fl_Js_Static::isNumberPrefix ( $this->tmp ) && ! Fl_Js_Static::isNumber ( $this->tmp )) {
			$this->tmp = '';
			$this->pendingNextChar = true;
			return true;
		}
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
	 * 跳过注释
	 * (non-PHPdoc)
	 * @see Fl_Token::skipComment()
	 */
	public function skipComment() {
		while ( true ) {
			$mulComment = $this->getComment ( 'multi' );
			if ($mulComment) {
				$this->commentBefore [] = $mulComment;
			} else {
				$inlineComment = $this->getComment ( 'inline' );
				if ($inlineComment) {
					$this->commentBefore [] = $inlineComment;
				} else {
					//处理最后一个行内注释，但没有换行符，就不能getComment方法匹配到了。
					if ($this->getCurrentChar () === '/' && $this->getPosChar ( $this->pos + 1 ) === '/') {
						$comment = substr ( $this->text, $this->pos );
						$this->commentBefore [] = $comment;
						$this->pos = $this->length;
					} else {
						break;
					}
				}
			}
		}
	}
	/**
	 * 获取最后一个节点(non-PHPdoc)
	 * @see Fl_Token::getLastToken()
	 */
	public function getLastToken() {
		if ($this->validate) {
			$data = array (array ('(', ')' ), array ('{', '}' ), array ('[', ']' ) );
			foreach ( $data as $item ) {
				if ($this->validateData [$item [0]] != $this->validateData [$item [1]]) {
					$this->throwException ( '"' . $item [0] . '"(' . $this->validateData [$item [0]] . ') & "' . $item [1] . '"(' . $this->validateData [$item [1]] . ') count not equal' );
				}
			}
		}
		return parent::getLastToken ();
	}
}