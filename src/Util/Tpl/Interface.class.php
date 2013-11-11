<?php
/**
 * 
 * 模版类接口
 * @author welefen
 *
 */
interface Fl_Tpl_Interface {

	/**
	 * 
	 * 获取模版的token
	 * @param Fl_Token $instance
	 */
	public function getToken(Fl_Token &$instance);

	/**
	 * 
	 * 检测模版变量是否有输出
	 * @param string $tpl
	 * @param Fl_Base $instance
	 */
	public function checkHasOutput($tpl, Fl_Base &$instance);

	/**
	 * 
	 * 压缩模版变量
	 * @param string $tpl
	 * @param Fl_Base $instance
	 */
	public function compress($tpl, Fl_Base &$instance);

	/**
	 * 
	 * 压缩模版变量
	 * @param array $token
	 * @param string $type
	 * @param Fl_Base $instance
	 */
	public function xss($token, $type, $instance = null);
}