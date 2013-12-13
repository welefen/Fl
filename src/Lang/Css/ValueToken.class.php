<?php
/**
 * 
 * css值的token分析
 * @author welefen
 *
 */
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
			$char = $this->text {$this->pos};
			if ($this->text {$this->pos + 1} === '(') {
				$match = $this->getMatched ( "(", ")" );
				$result .= $char . $match;
				continue;
			}
		
		}
	}
	/**
	 * 
	 * 获取token类型
	 */
	public function getTokenType() {
	
	}
}