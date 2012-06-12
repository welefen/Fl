<?php
/**
 * 
 * Css Selector Token
 * 抛弃selector中的注释等对效果无用的代码
 * 参见：http://www.w3.org/TR/selectors/
 * @author welefen
 *
 */
Fl::loadClass ( 'Fl_Token' );
Fl::loadClass ( 'Fl_Css_Static' );
class Fl_Css_SelectorToken extends Fl_Token {

	/**
	 * 
	 * check value is valid ?
	 * @var boolean
	 */
	public $validate = true;

	/**
	 * 
	 * prefix space for selector token
	 * @var boolean
	 */
	protected $spaceBefore = false;

	/**
	 * 
	 * check namespace
	 * @var boolean
	 */
	protected $namespaceChecked = false;

	/**
	 * no skip white space
	 * @see Fl_Token::skipWhiteSpace()
	 */
	public function skipWhiteSpace() {
		$this->spaceBefore = parent::skipWhiteSpace ();
	}

	/**
	 * skip comment
	 * @see Fl_Token::skipComment()
	 */
	public function skipComment() {
		while ( $comment = $this->getComment ( 'multi' ) ) {
			$this->commentBefore [] = $comment;
		}
	}

	public function run() {
		$output = array ();
		$result = array ();
		while ( $token = $this->getNextToken () ) {
			if ($token ['type'] === FL_TOKEN_CSS_SELECTOR_COMMA) {
				if (count ( $result )) {
					$output [] = $result;
					$result = array ();
				}
			} else {
				$result [] = $token;
			}
		}
		if (count ( $result )) {
			$output [] = $result;
		}
		return $output;
	}

	/**
	 * get token info
	 * @see Fl_Token::getTokenInfo()
	 */
	public function getTokenInfo($type = '', $value = '') {
		$result = parent::getTokenInfo ( $type, $value );
		$result ['spaceBefore'] = $this->spaceBefore;
		$this->spaceBefore = false;
		return $result;
	}

	/**
	 * get next token
	 * @see Fl_Token::getNextToken()
	 */
	public function getNextToken() {
		$token = parent::getNextToken ();
		if ($token || $token === false) {
			return $token;
		}
		$char = $this->text {$this->pos};
		if (! $this->namespaceChecked) {
			$this->namespaceChecked = true;
			if (Fl_Css_Static::checkNamespace ( $this->text )) {
				$result = $this->readWhile ( 'getNamespaceToken' );
				return $this->getTokenInfo ( FL_TOKEN_CSS_SELECTOR_NAMESPACE, $result );
			}
		}
		$type = '';
		$value = '';
		switch ($char) {
			case '*' :
				$type = FL_TOKEN_CSS_SELECTOR_UNIVERSAL;
				$this->getNextChar ();
				$value = $char;
				break;
			case ',' :
				$type = FL_TOKEN_CSS_SELECTOR_COMMA;
				$this->getNextChar ();
				$value = $char;
				break;
			case '>' :
			case '+' :
			case '~' :
				$this->getNextChar ();
				$value = $char;
				$type = FL_TOKEN_CSS_SELECTOR_COMBINATOR;
				break;
			case '#' :
				$type = FL_TOKEN_CSS_SELECTOR_ID;
				$value = $this->readWhile ( 'getIdToken' );
				break;
			case '.' :
				$type = FL_TOKEN_CSS_SELECTOR_CLASS;
				$value = $this->readWhile ( 'getClassToken' );
				break;
			case '[' :
				$type = FL_TOKEN_CSS_SELECTOR_ATTRIBUTES;
				$value = $this->getAttributeToken ( $char );
				break;
			case ':' :
				if ($this->text {$this->pos + 1} === ':') {
					$this->getNextChar ();
					$value = ':' . $this->readWhile ( 'getPseudoElementToken' );
					$type = FL_TOKEN_CSS_SELECTOR_PSEUDO_ELEMENT;
				} else {
					$value = $this->getPseudoClassToken ( $char );
					$type = FL_TOKEN_CSS_SELECTOR_PSEUDO_CLASS;
				}
				break;
			default :
				$value = $this->readWhile ( 'getTypeToken' );
				$type = FL_TOKEN_CSS_SELECTOR_TYPE;
		}
		if ($this->validate && ! Fl_Css_Static::checkSelectorToken ( $type, $value )) {
			$this->throwException ( $value . ' is not valid' );
		}
		return $this->getTokenInfo ( $type, $value );
	}

	/**
	 * 
	 * char util
	 * @param string $char
	 */
	public function charUtil($char = '') {
		if ($this->isWhiteSpace ( $char )) {
			return true;
		}
		if (Fl_Css_Static::isSelectorCharUtil ( $char )) {
			return true;
		}
		if ($this->hasTplToken && $this->ld == substr ( $this->text, $this->pos + 1, strlen ( $this->ld ) )) {
			return true;
		}
		return false;
	}

	/**
	 * @namespace foo "http://www.example.com";
	 * foo|*
	 * get namespace token
	 */
	public function getNamespaceToken($char = '') {
		if ($char === '|') {
			return false;
		}
	}

	/**
	 * 
	 * get #id token
	 * @param string $char
	 */
	public function getIdToken($char = '') {
		$nextChar = $this->text {$this->pos + 1};
		if ($this->charUtil ( $nextChar )) {
			return false;
		}
	}

	/**
	 * 
	 * get such as div token
	 * @param string $char
	 */
	public function getTypeToken($char = '') {
		$nextChar = $this->text {$this->pos + 1};
		if ($this->charUtil ( $nextChar )) {
			return false;
		}
	}

	/**
	 * 
	 * get .class token
	 * @param string $char
	 */
	public function getClassToken($char = '') {
		$nextChar = $this->text {$this->pos + 1};
		if ($this->charUtil ( $nextChar )) {
			return false;
		}
	}

	/**
	 * 
	 * get attrubite selectors token
	 * @param string $char
	 */
	public function getAttributeToken($char = '') {
		$result = '';
		while ( $this->pos < $this->length ) {
			$char = $this->text {$this->pos};
			if ($char === '"' || $char === "'") {
				$quote = $this->getQuoteText ( $char, true, true );
				$result .= $quote;
			} else {
				$result .= $char;
			}
			if ($char === ']') {
				$this->getNextChar ();
				break;
			}
			if (! $this->pendingNextChar) {
				$this->getNextChar ();
			} else {
				$this->pendingNextChar = false;
			}
		}
		return $result;
	}

	/**
	 * 
	 * get ::xxx token
	 */
	public function getPseudoElementToken($char = '') {
		$nextChar = $this->text {$this->pos + 1};
		if ($this->charUtil ( $nextChar )) {
			return false;
		}
	}

	/**
	 * 
	 * get such as :hover token
	 * @param string $char
	 */
	public function getPseudoClassToken($char) {
		$result = $char;
		$this->getNextChar ();
		$parenthesesNum = 0;
		while ( $this->pos < $this->length ) {
			$char = $this->text {$this->pos};
			if ($parenthesesNum === 0 && Fl_Css_Static::isSelectorCharUtil ( $char )) {
				break;
			} else if ($char === '(') {
				$parenthesesNum ++;
			} else if ($char === ')') {
				$parenthesesNum --;
			} else if ($char === '"' || $char === "'") {
				$char = $this->getQuoteText ( $char, true, true );
			}
			$result .= $char;
			if (! $this->pendingNextChar) {
				$this->getNextChar ();
			} else {
				$this->pendingNextChar = false;
			}
		}
		if ($parenthesesNum !== 0) {
			$this->throwException ( 'get Pseudo Class error in ' . __LINE__ );
		}
		return $result;
	}
}