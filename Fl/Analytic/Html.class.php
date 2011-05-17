<?php
/**
 * 
 * html词法分析类
 * @author lichengyin
 *
 */
class Fl_Analytic_Html{
	
	/**
	 * 
	 * 当前解析到的位置
	 * @var int
	 */
	public $parsePos = 0;
	
	/**
	 * 
	 * 要解析的内容
	 * @var string
	 */
	public $content = '';
	/**
	 * 
	 * 要解析的内容长度
	 * @var int
	 */
	public $contentLength = 0;
	
	/**
	 * 
	 * 单个标签
	 * @var array
	 */
	public $singleTag = array(
		"br", "input", "link", "meta", "!doctype", "basefont", "base", 
		"area", "hr", "wbr", "param", "img", "isindex", "?xml", "embed"
	);
	/**
	 * 
	 * 解析后的token存放处
	 * @var array
	 */
	private $_output = array();
	
	public function __construct(){
		
	}
	/**
	 * 
	 * 默认是进行html分析
	 * type不为1的时候进行tag属性分析
	 * @param string $content
	 * @param int $type
	 */
	public function run($content = '', $type = 1){
		$this->content = str_replace("\r\n", "\n", trim($content));
		if (stripos($this->content, '<?xml') !== false){
			return array(array($content, FL::HTML_XML));
		}
		$this->contentLength = strlen($this->content);
		if ($type === 1){
			$this->tokenAnalytic();
			return $this->_output;
		}
		return $this->getTagAttributes($content);
	}
	/**
	 * 
	 * 使用特征值进行分析
	 */
	public function tokenAnalytic(){
		while (true){
			$token = $this->getNextToken();
			if ($token){
				if ($token[1] === FL::FL_EOF) break;
				$this->_output[] = $token;
			}
		}
	}
	/**
	 * 
	 * 解析下一个特征值
	 */
	public function getNextToken(){
		if ($this->parsePos >= $this->contentLength){
			return array('', FL::FL_EOF);
		}
		$char = $this->content[$this->parsePos];
		$this->parsePos++;
		$outputCount = count($this->_output);
		if ($outputCount){
			$tokenType = $this->_output[$outputCount - 1][1];
			if ( $tokenType === FL::HTML_JS_START){
				$result = $this->_getScriptORSTYLE($char, 1);
				if ($result) return $result;
			}elseif ($tokenType === FL::HTML_CSS_START){
				$result = $this->_getScriptORSTYLE($char, 2);
				if ($result) return $result;
			}
		}
		if ($char === "\x0d") return ''; // \r
		if ($char === "\x0a"){
			return array($char, FL::FL_NEW_LINE);
		}
		//处理模板左右定界符
		$result = $this->fl_instance->getTplDelimiterToken($char, $this);
		if ($result) return $result;
		//处理pre标签，pre标签里任何内容都直接通过，不做任何处理
		if ($this->_checkEqual($this->content, $this->parsePos - 1, 4, '<pre')){
			$this->parsePos += 3;
			$result = $this->_getPreTagToken();
			if ($result) return $result;
		}
		//处理textarea标签，textarea标签里任何内容都直接通过，不做任何处理
		if ($this->_checkEqual($this->content, $this->parsePos - 1, 9, '<textarea')){
			$this->parsePos += 8;
			$result = $this->_getTextareaTagToken();
			if ($result) return $result;
		}
		//处理一般性的标签,当前字符为<并且下一个字符不为<
		if ($char === '<' && $this->content[$this->parsePos] !== '<'){
			$result = $this->_getTagToken($char);
			if ($result) return $result;
		}
		$result = $this->_getContentToken($char);
		if ($result) return $result;
		return array($char, FL::FL_NORMAL);
	}
	//标签
	private function _getTagToken($char){
		$resultString = $char;
		do {
			if ($this->parsePos >= $this->contentLength){
				break;
			}
			$char = $this->content[$this->parsePos];
			$this->parsePos++;
			$result = $this->fl_instance->getTplDelimiterToken($char, $this);
			if ($result){
				$resultString .= $result[0];
			}else {
				if ($char === '"' || $char === "'"){
					if ($resultString[1] !== '!'){
						$resultString .= $char;
						$resultString .= $this->_getUnformated($char);
					}
				}else {
					$resultString .= $char;
				}
			}
		}while ($char !== '>');
		//注释或者ie hack
		if ($resultString[1] === '!'){
			if (strpos($resultString, '[if') !== false){
				if (strpos($resultString, '!IE') !== false){
					$resultString .= $this->_getUnformated('-->', $resultString);
				}
				return array($resultString, FL::HTML_IE_HACK_START);
			}elseif (strpos($resultString, '[endif') !== false){
				return array($resultString, FL::HTML_IE_HACK_EDN);
			}elseif ($this->_checkEqual($resultString, 2, 7, 'doctype')){
				return array($resultString, FL::HTML_DOC_TYPE);
			}else if($this->_checkEqual($resultString, 4, 6, 'status')){
				$resultString .= $this->_getUnformated('-->', $resultString);
				return array($resultString, FL::HTML_STATUS_OK);
			}else {
				$resultString .= $this->_getUnformated('-->', $resultString);
				return array($resultString, FL::HTML_COMMENT);
			}
		}
		if ($this->_checkEqual($resultString, 0, 7, '<script')){
			return array($resultString, FL::HTML_JS_START);
		}else if ($this->_checkEqual($resultString, 0, 9, '</script>')){
			return array($resultString, FL::HTML_JS_END);
		}elseif ($this->_checkEqual($resultString, 0, 6, '<style')){
			return array($resultString, FL::HTML_CSS_START);
		}elseif ($this->_checkEqual($resultString, 0, 8, '</style>')){
			return array($resultString, FL::HTML_CSS_END);
		}
		if ($this->_checkEqual($resultString, 0, 2, '</')){
			return array($resultString, FL::HTML_TAG_END);
		}
		return array($resultString, FL::HTML_TAG_START);
	}
	/**
	 * 
	 * 检测一个字符串的截取部分是否等于一个特定的字符串
	 * @param string $str
	 * @param int $start
	 * @param int $len
	 * @param string $result
	 */
	private function _checkEqual($str, $start, $len, $result){
		return strtolower(substr($str, $start, $len)) === strtolower($result);
	}
	//pre标签
	private function _getPreTagToken(){
		$resultString = '<pre';
		while ($this->parsePos < $this->contentLength){
			if (strtolower(substr($this->content, $this->parsePos, 6)) === '</pre>'){
				$resultString .= '</pre>';
				$this->parsePos += 6;
				break;
			}else{
				$resultString .= $this->content[$this->parsePos];
				$this->parsePos++;
			}
		}
		return array($resultString, FL::HTML_PRE_TAG);
	}
	//textarea标签
	private function _getTextareaTagToken(){
		$resultString = '<textarea';
		while ($this->parsePos < $this->contentLength){
			if (strtolower(substr($this->content, $this->parsePos, 11)) === '</textarea>'){
				$resultString .= '</textarea>';
				$this->parsePos += 11;
				break;
			}else{
				$resultString .= $this->content[$this->parsePos];
				$this->parsePos++;
			}
		}
		return array($resultString, FL::HTML_TEXTAREA_TAG);
	}
	/**
	 * 
	 * 解析文本节点
	 * @param string $char
	 */
	private function _getContentToken($char){
		$resultString = $char;
		while (true){
			if ($this->parsePos >= $this->contentLength){
				break;
			}
			//增加对<a href=""><<<</a>的兼容，此时内容为<<<
			if ($this->content[$this->parsePos] === '<' 
				&& $this->content[$this->parsePos+1] 
				&& $this->content[$this->parsePos+1] !== '<' && $this->content[$this->parsePos+1] !== '>'){
				break;
			}
			$resultString .= $this->content[$this->parsePos];
			$this->parsePos++;
		}
		return array($resultString, FL::HTML_CONTENT);
	}
	//获取需要的字符
	private function _getUnformated($char, $orign = ''){
		if (strpos($orign, $char) !== false) return '';
		$resultString = '';
		do {
			if ($this->parsePos >= $this->contentLength){
				break;
			}
			$c = $this->content[$this->parsePos];
			$resultString .= $c;
			$this->parsePos++;
		}while (strpos($resultString, $char) === false);
		//增加一个字符的容错机制,如：value="""，这里一不小心多写了个引号
		if (strlen($char) === 1){
			while ($char === $this->content[$this->parsePos]){
				$resultString .= $this->content[$this->parsePos];
				$this->parsePos++;
			}
		}
		return $resultString;
	}
	//获取script或者style里的内容
	private function _getScriptORSTYLE($char, $type = 1){
		$tokenText = $type == 1 ? '</script>' : '</style>';
		$tokenLength = strlen($tokenText);
		if (strtolower(substr($this->content, $this->parsePos - 1, $tokenLength)) === $tokenText){
			return '';
		}
		$resultString = $char;
		while ($this->parsePos < $this->contentLength){
			if (strtolower(substr($this->content, $this->parsePos, $tokenLength)) === $tokenText){
				break;
			}else {
				$resultString .= $this->content[$this->parsePos];
				$this->parsePos++;
			}
		}
		$resultString = trim($resultString);
		$startEscape = array('<!--', '/*<![CDATA[*/', '//<![CDATA[');
		$endEscape = array('//-->', '/*]]>*/', '//]]>');
		foreach ($startEscape as $escape){
			if (strpos($resultString, $escape) === 0){
				$resultString = substr($resultString, strlen($escape));
				break;
			}
		}
		foreach ($endEscape as $escape){
			if (strrpos($resultString, $escape) === (strlen($resultString) - strlen($escape))){
				$resultString = substr($resultString, 0, strlen($resultString) - strlen($escape));
				break;
			}
		}
		return array(trim($resultString), $type === 1 ? FL::HTML_JS_CONTENT : FL::HTML_CSS_CONTENT);
	}
	/**
	 * 
	 * 分析tag标签的属性名和属性值
	 * 一个完整的tag里可能包含换行符
	 * @param string $tagContent
	 */
	public function getTagAttributes($tagContent = ''){
		//tag end
		$tagContent = trim($tagContent);
		//将还行符替换为空格
		$tagContent = str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $tagContent);
		if (substr($tagContent, 0, 2) === '</') {
			return array(
				FL::HTML_TAG_END, 
				trim(substr($tagContent, 2, strlen($tagContent) - 3))
			);
		}
		//tag start
		$result = array(FL::HTML_TAG_START, '', array());
		//将最后的>和/去掉, 最多只能去一个，因为smarty的右定界符可能含有/和>
		$tagContent = trim(substr($tagContent, 0, strlen($tagContent) - 1));
		if ($tagContent[strlen($tagContent) - 1] === '/'){
			$right_delimiter = $this->fl_instance->right_delimiter;
			$lastChars = substr($tagContent, strlen($tagContent) - strlen($right_delimiter) - 1);
			if ($right_delimiter != $lastChars){
				$tagContent = trim(substr($tagContent, 0, strlen($tagContent) - 1));
			}
		}
		$this->parsePos = 1;
		$this->content = $tagContent;
		$this->contentLength = strlen($tagContent);
		$tagName = '';
		while (true){
			if ($this->parsePos >= $this->contentLength){
				break;
			}
			$char = $this->content[$this->parsePos];
			$this->parsePos++;
			if (!preg_match("/^[a-z0-9]{1}$/", $char)){
				$this->parsePos--;
				break;
			}else{
				$tagName .= $char;
			}
		}
		//get tag name
		$result[1] = $tagName;
		$attr = $name = '';
		while (true){
			if ($this->parsePos >= $this->contentLength){
				break;
			}
			$char = $this->content[$this->parsePos];
			$this->parsePos++;
			$re = '';
			//处理href=<&$spDomain&>/sys/aa, 值左右没有引号但含有smarty变量的情况
			if (!$name){
				$re = $this->fl_instance->getTplDelimiterToken($char, $this);
			}
			if ($re){
				//处理这种情况：<&if $disabled&>disabled<&/if&>
				if ($attr){
					$result[2][] = array($attr, '');
					$attr = '';
				}
				$result[2][] = array(FL::HTML_TPL_ATTR_NAME, $re[0]);
			}else if ($char === '"' || $char === "'"){
				$re = $char . $this->_getUnformated($char);
				$result[2][] = array($name, $re);
				$name = $re = '';
			}else if ($char === '='){
				if ($attr){
					$name = $attr;
				}else {
					//处理key为smarty变量：<&key&>="<&$value&>"
					$preItem = $result[2][count($result[2]) - 1];
					if ($preItem[0] === FL::HTML_TPL_ATTR_NAME){
						$name = $preItem[1];
						array_pop($result[2]);
					}
				}
				$attr = '';
			}else if ($char === ' '){
				if ($attr){
					if ($name){
						$result[2][] = array($name, $attr);
					}else{
						$result[2][] = array($attr, '');
					}
				}
				$name = $attr = '';
			}else{
				if ($char !== ' ') $attr .= $char;
			}
		}
		if ($attr){
			if ($name){
				$result[2][] = array($name, $attr);
			}else{
				$result[2][] = array($attr, '');
			}
			$name = $attr = '';
		}
		return $result;
	}
}
