<?php
require_once dirname ( dirname ( __FILE__ ) ) . '/FlTest.class.php';
/**
 * 
 * HTML词法分析的单元测试
 * @author welefen
 *
 */
class HtmlCompressTest extends FlTest {
	public function getFlInstance() {
		Fl::loadClass ( 'Fl_Html_Compress' );
		$this->flInstance = new Fl_Html_Compress ();
		$this->flInstance->tpl = 'smarty';
		$this->flInstance->ld = '<&';
		$this->flInstance->rd = '&>';
	}
	public function getFlInstance1() {
		Fl::loadClass ( 'Fl_Html_Compress' );
		$this->flInstance = new Fl_Html_Compress ();
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
		$this->getFlInstance();
		$startTime = microtime(true);
		$tokens = $this->getTokens ( '1.text' );
		$size = strlen($this->getContent('1.text'));
		/*echo '<textarea style="display:block;width:100%;height:400px;">';
		echo $tokens;
		echo '</textarea>';
		$outSize = strlen($tokens);
		echo $outSize . '/' . $size . "\n";
		echo number_format(($size-$outSize)*100/$size, 2) . '%';
		echo (microtime(true) - $startTime) . 's';*/
		//$this->assertEqual ( count ( $tokens ), 1 );
		//$this->assertEqual($tokens[0]['type'], FL_TOKEN_HTML_TAG_START);
	}
}