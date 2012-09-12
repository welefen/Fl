<?php
/**
 * 
 * PHP原生的模版
 * @license MIT
 * @author welefen
 * @copyright 2011 - 2012
 * @version 1.0 - 2012.02.25
 *
 */
Fl::loadClass ( 'Fl_Tpl_Interface' );

class Fl_Tpl_PHP implements Fl_Tpl_Interface {

	public static function compress($tpl, Fl_Base &$instance){
		return $tpl;
	}

	public static function xss(){

	}

	public static function getToken(Fl_Token &$instance){
		return $instance->getMatched ( $instance->ld, $instance->rd, true );
	}

	public static function checkHasOutput($tpl, Fl_Base &$instance){
		
	}
	
}