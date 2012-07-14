<?php
Fl::loadClass ( "Fl_Base" );
Fl::loadClass ( "Fl_Html_Static" );
/**
 * 
 * Html DOM Class
 * @author welefen
 *
 */
class Fl_Html_Dom extends Fl_Base {

	/**
	 * run
	 * @see Fl_Base::run()
	 */
	public function run() {
		if ($this->checkHasTplToken ()) {
			$this->throwException ( "Dom can't not support tpl syntax in html" );
		}
		$ast = $this->getInstance ( "Fl_Html_Ast" )->run ();
	}
}