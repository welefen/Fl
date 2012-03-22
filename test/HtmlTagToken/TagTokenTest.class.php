<?php
require_once dirname ( dirname ( __FILE__ ) ) . '/FlTest.class.php';
/**
 * 
 * tag分析的单元测试
 * @author welefen
 *
 */
class TagTokenTest extends FlTest{
	public function getFlInstance() {
		Fl::loadClass ( 'Fl_Html_TagToken' );
		$this->flInstance = new Fl_Html_TagToken ();
		$this->flInstance->tpl = 'smarty';
		$this->flInstance->ld = '<&';
		$this->flInstance->rd = '&>';
	}
	public function setTextPath() {
		$this->textPath = dirname ( __FILE__ );
	}
	public function getTokens($file) {
		$content = $this->getContent ( $file );
		return $this->flInstance->getAttrs ( $content );
	}
	public function test1(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '1.text' );
		$attrs = $tokens['attrs'];
		$this->assertEqual ( count ( $attrs ), 0 );
		$this->assertEqual($tokens['tag'], 'div');
	}
	public function test2(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '2.text' );
		$attrs = $tokens['attrs'];
		$this->assertEqual ( count ( $attrs ), 1 );
		$this->assertEqual ( count ( $attrs[0] ), 1 );
		$this->assertEqual($tokens['tag'], 'input');
	}
	public function test3(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '3.text' );
		$attrs = $tokens['attrs'];
		$this->assertEqual ( count ( $attrs ), 1 );
		$this->assertEqual ( count ( $attrs[0] ), 1 );
		$this->assertEqual($tokens['tag'], 'input');
	}
	public function test4(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '4.text' );
		$attrs = $tokens['attrs'];
		$this->assertEqual ( count ( $attrs ), 1 );
		$this->assertEqual ( count ( $attrs[0] ), 3 );
		$this->assertEqual (  ( $attrs[0][1] ), '=' );
		$this->assertEqual($tokens['tag'], 'div');
	}
	public function test5(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '5.text' );
		$attrs = $tokens['attrs'];
		$this->assertEqual ( count ( $attrs ), 2 );
		$this->assertEqual ( count ( $attrs[0] ), 3 );
		$this->assertEqual (  ( $attrs[0][1] ), '=' );
		$this->assertEqual (  ( $attrs[0][2] ), 'welefen' );
		$this->assertEqual($tokens['tag'], 'div');
	}
	public function test6(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '6.text' );
		$attrs = $tokens['attrs'];
		$this->assertEqual ( count ( $attrs ), 2 );
		$this->assertEqual ( count ( $attrs[0] ), 3 );
		$this->assertEqual (  ( $attrs[0][1] ), '=' );
		$this->assertEqual (  ( $attrs[1][2] ), "'suredy'" );
		$this->assertEqual($tokens['tag'], 'div');
	}
	public function test7(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '7.text' );
		$attrs = $tokens['attrs'];
		$this->assertEqual ( count ( $attrs ), 2 );
		$this->assertEqual ( count ( $attrs[0] ), 3 );
		$this->assertEqual (  ( $attrs[0][1] ), '=' );
		$this->assertEqual (  ( $attrs[0][2] ), '"welefen"' );
		$this->assertEqual($tokens['tag'], 'div');
	}
	public function test8(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '8.text' );
		$attrs = $tokens['attrs'];
		$this->assertEqual ( count ( $attrs ), 2 );
		$this->assertEqual ( count ( $attrs[0] ), 3 );
		$this->assertEqual (  ( $attrs[0][1] ), '=' );
		$this->assertEqual (  ( $attrs[0][2] ), 'http://www.baidu.com' );
		$this->assertEqual($tokens['tag'], 'a');
	}
	public function test9(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '9.text' );
		$attrs = $tokens['attrs'];
		$this->assertEqual ( count ( $attrs ), 2 );
		$this->assertEqual ( count ( $attrs[0] ), 3 );
		$this->assertEqual (  ( $attrs[0][1] ), '=' );
		$this->assertEqual (  ( $attrs[0][2] ), '"<&$has&>name"' );
		$this->assertEqual($tokens['tag'], 'a');
	}
	public function test10(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '10.text' );
		$attrs = $tokens['attrs'];
		$this->assertEqual ( count ( $attrs ), 1 );
		$this->assertEqual ( count ( $attrs[0] ), 3 );
		$this->assertEqual (  ( $attrs[0][1] ), '=' );
		$this->assertEqual (  ( $attrs[0][2] ), '<&$spDomain.space&>/name' );
		$this->assertEqual($tokens['tag'], 'a');
	}
	public function test11(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '11.text' );
		$attrs = $tokens['attrs'];
		$this->assertEqual ( count ( $attrs ), 9 );
		$this->assertEqual ( count ( $attrs[0] ), 3 );
		$this->assertEqual (  ( $attrs[0][1] ), '=' );
		$this->assertEqual (  ( $attrs[7][0] ), '<&$bsLogo.bdLogoRec|sp_no_escape&>' );
		$this->assertEqual($tokens['tag'], 'area');
	}
	public function test12(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '12.text' );
		$attrs = $tokens['attrs'];
		$this->assertEqual ( count ( $attrs ), 2 );
		$this->assertEqual ( count ( $attrs[0] ), 1 );
		$this->assertEqual (  ( $attrs[0][0] ), 'readable' );
		$this->assertEqual (  ( $attrs[1][0] ), 'disabled' );
		$this->assertEqual($tokens['tag'], 'input');
	}
	public function test13(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '13.text' );
		$attrs = $tokens['attrs'];
		$this->assertEqual ( count ( $attrs ), 6 );
		$this->assertEqual ( count ( $attrs[0] ), 3 );
		$this->assertEqual (  ( $attrs[0][2] ), '"<&$bsLogo.bdLogoAlt|sp_no_escape&>"' );
		$this->assertEqual (  ( $attrs[1][0] ), '<&if $bsLogo.bdLogoRec&>' );
		$this->assertEqual (  ( $attrs[2][2] ), '"welefen suredy"' );
		$this->assertEqual($tokens['tag'], 'area');
	}
	public function test14(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '14.text' );
		$attrs = $tokens['attrs'];
		$this->assertEqual ( count ( $attrs ), 4 );
		$this->assertEqual ( count ( $attrs[0] ), 3 );
		$this->assertEqual (  ( $attrs[1][2] ), '"suredy"' );
		$this->assertEqual($tokens['tag'], 'div');
	}
	public function test15(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '15.text' );
		$attrs = $tokens['attrs'];
		$this->assertEqual ( count ( $attrs ), 4 );
		$this->assertEqual ( count ( $attrs[0] ), 1 );
		$this->assertEqual (  ( $attrs[3][2] ), '"<&if $test==""&>11<&else&>22<&/if&>"' );
		$this->assertEqual($tokens['tag'], 'div');
	}
	public function test16(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '16.text' );
		$attrs = $tokens['attrs'];
		$this->assertEqual ( count ( $attrs ), 2 );
		$this->assertEqual ( count ( $attrs[0] ), 3 );
		$this->assertEqual (  ( $attrs[0][2] ), '"welefen""' );
		$this->assertEqual (  ( $attrs[1][0] ), '<&$name&>' );
		$this->assertEqual (  ( $attrs[1][2] ), '<&$value&>' );
		$this->assertEqual($tokens['tag'], 'div');
	}
	public function test17(){
		$this->getFlInstance();
		$tokens = $this->getTokens ( '17.text' );
		$attrs = $tokens['attrs'];
		$this->assertEqual ( count ( $attrs ), 2 );
		$this->assertEqual (  ( $attrs[0][0] ), 'welefen' );
		$this->assertEqual (  ( $attrs[1][2] ), 'javascript:alert(/xss/)' );
		$this->assertEqual($tokens['tag'], 'img');
	}
}