<?php
require_once dirname ( dirname ( __FILE__ ) ) . '/FlTest.class.php';
/**
 * 
 * Css词法分析的单元测试
 * @author welefen
 *
 */
class CssSelectorTokenTest extends FlTest {
	public function getFlInstance() {
		Fl::loadClass ( 'Fl_Css_SelectorToken' );
		$this->flInstance = new Fl_Css_SelectorToken ();
		$this->flInstance->tpl = 'smarty';
		$this->flInstance->ld = '<&';
		$this->flInstance->rd = '&>';
	}
	public function getFlInstance1() {
		Fl::loadClass ( 'Fl_Css_SelectorToken' );
		$this->flInstance = new Fl_Css_SelectorToken ();
		$this->flInstance->tpl = 'smarty';
		$this->flInstance->ld = '{=';
		$this->flInstance->rd = '=}';
	}
	public function setTextPath() {
		$this->textPath = dirname ( __FILE__ );
	}
	public function getTokens($file) {
		$content = $this->getContent ( $file );
		return $this->flInstance->getAllTokens ( $content );
	}
	public function test1() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '1.text' );
		$this->assertEqual ( count ( $tokens ), 6 );
		$this->assertEqual ( $tokens [0][0] ['type'], FL_TOKEN_CSS_SELECTOR_ID );
		$this->assertEqual ( $tokens [0][0] ['value'], '#id' );
		$this->assertEqual ( $tokens [1][0] ['type'], FL_TOKEN_CSS_SELECTOR_CLASS );
		$this->assertEqual ( $tokens [1][0] ['value'], '.red' );
		$this->assertEqual ( $tokens [2][0] ['type'], FL_TOKEN_CSS_SELECTOR_TYPE );
		$this->assertEqual ( $tokens [2][0] ['value'], 'div' );
		$this->assertEqual ( $tokens [5][0] ['type'], FL_TOKEN_CSS_SELECTOR_UNIVERSAL );
		$this->assertEqual ( $tokens [5][0] ['value'], '*' );
	}
	public function test2() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '2.text' );
		$tokens = $tokens[0];
		$this->assertEqual ( count ( $tokens ), 4 );
		$this->assertEqual ( $tokens [0] ['type'], FL_TOKEN_CSS_SELECTOR_TYPE );
		$this->assertEqual ( $tokens [0] ['value'], 'input' );
		$this->assertEqual ( $tokens [1] ['type'], FL_TOKEN_CSS_SELECTOR_ATTRIBUTES );
		$this->assertEqual ( $tokens [1] ['value'], '[name="welefen\'\\"suredy"]' );
		$this->assertEqual ( $tokens [2] ['type'], FL_TOKEN_CSS_SELECTOR_CLASS );
		$this->assertEqual ( $tokens [2] ['value'], '.class' );
		$this->assertEqual ( $tokens [3] ['type'], FL_TOKEN_CSS_SELECTOR_PSEUDO_CLASS );
		$this->assertEqual ( $tokens [3] ['value'], ':not(#welefen)' );
	}
	public function test3() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '3.text' );
		$tokens = $tokens[0];
		$score = Fl_Css_Static::getSelectorSpecificity($tokens);
		//print_r($score);
	}
}