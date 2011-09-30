<?php
/*!
 * CSS Beautify Library
 * 
 * 
 * @author lichengyin
 *
 */
class Fl_Beautify_css{
	
	public $options = array();
	
	private $_indentDepth = 0;
	
	public function setOptions($options){
		$options = array_merge(array(
			'indent' => "    ", 	//缩进
			'header' => "/*@Beautify at ".date('Y-m-d H:i:s', time()).":md5({md5})*/",
			'header-pattern' => "/\/\*\@Beautify\sat.*?\:md5\(([a-f0-9]{32})\)\*\//ies",
			'header-var' => '~!header!~'
		), $options);
		$this->options = $options;
	}
	public function run($content, $options = array()){
		$content = trim($content);
		$this->setOptions($options);
		if (preg_match($this->options['header-pattern'], $content, $matches)){
			$content = preg_replace($this->options['header-pattern'], "", $content);
			$md5Content = md5(serialize($options) . $content);
			if ($md5Content === $matches[1]) return false;
		}
		$analyicResult = $this->fl_instance->analytic_css($content);
		$ouput = '';
		$newLineText = "\n";
		$totalLine = 0;
		$addHeader = false;
		$lastType = '';
		for ($i=0,$count=count($analyicResult);$i<$count;$i++){
			list($tokenText, $tokenType) = $analyicResult[$i];
			$tokenText = trim($tokenText);
			if ($tokenType === FL::FL_NEW_LINE){
				$lastType = $tokenType;
				continue;
			}
			if ($tokenType === FL::CSS_AT){
				$output .= $tokenText . $newLineText;
			}else {
				if (!$addHeader){
					$output .= $this->options['header-var'];
					$addHeader = true;
				}
				switch ($tokenType){
					case FL::CSS_DEVICE_START :
					case FL::CSS_SELECTOER_START :
						$output .= $tokenText;
						$this->_indentDepth++;
						break;
					case FL::CSS_DEVICE_END :
					case FL::CSS_SELECTOER_END :
						$this->_indentDepth--;
						$output .=  $newLineText . $this->getIndent() . $tokenText ;
						break;
					case FL::CSS_PROPERTY:
						$output .= $newLineText . $this->getIndent() . $tokenText ;
						break;
					case FL::CSS_VALUE;
						$output .= ' ' . $tokenText ;
						break;
					case FL::CSS_DEVICE_DESC:
						$output .= $newLineText . $this->getIndent() . $tokenText . ' ';
						break;
					case FL::CSS_SELECTOER :
						$result = $this->fl_instance->splitCssSelector($tokenText);
						$s = $newLineText . $this->getIndent() ;
						$s .= join(',' . $newLineText . $this->getIndent(), $result);
						$output .= $s . ' ';
						break;
					case FL::CSS_COMMENT :
					case FL::FL_TPL_DELIMITER :
						if ($lastType === FL::FL_NEW_LINE){
							$output .= $newLineText . $this->getIndent() . $tokenText;
						}elseif($lastType === FL::CSS_SELECTOER_END || $lastType === FL::CSS_DEVICE_END){
							$output .=  $tokenText;
						}else{
							$output .= ' ' . $tokenText;
						}
						break;
					default: 
						if ($tokenText){
							$output .= $this->getIndent() . $tokenText;
						}
				}
			}
			$lastType = $tokenType;
		}
		$md5Content = md5(str_replace($this->options['header-var'], '', $output));
		$this->options['header'] = str_replace("{md5}", $md5Content, $this->options['header']);
		$output = str_replace($this->options['header-var'], $this->options['header'], $output);
		
		return $output;
	}
	/**
	 * 
	 * get indent string
	 */
	public function getIndent(){
		if ($this->_indentDepth < 0) return  '';
		return str_repeat($this->options['indent'], $this->_indentDepth);
	}
}