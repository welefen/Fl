<?php
/**
 * 
 * javascript beautify
 * @author lichengyin
 *
 */
class Fl_Beautify_Js{
	
	public $options = array();
	/**
	 * 
	 * indent depth
	 * @var int
	 */
	private $_indentDepth = 0; 
	/**
	 * current token type
	 * @var const
	 */
	private $_type = 0; 
	
	private $_text = '';
	
	private $_preType = 0; //上一个类型
	
	private $_preText = ''; //上一个文本
	
	private $_prePreText = ''; //上上一个文本
	
	private $_lineStarters = array();
	
	private $_output = array(); //最终输出文本数组
	
	private $_flags = array(); //beautify过程中的状态
	
	
	public function setOptions($options = array()){
		$this->_fl_instance = Fl::getInstance();
		$this->_indentDepth = 0;
		$this->_type = 0;
		$this->_text = '';
		$this->_preType = 0;
		$this->_preText = '';
		$this->_prePreText = '';
		$this->_mode = 0;
		$this->_output = array();
		$this->_lineStarters = split(',', 'continue,try,throw,return,var,if,switch,case,default,for,while,break,function');
		$options = array_merge(array(
			'indent' => "    ", 				//缩进
			'braces-on-own-line' => false, //{是否占一行
			'keep-array-ident' => false, //数组是否要缩进
			'header' => "/*@Beautify at ".date('Y-m-d H:i:s', time()).":md5({md5})*/",
			'header-pattern' => "/\/\*\@Beautify\sat.*?\:md5\(([a-f0-9]{32})\)\*\//ies",
			'header-var' => '~!header!~'
		), $options);
		$this->options = $options;
		
		$this->_flags = array(
			'var-line' => false,
			'var-line-reindented' => false,
			'mode' => 0, //current mode
		);
	}
	public function printSpace(){
		$count = count($this->_output);
		$last = $this->_output[$count - 1];
		if ($last !== ' ' && $last != "\n" && $last != $this->options['indent']){
			$this->_output[] = ' ';
		}
	}
	public function printToken(){
		$this->_output[] = $this->_text;
	}
	public function printIndent(){
		$this->_output[] = str_repeat($this->options['indent'], $this->_indentDepth);
	}
	public function printNewLine(){
		$count = count($this->_output);
		$last = $this->_output[$count - 1];
		if ($last !== "\n"){
			$this->_output[] = "\n";
		}
		$this->_output[] = str_repeat($this->options['indent'], $this->_indentDepth);
		if ($this->_flags['var_line'] && $this->_flags['var_line_reindented']){
			$this->_output[] = $this->options['indent'];
		}
	}
	public function run($content, $options = array()){
		$content = trim($content);
		$this->setOptions($options);
		if (preg_match($this->options['header-pattern'], $content, $matches)){
			$content = preg_replace($this->options['header-pattern'], "", $content);
			$md5Content = md5(serialize($options) . $content);
			if ($md5Content === $matches[1]) return false;
		}
		$analyicResult = $this->_fl_instance->analytic_js($content);
		//print_r($analyicResult);
		for ($i=0,$count=count($analyicResult);$i<$count;$i++){
			$this->_text = $analyicResult[$i][0];
			$this->_type = $analyicResult[$i][1];
			if ($this->_type === FL::FL_NEW_LINE) continue; 
			switch ($this->_type){
				case FL::JS_START_EXPR :
					$this->_beautify_start_exp();
					break;
				case FL::JS_END_EXPR :
					$this->_beautify_end_exp();
					break;
				case FL::JS_START_BLOCK:
					$this->_beautify_start_block();
					break;
				case FL::JS_END_BLOCK:
					$this->_beautify_end_block();
					break;
				case FL::JS_WORD:
					$this->_beautify_word();
					break;
				case FL::JS_EQUALS :
					$this->_beautify_equals();
					break;
				case FL::JS_SEMICOLON :
					$this->_beautify_semicolon();
					break;
				case FL::JS_STRING:
					$this->_beautify_string();
					break;
				default:
					$this->printToken();
					break;
			}
			$this->_prePreText = $this->_preText;
			$this->_preText = $this->_text;
			$this->_preType = $this->_type;
		}
		return join('', $this->_output);
	}

	
	private function _beautify_start_exp(){
		if ($this->_text === '['){
			if ($this->_preType = FL::JS_WORD || $this->_preText === ')'){
				if (in_array($this->_text, $this->_lineStarters)){
					$this->printSpace();
				}
				$this->_mode = FL::JS_MODE_EXPRESSION;
				$this->printToken();
				return ;
			}
			if ($this->_mode === FL::JS_MODE_EXPRESSION || $this->_mode === FL::JS_MODE_INDENT_EXPRESSION){
				if (($this->_prePreText === ']' && $this->_preText === ',') || $this->_preText === '['){
					if ($this->_flags['mode'] === FL::JS_MODE_EXPRESSION){
						$this->_flags['mode'] = FL::JS_MODE_INDENT_EXPRESSION;
						if (!$this->options['keep-array-indent']){
							$this->_indentDepth++;
						}
					}
					$this->_flags['mode'] = FL::JS_MODE_EXPRESSION;
					if (!$this->options['keep-array-indent']){
						$this->printNewLine();
					}
				}else {
					$this->_flags['mode'] = FL::JS_MODE_EXPRESSION;
				}
			}else {
				$this->_flags['mode'] = FL::JS_MODE_EXPRESSION;
			}
		}
		switch (true){
			case $this->_preText === ';' || $this->_preType === FL::JS_START_BLOCK :
				$this->printNewLine();
				break;
			case $this->_preType === FL::JS_END_BLOCK 
					|| $this->_preType === FL::JS_START_EXPR 
					|| $this->_preType === FL::JS_END_EXPR
					|| $this->_preText === '.' :
				break;
			case $this->_preType !== FL::JS_WORD && $this->_preType !== FL::JS_OPERATOR :
				$this->printSpace();
				break;
			case $this->_preText === 'function' :
				$this->printSpace();
				break;
			case in_array($this->_preText, $this->_lineStarters) || $this->_preText === 'catch' :
				$this->printSpace();
				break;
		}
		$this->printToken();
	}
	private function _beautify_end_exp(){
		if ($this->_preText === ']'){
			
		}
		$this->printToken();
	}
	private function _beautify_start_block(){
		if ($this->_preText === 'do'){
			$this->_flags['mode'] = FL::JS_MODE_DO_BLOCK;
		}else {
			$this->_flags['mode'] = FL::JS_MODE_BLOCK;
		}
		if ($this->options['braces-on-own-line']){
			if ($this->_preType !== FL::JS_OPERATOR){
				if ($this->_preText === 'return'){
					$this->printSpace();
				}else{
					$this->printNewLine(true);
				}
			}
			$this->printToken();
			$this->_indentDepth++;
		}else{
			if ($this->_preType !== FL::JS_OPERATOR && $this->_preType !== FL::JS_START_EXPR){
				if ($this->_preType === FL::JS_START_BLOCK){
					$this->printNewLine();
				}else{
					$this->printSpace();
				}
			}else{
				//$this->printNewLine();
			}
			$this->_indentDepth++;
			$this->printToken();
		}
	}
	private function _beautify_end_block(){
		if ($this->options['braces-on-own-line']){
			$this->printNewLine();
			$this->printToken();
		}else{
			if ($this->_preType === FL::JS_START_BLOCK){
				
			}else{
				$this->printNewLine();
			}
			$this->printToken();
		}
	}
	private function _beautify_word(){
		//刚刚是do{}的}
		if ($this->_flags['just_do_block_end']){
			$this->printSpace();
			$this->printToken();
			$this->printSpace();
			$this->_flags['just_do_block_end'] = false;
			return;
		}
		if ($this->_preType === FL::JS_WORD){
			$this->printSpace();
		}
		$this->printToken();
	}
	private function _beautify_equals(){
		$this->printSpace();
		$this->printToken();
		$this->printSpace();
	}
	/**
	 * 
	 * print ; 
	 */
	private function _beautify_semicolon(){
		$this->printToken();
		$this->_flags['var-line'] = false;
		$this->_flags['var-line-reindented'] = false;
	}
	/**
	 * print string
	 */
	private function _beautify_string(){
		if ($this->_preType === FL::JS_START_BLOCK || $this->_preType === FL::JS_END_BLOCK || $this->_preType == FL::JS_SEMICOLON){
			$this->printNewLine();
		}else if($this->_preType === FL::JS_STRING){
			$this->printSpace();
		}
		$this->printToken();
	}
}