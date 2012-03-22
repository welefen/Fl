<?php
/**
 * 
 * Fl基础类,其他类由该类继承
 * @author welefen
 * @version 1.0
 *
 */
class Fl_Base {
	/**
	 * 
	 * 要操作的文本
	 * @var string
	 */
	public $text = '';
	/**
	 * 
	 * 文本的长度
	 * @var int
	 */
	public $length = 0;
	/**
	 * 模版语法
	 */
	public $tpl = 'Smarty';
	/**
	 * 
	 * 左定界符
	 * @var string
	 */
	public $ld = '';
	/**
	 * 
	 * 右定界符
	 * @var string
	 */
	public $rd = '';
	/**
	 * 
	 * token对应的class名
	 * @var string
	 */
	public $tokenClass = '';
	/**
	 * 
	 * token对应的类实例
	 * @var object
	 */
	public $tokenInstance = null;
	/**
	 * 
	 * 构造函数
	 * @param string $text
	 */
	public function __construct($text = '') {
		$this->setText ( $text );
		$this->init ();
	}
	/**
	 * 
	 * 初始化
	 * @param string $text
	 */
	public function setText($text = '') {
		if (is_array ( $text )) {
			$text = $text [0];
		}
		if (strlen ( $text )) {
			$this->text = $this->trimText ( $text );
			$this->length = strlen ( $this->text );
		}
	}
	/**
	 * 
	 * 初始化
	 */
	public function init() {
	
	}
	/**
	 * 
	 * 过滤文本里没用的字符 
	 * @param string $text
	 */
	public function trimText($text = '') {
		$text = preg_replace ( '/\r\n?|[\n\x{2028}\x{2029}]/u', "\n", $text );
		$text = preg_replace ( '/^\x{FEFF}/u', '', $text );
		return $text;
	}
	/**
	 * 
	 * 异常处理
	 */
	public function throwException($msg = '') {
		throw new Fl_Exception ( $msg, $code );
	}
	/**
	 * 
	 * 获取对应token类的实例
	 */
	public function getTokenInstance($tokenClass = '') {
		if (! $tokenClass) {
			$tokenClass = $this->tokenClass;
		}
		if (! $tokenClass) {
			$this->throwException ( $tokenClass . ' must be a string' );
		}
		Fl::loadClass ( $tokenClass );
		$instance = new $tokenClass ( $this->text );
		$instance->tpl = $this->tpl;
		$instance->ld = $this->ld;
		$instance->rd = $this->rd;
		return $instance;
	}
	/**
	 * 
	 * 检测是否是模版语法
	 */
	public function isTpl($text = '') {
		if (! $text || ! $this->tpl || ! $this->ld || ! $this->rd) {
			return false;
		}
		$start = substr ( $text, 0, strlen ( $this->ld ) );
		$end = substr ( $text, strlen ( $text ) - strlen ( $this->rd ) );
		return $this->ld === $start && $this->rd === $end;
	}
	/**
	 * 
	 * 获取模版语法的内容，也就是去除左右定界符
	 * @param string $tpl
	 * @param boolean $trim
	 */
	public function getTplText($tpl = '', $trim = true) {
		$ldLen = strlen ( $this->ld );
		$text = substr ( $tpl, $ldLen, strlen ( $tpl ) - $ldLen - strlen ( $this->rd ) );
		if ($trim) {
			$text = trim ( $text );
		}
		return $text;
	}
	/**
	 * 
	 * 检测模版语法是否会输出
	 */
	public function checkTplHasOutput($tpl) {
		return Fl_Tpl::factory ( $this )->checkHasOutput ( $tpl, $this );
	}
}