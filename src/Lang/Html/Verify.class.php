<?php
/**
 * 
 * HTML编码规范验证类
 * @author welefen
 *
 */
class Fl_Html_Verify extends Fl_Base {
	/**
	 * 
	 * token类名
	 * @var string
	 */
	public $tokenClass = 'Fl_Html_Token';
	/**
	 * 
	 * token类实例
	 * @var object
	 */
	public $tokenInstance = null;
	/**
	 * 
	 * 检测选项
	 * @var array
	 */
	public $options = array ();
	/**
	 * 
	 * 检测结果
	 * @var array
	 */
	public $output = array ();
	/**
	 * 
	 * 设置默认配置项
	 */
	public function setDefaultOptions() {
		$options = array ();
		
		$this->options = $options;
	}
	/**
	 * 
	 * 检测
	 * @param string $text
	 * @param array $options
	 */
	public function run($text = '', $options = array()) {
	
	}
}