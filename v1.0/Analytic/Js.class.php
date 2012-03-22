<?php
/**
 * 
 * javascript词法分析类
 * 对原内容进行分析，不做任何trim处理
 * 
 * @author lichengyin
 *
 */
class Fl_Analytic_Js{
	
	public $parsePos = 0;
	
	public $content = '';
	
	public $contentLength = 0;
	
	private $_output = array();
	
    /**
     * 空白字符
     * @var type 
     */
	static $whitespace = array(
        '\n','\r',' ','\t'
    );
	
    /**
     * 字母表
     * @var type 
     */
	static $wordchar = array(
        'a','b','c','d','e','f','g','h','i','j','k','l','m',
        'n','o','p','q','r','s','t','u','v','w','x','y','z',
        'A','B','C','D','E','F','G','H','I','J','K','L','M',
        'N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
        '0','1','2','3','4','5','6','7','8','9','_','$','.'
    );
	
    /**
     * 数字
     * @var type 
     */
	static $digits = array(
        '0','1','2','3','4','5','6','7','8','9'
    );
	
    /**
     * 操作符
     * @var type 
     */
	static $punct = array(
        '+','-','*','/','%','&','++','--','=','+=','-=',
        '*=','/=','%=','==','===','!=','!==','>','<','>=',
        '<=','>>','<<','>>>', '>>>=','>>=','<<=','&&','&=',
        '|','||','!','!!',',',':','?','^','^=','|=','::'
    );
	
    /**
     * 关键字
     * @var type 
     */
	static $keyword = array(
        "break",    "case",         "catch",    "const",    "continue",
        "default",  "delete",       "do",       "else",     "finally",
        "for",      "function",     "if",       "in",       "instanceof",
        "new",      "return",       "switch",   "throw",    "try",
        "typeof",   "var",          "void",     "while",    "with"
    );
	
    /**
     * 保留字
     * @var type 
     */
	static $reservedWord = array(
        "abstract",     "boolean",      "byte",     "char",         "class",
        "debugger",     "double",       "enum",     "export",       "extends",
        "final",        "float",        "goto",     "implements",   "import",
        "int",          "interface",    "long",     "native",       "package",
        "private",      "protected",    "public",   "short",        "static",
        "super",        "synchronized", "throws",   "transient",    "volatile"
    );
	
	
	public function __construct(){
	}
	
	public function run($content = ''){
		$this->content = $content;
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
		
		//while (in_array($char, $this->_whitespace)){
		//在数量比较小的情况下（小于5），直接判断比in_array要快一倍
		if ($char === " " || $char === "\n" || $char === "\t" || $char === "\r"){
			if ($this->parsePos >= $this->contentLength){
				return array($char, FL::FL_EOF);
			}else if ($char === "\x0d") {
				return '';	
			}else if ($char === "\x0a"){
				return array($char, FL::FL_NEW_LINE);
			}
		}
		
		//处理模板左右定界符
		$result = $this->fl_instance->getTplDelimiterToken($char, $this);
		if ($result) return $result;
		//处理正常的字符
		if (in_array($char, self::$wordchar)){
			$result = $this->_getWordToken($char);
			if ($result) return $result;
		}
		switch (true){
			case $char === '(' || $char === '[' : return array($char, FL::JS_START_EXPR);
			case $char === ')' || $char === ']' : return array($char, FL::JS_END_EXPR);
			case $char === '{' : return array($char, FL::JS_START_BLOCK);
			case $char === '}' : return array($char, FL::JS_END_BLOCK);
			case $char === ';' : return array($char, FL::JS_SEMICOLON);
		}
		//评论或者正则
		if ($char === '/'){
			//注释
			$result = $this->_getCommentToken($char);
			if ($result) return $result;
			
			//正则
			$tokenCount = count($this->_output);
			if ($tokenCount){
				list($lastText, $lastType) = $this->_output[$tokenCount - 1];
			}else {
				$lastType = FL::JS_START_EXPR;
			}
			if (($lastType === FL::JS_WORD && ($lastText === 'return' || $lastText === 'to'))
				|| ($lastType === FL::JS_START_EXPR
					|| $lastType === FL::JS_START_BLOCK
					|| $lastType === FL::JS_END_BLOCK
					|| $lastType === FL::JS_OPERATOR
					|| $lastType === FL::JS_EQUALS 
					|| $lastType === FL::JS_SEMICOLON
					|| $lastType === FL::FL_EOF
					)){
						
				$result = $this->_getRegexpToken($char);
				if ($result) return $result;
			}
		}
		//引号
		if ($char === '"' || $char === "'"){
			$result = $this->_getQuoteToken($char);
			if ($result) return $result;	
		}
		//sharp variables
		if ($char === '#'){
			$result = $this->_getSharpVariblesToken($char);
			if ($result) return $result;
		}
		//操作符
		if (in_array($char, self::$punct)){
			$result = $this->_getPunctToken($char);
			if ($result) return $result;
		}
		
		return array($char, FL::FL_NORMAL);
	}
	
	//正常的字符
	private function _getWordToken($char){
		while (in_array($this->content[$this->parsePos], self::$wordchar) 
			&& $this->parsePos < $this->contentLength){

			$char .= $this->content[$this->parsePos];
			$this->parsePos++;
		}
		//处理带E的数字，如：20010E+10,0.10E-10
		if (($this->content[$this->parsePos] === '+' || $this->content[$this->parsePos] === '-')
			&& preg_match("/^[0-9]+[Ee]$/", $char)
			&& $this->parsePos < $this->contentLength){
				
			$sign = $this->content[$this->parsePos];
			$this->parsePos++;
			$t = $this->getNextToken();
			$char .= $sign . $t[0];
			return array($char, FL::JS_WORD);
		}
		//for in operator
		if ($char === 'in'){
			return array($char , FL::JS_OPERATOR);
		}
		return array($char, FL::JS_WORD);
	}
	//注释
	private function _getCommentToken($char){
		$comment = '';
		$lineComment = true;
		$c = $this->content[$this->parsePos];
		//单行或者多行注释
		if($c === '*'){
			$this->parsePos++;
			while (!($this->content[$this->parsePos] === '*' 
					&& $this->content[$this->parsePos + 1] 
					&& $this->content[$this->parsePos + 1] === '/') 
					&& $this->parsePos < $this->contentLength){
						
				$cc = $this->content[$this->parsePos];
				$comment .= $cc;
				//\x0d为\r, \x0a为\n
				if ($cc === "\x0d" || $cc === "\x0a"){
					$lineComment = false;
				}
				$this->parsePos++;
			}
			
			$this->parsePos += 2;
			//ie下的条件编译
			if (strpos($comment, '@cc_on') === 0){
				return array('/*'.$comment.'*/', FL::JS_IE_CC);
			}
			if ($lineComment){
				return array('/*'.$comment.'*/', FL::JS_INLINE_COMMENT);
			}else{
				return array('/*'.$comment.'*/', FL::JS_BLOCK_COMMENT);
			}
		}
		//单行注释
		if ($c === '/'){
			$comment = $char;
			//\x0d为\r, \x0a为\n
			while ($this->content[$this->parsePos] !== "\x0d" 
					&& $this->content[$this->parsePos] !== "\x0a"
					&& $this->parsePos < $this->contentLength){
				
				$comment .= $this->content[$this->parsePos];
				$this->parsePos++;
			}
			return array($comment, FL::JS_COMMENT);
		}
	}
	//引号
	private function _getQuoteToken($char){
		$sep = $char;
		$escape = false;
		$resultString = $char;
		while ($this->content[$this->parsePos] !== $sep || $escape){
			//引号里含有smarty语法，smarty语法里含有引号
			$result = $this->fl_instance->getTplDelimiterToken($char, $this);
			if($result){
				$resultString .= substr($result[0], 1);
			}else{
				$resultString .= $this->content[$this->parsePos];
				$escape = !$escape ? ($this->content[$this->parsePos] === "\\") : false;
				$this->parsePos++;
			}
			if ($this->parsePos >= $this->contentLength){
				return array($resultString, FL::JS_STRING);
			}
		}
		$this->parsePos++;
		$resultString .= $sep;
		return array($resultString, FL::JS_STRING);
	}
	//正则
	private function _getRegexpToken($char){
		$sep = $char;
		$escape = false;
		$resultString = $char;
		$inCharClass = false;
		while ($escape || $inCharClass || $this->content[$this->parsePos] !== $sep){
			$resultString .= $this->content[$this->parsePos];
			if (!$escape){
				$escape = ($this->content[$this->parsePos] === "\\");
				if ($this->content[$this->parsePos] === '['){
					$inCharClass = true;
				}else if($this->content[$this->parsePos] === ']'){
					$inCharClass = false;
				}
			}else {
				$escape = false;
			}
			$this->parsePos++;
			if ($this->parsePos >= $this->contentLength){
				return array($resultString, FL::JS_REGEXP);
			}
		}
		$this->parsePos++;
		$resultString .= $sep;
		while (in_array($this->content[$this->parsePos], self::$wordchar) 
			&& $this->parsePos < $this->contentLength ) {
				
			$resultString .= $this->content[$this->parsePos];
			$this->parsePos++;
		}
		return array($resultString, FL::JS_REGEXP);
	}
	//sharp varibles
	private function _getSharpVariblesToken($char){
		$sharp = $char;
		if (in_array($this->content[$this->parsePos], self::$digits)){
			do{
				$c = $this->content[$this->parsePos];
				$sharp .= $c;
				$this->parsePos++;
			}while ($c !== '#' && $c !== '=' && $this->parsePos < $this->contentLength);
			$next = substr($this->content, $this->parsePos, 2);
			if ($next === '[]' || $next === '{}'){
				$sharp .= $next;
				$this->parsePos += 2;
			}
			return array($sharp, FL::JS_WORD);
		}
	}
	//操作符
	private function _getPunctToken($char){
		while (in_array($char . $this->content[$this->parsePos], self::$punct) 
			&& $this->parsePos < $this->contentLength){
				
			$char .= $this->content[$this->parsePos];
			$this->parsePos++;
		}
		return array($char, $char === '=' ? FL::JS_EQUALS : FL::JS_OPERATOR);
	}
}