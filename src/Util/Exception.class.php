<?php
/**
 * 
 * 异常类
 * @author welefen
 * @copyright 2011 - 2012
 *
 */
class Fl_Exception extends Exception {

	public $message = '';

	public static function stringify($array = array()) {
		return json_encode ( $array );
	}

	public static function parse($json = '') {
		return json_decode ( $json, true );
	}
}