<?php
Fl::loadClass ( 'Fl_Token' );
Fl::loadClass ( 'Fl_Html_Static' );
/**
 * 
 * tag相关的分析
 * 分析tag的属性以及切分属性值
 * @author welefen
 *
 */
class Fl_Html_TagToken extends Fl_Token {
	/**
	 * 
	 * tagname
	 * @var string
	 */
	public $tagName = '';
	/**
	 * 
	 * tag分析初始化
	 * @param string $text
	 */
	private function _init($text = '') {
		$text = $text ? $text : $this->text;
		if ($text {0} != Fl_Html_Static::LEFT || $text {strlen ( $text ) - 1} != Fl_Html_Static::RIGHT) {
			$this->throwException ( 'getAttrs must be for a tag' );
		}
		//去除开始的<和最后的>
		$text = trim ( substr ( $text, 1, strlen ( $text ) - 2 ) );
		//去除最后一个/，但/有可能是模版语法的定界符，这里要加判断
		if ($text {strlen ( $text ) - 1} === '/') {
			if ($this->tpl && $this->rd) {
				$lastChars = substr ( $text, strlen ( $text ) - strlen ( $this->rd ) - 1 );
				if ($lastChars !== $this->rd) {
					$text = trim ( substr ( $text, 0, strlen ( $text ) - 1 ) );
				}
			} else {
				$text = trim ( substr ( $text, 0, strlen ( $text ) - 1 ) );
			}
		}
		$this->setText ( $text );
	}
	/**
	 * 
	 * 获取tag名
	 */
	public function getTagName() {
		if ($this->tagName) {
			return $this->tagName;
		}
		preg_match ( Fl_Html_Static::$tagNamePattern, $this->text, $matches );
		if (! is_array ( $matches ) || ! count ( $matches ) || ! $matches [1]) {
			$this->throwException ( 'get tagName error' );
		}
		$this->tagName = $matches [1];
		$this->pos = strlen ( $this->tagName );
		return $this->tagName;
	}
	/**
	 * 
	 * 获取tag的属性， 并返回tag name
	 */
	public function getAttrs($text = '') {
		$this->_init ( $text );
		$tagName = $this->getTagName ();
		$tokens = $this->getAttrTokens ();
		return array ('tag' => $tagName, 'attrs' => $tokens );
	}
	
	public function run($text = ''){
		return $this->getAttrs($text);
	}
	/**
	 * 
	 * 获取tag属性，根据HTML5规范进行分析
	 * http://www.w3.org/TR/html5/syntax.html
	 */
	public function getAttrTokens() {
		$name = $value = '';
		$return = array ();
		$hasEqual = false; //是否有等号
		$preSpace = false;
		while ( ! $this->isEof () ) {
			$char = $this->getCurrentChar ();
			$tpl = $this->getTplToken ();
			if ($tpl) {
				if ($this->checkTplHasOutput ( $tpl )) {
					if ($hasEqual) {
						$value .= $tpl;
					} else {
						$name .= $tpl;
					}
				} else {
					if ($name || $value) {
						$return [] = $hasEqual ? array ($name, '=', $value ) : array ($name );
						$name = $value = '';
						$hasEqual = false;
					}
					$return [] = array ($tpl );
				}
				$preSpace = false;
				continue;
			}
			
			if ($char === '=') {
				$hasEqual = true;
				$preSpace = false;
			} else if (! $hasEqual && $char === '/') {
				if ($this->getPosChar ( $this->pos - 1 ) !== $char) {
					if ($name) {
						$return [] = array ($name );
						$name = $value = '';
						$hasEqual = false;
					}
				}
			} else if ($char === '"' || $char === "'") {
				if (! $hasEqual) {
					$this->throwException ( "can't find = in tag" );
				}
				$this->getNextChar ();
				$this->pendingNextChar = true;
				$value = $this->getQuoteText ( $char, false );
				$preSpace = false;
				$return [] = $hasEqual ? array ($name, '=', $value ) : array ($name );
				$name = $value = '';
				$hasEqual = false;
			} else if ($this->isWhiteSpace ( $char )) {
				if ($hasEqual) {
					if ($value) {
						$return [] = array ($name, '=', $value );
						$name = $value = '';
						$hasEqual = false;
						$preSpace = false;
					}
				} else {
					$preSpace = true;
				}
			} else {
				if ($preSpace) {
					if ($name) {
						$return [] = array ($name );
						$name = '';
					}
				}
				if ($hasEqual) {
					$value .= $char;
				} else {
					$name .= $char;
				}
				$preSpace = false;
			}
			if (! $this->pendingNextChar) {
				$this->getNextChar ();
			} else {
				$this->pendingNextChar = false;
			}
		}
		if ($name || $value) {
			$return [] = $hasEqual ? array ($name, '=', $value ) : array ($name );
		}
		return $return;
	}
}