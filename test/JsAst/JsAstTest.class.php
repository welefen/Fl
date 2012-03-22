<?php
require_once dirname ( dirname ( __FILE__ ) ) . '/FlTest.class.php';
/**
 * 
 * Js词法分析的单元测试
 * @author welefen
 *
 */
class JsAstTest extends FlTest {
	public function getFlInstance() {
		Fl::loadClass ( 'Fl_Js_Ast' );
		$this->flInstance = new Fl_Js_Ast ();
		$this->flInstance->tpl = 'smarty';
		$this->flInstance->ld = '<&';
		$this->flInstance->rd = '&>';
	}
	public function getFlInstance1() {
		Fl::loadClass ( 'Fl_Js_Ast' );
		$this->flInstance = new Fl_Js_Ast ();
		$this->flInstance->tpl = 'smarty';
		$this->flInstance->ld = '{=';
		$this->flInstance->rd = '=}';
	}
	public function setTextPath() {
		$this->textPath = dirname ( __FILE__ );
	}
	public function getTokens($file) {
		$content = $this->getContent ( $file );
		return $this->flInstance->run ( $content );
	}
	public function test1() {
		$this->getFlInstance ();
		$output = $this->getTokens ( '1.text' );
		print_r($output);
		$this->assertEqual ( count ( $tokens ), 21 );
		$this->assertEqual ( $tokens [20] ['type'], FL_TOKEN_JS_PUNC );
		$this->assertEqual ( $tokens [15] ['type'], FL_TOKEN_JS_PUNC );
		$this->assertEqual ( $tokens [15] ['value'], ',' );
	}
}