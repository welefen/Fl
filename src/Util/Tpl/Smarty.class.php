<?php
/**
 * 
 * 模版语言为Smarty的分析方法
 * @author welefen
 *
 */
Fl::loadClass ( 'Fl_Tpl_Interface' );
class Fl_Tpl_Smarty implements Fl_Tpl_Interface {
	/**
	 * 
	 * 输出模版语法的正则
	 * @var RegexIterator
	 */
	public $tplOutputPattern = '/^\s*{LD}\s*\\$[\w\\$\_].*?/';
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
		$this->tplOutputPattern = str_replace ( "{LD}", preg_quote ( $instance->ld, "/" ), $this->tplOutputPattern );
		return preg_match ( $this->tplOutputPattern, $tpl );
	}
	/**
	 * 
	 * 压缩当前的模板Token
	 */
	public function compress($tpl, Fl_Base &$instance) {
		$tplText = $instance->getTplText ( $tpl );
		//smarty的extends后必须有个空白字符
		if (strpos ( $tplText, 'extends ' ) === 0) {
			$tpl .= ' ';
		}
		return $tpl;
	}
}