<?php
/**
 * 
 * ejs模版
 * @license MIT
 * @author welefen
 * @copyright 2011 - 2012
 * @version 1.0 - 2012.02.25
 *
 */
Fl::loadClass ( 'Fl_Tpl_Interface' );
Fl::loadClass ( 'Fl_Static' );
class Fl_Tpl_Base implements Fl_Tpl_Interface {

	public function getToken(Fl_Token &$instance) {
		return $instance->getMatched ( $instance->ld, $instance->rd, true );
	}

	public function compress($tpl, Fl_Base &$instance) {
		return $tpl;
	}

	public function checkHasOutput($tpl, Fl_Base &$instance) {
		return true;
	}

	public function xss($token, $type, $instance = null) {
		return $token;
	}
}