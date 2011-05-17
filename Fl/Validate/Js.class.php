<?php
class Fl_Validate_Js{
	
	public $analyticContent = array();
	
	public $analyticCount = 0;
	
	public $validateCount = 0;
	
	public $options = array();
	
	public function setOptions($options){
		$options = array_merge(array(
			'keyword' => 'keyword',  //关键字
			'string' => 'string', //字符串
			//'regexp' => 'regexp', //正则
			'expression' => 'expression', //表达式符号[],()
			'block'	=> 'block', //块符号,{}
			'operator' => 'operator', //操作符
			'callback' => 'callback', //最后的回调
		), $options);
		$this->options = $options;
	}
	public function run($content = '', $validateInstance = null, $options = array()){
		$this->setOptions($options);
		$this->analyticContent = $this->fl_instance->analytic_js($content);
		$this->analyticCount = count($this->analyticContent);
		while ($this->validateCount < $this->analyticCount){
			list($tokenText, $tokenType) = $this->analyticContent[$this->validateCount];
			$this->validateCount++;
			$method = '';
			switch ($tokenType){
				case FL::JS_WORD :
					$method = 'keyword';
					break;
				case FL::JS_STRING :
					$method = 'string';
					break;
				case FL::JS_START_EXPR:
				case FL::JS_END_EXPR :
					$method = 'expression';
					break;
				case FL::JS_START_BLOCK:
				case FL::JS_END_BLOCK:
					$method = 'block';
					break;
				case FL::JS_OPERATOR:
					$method = 'operator';
					break;
			}
			if ($method){
				$method = $this->options[$method];
				if ($method){
					$validateInstance->$method($tokenText, $this);
				}
			}
		}
		$callback = $this->options['callback'];
		if ($callback){
			$validateInstance->$callback($this);
		}
	}
	/**
	 * 
	 * get next token
	 * @param int $int
	 */
	public function getNextToken($int = 1, $add = false){
		$int = intval($int);
		if ($int < 1) {
			$int = 1;
		}
		$int = $int - 1;
		if ($add){
			$this->validateCount += $int;
			$c = $this->validateCount;
		}else{
			$c = $this->validateCount + $int;
		}
		if (($c) > $this->analyticCount) return false;
		return $this->analyticContent[$c];
	}
	/**
	 * 
	 * get given token
	 * @param string $token
	 */
	public function getFetchToken($token = '{'){
		do {
			list($tokenText, $tokenType) = $this->analyticContent[$this->validateCount];
			$this->validateCount++;
			if ($this->validateCount >= $this->analyticCount) break;
		}while ($tokenText !== $token);
	}
}