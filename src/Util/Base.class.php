<?php
/**
 * 
 * Fl基础类,其他类由该类继承
 * @author welefen
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
	 * 文本的长度
	 * @var int
	 */
	protected $length = 0;

	/**
	 * 
	 * 是否有模板语法的TOKEN
	 * @var boolean
	 */
	public $hasTplToken = false;

	/**
	 * 
	 * tpl token has checked
	 * @var boolean
	 */
	protected $tplTokenHasChecked = false;

	/**
	 * 
	 * 构造函数
	 * @param string $text
	 */
	public function __construct($text) {
		$this->setText ( $text );
		$this->init ();
	}

	/**
	 * 
	 * 初始化
	 */
	public function init() {
		//
	}

	/**
	 * 
	 * 统一的执行方法
	 */
	public function run() {
		//
	}

	/**
	 * 
	 * 设置要分析的文本
	 * @param string $text
	 */
	public function setText($text = '') {
		if (strlen ( $text )) {
			$this->text = $this->trim ( $text );
			$this->length = strlen ( $this->text );
		}
	}

	/**
	 * 
	 * 过滤文本里没用的字符 
	 * @param string $text
	 */
	public function trim($text = '') {
		$text = preg_replace ( '/\r\n?|[\n\x{2028}\x{2029}]/u', "\n", $text );
		$text = preg_replace ( '/^\x{FEFF}/u', '', $text );
		return $text;
	}

	/**
	 * 
	 * 异常处理
	 */
	public function throwException($msg = '') {
		throw new Fl_Exception ( $msg, - 1 );
	}

	/**
	 * 
	 * 检测文本里是否含有模版语法
	 */
	public function checkHasTplToken() {
		if ($this->tplTokenHasChecked) {
			return $this->hasTplToken;
		}
		$this->tplTokenHasChecked = true;
		if (! $this->tpl || ! $this->ld || ! $this->rd) {
			return false;
		}
		if (strpos ( $this->text, $this->ld ) === false || strpos ( $this->text, $this->rd ) === false) {
			return false;
		}
		return $this->hasTplToken = true;
	}

	/**
	 * 
	 * 获取对应class的实例
	 */
	public function getInstance($class = '', $text = '') {
		Fl::loadClass ( $class );
		$instance = new $class ( $text ? $text : $this->text );
		$instance->tpl = $this->tpl;
		$instance->ld = $this->ld;
		$instance->rd = $this->rd;
		return $instance;
	}

	/**
	 * 
	 * 检测传递的文本是否是个模版语法
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
	 * contain tpl in value
	 * @param string $value
	 */
	public function containTpl($value = '') {
		if (! $this->tpl || ! $this->ld || ! $this->rd) {
			return false;
		}
		if (strpos ( $value, $this->ld ) === false || strpos ( $value, $this->rd ) === false) {
			return false;
		}
		return true;
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
	 * 检测传递的模版语法是否会输出
	 */
	public function checkTplHasOutput($tpl) {
		return Fl_Tpl::factory ( $this )->checkHasOutput ( $tpl, $this );
	}
}