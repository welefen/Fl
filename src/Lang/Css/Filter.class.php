<?php
Fl::loadClass ( 'Fl_Base' );
Fl::loadClass ( "Fl_Static" );
/**
 * 
 * css 过滤器
 * @author welefen
 *
 */
class Fl_Css_Filter extends Fl_Base {

	/**
	 * 
	 * 属性名白名单
	 * @var array
	 */
	public $blankPropertyList = array (
		'background-attachment' => true, 
		'background-color' => true, 
		'background-image' => true, 
		'background-position' => true, 
		'background-repeat' => true, 
		'background' => true, 
		'border-collapse' => true, 
		'border-color' => true, 
		'border-spacing' => true, 
		'border-style' => true, 
		'border-top' => true, 
		'border-left' => true, 
		'border-right' => true, 
		'border-bottom' => true, 
		'border-top-color' => true, 
		'border-left-color' => true, 
		'border-right-color' => true, 
		'border-bottom-color' => true, 
		'border-top-style' => true, 
		'border-left-style' => true, 
		'border-right-style' => true, 
		'border-bottom-style' => true, 
		'border-top-width' => true, 
		'border-left-width' => true, 
		'border-right-width' => true, 
		'border-bottom-width' => true, 
		'border-width' => true, 
		'border' => true, 
		'bottom' => true, 
		'clear' => true, 
		'clip' => true, 
		'color' => true, 
		'display' => true, 
		'float' => true, 
		'font-family' => true, 
		'font-size' => true, 
		'font-style' => true, 
		'font-variant' => true, 
		'font-weight' => true, 
		'font' => true, 
		'height' => true, 
		'left' => true, 
		'letter-spacing' => true, 
		'line-height' => true, 
		'list-style-image' => true, 
		'list-style-position' => true, 
		'list-style-type' => true, 
		'list-style' => true, 
		'margin-left' => true, 
		'margin-top' => true, 
		'margin-right' => true, 
		'margin-bottom' => true, 
		'margin' => true, 
		'padding-left' => true, 
		'padding-top' => true, 
		'padding-right' => true, 
		'padding-bottom' => true, 
		'padding' => true, 
		'position' => true, 
		'text-align' => true, 
		'text-decoration' => true, 
		'text-indent' => true, 
		'top' => true, 
		'white-space' => true, 
		'width' => true, 
		'word-spacing' => true, 
		'z-index' => true 
	);

	/**
	 * 
	 * 当前css文件所对应的地址
	 * @var string
	 */
	public $url = '';

	/**
	 * 获取外部资源的回调方法,外部提供
	 */
	public $getResourceContentFn = '';

	/**
	 * 
	 * 过滤选项
	 * @var array
	 */
	public $options = array (
		'css_use_blank' => true, 
		'css_use_blank_property_filter' => false, 
		'css_remove_expression' => true, 
		'css_value_max_length' => 50 
	);

	/**
	 * css token instance
	 */
	protected $tokenInstance = null;

	/**
	 * 
	 * 输出变量
	 * @var array
	 */
	public $output = array ();

	/**
	 * 
	 * 是否忽略下一个value
	 * @var boolean
	 */
	private $ignoreValue = false;

	/**
	 * 
	 * 背景图正则
	 * @var RegExp
	 */
	private $backgroundImagePattern = '/url\s*\(\s*([\'\"]?)(.+)\\1\s*\)/ies';

	/**
	 * run
	 * @see Fl_Base::run()
	 */
	public function run($options = array()) {
		$this->options = array_merge ( $this->options, $options );
		$this->tokenInstance = $this->getInstance ( "Fl_Css_Token" );
		while ( true ) {
			$token = $this->tokenInstance->getNextToken ();
			if (empty ( $token )) {
				break;
			}
			switch ($token ['type']) {
				case FL_TOKEN_CSS_AT_IMPORT :
					$this->output [] = $this->filterImportUrl ( $token );
					break;
				case FL_TOKEN_CSS_COLON :
					if (! $this->ignoreValue) {
						$this->output [] = $token ['value'];
					}
					break;
				case FL_TOKEN_CSS_PROPERTY :
					$this->ignoreValue = false;
					$this->output [] = $this->filterProperty ( $token );
					break;
				case FL_TOKEN_CSS_VALUE :
					$this->output [] = $this->filterValue ( $token );
					break;
				case FL_TOKEN_CSS_AT_OTHER :
					break;
				default :
					$this->output [] = $token ['value'];
			}
		}
		$text = join ( '', $this->output );
		return $text;
	}

	/**
	 * 
	 * 过滤属性
	 * @param array $token
	 */
	public function filterProperty($token) {
		$property = strtolower ( $token ['value'] );
		if ($this->options ['css_use_blank'] && ! $this->blankPropertyList [$property]) {
			$this->ignoreValue = true;
			return '';
		}
		return $token ['value'];
	}

	/**
	 * 
	 * @import url
	 * @param array $token
	 */
	public function filterImportUrl($token) {
		if (empty ( $this->getResourceContentFn )) {
			return '';
		}
		//@import url的正则
		$pattern = "/^\@import\s*url\s*\(\s*([\'\"])(.*?)\\1\s*\)\s*\;$/ies";
		preg_match ( $pattern, $token ['value'], $matches );
		if (empty ( $matches ) || empty ( $matches [2] )) {
			return '';
		}
		$url = Fl_Static::getFixedUrl ( $matches [2], $this->url );
		$content = call_user_func ( $this->getResourceContentFn, $url, $this );
		if (empty ( $content )) {
			return '';
		}
		$instance = $this->getInstance ( 'Fl_Css_Filter', $content );
		$instance->url = $url;
		$instance->getResourceContentFn = $this->getResourceContentFn;
		$content = $instance->run ( $this->options );
		return $content;
	}

	/**
	 * 
	 * 过滤value
	 * @param array $token
	 */
	public function filterValue($token) {
		if ($this->ignoreValue) {
			$this->ignoreValue = false;
			return '';
		}
		$value = trim ( $token ['value'] );
		$value = stripslashes ( $value );
		if ($this->options ['css_remove_expression']) {
			$sValue = strtolower ( $value );
			$sValue = preg_replace ( "/\/\*(.*?)\*\//", "", $sValue );
			if (strpos ( $sValue, "expression" ) !== false) {
				return '';
			}
		}
		if (preg_match ( $this->backgroundImagePattern, $value )) {
			$value = preg_replace ( $this->backgroundImagePattern, "self::replaceImg('\\2', '\\1')", $value );
			return $value;
		} else {
			if ($this->options ['css_value_max_length']) {
				$value = substr ( $value, 0, $this->options ['css_value_max_length'] );
			}
		}
		return $value;
	}

	/**
	 * 
	 * 替换图片地址
	 * @param string $url
	 */
	public function replaceImg($url, $quote = '') {
		$quote = stripslashes ( $quote );
		$url = trim ( $url );
		if (strpos ( strtolower ( $url ), 'data:' ) === 0) {
			return 'url(' . $url . ')';
		}
		$url = Fl_Static::getFixedUrl ( $url, $this->url );
		if (empty ( $url )) {
			return '';
		}
		return 'url(' . $quote . $url . $quote . ')';
	}
}