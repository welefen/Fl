<?php
Fl::loadClass ( 'Fl_Base' );
/**
 * 
 * 将css里的小图片转为dataURI的方式
 * 并且对css进行优化，Ie6,7下访问的css可以把css3相关的去除，非ie6,7下可以将ie6,7的hack去除
 * 从而达到优化的目的
 * @author welefen
 *
 */
class Fl_Css_DataUri extends Fl_Base {

	/**
	 * 
	 * options
	 * @var array
	 */
	public $options = array (
		'maxlength' => 3000 
	);

	/**
	 * 
	 * 获取图片真实地址的函数
	 * @var string
	 */
	public $getImgRealPath = '';

	/**
	 * 
	 * css token instance
	 * @var object
	 */
	private $tokenInstance = null;

	/**
	 * 
	 * ie6,7下的输出
	 * @var array
	 */
	private $ie6_output = array ();

	/**
	 * 
	 * 非ie6,7下的输出
	 * @var array
	 */
	private $css3_output = array ();

	/**
	 * 
	 * css3的前缀
	 * @var array
	 */
	private $css3Prefix = array (
		'-webkit-', 
		'-moz-', 
		'-o-' 
	);

	/**
	 * 
	 * ie6,7下忽略的属性
	 * @var array
	 */
	private $ie6IgnoreProperyList = array (
		'-radius', 
		'-shadow', 
		'transform', 
		'perspective', 
		'transition', 
		'-sizing', 
		'background-size', 
		'column-', 
		'animation', 
		'tab-size' 
	);

	/**
	 * 
	 * 每个图片转化的次数
	 * @var array
	 */
	public $imgNums = array ();

	/**
	 * 
	 * css里的图片正则
	 * @var RegExp
	 */
	private $backgroundImgPattern = '/url\s*\(\s*([\'\"]?)([\w\-\/\.]+\.(?:png|jpg|gif|jpeg|ico|cur))(?:\?[^\?\'\"\)\s]*)?\\1\s*\)/ies';

	/**
	 * (non-PHPdoc)
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
				case FL_TOKEN_CSS_SELECTOR :
					$result = $this->collectionSelector ( $token );
					$this->addOutput ( $result );
					break;
				case FL_TOKEN_CSS_AT_MOZILLA :
					$ret = $this->getMozHack ( $token );
					$this->addOutput ( $ret );
					break;
				case FL_TOKEN_CSS_AT_KEYFRAMES :
					$this->addOutput ( $this->getKeyFrames ( $token ) );
					break;
				default :
					$this->addOutput ( $token ['value'] );
			}
		}
		return array (
			'ie6' => $this->ie6_output, 
			'css3' => $this->css3_output 
		);
	}

	/**
	 * 
	 * 添加到输出
	 * @param array $output
	 */
	public function addOutput($output = array()) {
		if (is_array ( $output ) && (isset ( $output ['ie6'] ) || isset ( $output ['css3'] ))) {
			$this->ie6_output [] = $output ['ie6'];
			$this->css3_output [] = $output ['css3'];
		} else {
			$this->ie6_output [] = $output;
			$this->css3_output [] = $output;
		}
	}

	/**
	 * 
	 * get key frames
	 * @param array $t
	 */
	public function getKeyFrames($token = array()) {
		$result = array ();
		while ( true ) {
			$t = $this->tokenInstance->getNextToken ();
			if (empty ( $t ) || $t ['type'] === FL_TOKEN_CSS_BRACES_TWO_END) {
				break;
			}
			if ($t ['type'] !== FL_TOKEN_CSS_BRACES_TWO_START) {
				$result [] = $t;
			}
		}
		$text = Fl_Css_Static::selectorTokenToText ( $result, false );
		return array (
			'ie6' => '', 
			'css3' => $token ['value'] . '{' . $text . '}' 
		);
	}

	/**
	 * 
	 * moz hack里的css直接返回
	 * @param array $token
	 */
	public function getMozHack($token = array()) {
		$tokens = array ();
		$result = $token ['value'];
		while ( true ) {
			$token = $this->tokenInstance->getNextToken ();
			if (empty ( $token )) {
				break;
			}
			$tokens [] = $token;
			if ($token ['type'] === FL_TOKEN_CSS_BRACES_TWO_END) {
				break;
			}
		}
		$result .= Fl_Css_Static::selectorTokenToText ( $tokens, false );
		return array (
			'ie6' => '', 
			'css3' => $result 
		);
	}

	/**
	 * 
	 * 
	 * @param array $token
	 */
	public function collectionSelector($token) {
		$attrs = $this->getSelectorProperties ();
		$ie6Attrs = array ();
		$css3Attrs = array ();
		foreach ( $attrs as $item ) {
			if (! $this->isIe6IgnoreProperty ( $item ) && ! $this->isIe6IgnoreValue ( $item )) {
				$ie6Item = $item;
				if ($ie6Item ['prefix'] == '*') {
					$ie6Item ['prefix'] = '';
				}
				$ie6Attrs [] = $this->attrItemToText ( $ie6Item );
			}
			if (! $this->isCss3IgnoreProperty ( $item )) {
				$item ['value'] = preg_replace ( $this->backgroundImgPattern, "self::replaceImgToDataUri('\\2')", $item ['value'] );
				$css3Attrs [] = $this->attrItemToText ( $item );
			}
		}
		return array (
			'ie6' => $token ['value'] . '{' . join ( ";", $ie6Attrs ) . "}", 
			'css3' => $token ['value'] . "{" . join ( ";", $css3Attrs ) . "}" 
		);
	}

	/**
	 * 
	 * Enter description here ...
	 * @param array $item
	 */
	public function attrItemToText($item) {
		$str = $item ['prefix'] . $item ['property'] . ':' . $item ['value'] . $item ['suffix'];
		if ($item ['important']) {
			$str .= '!important';
		}
		return $str;
	}

	/**
	 * 
	 * 替换value里的img地址
	 * @param string value
	 */
	public function replaceImgToDataUri($url = '') {
		$defaultValue = 'url("' . $url . '")';
		if (! $this->getImgRealPath) {
			return $defaultValue;
		}
		$realPath = call_user_func ( $this->getImgRealPath, $url, $this );
		if (empty ( $realPath ) || ! file_exists ( $realPath )) {
			return $defaultValue;
		}
		$imgContent = file_get_contents ( $realPath );
		if (strlen ( $imgContent ) > $this->options ['maxlength']) {
			return $defaultValue;
		}
		if (! isset ( $this->imgNums [$realPath] )) {
			$this->imgNums [$realPath] = 0;
		}
		$this->imgNums [$realPath] ++;
		$imgInfo = getimagesize ( $realPath );
		$value = 'data:' . $imgInfo ['mime'] . ';base64,' . base64_encode ( $imgContent );
		return 'url("' . $value . '")';
	}

	/**
	 * 
	 * 是否是css3可以忽略的
	 * @param array $detail
	 */
	public function isCss3IgnoreProperty($detail = array()) {
		if ($detail ['prefix'] == '*' || $detail ['prefix'] == '_') {
			return true;
		}
		return false;
	}

	/**
	 * 
	 * 是否是ie6,7下可以忽略的属性
	 * @param array $propertyDetail
	 */
	public function isIe6IgnoreProperty($propertyDetail = array()) {
		if (in_array ( $propertyDetail ['prefix'], $this->css3Prefix )) {
			return true;
		}
		foreach ( $this->ie6IgnoreProperyList as $item ) {
			if (strpos ( $propertyDetail ['property'], $item ) !== false) {
				return true;
			}
		}
		return false;
	}

	/**
	 * 
	 * 是否是ie6,7可以忽略的value
	 * @param array $detail
	 */
	public function isIe6IgnoreValue($detail = array()) {
		foreach ( $this->css3Prefix as $item ) {
			if (strpos ( $detail ['value'], $item ) != false) {
				return true;
			}
		}
		return false;
	}

	/**
	 * 
	 * 获取选择器的属性
	 */
	public function getSelectorProperties() {
		$attr = $value = '';
		$attrs = array ();
		$hasColon = false;
		$hasTpl = false;
		while ( true ) {
			$token = $this->tokenInstance->getNextToken ();
			if (empty ( $token )) {
				break;
			}
			if ($token ['type'] === FL_TOKEN_CSS_PROPERTY) {
				$attr = $token ['value'];
			} elseif ($token ['type'] === FL_TOKEN_CSS_COLON) {
				$hasColon = true;
			} elseif ($token ['type'] === FL_TOKEN_CSS_VALUE) {
				$value = $token ['value'];
			} elseif ($token ['type'] === FL_TOKEN_TPL) {
				if ($hasColon) {
					$value .= $token ['value'];
				} else {
					$attr .= $token ['value'];
				}
				$hasTpl = true;
			} elseif ($token ['type'] === FL_TOKEN_CSS_HACK) {
				$attrs [] = array (
					'property' => '', 
					'value' => $token ['value'], 
					'type' => FL_TOKEN_CSS_HACK 
				);
			} elseif ($token ['type'] === FL_TOKEN_CSS_SEMICOLON || $token ['type'] === FL_TOKEN_CSS_BRACES_ONE_END) {
				if ($hasTpl || $this->containTpl ( $attr )) {
					$attrs [] = array (
						'property' => $attr, 
						'value' => $value, 
						'type' => FL_TOKEN_TPL 
					);
				} else {
					if (! empty ( $attr ) || ! empty ( $value )) {
						$propertyDetail = Fl_Css_Static::getPropertyDetail ( $attr );
						$valueDetail = Fl_Css_Static::getValueDetail ( $value );
						$detail = $propertyDetail + $valueDetail;
						$attrs [] = $detail;
					}
				}
				$hasTpl = $hasColon = false;
				$attr = $value = '';
			}
			if ($token ['type'] === FL_TOKEN_CSS_BRACES_ONE_END) {
				break;
			}
		}
		return $attrs;
	}

}