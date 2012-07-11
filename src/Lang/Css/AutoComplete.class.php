<?php
/**
 * 
 * css beautify class
 * @author welefen
 *
 */
Fl::loadClass ( 'Fl_Base' );
class Fl_Css_AutoComplete extends Fl_Base {

	/**
	 * 
	 * beautify options
	 * @var array
	 */
	public $options = array (
		"w3c" => true, 
		"webkit" => true, 
		"moz" => true, 
		"ms" => true, 
		"o" => true 
	);

	public function run($options = array()) {
		$this->options = array_merge ( $this->options, $options );
	}
}