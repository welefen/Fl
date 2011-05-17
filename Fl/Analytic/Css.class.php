<?php
/**
 * 
 * css分析器
 * 支持一下css规则：
 * css规则
	1、@charset "utf-8"; //设置字符集
	2、@import url("a.css"); //import
	3、_property:value //ie6
	4、*property:value //ie6,7
	5、property:value\9; //ie6,7,8,9
	6、property//:value  //非ie6
	7、* html selector{} //各种选择符
	8、@media all and (-webkit-min-device-pixel-ratio:10000),not all and (-webkit-min-device-pixel-ratio:0) { ... } //设备
	9、@-moz-xxx  //firefox
	10、property:value !important; //important
	11、property:expression(onmouseover=function(){})  //expression，值里有可能有 { 和 } 
	12、-webkit-border-radious:value //浏览器私有，减号开头
	13、filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src="ds_laby.png", sizingMethod='crop')  //ie下的filter，有(和)
	14、@-css-compiler {
    selector-compile: no-combinator;
    rule-compile: all;
    } 只有设备的
 * @author lichengyin
 *
 */
class Fl_Analytic_Css{
	/**
	 * 
	 * 当前解析到的位置
	 * @var int
	 */
	public $parsePos = 0;
	
	public $content = '';
	
	public $contentLength = 0;
	
	private $_output = array();
	
	private $_pre_type = ''; //上一个特殊类型

	public function run($content = ''){
		$this->content = str_replace("\r\n", "\n", trim($content));
		$this->contentLength = strlen($this->content);
		$this->tokenAnalytic();
		return $this->_output;
	}
	public function tokenAnalytic(){
		while (true){
			$token = $this->getNextToken();
			if ($token){
				if ($token[1] === FL::FL_EOF) break;
				$this->_output[] = $token;
			}
		}
	}
	public function getNextToken(){
		if ($this->parsePos >= $this->contentLength){
			return array('', FL::FL_EOF);
		}
		$char = $this->content[$this->parsePos];
		$this->parsePos++;
		
		if ($char === "\x0d") return ''; //\r
		if ($char === "\x0a") return array($char, FL::FL_NEW_LINE);
		//避免出现多个空格在一起的情况
		if (trim($char) === '' || $char === ';') return '';
		
		$result = $this->fl_instance->getTplDelimiterToken($char, $this);
		if ($result) return $result;
		//处理@开头的；如：@charset "utf-8";@import url("a.css"), @media xxx{}
		if ($char === '@'){
			$result = $this->_getAtToken($char);
			if ($result) return $result;
		}else if ($char === '{'){
			switch ($this->_pre_type){
				case FL::CSS_DEVICE_DESC : 
					$this->_pre_type = FL::CSS_DEVICE_START;
					return array($char, FL::CSS_DEVICE_START);
				default : 
					$this->_pre_type = FL::CSS_SELECTOER_START;
					return array($char, FL::CSS_SELECTOER_START);
			}
		}else if ($char === '}'){
			switch ($this->_pre_type){
				case FL::CSS_SELECTOER_END:
					$this->_pre_type = FL::CSS_DEVICE_END;
					return array($char, FL::CSS_DEVICE_END);
				default: 
					/**
					* 处理只有device和内容，没有selector的情况
					*/
					for($i=count($this->_output)-1;$i>=0;$i--){
						$item = $this->_output[$i];
						if($item[1] === FL::CSS_SELECTOER_START){
							$this->_pre_type = FL::CSS_SELECTOER_END;
							return array($char, FL::CSS_SELECTOER_END);
						}else if($item[1] === FL::CSS_DEVICE_START){
							$this->_pre_type = FL::CSS_DEVICE_END;
							return array($char, FL::CSS_DEVICE_END);
						}
					}
					$this->_pre_type = FL::CSS_SELECTOER_END;
					return array($char, FL::CSS_SELECTOER_END);
			}
		}else if (substr($this->content, $this->parsePos - 1, 2) === '/*'){
			$result = $this->_getCommentToken($char);
			if ($result) return $result;
		}else if ($char === "\x0d" || $char === "\x0a"){
			return array($char, FL::FL_NEW_LINE);
		}
		switch ($this->_pre_type){
			case FL::CSS_SELECTOER_START : 
			case FL::CSS_VALUE : 
				$result = $this->_getPropertyToken($char);
				$this->_pre_type = FL::CSS_PROPERTY;
				return $result;
			case FL::CSS_PROPERTY : 
				$result = $this->_getValueToken($char);
				$this->_pre_type = FL::CSS_VALUE;
				return $result;
			case FL::CSS_DEVICE_START:
				$pos = $this->parsePos;
				$result = $this->_getPropertyToken($char);
				$str = $result[0];
				/*
				* 处理只要device和内容，没有selector的情况
				* @-css-compiler {
				*	selector-compile: no-combinator;
				*	rule-compile: all;
				* }
				*/
				if(strpos($str, '{') !== false){
					$this->parsePos = $pos;
					$result = $this->_getSelectorToken($char);
					$this->_pre_type = FL::CSS_DEVICE_START;
					if ($result) return $result;
				}else{
					$this->_pre_type = FL::CSS_PROPERTY;
					return $result;
				}
			default:
				$result = $this->_getSelectorToken($char);
				if ($result) return $result;
			
		}
		return array($char, FL::CSS_NORMAL);
	}
	//处理@开头的；如：@charset "utf-8";@import url("a.css"), @media xxx{}
	private function _getAtToken($char){
		$resultString = $char;
		while ($this->content[$this->parsePos] !== ';' 
			&& $this->content[$this->parsePos] !== '{' 
			&& $this->parsePos < $this->contentLength){
			
			$resultString .= $this->content[$this->parsePos];
			$this->parsePos++;
		}
		if ($this->content[$this->parsePos] === ';'){
			$resultString .= ';';
			$this->parsePos++;
			return array(trim($resultString), FL::CSS_AT);
		}
		$this->_pre_type = FL::CSS_DEVICE_DESC;
		return array(trim($resultString), FL::CSS_DEVICE_DESC);
	}
	//comment
	private function _getCommentToken($char, $fromSelector = false){
		$this->parsePos++;
		$resultString = '';
		while (!($this->content[$this->parsePos] === '*' 
			&& $this->content[$this->parsePos + 1] 
			&& $this->content[$this->parsePos + 1] === '/') 
			&& $this->parsePos < $this->contentLength){
			
			$resultString .= $this->content[$this->parsePos];
			$this->parsePos++;
		}
		$this->parsePos += 2;
		if ($fromSelector){
			return '/*' . $resultString . '*/';
		}
		return array('/*' . $resultString . '*/', FL::CSS_COMMENT);
	}
	/**
	 * selector content
	 * 选择符里可能还有注释，注释里可能含有{}等字符
	 */
	private function _getSelectorToken($char){
		$resultString = $char;
		while ($this->content[$this->parsePos] !== '{' 
				&& $this->content[$this->parsePos] !== '}' 
				&& $this->parsePos < $this->contentLength){
			//如果选择符中含有注释
			if ($this->content[$this->parsePos] === '/' &&
				$this->content[$this->parsePos+1] &&
				$this->content[$this->parsePos+1] === '*'){
                $this->parsePos++;
				$resultString .= $this->_getCommentToken('/', true);
			}else{
				$resultString .= $this->content[$this->parsePos];
				$this->parsePos++;
			}
		}
		return array(trim($resultString), FL::CSS_SELECTOER);
	}
	//css property
	private function _getPropertyToken($char){
		$resultString = $char;
		while ($this->content[$this->parsePos] !== ':' && $this->content[$this->parsePos] !== ';' && $this->content[$this->parsePos] !== '}' && $this->parsePos < $this->contentLength){
			$resultString .= $this->content[$this->parsePos];
			$this->parsePos++;
		}
		//增加对div{color}的容错机制
		if ($this->content[$this->parsePos] !== '}'){
			$this->parsePos++;
		}
		return array(strtolower(trim($resultString)), FL::CSS_PROPERTY);
	}
	//css value
	private function _getValueToken($char){
		$resultString = $char;
		$isExpression = false;
		while ($this->content[$this->parsePos] !== ';' 
			&& $this->content[$this->parsePos] !== '}' 
			&& $this->parsePos < $this->contentLength){
			
			$char = $this->content[$this->parsePos];
			$this->parsePos++;
			$resultString .= $char;
			if (!$isExpression && strtolower($resultString) === 'expression('){
				$isExpression = true;
				$resultString .= $this->_getJSToken();
			}
		}
		if ($this->content[$this->parsePos] === ';'){
			$this->parsePos++;
		}
		//将多个空格变成一个空格
		$resultString = preg_replace("/\s+/is", " ", trim($resultString));
		return array($resultString, FL::CSS_VALUE);
	}
	//处理expression里的javascript
	private function _getJSToken(){
		$string = '';
		while ($this->parsePos < $this->contentLength){
			$char = $this->content[$this->parsePos];
			$this->parsePos++;
			$string .= $char;
			//这里使用js分析器，然后判断（和） 个数是否相等
			if ($char === ')' && $this->_checkJSToken($this->fl_instance->analytic_js('(' . $string))){
				break;
			}
		}
		return $string;
	}
	/**
	 * check js for expression
	 * @param array $output
	 */
	private function _checkJSToken($output){
		$expr_start = 0;
		$expr_end = 0;
		for ($i=0,$count=count($output);$i<$count;$i++){
			$item = $output[$i];
			if ($item[0] === '(') $expr_start++;
			elseif ($item[0] === ')') $expr_end++;
		}
		return $expr_start === $expr_end;
	}
}
