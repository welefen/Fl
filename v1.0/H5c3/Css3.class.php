<?php
/**
 * 
 * css3样式自动不全，也就是开发的时候只要写一份，其他浏览器会自动不全,如：
 * 开发的时候写-webkit-border-radius:5px;编译后自动成为下面的
 * -webkit-border-radius:5px;
 * -moz-border-radius:5px;
 * border-radius:5px;
 * 
 * @author welefen
 *
 */
class Fl_H5c3_Css3{
	
	private $_output = array();
	
	/**
	 * 
	 * radius property list
	 * @var array
	 */
	private $_radius = array(
		array('-moz-border-radius', '-webkit-border-radius', 'border-radius'),
		array('-moz-border-radius-topleft', '-webkit-border-top-left-radius', 'border-top-left-radius'),
		array('-moz-border-radius-topright', '-webkit-border-top-right-radius', 'border-top-right-radius'),
		array('-moz-border-radius-bottomleft', '-webkit-border-bottom-left-radius', 'border-bottom-left-radius'),
		array('-moz-border-radius-bottomright', '-webkit-border-bottom-right-radius', 'border-bottom-right-radius'),
	);
	/**
	 * box shadow property list
	 * @var array
	 */
	private $_boxshadow = array(
		'-moz-box-shadow', '-webkit-box-shadow', 'box-shadow',
	);
	/**
	 * 
	 */
	private $_transform = array(
		'-moz-transform', '-o-transform', '-webkit-transform', '-ms-transform', 'transform',
	);
	
	private $_transition = array(
		'-moz-transition', '-o-transition', '-webkit-transition', 'transition',
	);
	/**
	 * css里含有radius的selector
	 */
	private $_hasRadiusSelector = array();
	
	public function run($content = ''){
		$analyticContent = $this->fl_instance->analytic_css($content);
		$selector = array();
		$selectorText = '';
		for ($i=0,$count=count($analyticContent); $i<$count; $i++){
			list($tokenText, $tokenType) = $analyticContent[$i];
			$tokenText = trim($tokenText);
			if ($tokenType === FL::FL_NEW_LINE || ($tokenType === FL::CSS_COMMENT && substr($tokenText, 0, 3) !== '/*!')){
				continue;
			}else if ($tokenType === FL::CSS_SELECTOER){
				$selectorText = $tokenText;
			}else if ($tokenType === FL::CSS_SELECTOER_START){
				$selector = array();
			}else if ($tokenType === FL::CSS_SELECTOER_END){
				$selector = $this->_selector($selector, $selectorText);
				foreach ($selector as $item){
					list($text, $type) = $item;                    
					$text = trim($text);
                    if($type === FL::CSS_PROPERTY){
                        $this->_output[] = $text . ":";
                    }elseif($type === FL::CSS_VALUE){
                        $this->_output[] = $text . ";";
                    }else{
                        $this->_output[] = $text;
                    }
				}
			}
			if ($tokenType !== FL::CSS_SELECTOER_START && $tokenType !== FL::CSS_COLON && $tokenType !== FL::CSS_SEMICOLON
                     && $tokenType !== FL::CSS_WHITESPACE){
				$selector[] = $analyticContent[$i];
			}
			if ($tokenType !== FL::CSS_PROPERTY && $tokenType !== FL::CSS_VALUE
				&& $tokenType !== FL::CSS_COLON && $tokenType !== FL::CSS_SEMICOLON
                     && $tokenType !== FL::CSS_WHITESPACE){
				$this->_output[] = $tokenText;
			}
		}
		/* useful if you don't want a bg color from leaking outside the border: */
		if (count($this->_hasRadiusSelector)){
			$this->_output[] = join(',', $this->_hasRadiusSelector) . '{-moz-background-clip: padding; -webkit-background-clip: padding-box; background-clip: padding-box;}';
		}
		return join('', $this->_output);
	}
	private function _selector($selector, $selectorText){
		$types = array('_radius', '_boxshadow', '_transform', '_transition');
		$keys = $this->_getKeys($selector);
		$result = array();
		$css3Keys = array();
		$gradientValues = array();
		$hasRadius = false;
		for ($i=0,$count=count($selector);$i<$count;$i=$i+2){
			$key = strtolower($selector[$i][0]);
			$result[] = $selector[$i];
			$result[] = $selector[$i+1];
			//deal for radius, box shadow, transform, transition
			foreach ($types as $type){
				$css3List = $this->{$type};
				if (is_array($css3List[0])){//第一个元素是否为数组，主要是处理radius
					foreach ($css3List as $item){
						$this->_addCss($key, $item, $css3Keys, $keys, $selector, $i, $result);
						if (in_array($key, $item)){
							$hasRadius = true;
						}
					}
				}else{
					$this->_addCss($key, $css3List, $css3Keys, $keys, $selector, $i, $result);
				}
			}
			//deal for gradient
			if ($key === 'background-image'){
				$values = $this->_getValues($selector);
				$value = strtolower($selector[$i+1][0]);
				if (strpos($value, '-gradient')){
					//foreach ()
				}
			}
		}
		/* useful if you don't want a bg color from leaking outside the border: */
		if ($hasRadius){
			$this->_hasRadiusSelector[] = $selectorText;
		}
		return $result;
	}
	private function _addCss($key, $type, &$css3Keys, $keys, $selector, $i, &$result){
		if (is_array($type) && in_array($key, $type)){
			$css3Keys[] = $key;
			foreach ($type as $item){
				if (in_array($item, $css3Keys) || in_array($item, $keys)){
					continue;
				}
				$css3Keys[] = $item;
				$result[] = array($item, FL::CSS_PROPERTY);
				$result[] = $selector[$i+1];
			}
			return true;
		}
		return false;
	}
	/**
	 * 
	 * 获取css selector的key，即属性名
	 * @param array $selector
	 */
	private function _getKeys($selector){
		$result = array();
		foreach ($selector as $item){
			if ($item[1] === FL::CSS_PROPERTY){
				$result[] = strtolower(trim($item[0]));
			}
		}
		return $result;
	}
	
	private function _getValues($selector){
		$result = array();
		foreach ($selector as $item){
			if ($item[1] === FL::CSS_VALUE){
				$result[] = strtolower(trim($item[0]));
			}
		}
		return $result;
	}
}