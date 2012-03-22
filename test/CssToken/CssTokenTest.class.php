<?php
require_once dirname ( dirname ( __FILE__ ) ) . '/FlTest.class.php';
/**
 * 
 * Css词法分析的单元测试
 * @author welefen
 *
 */
class CssTokenTest extends FlTest {
	public function getFlInstance() {
		Fl::loadClass ( 'Fl_Css_Token' );
		$this->flInstance = new Fl_Css_Token ();
		$this->flInstance->tpl = 'smarty';
		$this->flInstance->ld = '<&';
		$this->flInstance->rd = '&>';
	}
	public function getFlInstance1() {
		Fl::loadClass ( 'Fl_Css_Token' );
		$this->flInstance = new Fl_Css_Token ();
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
		$this->assertEqual ( count ( $tokens ), 7 );
		$this->assertEqual ( $tokens [0] ['type'], FL_TOKEN_CSS_AT_PAGE );
		$this->assertEqual ( $tokens [0] ['value'], '@page:first' );
		$this->assertEqual ( $tokens [5] ['value'], ';' );
	}
	public function test2() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '2.text' );
		$this->assertEqual ( count ( $tokens ), 1 );
		$this->assertEqual ( $tokens [0] ['type'], FL_TOKEN_CSS_AT_IMPORT );
	}
	public function test3() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '3.text' );
		$this->assertEqual ( count ( $tokens ), 1 );
		$this->assertEqual ( $tokens [0] ['type'], FL_TOKEN_CSS_AT_CHARSET );
		$this->assertEqual ( $tokens [0] ['value'], '@charset/**welefen**/ "utf-8";' );
	}
	public function test4() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '4.text' );
		$this->assertEqual ( count ( $tokens ), 11 );
		$this->assertEqual ( $tokens [0] ['type'], FL_TOKEN_CSS_AT_OTHER );
	}
	public function test5() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '5.text' );
		$this->assertEqual ( count ( $tokens ), 9 );
		$this->assertEqual ( $tokens [0] ['type'], FL_TOKEN_CSS_SELECTOR );
		$this->assertEqual ( $tokens [2] ['value'], '[;color:red;]' );
	}
	public function test6() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '6.text' );
		$this->assertEqual ( count ( $tokens ), 10 );
		$this->assertEqual ( $tokens [0] ['value'], '@media screen' );
		$this->assertEqual ( $tokens [8] ['type'], FL_TOKEN_CSS_BRACES_ONE_END);
	}
	public function test7() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '7.text' );
		$this->assertEqual ( count ( $tokens ), 7 );
		$this->assertEqual ( $tokens [0] ['value'], '* html div a > .cls[href^="http://www.welefen.com"]' );
	}
	public function test8() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '8.text' );
		$this->assertEqual ( count ( $tokens ), 45 );
		$this->assertEqual ( $tokens [0] ['value'], '@keyframes testanimations' );
	}
	public function test9() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '9.text' );
		//print_r($tokens);
		//$this->assertEqual ( count ( $tokens ), 7 );
		//$this->assertEqual ( $tokens [0] ['value'], '@keyframes testanimations' );
	}
	public function test10() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '10.text' );
		$this->assertEqual ( count ( $tokens ), 7 );
		$this->assertEqual ( $tokens [4] ['value'], 'expression((documentElement.clientWidth < 725) ? "725px" : "auto" )' );
	}
	public function test11() {
		$this->getFlInstance ();
		$tokens = $this->getTokens ( '11.text' );
		$this->assertEqual ( count ( $tokens ), 7 );
		$this->assertEqual ( $tokens [4] ['value'], 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src="http://dbl-space-test02.vm.baidu.com:8009/st/static/superpage/img/round_cover_grey.png?v=md5", enabled=true,sizingMethod="noscale")' );
	}
	public function test12() {
		$this->getFlInstance ();
		return true;
		$tokens = $this->getTokens ( '12.text' );
		print_r($tokens);
		$this->assertEqual ( count ( $tokens ), 7 );
		$this->assertEqual ( $tokens [4] ['value'], 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src="http://dbl-space-test02.vm.baidu.com:8009/st/static/superpage/img/round_cover_grey.png?v=md5", enabled=true,sizingMethod="noscale")' );
	}
}