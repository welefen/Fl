<?php
/**
 * 
 * js压缩，只做去除换行和空格功能。
 * 主要是用作行内压缩
 * @author lichengyin
 *
 */
class Fl_Compress_Js{
	
	private $_fl_instance = null;
	
	private static $_instance = null;
	
	private $_output = array();
	
	private $_preType = 0;
	
	private $_preText = '';
	
	private $_punct = array();
	
	public function setOptions(){
		$this->_fl_instance = FL::getInstance();
		$this->_output = array();
		$this->_preType = 0;
		$this->_preText = '';
		$this->_punct = split(' ', '+ - * / % & ++ -- = += -= *= /= %= == === != !== > < >= <= >> << >>> >>>= >>= <<= && &= | || ! !! , : ? ^ ^= |= ::');
	}
	public function run($content = ''){
		return $content;
		$this->setOptions();
		$analyticContent = $this->_fl_instance->analytic_js($content);
		$space = ' ';
		for ($i=0,$count=count($analyticContent);$i<$count;$i++){
			list($tokenText, $tokenType) = $analyticContent[$i];
			switch ($tokenType){
				case FL::JS_COMMENT:break;
				case FL::JS_BLOCK_COMMENT:break;
				case FL::JS_INLINE_COMMENT:break;
				case FL::FL_NEW_LINE:
					if (!in_array($this->_preText, array(';','{','}','[')) && !in_array($this->_preText, $this->_punct)){
						$nextTokenText = $analyticContent[$i+1][0];
						//上一个不为;且下一个不为.
						if ($analyticContent[$i+1] && $analyticContent[$i+1][0] !== '.' &&  $analyticContent[$i+1][0] !== ']' && $analyticContent[$i+1][0] !== ',' && $this->_preText !== ';'){
							$this->_output[] = ';';
							$tokenText = ';';
							$tokenType = FL::JS_SEMICOLON;
						}
					}
					break;
				case FL::JS_WORD:
					if ($this->_preType === FL::JS_WORD || $this->_preText === 'in'){
						$this->_output[] = $space . $tokenText;
					}else{
						if ($tokenText === 'var' && count($this->_output) && $this->_preText !== '{' && $this->_preText !== '('){
							$this->_output[] = ';';
						}
						if ($tokenText !== 'var' && $tokenText != 'else' && $tokenText != 'while' && $tokenText != 'catch' && $tokenText != 'finally' && $this->_output[count($this->_output)-1] === '}'){
							$this->_output[] = ';';
						}
						$this->_output[] = $tokenText;
					}
					break;
				case FL::JS_OPERATOR:
					if ($tokenText === 'in'){
						$this->_output[] = $space . $tokenText;
					}else{
						$this->_output[] = $tokenText;
					}
					break;
				case FL::JS_END_BLOCK:
					//这里不能使用$this->_preText;
					if ($this->_output[count($this->_output)-1] === ';'){
						array_pop($this->_output);
					}
					$this->_output[] = $tokenText;
					break;
				case FL::JS_SEMICOLON:
					if ($this->_output[count($this->_output)-1] !== ';'){
						$this->_output[] = $tokenText;
					}
					break;
				default:
					$this->_output[] = $tokenText;
			}
			if (!in_array($tokenType, array(FL::JS_COMMENT, FL::JS_BLOCK_COMMENT, FL::JS_INLINE_COMMENT, FL::FL_NEW_LINE))){
				$flag = true;
				if ($tokenType === FL::FL_TPL_DELIMITER){
					//检测是否是输出的smarty变量
					$pattern = "/^".preg_quote($this->_fl_instance->left_delimiter, "/") . "\s*\$.*/is";
					if (!preg_match($pattern, $tokenText)){
						$flag = false;
					}
				}
				if ($flag){
					$this->_preType = $tokenType;
					$this->_preText = $tokenText;
				}
			}
		}
		if ($this->_output[count($this->_output)-1] === ';'){
			array_pop($this->_output);
		}
		return join('', $this->_output);
	}
}