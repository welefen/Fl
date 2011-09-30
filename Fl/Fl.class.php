<?php
/**
 * font-end language,such as html,javscript,css
 * contain: code analytic, code beautify, code validate, code compress
 * support smarty delimiter.
 * 2010-11-20
 * @author lichengyin
 *
 */
class Fl {
	const FL_EOF 						= 1;   //结束
	const FL_TPL_DELIMITER 				= 2;   //模板语法
	const FL_NEW_LINE 					= 3;   //new line
	const FL_NORMAL 					= 4;   //normal,一般很少出现这个
	
	const HTML_CONTENT 					= 111; //文本
	const HTML_TAG 						= 112; //一般标签
	const HTML_JS_START 				= 113; //js start
	const HTML_JS_CONTENT 				= 114; //js content,要手工调用js analytic
	const HTML_JS_END 					= 115; //js end
	const HTML_CSS_START 				= 116; //css start
	const HTML_CSS_CONTENT 				= 117; //css content,要手工调用css analytic
	const HTML_CSS_END 					= 118; //css end
	const HTML_IE_HACK_START 			= 119; //ie hack start
	const HTML_IE_HACK_EDN 				= 120; //ie hack end
	const HTML_DOC_TYPE 				= 121; //doc type
	const HTML_COMMENT 					= 122; //html comment
	const HTML_PRE_TAG 					= 123; //pre tag
	const HTML_STATUS_OK				= 124; //status ok
	const HTML_TEXTAREA_TAG				= 125; //textarea tag
	const HTML_TAG_START				= 126; //tag start
	const HTML_TAG_END					= 127; //tag end
	const HTML_TPL_ATTR_NAME			= 128; //tpl attributes name
	const HTML_XML						= 129; //is xml
	
	const JS_START_EXPR 				= 211; //start expression
	const JS_END_EXPR 					= 212; //end expression
	const JS_START_BLOCK 				= 213; //start block
	const JS_END_BLOCK 					= 214; //end block
	const JS_SEMICOLON 					= 215; //分号
	const JS_WORD						= 216; //单词
	const JS_OPERATOR					= 217; //操作符
	const JS_EQUALS						= 218; //等号
	const JS_INLINE_COMMENT				= 219; //行内注释
	const JS_BLOCK_COMMENT				= 220; //跨级注释
	const JS_COMMENT					= 221; //注释
	const JS_STRING						= 222; //字符串	
	const JS_IE_CC						= 223; //条件编译	
	const JS_REGEXP						= 224; //正则
	
	const JS_MODE_EXPRESSION			= 250; //
	const JS_MODE_INDENT_EXPRESSION		= 251; //
	const JS_MODE_DO_BLOCK				= 252; //
	const JS_MODE_BLOCK					= 253; //
	const JS_MODE_ARRAY					= 254;
	

	const CSS_AT						= 311; //@
	const CSS_NORMAL					= 312; //
	const CSS_DEVICE_DESC				= 313; //设备描述内容
	const CSS_DEVICE_START				= 314; //设备开始符,为{
	const CSS_DEVICE_END				= 315; //设备结束符，为}
	const CSS_SELECTOER					= 316; //选择器
	const CSS_SELECTOER_START			= 317; //选择器开始符，为{
	const CSS_SELECTOER_END				= 318; //选择器结束符，为}
	const CSS_COMMENT					= 319; //注释
	const CSS_PROPERTY					= 320; //属性
	const CSS_VALUE						= 321; //值
	const CSS_COLON						= 322; //冒号
	const CSS_SEMICOLON					= 323; //分号
	const CSS_WHITESPACE				= 324; //空白（\s+）
	
	
	public $left_delimiter = "<&";
	
	public $right_delimiter = "&>";
	
	protected $_leftDelimiterLen = 0;
	
	protected $_rightDelimiterLen = 0;
	
	private static $_instance = null;
	
	private $_cache = array();
	
	public static function getInstance(){
		if(self::$_instance === null){
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	/**
	 * 
	 * 解析tpl
	 * @param string $char
	 * @param object $instance
	 */
	public function getTplDelimiterToken($char, &$instance){
		if (!$this->left_delimiter || !$this->right_delimiter) return '';
		if ($this->_leftDelimiterLen === 0){
			$this->_leftDelimiterLen = strlen($this->left_delimiter);
			$this->_rightDelimiterLen = strlen($this->right_delimiter);
		}
		$leftDelimiterLen = $this->_leftDelimiterLen;
		$rightDelimiterLen = $this->_rightDelimiterLen;
		$escape = false;
		$resultString = '';
		if (substr($instance->content, ($instance->parsePos - 1), $leftDelimiterLen) === $this->left_delimiter){
			$resultString .= $this->left_delimiter;
			$instance->parsePos += $leftDelimiterLen - 1;
			$delimiterNum = 1;
			while (true){
				if (!$escape){
					$escape = ($instance->content[$instance->parsePos] === "\\");
				}else {
					$escape = false;
				}
				if (!$escape && substr($instance->content, $instance->parsePos, $rightDelimiterLen) === $this->right_delimiter){
					$resultString .= $this->right_delimiter;
					$instance->parsePos += $rightDelimiterLen;
					if ($delimiterNum === 1){
						break;
					}else{
						$delimiterNum -= 1;
					}
				}else{
					if (substr($instance->content, $instance->parsePos, $leftDelimiterLen) === $this->left_delimiter){
						$resultString .= $this->left_delimiter;
						$instance->parsePos += $leftDelimiterLen;
						$delimiterNum += 1;
					}else {
						$resultString .= $instance->content[$instance->parsePos];
						$instance->parsePos++;
					}
				}
				if ($instance->parsePos >= $instance->contentLength){
					break;
				}
			}
			return array($resultString, FL::FL_TPL_DELIMITER);
		}
	}
	/**
	 * 去除字符串左右的引号
	 * @param string $value
	 */
	public function trimQuote($value){
		$quote = substr($value, 0, 1);
		if ($quote === '"' || $quote === "'"){
			return trim($value, $quote);
		}
		return $value;
	}
	
	/**
	 * 
	 * 切分css的selector
	 * @param string $selector
	 */
	public function splitCssSelector($selector){
		$selector = split(',', $selector);
		$result = array();
		for ($i = 0, $count=count($selector); $i < $count; $i++) {
			$item = trim($selector[$i]);
			if ($item){
				$result[] = $item;
			}
		}
		return $result;
	}

	/**
	 * 根据分析结果重新拼接内容
	 */
	public function joinAnalyticResultToContent($analyticResult = array()){
		$content_output = array();
		foreach($analyticResult as $key => $item){
			$content_output[] = $item[0];
		}
		return join('',$content_output);
	}

	public function __call($method, $args){
		$m = ucwords(strtolower(str_replace('_', ' ', $method))); 
		$method = str_replace(' ', '_', ($m));
		if (stripos($method, 'analytic_') !== false){
			$md5 = md5($method . join("&^", $args));
			if ($this->_cache[$md5]) return $this->_cache[$md5];
		}
		
		$className = 'Fl_' . $method;
		if (!class_exists($className)){
			require_once dirname(__FILE__) . '/' . str_replace(' ', '/', $m) . '.class.php';
		}
		$classInstance = new $className;
		$classInstance->fl_instance = $this; //设置fl_instance属性的值
		//如果仅有一个参数并且值为false,则返回值实例化对象，不执行run方法
		if (count($args) === 1 && $args[0] === false){
			return $classInstance;
		}
		$result = call_user_func_array(array($classInstance , 'run'), $args);
		if (stripos($method, 'analytic_') !== false){
			$this->_cache[$md5] = $result;
		}
		return $result;
	}
}