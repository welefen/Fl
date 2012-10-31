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
//Fl::loadClass ( 'Fl_Tpl_Interface' );
class Fl_Tpl_PHP {

	/**
	 * 
	 * 获取模版语法的Token
	 * @param object $instance
	 */
	public function getToken(Fl_Token &$instance) {
		return $instance->getMatched ( $instance->ld, $instance->rd, true );
	}

	/**
	 * 
	 * 检测当前的tpl是否会输出
	 * @param string $tpl
	 */
	public function checkHasOutput($tpl, Fl_Base &$instance) {
		return true;
	}

	/**
	 * 
	 * 压缩当前的模板Token
	 */
	public function compress($tpl, Fl_Base &$instance) {
		return $tpl;
	}
}