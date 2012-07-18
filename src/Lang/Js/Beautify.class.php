<?php
Fl::loadClass ( 'Fl_Base' );
/**
 * 
 * js beautify, support tpl
 * @author welefen
 *
 */
class Fl_Js_Beautify extends Fl_Base {

	/**
	 * run
	 * @see Fl_Base::run()
	 */
	public function run() {
		return trim ( $this->text );
	}
}