<?php
$testPath = dirname ( dirname ( __FILE__ ) ) . '/tools/simpletest';
$flPath = dirname ( dirname ( __FILE__ ) ) . '/src/';
require_once $testPath . '/unit_tester.php';
require_once $testPath . '/reporter.php';
require_once $flPath . '/Fl.class.php';
class FlTest extends UnitTestCase {
	public $flInstance = null;
	public $textPath = '';
	public function __construct($label = false) {
		parent::__construct ( $label );
		//$this->getFlInstance ();
		$this->setTextPath ();
	}
	
	public function getFlInstance() {
	
	}
	public function setTextPath() {
	
	}
	public function getContent($file) {
		$path = rtrim ( $this->textPath, '/' ) . '/' . $file;
		if (!file_exists($path)){
			throw new Exception($path . ' is not exist', $code);
		}
		return file_get_contents ($path);
	}
}