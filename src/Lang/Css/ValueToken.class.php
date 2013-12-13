<?php
/**
 * 
 * css值的token分析
 * @author welefen
 *
 */
Fl::loadClass ( "Fl_Token" );
class Fl_Css_ValueToken extends Fl_Token {
	
	/**
	 * 
	 * 当前分析css属性值对应的属性名
	 * @var string
	 */
	public $property = "";
	
	/**
	 * 执行
	 * @see Fl_Token::run()
	 */
	public function run() {
		$tokens = array ();
		while ( true ) {
			$token = $this->getNextToken ();
			if (empty ( $token )) {
				break;
			}
			$tokens [] = $token;
		}
		return $tokens;
	}
	
	/**
	 * 获取下一个token
	 * @see Fl_Token::getNextToken()
	 */
	public function getNextToken() {
		$token = parent::getNextToken ();
		if ($token || $token === false) {
			return $token;
		}
		$result = "";
		while ( $this->pos < $this->length ) {
			if ($this->text {$this->pos} === '(') {
				$match = $this->getMatched ( "(", ")" );
				$result .= $match;
				continue;
			}
			$char = $this->getNextChar ();
			if ($this->isWhiteSpace ( $char ) && ! preg_match ( "/\s*\(/", substr ( $this->text, $this->pos ) )) {
				break;
			} else {
				$result .= $char;
			}
		}
		if (strlen ( $result )) {
			return $this->getTokenInfo ( '', $result );
		}
		return false;
	}
	/**
	 * 
	 * 获取token类型
	 */
	public function getTokenType() {
	
	}
}