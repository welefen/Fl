<?php
/**
 * 
 * html压缩
 * @author lichengyin
 *
 */
class Fl_Compress_Html{

	private $_output = array();
	
	private $_tokenText = '';
	
	/**
	 * 
	 * 单一标签，这些标签不需要对应的闭合标签
	 * @var array
	 */
	private $_singleTag = array(
		"br", "input", "link", "meta", "!doctype", "basefont", "base", 
		"area", "hr", "wbr", "param", "img", "isindex", "?xml", "embed"
	);
	/**
	 * 
	 * 可删除的闭合标签
	 * @var array
	 */
	private $_removeEndTag = array(
		"html", "body", "colgroup", "thead", "tr", "tbody", "td", "p", 
		"dt", "dd", "li", "option", "tfoot"
	);
	/**
	 * 
	 * 块级元素，2个块级元素之间的空格是可以删除的
	 * @var array
	 */
	private $_blockElements = array(
		'address','blockquote','center','dir','div','dl','fieldset','form',
		'h1','h2','h3','h4','h5','h6','hr','menu','noframes','noscript',
		'ol','p','pre','table','ul'
	);
	/**
	 * 
	 * 标签属性默认值,这些默认指是可以删除的
	 * @var array
	 */
	private $_tagAttrDefaultValue = array(
		'*' => array(
			'class' => '',
			'alt' => ''
		),
		'link' => array(
			'media' => 'screen'
		),
		'input' => array(
			'type' => 'text'
		),
		'form' => array(
			'method' => 'get'
		),
		'script' => array(
			'type' => 'text/javascript'
		)
	);
	
	private $_tagAttrOnlyName = array(
		'disabled','selected','checked', 'readonly', 'multiple',
	);
	
	public function __construct(){
		
	}
	public function run($content = ''){
		$analyticContent = $this->fl_instance->analytic_html($content);
		$newLine = "\n";
		for ($i=0,$count=count($analyticContent);$i<$count;$i++){
			$this->_tokenText = $analyticContent[$i][0];
			$tokenType = $analyticContent[$i][1];
			if ($tokenType === FL::FL_NEW_LINE) continue;
			switch ($tokenType){
				case FL::HTML_COMMENT : break;
				case Fl::HTML_CONTENT:
					$this->_compressTextContent();
					break;
				case FL::HTML_TAG_END :
					if (!in_array(trim(trim($this->_tokenText, '<>/')), $this->_removeEndTag)){
						$this->_output[] = $this->_tokenText;
					}
					break;
				case FL::HTML_TAG_START :
				case FL::HTML_JS_START:
					$this->_compressTag();
					break;
				case FL::HTML_CSS_CONTENT :
					$this->_compressCss();
					break;
				case FL::HTML_JS_CONTENT :
					$this->_compressJs();
					break;
				default:
					$this->_output[] = $this->_tokenText;
			}
		}
		return join('', $this->_output);
	}
	private function _compressTextContent(){
		if (strlen(trim($this->_tokenText)) == 0){
			//return '';
		}
		//如果内容部完全是空格,将多个空格合并为一个空格
		if ($this->_tokenText !== ''){
			//如果内容里还有//，有可能含有注释，则不能去掉换行
			if (strpos($this->_tokenText, '//') !== false){
				$this->_output[] = $this->_tokenText;
				return true;
			}
			$this->_tokenText = str_replace(array("\r","\n","\t"), "", $this->_tokenText);
			$this->_tokenText = preg_replace("/\s\s+/", ' ', $this->_tokenText);
			$this->_output[] = $this->_tokenText;
		}
	}
	/**
	 * compress tag
	 */
	private function _compressTag(){
		$result = $this->fl_instance->analytic_html($this->_tokenText, 2);
		if ($result[0] === FL::HTML_TAG_END){
			$this->_output[] = '</' . $result[1] . '>';
			return true;
		}
		$resultString = '<' . $result[1];
		if (count($result[2])){
			$blankSpace = ' ';
			$chPattern = $this->_tagAttrDefaultValue['*'];
			if (is_array($this->_tagAttrDefaultValue[$result[1]])){
				$chPattern = array_merge($chPattern, $this->_tagAttrDefaultValue[$result[1]]);
			}
			foreach ($result[2] as $item){
				$resultString = rtrim($resultString);
				//smarty变量
				if ($item[0] === FL::HTML_TPL_ATTR_NAME){
					$resultString .= $blankSpace . $item[1];
					continue;
				}
				//只要属性名的，属性值可以省略的
				if (in_array($item[0], $this->_tagAttrOnlyName)){
					$resultString .= $blankSpace . $item[0];
					continue;
				}
				//only attr,no value.such as "disabled"
				if (!strlen($item[1])){
					$resultString .= $blankSpace . $item[0];
					continue;
				}
				$v = $this->fl_instance->trimQuote($item[1]);
				$tv = $chPattern[$item[0]];
				//如果值在默认可去除的值范围内，则将该属性去除
				if ($v === $tv){
					continue;
				}
				//如果值内没有一些特殊字符，可以将两边的引号去除
				if (preg_match("/^[\w\-\/\:\.\?\=]+$/", $v)){
					$resultString .= $blankSpace . $item[0] . '=' . $v;
					continue;
				}
				$resultString .= $blankSpace . $item[0] . '=' . $item[1];
			}
		}
		$resultString .= '>';
		$this->_output[] = $resultString;
	}
	/**
	 * 
	 * compress css
	 * call Fl_Compress_Css class
	 */
	private function _compressCss(){
		if ($this->_tokenText){
			$content = $this->fl_instance->compress_css($this->_tokenText);
			$this->_output[] = $content;
		}
	}
	/**
	 * 
	 * compress js
	 */
	private function _compressJs(){
		if ($this->_tokenText){
			$content = $this->fl_instance->compress_js($this->_tokenText);
			$this->_output[] = $content;
		}
	}
}