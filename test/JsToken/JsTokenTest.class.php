<?php
require_once dirname ( dirname ( __FILE__ ) ) . '/FlTest.class.php';
/**
 * 
 * Js词法分析的单元测试
 * @author welefen
 *
 */
class JsTokenTest extends FlTest {
	public function getFlInstance() {
		Fl::loadClass ( 'Fl_Js_Token' );
		$this->flInstance = new Fl_Js_Token ();
		$this->flInstance->tpl = 'smarty';
		$this->flInstance->ld = '<&';
		$this->flInstance->rd = '&>';
	}
	public function getFlInstance1() {
		Fl::loadClass ( 'Fl_Js_Token' );
		$this->flInstance = new Fl_Js_Token ();
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
		$this->assertEqual ( count ( $tokens ), 21 );
		$this->assertEqual ( $tokens [20] ['type'], FL_TOKEN_JS_PUNC );
		$this->assertEqual ( $tokens [15] ['type'], FL_TOKEN_JS_PUNC );
		$this->assertEqual ( $tokens [15] ['value'], ',' );
	}
	public function test2() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '2.text' );
		$this->assertEqual ( count ( $tokens ), 32 );
		$this->assertEqual ( $tokens [31] ['type'], FL_TOKEN_JS_PUNC );
		$this->assertEqual ( $tokens [22] ['type'], FL_TOKEN_JS_NUMBER );
		$this->assertEqual ( $tokens [15] ['value'], 'new' );
	}
	public function test3() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '3.text' );
		$this->assertEqual ( count ( $tokens ), 30 );
		$this->assertEqual ( $tokens [29] ['type'], FL_TOKEN_JS_PUNC );
		$this->assertEqual ( $tokens [22] ['type'], FL_TOKEN_JS_PUNC );
		$this->assertEqual ( $tokens [19] ['value'], 'Array' );
	}
	
	public function test16() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '16.text' );
		$this->assertEqual ( count ( $tokens ), 5 );
		$this->assertEqual ( $tokens [3] ['type'], FL_TOKEN_JS_NUMBER );
		$this->assertEqual ( $tokens [3] ['value'], '0xC1BDCEEE' );
	}
	
	public function test18() {
		$this->getFlInstance ();
		//return;
		$tokens = $this->getTokens ( '18.text' );
		//print_r($tokens);
		return;
		$this->assertEqual ( count ( $tokens ), 18 );
		$this->assertEqual ( $tokens [11] ['value'], 'd' );
		$this->assertEqual ( $tokens [2] ['type'], FL_TOKEN_JS_OPERATOR );
		$this->assertEqual ( $tokens [4] ['value'], 'b' );
	}
	
	public function test20() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '20.text' );
		$this->assertEqual ( count ( $tokens ), 18 );
		$this->assertEqual ( $tokens [11] ['value'], 'd' );
		$this->assertEqual ( $tokens [2] ['type'], FL_TOKEN_JS_OPERATOR );
		$this->assertEqual ( $tokens [4] ['value'], 'b' );
	}
	public function test21() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '21.text' );
		$this->assertEqual ( count ( $tokens ), 14 );
		$this->assertEqual ( $tokens [10] ['type'], FL_TOKEN_JS_NUMBER );
		$this->assertEqual ( $tokens [8] ['value'], 'b' );
		$this->assertEqual ( $tokens [2] ['value'], '=' );
	}
	public function test22() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '22.text' );
		$this->assertEqual ( count ( $tokens ), 5 );
		$this->assertEqual ( $tokens [3] ['type'], FL_TOKEN_JS_REGEXP );
		$this->assertEqual ( $tokens [2] ['value'], '=' );
	}
	public function test23() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '23.text' );
		$this->assertEqual ( count ( $tokens ), 5 );
		$this->assertEqual ( $tokens [3] ['type'], FL_TOKEN_JS_REGEXP );
		$this->assertEqual ( $tokens [3] ['value'] [0], '/^(?:(\w+):)?(?:\/\/(?:(?:([^:@\/]*):?([^:@\/]*))?@)?([^:\/?#])(?::(\d))?)?(..?$|(?:[^?#\/]\/))([^?#]*)(?:\?([^#]))?(?:#(.))?/' );
	}
	public function test24() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '24.text' );
		$this->assertEqual ( count ( $tokens ), 33 );
		$this->assertEqual ( $tokens [30] ['value'], '\'\\\\\\\\\'' );
		$this->assertEqual ( $tokens [26] ['value'], '\'\\\\"\'' );
		$this->assertEqual ( $tokens [24] ['value'], '\'"\'' );
		$this->assertEqual ( $tokens [22] ['value'], '\'\\\\r\'' );
	}
	public function test25() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '25.text' );
		$this->assertEqual ( count ( $tokens ), 4 );
		$this->assertEqual ( $tokens [3] ['value'], '"\\0"' );
	}
	public function test26() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '26.text' );
		$this->assertEqual ( count ( $tokens ), 72 );
		$this->assertEqual ( $tokens [71] ['value'], '<&/if&>' );
		$this->assertEqual ( $tokens [64] ['value'], ',' );
		$this->assertEqual ( $tokens [55] ['value'], '"su"' );
		$this->assertEqual ( $tokens [49] ['value'], '"<&$sc.source|sp_escape_data&>"' );
	}
}