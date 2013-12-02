<?php
/**
 * 
 * css auto complete class
 * @author welefen
 *
 */
Fl::loadClass ( 'Fl_Base' );
class Fl_Css_AutoComplete extends Fl_Base {

	/**
	 * 
	 * css里的配置背景图片的正则
	 * @var RegExp
	 */
	protected $backgroundImgPattern = '/url\s*\(\s*([\'\"]?)([\w\-\/\.]+\.(?:png|jpg|gif|jpeg|ico|cur))(?:\?[^\?\'\"\)\s]*)?\\1\s*\)/ies';

	/**
	 * 
	 * 匹配keyframes的正则
	 * @var RegExp
	 */
	protected $keyFramesPattern = '/@(?:\-(webkit|moz|ms|o)\-)?keyframes\s+([\w\-]+)/ies';

	/**
	 * 
	 * 可以自动完成的属性
	 * @var array
	 */
	protected $completeAttrs = array (
		'radius' => array (
			'w3c' 
		), 
		'box-shadow' => array (
			'w3c' 
		), 
		'background' => array (
			'webkit', 
			'o', 
			'w3c' 
		), 
		'opacity' => array (
			'filter' 
		), 
		'transform' => array (
			'webkit', 
			'ms', 
			'w3c' 
		), 
		'perspective' => array (
			'webkit', 
			'moz', 
			'ms', 
			'w3c' 
		), 
		'transition' => array (
			'webkit', 
			'w3c' 
		), 
		'box-sizing' => array (
			'w3c' 
		), 
		'background-size' => array (
			'w3c' 
		), 
		'background-clip' => array (
			'w3c' 
		), 
		'column' => array (
			'webkit', 
			'moz', 
			'w3c' 
		), 
		'animation' => array (
			'w3c' 
		), 
		'tab-size' => array (
			'moz', 
			'o', 
			'w3c' 
		), 
		'keyframes' => array (
			'webkit', 
			'w3c' 
		) 
	);

	/**
	 * 
	 * typelist
	 * @var array
	 */
	public $typeList = array (
		'w3c', 
		'webkit', 
		'moz', 
		'o', 
		'ms' 
	);

	/**
	 * 
	 * css token instance
	 * @var object
	 */
	protected $tokenInstance = null;

	/**
	 * 
	 * beautify options
	 * @var array
	 */
	public $options = array (
		"w3c" => true, 
		"webkit" => true, 
		"moz" => true, 
		"ms" => true, 
		"o" => true, 
		"opacity" => true, 
		"filter_img_fn" => "", 
		'keyframes' => false 
	);

	/**
	 * 
	 * 当前selector下所有属性
	 * @var array
	 */
	protected $currentAttrs = array ();

	/**
	 * 
	 * 当前运行到哪个
	 * @var int
	 */
	protected $currentIndex = 0;

	/**
	 * 
	 * 含有radius的selector
	 * @var array
	 */
	protected $radiusSelector = array ();

	/**
	 * 
	 * 是否含有圆角的属性
	 * @var boolean
	 */
	protected $hasRadius = false;

	/**
	 * 
	 * 生成后的selector列表
	 * @var array
	 */
	protected $selectors = array ();

	/**
	 * 
	 * 输出
	 * @var array
	 */
	protected $output = array ();

	/**
	 * 
	 * keyframe列表
	 * @var array
	 */
	protected $keyframesList = array ();

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
				case FL_TOKEN_CSS_SELECTOR :
					$this->selectors [] = $this->collectionSelector ( $token );
					break;
				case FL_TOKEN_CSS_AT_MOZILLA :
					$this->output [] = $this->getMozHack ( $token );
					break;
				case FL_TOKEN_CSS_AT_MEDIA :
				case FL_TOKEN_CSS_BRACES_TWO_END :
					$this->output [] = $this->selectorToText ();
					$this->output [] = $this->getRadiusBackgroundClip ();
					$this->output [] = $token ['value'];
					break;
				case FL_TOKEN_CSS_AT_KEYFRAMES :
					$this->output [] = $this->selectorToText ();
					$this->output [] = $this->getRadiusBackgroundClip ();
					$this->output [] = $this->getKeyFrames ( $token );
					break;
				default :
					$this->output [] = $token ['value'];
			}
		}
		$this->output [] = $this->selectorToText ();
		$this->output [] = $this->getRadiusBackgroundClip ();
		$result = join ( '', $this->output );
		return $result;
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
		return $result;
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
		preg_match ( $this->keyFramesPattern, $token ['value'], $matches );
		$type = $matches [1];
		if (empty ( $type )) {
			$type = 'w3c';
		}
		$name = strtolower ( $matches [2] );
		if (isset ( $this->keyframesList [$name] )) {
			return $token ['value'] . '{' . $text . '}';
		}
		$output = '';
		if (in_array ( $type, $this->completeAttrs ['keyframes'] )) {
			$output = $token ['value'] . '{' . $text . '}';
		}
		$this->keyframesList [$name] = true;
		$extTypes = array ();
		$searchText = substr ( $this->text, $token ['pos'] );
		foreach ( $this->completeAttrs ['keyframes'] as $item ) {
			if (! $this->options [$item] || $item == $type) {
				continue;
			}
			if ($item == 'w3c') {
				$pattern = '/@keyframes\s+' . $name . '/ies';
			} else {
				$pattern = '/@\-' . $item . '\-keyframes\s+' . $name . '/ies';
			}
			//这里为了方便直接通过正则向后匹配
			if (preg_match ( $pattern, $searchText )) {
				continue;
			}
			$instance = $this->getInstance ( "Fl_Css_AutoComplete", $text );
			$options = array (
				'w3c' => false, 
				'webkit' => false, 
				'moz' => false, 
				'ms' => false, 
				'o' => false, 
				'opacity' => false, 
				'keyframes' => true 
			);
			$options [$item] = true;
			if ($item == 'ms') {
				$options ['opacity'] = true;
			}
			$return = $instance->run ( $options );
			if ($item == 'w3c') {
				$output .= '@keyframes ' . $name;
			} else {
				$output .= '@-' . $item . '-keyframes ' . $name;
			}
			$output .= '{' . $return . '}';
		}
		return $output;
	}

	/**
	 * 
	 * 收集一个selector
	 * @param array $token
	 */
	public function collectionSelector($token = array()) {
		$this->currentAttrs = $this->getSelectorProperties ();
		$tokens = array ();
		for($i = 0, $count = count ( $this->currentAttrs ); $i < $count; $i ++) {
			$this->currentIndex = $i;
			if (isset ( $this->currentAttrs [$this->currentIndex] ['type'] )) {
				$tokens [] = $this->currentAttrs [$this->currentIndex];
				continue;
			}
			$cAttrs = $this->getItemComplete ();
			if (isset ( $cAttrs ['property'] ) || isset ( $cAttrs ['value'] )) {
				$tokens [] = $cAttrs;
			} else {
				$tokens = array_merge ( $tokens, $cAttrs );
			}
		}
		if ($this->hasRadius) {
			$this->radiusSelector [] = $token ['value'];
			$this->hasRadius = false;
		}
		return array (
			'selector' => $token ['value'], 
			'attrs' => $tokens 
		);
	}

	/**
	 * 
	 * 消除border-radius的锯齿
	 */
	protected function getRadiusBackgroundClip() {
		if (empty ( $this->radiusSelector ) || $this->options ['keyframes']) {
			return '';
		}
		$result = join ( ',', $this->radiusSelector ) . '{background-clip:padding-box}';
		$this->radiusSelector = array ();
		return $result;
	}

	/**
	 * 
	 * 获取已经自动完成后属性
	 */
	public function getItemComplete() {
		$item = $this->currentAttrs [$this->currentIndex];
		if ($item ['isCompleted']) {
			return $item;
		}
		$property = strtolower ( $item ['property'] );
		$zname = '';
		if (isset ( $this->completeAttrs [$property] )) {
			$zname = $property;
		} else {
			foreach ( $this->completeAttrs as $name => $value ) {
				if (strpos ( $property, $name ) !== false) {
					$zname = $name;
					break;
				}
			}
		}
		if ($zname) {
			$fn = '_' . str_replace ( "-", "", $zname ) . '_';
			if (! method_exists ( $this, $fn )) {
				$fn = '_common_';
			}
			return $this->$fn ( $zname, $property, $item );
		}
		return $item;
	}

	/**
	 * 
	 * 获取原始的item
	 */
	public function getOriginItem($zname, $item) {
		$type = str_replace ( "-", '', $item ['prefix'] );
		if (empty ( $type )) {
			$type = 'w3c';
		}
		if (! $this->options [$type] && $this->options ['keyframes']) {
			return array ();
		}
		$types = $this->completeAttrs [$zname];
		if (! in_array ( $type, $types )) {
			return array ();
		}
		return array (
			$item 
		);
	}

	/**
	 * 
	 * 通用的完成
	 * @param string $zname
	 */
	public function _common_($zname = '', $property = '', $item = array()) {
		$existType = $this->getExistType ( $property );
		$oldtype = str_replace ( "-", '', $item ['prefix'] );
		$additionalTypes = $this->getAdditionalType ( $zname, $oldtype, $existType );
		$types = $this->completeAttrs [$zname];
		$result = $this->getOriginItem ( $zname, $item );
		foreach ( $additionalTypes as $type ) {
			$result [] = array_merge ( $item, array (
				'prefix' => $this->getPushPrefix ( $zname, $type ), 
				'property' => $this->getPushAttr ( $property, $type ), 
				'value' => $this->getPushValue ( $item ['value'], $type ) 
			) );
		}
		return $result;
	}

	/**
	 * 
	 * 圆角
	 * @param string $zname
	 * @param string $property
	 * @param array $item
	 */
	public function _radius_($zname = '', $property = '', $item = array()) {
		$this->hasRadius = true;
		return $this->_common_ ( $zname, $property, $item );
	}

	/**
	 * 
	 * 透明度
	 * @param string $zname
	 * @param string $property
	 * @param array $item
	 */
	public function _opacity_($zname = '', $property = '', $item = array()) {
		if (! $this->options ['opacity']) {
			return $item;
		}
		$result = array (
			$item 
		);
		$existType = $this->getExistType ( $property );
		if (empty ( $existType )) {
			$result [] = array_merge ( $item, array (
				'property' => 'filter', 
				'value' => 'alpha(opacity=' . number_format ( (floatval ( $item ['value'] ) * 100), 0 ) . ')' 
			) );
		}
		return $result;
	}

	/**
	 * 背景
	 * 
	 */
	public function _background_($zname = '', $property = '', $item = array()) {
		$result = $this->getOriginItem ( $zname, $item );
		//背景渐变
		if (strpos ( $item ['value'], '-gradient' ) !== false) {
			$item ['value'] = trim ( $item ['value'] );
			$type = $this->getBackgroundGradientValueType ( $item ['value'] );
			$existTypes = $this->getExistTypeForBackgroundGradient ();
			$cleanValue = str_replace ( array (
				'-webkit-', 
				'-moz-', 
				'-o-' 
			), "", $item ['value'] );
			foreach ( $this->completeAttrs ['background'] as $typeItem ) {
				if ($this->options [$typeItem] && $typeItem != $type && ! in_array ( $typeItem, $existTypes )) {
					$result [] = array_merge ( $item, array (
						'value' => $typeItem == 'w3c' ? $cleanValue : '-' . $typeItem . '-' . $cleanValue 
					) );
				}
			}
			return $result;
		}
		//IE6下含有alpha通道透明的png自动添加filter
		if (preg_match ( $this->backgroundImgPattern, $item ['value'] )) {
			if ($this->options ['filter_img_fn'] && $this->checkBackgroundFilter ()) {
				$url = call_user_func ( $this->options ['filter_img_fn'], $item ['value'] );
				if ($url) {
					$result [] = array (
						'property' => 'filter', 
						'prefix' => '_', 
						'value' => 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src="' . $url . '")' 
					);
					$result [] = array (
						'property' => 'background', 
						'prefix' => '_', 
						'value' => 'none' 
					);
				}
			}
			return $result;
		}
		return $item;
	}

	/**
	 * 
	 * 获取是哪一个类型
	 * @param string $value
	 */
	protected function getBackgroundGradientValueType($value) {
		$type = 'w3c';
		foreach ( $this->typeList as $item ) {
			if (strpos ( $value, '-' . $item . '-' ) === 0) {
				$type = $item;
				break;
			}
		}
		return $type;
	}

	/**
	 * 
	 * 渐变下已有的类型
	 */
	protected function getExistTypeForBackgroundGradient() {
		$count = count ( $this->currentAttrs );
		if ($this->currentIndex == ($count - 1)) {
			return array ();
		}
		$result = array ();
		for($i = $this->currentIndex + 1; $i < $count; $i ++) {
			$item = $this->currentAttrs [$i];
			if (strpos ( $item ['value'], '-gradient' ) !== false) {
				$type = $this->getBackgroundGradientValueType ( $item ['value'] );
				$result [] = $type;
				//将当前item标记为已经自动添加
				$this->currentAttrs [$i] ['isCompleted'] = true;
			}
		}
		return array_unique ( $result );
	}

	/**
	 * 
	 * 检测背景所使用的filter
	 */
	protected function checkBackgroundFilter() {
		$count = count ( $this->currentAttrs );
		if ($this->currentIndex == ($count - 1)) {
			return true;
		}
		for($i = $this->currentIndex + 1; $i < $count; $i ++) {
			$item = $this->currentAttrs [$i];
			if (strpos ( $item ['property'], 'background' ) !== false) {
				//如果有ie6的background相关的hack，则不添加filter
				if ($item ['prefix'] == '_') {
					return false;
				}
				if (preg_match ( $this->backgroundImgPattern, $item ['value'] )) {
					$this->currentAttrs [$i] ['isCompleted'] = true;
					return false;
				}
				if ($item ['value'] === 'none' || $item ['value'] === '0') {
					return false;
				}
			}
			if (strpos ( $item ['property'], 'filter' ) !== false) {
				if (strpos ( $item ['value'], 'Microsoft.AlphaImageLoader' ) !== false) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * 
	 * 获取需要不全的类型
	 * @param string $type
	 * @param array $existType
	 */
	public function getAdditionalType($zname, $type, $existType) {
		if (empty ( $type )) {
			$type = 'w3c';
		}
		$types = $this->completeAttrs [$zname];
		$result = array ();
		foreach ( $types as $item ) {
			if ($type != $item && ! in_array ( $item, $existType ) && $this->options [$item]) {
				$result [] = $item;
			}
		}
		return $result;
	}

	/**
	 * 
	 * 获取属性名
	 * @param string $name
	 * @param string $type
	 */
	public function getPushAttr($name, $type) {
		if (strpos ( $name, 'radius' ) !== false) {
			$array = array (
				'border-top-left-radius' => 'border-radius-topleft', 
				'border-top-right-radius' => 'border-radius-topright', 
				'border-bottom-left-radius' => 'border-radius-bottomleft', 
				'border-bottom-right-radius' => 'border-radius-bottomright' 
			);
			if ($type == 'moz') {
				if (isset ( $array [$name] )) {
					return $array [$name];
				}
			} else {
				$flip = array_flip ( $array );
				if (isset ( $flip [$name] )) {
					return $flip [$name];
				}
			}
		}
		return $name;
	}

	/**
	 * 获取前缀
	 */
	public function getPushPrefix($zname = '', $type = '') {
		if ($zname == 'background' || $type == 'w3c') {
			return '';
		}
		return '-' . $type . '-';
	}

	/**
	 * 
	 * 获取值
	 * @param string $value
	 * @param string $type
	 */
	public function getPushValue($value, $type = '') {
		return $value;
	}

	/**
	 * 
	 * 获取已经存在的属性
	 * @param string $zname
	 */
	public function getExistType($property) {
		$count = count ( $this->currentAttrs );
		if ($this->currentIndex == ($count - 1)) {
			return array ();
		}
		$result = array ();
		for($i = $this->currentIndex + 1; $i < $count; $i ++) {
			$item = $this->currentAttrs [$i];
			if ($property == 'opacity' && 'filter' == strtolower ( $item ['property'] )) {
				if (strpos ( $item ['value'], 'opacity' ) !== false) {
					return array (
						'ms' 
					);
				}
			}
			if ($property == strtolower ( $item ['property'] )) {
				$prefix = str_replace ( "-", "", $item ['prefix'] );
				if (empty ( $prefix )) {
					$prefix = 'w3c';
				}
				$result [] = $prefix;
				//将当前item标记为已经自动添加
				$this->currentAttrs [$i] ['isCompleted'] = true;
			}
		}
		return array_unique ( $result );
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

	/**
	 * 
	 * selector转为文本
	 */
	protected function selectorToText() {
		if (empty ( $this->selectors )) {
			return '';
		}
		$result = '';
		foreach ( $this->selectors as $item ) {
			$result .= $item ['selector'] . '{';
			$attrs = array ();
			foreach ( $item ['attrs'] as $item ) {
				$str = $item ['prefix'] . $item ['property'] . ':' . $item ['value'] . $item ['suffix'];
				if ($item ['important']) {
					$str .= '!important';
				}
				$attrs [] = $str;
			}
			$result .= join ( ";", $attrs );
			$result .= '}';
		}
		$this->selectors = array ();
		return $result;
	}
}