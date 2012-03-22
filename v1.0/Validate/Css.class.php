<?php
class Fl_Validate_Css{
	
	public $analyticContent = array();
	
	public $analyticCount = 0;
	
	public $validateCount = 0;
	
	public $options = array();
	
	public function setOptions($options){
		$options = array_merge(array(
			'selector' => '',  	//检测选择器
			'property' => '', 		//检测属性
			'value' => '', 		//检测value
			'callback' =>'',	//结束的回调
		), $options);
		$this->options = $options;
	}
	public function run($content = '', $validateInstance = null, $options = array()){
		$this->setOptions($options);
		$this->analyticContent = $this->fl_instance->analytic_css($content);
		$this->analyticCount = count($this->analyticContent);
		while ($this->validateCount < $this->analyticCount){
			list($tokenText, $tokenType) = $this->analyticContent[$this->validateCount];
			$this->validateCount++;
			$method = '';
			switch ($tokenType){
				case FL::CSS_SELECTOER :
					$method = 'selector';
					break;
				case FL::CSS_PROPERTY :
					$method = 'property';
					break;
				case FL::CSS_VALUE :
					$method = 'value';
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
}