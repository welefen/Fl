<?php
require_once dirname ( dirname ( __FILE__ ) ) . '/FlTest.class.php';
/**
 * 
 * HTML词法分析的单元测试
 * @author welefen
 *
 */
class HtmlTokenTest extends FlTest {
	public function getFlInstance() {
		Fl::loadClass ( 'Fl_Html_Token' );
		$this->flInstance = new Fl_Html_Token ();
		$this->flInstance->tpl = 'smarty';
		$this->flInstance->ld = '<&';
		$this->flInstance->rd = '&>';
	}
	public function getFlInstance1() {
		Fl::loadClass ( 'Fl_Html_Token' );
		$this->flInstance = new Fl_Html_Token ();
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
		$this->getFlInstance();
		$tokens = $this->getTokens ( '1.text' );
		$this->assertEqual ( count ( $tokens ), 1 );
		$this->assertEqual($tokens[0]['type'], FL_TOKEN_HTML_TAG_START);
	}
	public function test2(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '2.text' );
		$this->assertEqual ( count ( $tokens ), 1 );
		$this->assertEqual($tokens[0]['type'], FL_TOKEN_HTML_TEXT);
		$this->assertEqual($tokens[0]['value'], 'welefen');
	}
	public function test3(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '3.text' );
		$this->assertEqual ( count ( $tokens ), 1 );
		$this->assertEqual($tokens[0]['type'], FL_TOKEN_LAST);
		$this->assertEqual($tokens[0]['value'], '');
		$this->assertEqual(count($tokens[0]['commentBefore']), 1);
	}
	public function test4(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '4.text' );
		$this->assertEqual ( count ( $tokens ), 1 );
		$this->assertEqual($tokens[0]['type'], FL_TOKEN_TPL);
	}
	public function test5(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '5.text' );
		$this->assertEqual ( count ( $tokens ), 9 );
		$this->assertEqual($tokens[0]['type'], FL_TOKEN_HTML_STYLE_TAG);
		$this->assertEqual($tokens[4]['type'], FL_TOKEN_HTML_PRE_TAG);
		$this->assertEqual($tokens[6]['type'], FL_TOKEN_HTML_SCRIPT_TAG);
	}
	public function test6(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '6.text' );
		$this->assertEqual ( count ( $tokens ), 9 );
		$this->assertEqual($tokens[0]['type'], FL_TOKEN_HTML_TAG_START);
		$this->assertEqual($tokens[4]['type'], FL_TOKEN_HTML_TAG_END);
		$this->assertEqual($tokens[6]['type'], FL_TOKEN_HTML_TEXT);
		$this->assertEqual($tokens[6]['value'], 'welefen');
	}
	public function test7(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '7.text' );
		$this->assertEqual ( count ( $tokens ), 8 );
		$this->assertEqual($tokens[0]['type'], FL_TOKEN_TPL);
		$this->assertEqual($tokens[4]['type'], FL_TOKEN_HTML_TAG_START);
		$this->assertEqual($tokens[6]['type'], FL_TOKEN_TPL);
		$this->assertEqual($tokens[6]['value'], '<&value&>');
	}
	public function test8(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '8.text' );
		$this->assertEqual ( count ( $tokens ), 6 );
		$this->assertEqual($tokens[0]['type'], FL_TOKEN_HTML_TAG_START);
		$this->assertEqual($tokens[0]['value'], '<div class=""">');
		$this->assertEqual($tokens[1]['value'], '>>>welefen<<<');
	}
	public function test9(){
		$this->getFlInstance1();
		$tokens = $this->getTokens ( '9.text' );
		$this->assertEqual ( count ( $tokens ), 8 );
		$this->assertEqual($tokens[0]['type'], FL_TOKEN_TPL);
		$this->assertEqual($tokens[4]['type'], FL_TOKEN_HTML_TAG_START);
		$this->assertEqual($tokens[6]['type'], FL_TOKEN_TPL);
		$this->assertEqual($tokens[6]['value'], '{=value=}');
	}
	public function test10(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '10.text' );
		$this->assertEqual ( count ( $tokens ), 3 );
		$this->assertEqual($tokens[1]['type'], FL_TOKEN_HTML_TEXT);
	}
	public function test11(){
		$this->getFlInstance1();
		$tokens = $this->getTokens ( '11.text' );
		$this->assertEqual ( count ( $tokens ), 68 );
		$this->assertEqual($this->flInstance->hasTplToken, false);
		$this->assertEqual($tokens[0]['type'], FL_TOKEN_XML_HEAD);
	}
	public function test12(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '12.text' );
		$this->assertEqual ( count ( $tokens ), 194 );
		$this->assertEqual($this->flInstance->hasTplToken, true);
		$this->assertEqual($tokens[24]['type'], FL_TOKEN_HTML_TAG_START);
	}
	public function test13(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '13.text' );
		$this->assertEqual ( count ( $tokens ), 5 );
		$this->assertEqual($this->flInstance->hasTplToken, true);
		$this->assertEqual($tokens[0]['type'], FL_TOKEN_TPL);
		$this->assertEqual($tokens[0]['value'], '<&$name=<&$value&>+1&>');
	}
	public function test14(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '14.text' );
		$this->assertEqual ( count ( $tokens ), 3 );
		$this->assertEqual($this->flInstance->hasTplToken, false);
		$this->assertEqual($tokens[2]['type'], FL_TOKEN_LAST);
	}
	public function test15(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '15.text' );
		//print_r($tokens);
	}
	public function test16(){
		$this->getFlInstance1();
		$tokens = $this->getTokens ( '16.text' );
		$this->assertEqual ( count ( $tokens ), 13 );
		$this->assertEqual($tokens[12]['value'], '{=$pager.cl = ($urlPara.cl != 3)?"&cl=`$urlPara.cl`":"" =}');
	}
}