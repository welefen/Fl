<?php
/**
 * Javascript代码解析
 * 
 * @author zhaoxianlie
 */
class Fl_Analytic_Javascript {
    
    /**
     * 关键字
     * @var type 
     */
	static $_KEYWORDS = array(
        "break",    "case",         "catch",    "const",    "continue",
        "default",  "delete",       "do",       "else",     "finally",
        "for",      "function",     "if",       "in",       "instanceof",
        "new",      "return",       "switch",   "throw",    "try",
        "typeof",   "var",          "void",     "while",    "with",
        "false",    "true",         "null",     "undefined"
    );
	
    /**
     * 保留字
     * @var type 
     */
	static $_RESERVED_WORDS = array(
        "abstract",     "boolean",      "byte",     "char",         "class",
        "debugger",     "double",       "enum",     "export",       "extends",
        "final",        "float",        "goto",     "implements",   "import",
        "int",          "interface",    "long",     "native",       "package",
        "private",      "protected",    "public",   "short",        "static",
        "super",        "synchronized", "throws",   "transient",    "volatile"
    );
    
    /**
     * 能在表达式之前出现的关键字
     * @var type 
     */
    static $_KEYWORDS_BEFORE_EXPRESSION = array(
        "return",   "new",  "delete",   "throw",    "else",     "case"
    );
    
    /**
     * Javascript语言原子关键字
     * @var type 
     */
    static $_KEYWORDS_ATOM = array(
        "false",    "true",     "null",     "undefined"
    );
    
    /**
     * 操作符基本元素
     * @var type 
     */
    static $_OPERATOR_CHARS = array(
        "+",    "-",    "*",    "&",    "%",    "=",    "/",
        "<",    ">",    "!",    "?",    "|",    "~",    "^"
    );
    
    /**
     * 数字正则
     * @var type 
     */
    static $_RE_DIGIT = "/\d/";
    /**
     * 16进制数正则
     * @var type 
     */
    static $_RE_HEX_NUMBER = "/^0x[0-9a-f]+$/i";
    /**
     * 8进制数正则
     * @var type 
     */
    static $_RE_OCT_NUMBER = "/^0[0-7]+$/";
    /**
     * 10进制数正则
     * @var type 
     */
    static $_RE_DEC_NUMBER = "/^\d*\.?\d*(?:e[+-]?\d*(?:\d\.?|\.?\d)\d*)?$/i";
    /**
     * 操作符
     * @var type 
     */
    static $_OPERATORS = array(
        "++",   "--",    "+",    "-",     "!",     "~",     "&",    "|",     
        "^",    "*",     "/",    "%",     ">>",    "<<",    ">>>",  "<",    
        ">",    "<=",    ">=",   "==",    "===",   "!=",    "!==",  "?",
        "=",    "+=",    "-=",   "/=",    "*=",    "%=",    ">>=",  "<<=",   
        ">>>=", "|=",    "^=",   "&=",    "&&",    "||",    "in",       
        "instanceof",    "typeof",   "new",  "void",     "delete",
    );
    
    /**
     * 空白符
     * @var type 
     */
    static $_WHITESPACE_CHARS = array(
        "\n",   "\r",   "\t",   "\f",   "\v",   " ",    "　"
    );
    
    /**
     * 允许在表达式之前出现的标点或符号
     * @var type 
     */
    static $_PUNC_BEFORE_EXPRESSION = array(
        "[",    "{",    "}",    "(",    ",",    ".",    ";",    ":"
    );
    
    /**
     * 标点符号
     * @var type 
     */
    static $_PUNC_CHARS = array(
        "[",    "]",    "{",    "}",    "(",    ")",    ",",    ";",    ":"
    );
    
    /**
     * 允许在正则表达式之后出现的修饰符
     * @var type 
     */
    static $_REGEXP_MODIFIERS = array(
        "g",    "m",    "s",    "i",    "y"
    );
    
    /**
     * 一元前置运算符
     * @var type 
     */
    static $_UNARY_PREFIX = array(
        "typeof",   "void",     "delete",   "--",
        "++",       "!",         "~",       "-",    "+"
    );
    
    /**
     * 一元后置运算符
     * @var type 
     */
    static $_UNARY_POSTFIX = array(
        "--",       "++" 
    );
    
    /**
     * 赋值运算符
     * @var type 
     */
    static $_ASSIGNMENT = array(
        "="     => true,
        "%="    => "%",
        "&="    => "&",
        "*="    => "*",
        "+="    => "+",
        "-="    => "-",
        "/="    => "/",
        "<<="   => "<<",
        ">>="   => ">>",
        ">>>="  => ">>>",
        "^="    => "^",
        "|="    => "|"
    );
    
    /**
     * 优先级顺序
     * @var type 
     */
    static $_PRECEDENCE = array(
        "*"             => 10,
        "/"             => 10,
        "%"             => 10,
        "+"             => 9,
        "-"             => 9,
        "<<"            => 8,
        ">>"            => 8,
        ">>>"           => 8,
        "<="            => 7,
        "<"             => 7,
        ">"             => 7,
        ">="            => 7,
        "in"            => 7,
        "instanceof"    => 7,
        "!="            => 6,
        "!=="           => 6,
        "=="            => 6,
        "==="           => 6,
        "&"             => 5,
        "^"             => 4,
        "|"             => 3,
        "&&"            => 2,
        "||"            => 1,
    );
    
    /**
     * 能在前面定义标签的关键词
     * @var type 
     */
    static $_KEYWORDS_WITH_LABELS = array(
        "for",  "do",   "while",    "switch"
    );
    
    /**
     * js源代码
     * @var string 
     */
    private $content = '';
    /**
     * 当前解析的位置
     * @var int
     */
    private $cur_pos = 0;
    /**
     * 当前解析到的token所在行
     * @var int
     */
    private $cur_line = 1;
    /**
     * 当前token所在列
     * @var int
     */
    private $prev_column = 1;
    /**
     * 当前字符的宽度
     * @var int
     */
    private $prev_token_length = 0;
    /**
     * js源码的长度
     * @var int
     */
    private $length_of_content = 0;
    /**
     * js源码的字符集，默认为ISO-8859-1
     * @var string
     */
    private $encoding_of_content = "";
    /**
     * 是否允许正则判断
     * @var boolean
     */
    private $is_allow_regexp = true;
    /**
     * 词法分析后的产物
     * @var array
     */
    private $output_analytic = array();
    
    /**
     * 构造器
     */
    public function __construct() {
    }

    /**
     * 解析js源代码
     * @param string $content  待分析的内容
     * @param boolean $token_filter 需要被过滤掉的token类型 
     * @param boolean $encoding 文件内容编码，默认为GBK
     * @return type 
     */
    public function run($content = "",$token_filter = array(),$encoding = 'GBK'){
        //初始化
        $this->init($content,$encoding);
        
        while(!$this->eof()){
            $token = $this->read_next_token();
       
            //保存每个token的下一个token
            $len = count($this->output_analytic);
            if(count($this->output_analytic) && $this->output_analytic[$len - 1]["next"] === null){
                $this->output_analytic[$len - 1]["next"] = $token;
            }
            //token过滤
            if(!in_array($token["type"], $token_filter) || count($token["errMsg"]) !== 0){
                $this->output_analytic[] = $token;
            }
        }
        return $this->output_analytic;
    }
    
    /**
     * 初始化
     * @param type $content 文件内容
     * @param type $encoding  文件编码
     */
    private function init($content = "",$encoding = 'GBK'){
        $this->content = '';
        $this->cur_pos = 0;
        $this->cur_line = 1;
        $this->prev_column = 1;
        $this->prev_token_length = 0;
        $this->length_of_content = 0;
        $this->is_allow_regexp = true;
        $this->output_analytic = array();
        
        try {
            $this->encoding_of_content = strtoupper($encoding);
            //设置文件的内部编码
            mb_internal_encoding($this->encoding_of_content);
        } catch (Exception $exc) {
        }

        //如果文件是UTF-8 BOM编码，先去掉BOM，三个字节的ascii码分别依次为：239,187,191
        if(ord($content[0]) === 239 && ord($content[1]) === 187  && ord($content[2])=== 191){
            $content = substr($content, 3);
        }
        
        $this->content = $content;
        $this->length_of_content = strlen($content);
    }
    
    /**
     * 判断某字符是否为一个合法的letter
     */
    private function is_letter($ch){
        //[a-zA-Z]
        if($ch >= 'a' && $ch <= 'z' || $ch >= 'A' && $ch <= 'Z'){
            return true;
        }
        return false;
    }
    
    /**
     * 判断某一个字符是否为数字
     * @param type $ch
     * @return type 
     */
    private function is_digit($ch){
        if($ch >= '0' && $ch <= '9'){
            return true;
        }
        return false;
    }
    
    /**
     * 判断某字符是否为普通letter或者数字
     * @param type $ch
     * @return type 
     */
    private function is_alphanumeric_char($ch){
        return $this->is_letter($ch) || $this->is_digit($ch);
    }
    
    /**
     * 判断某字符是否为合法的标识符开始符号
     * @param type $ch
     * @return type 
     */
    private function is_identifier_start($ch){
        return $ch === "$" || $ch === "_" || $this->is_letter($ch);
    }
    
    /**
     * 判断某字符是否能成为合法的标识符组成字符
     * @param type $ch
     * @return type 
     */
    private function is_identifier_char($ch){
        return $this->is_identifier_start($ch) || $this->is_digit($ch) ;
    }
    
    /**
     * 获取当前正在解析的字符
     */
    private function current(){
        $the_char = null;
        $pos = $this->cur_pos;
        $the_char = $this->content[$pos];
      
        return $the_char;
    }
    
    /**
     * 获取下一个将要解析到的字符
     * @param type $move_cursor_next_step 将指针下移x位
     * @return type 
     */
    private function next($move_cursor_next_step = 0){
        $the_char = null;
        $len = ($move_cursor_next_step === 0 ? 1 : $move_cursor_next_step);
        $the_char = $this->content[$this->cur_pos + $len];
        if($move_cursor_next_step){
            $this->cur_pos += $len;
        }
        return $the_char;
    }
    
    /**
     * 判断当前js文件是否解析到最后
     */
    private function eof() {
        return $this->cur_pos >= $this->length_of_content;
    }
    
    /**
     * 从某个偏移开始，搜索某字符，并获取其位置
     * @param type $what 默认为"\n"
     * @param type $offset 默认为当前解析位置
     * @return type 索引
     */
    private function find($what = "\n",$offset = -1){
        if($offset === -1){
            $offset = $this->cur_pos;
        }
        return stripos($this->content, $what, $offset);
    }
    
    /**
     * 生成token
     * @param type $type token类型
     * @param type $value token内容
     * @param type $errMsg 解析时候出现的错误信息
     */
    private function token($type = "",$value = "",$errMsg = array()){
        //修正is_allow_regexp的值
        switch ($type) {
            case FL::JS_INLINE_COMMENT:
            case FL::JS_BLOCK_COMMENT:
            case FL::JS_WHITESPACE:
                break;
            default:
                $this->is_allow_regexp = (($type === FL::JS_OPERATOR && !in_array($value, self::$_UNARY_POSTFIX))
                        || ($type === FL::JS_KEYWORDS && in_array($value, self::$_KEYWORDS_BEFORE_EXPRESSION))
                        || ($type === FL::JS_PUNCTUATION && in_array($value, self::$_PUNC_BEFORE_EXPRESSION)));
                break;
        }
        //保存当前行代码的位置
        $cur_line = $this->cur_line;
        //计算当前token占据到的代码行位置
        //防止字符串中包含"\n"字样，而造成字符数量统计不准确
        $true_value = str_replace(array("\\\\n","\\\\t","\t"),array("**","**","****"),$value);
   
        $code_lines = explode("\n", $true_value);
        $lines_count = count($code_lines);
        $this->cur_line += $lines_count -1; 

        //计算当前token占据到的column位置
        if($cur_line === $this->cur_line 
                || ($lines_count > 1 && ($type === FL::JS_WHITESPACE || $type === FL::JS_STRING))){
            $cur_column = $this->prev_column + $this->prev_token_length;
            if($lines_count > 1 && ($type === FL::JS_WHITESPACE || $type === FL::JS_STRING)){
                $str = $code_lines[$lines_count - 1];
                $this->prev_token_length = strlen($str);
                //保存当前token所在列
                $this->prev_column = 1;
            }else{
                $this->prev_token_length = strlen($true_value);
                //保存当前token所在列
                $this->prev_column = $cur_column;
            }
        }else{
            $cur_column = 1;
            $str = $code_lines[$lines_count - 1];
            $this->prev_token_length = strlen($str);
            //保存当前token所在列
            $this->prev_column = 1;
        }
        
        //生成token
        $token = array(
            type        =>  $type,
            value       =>  $value,
            row         =>  $cur_line,
            column      =>  $cur_column,
            errMsg      =>  $errMsg
        );
        
        return $token;
    }
    
    
    /**
     * 解析数字，包括8、10、16进制。失败时返回null
     * @param type $num
     * @return type 
     */
    private function parse_js_number($num = ""){
        if(preg_match(self::$_RE_HEX_NUMBER, $num)){
            return intval(substr($num, 2), 16);
        }elseif(preg_match(self::$_RE_OCT_NUMBER, $num)){
            return intval(substr($num, 1), 8);
        }elseif(preg_match(self::$_RE_DEC_NUMBER, $num)){
            return floatval($num);
        }else{
            return null;
        }
    }

    /**
     * 读取空白符
     */
    private function read_whitespace(){
        $result_string = "";
        $the_char = $this->current();
        while(in_array($the_char, self::$_WHITESPACE_CHARS)){
            $result_string .= $the_char;
            $the_char = $this->next(1);
        }
        
        return $this->token(FL::JS_WHITESPACE, $result_string,$errMsg);
    }

    /**
     * 读取字符，直到遇到$target为止
     * @param type $target 
     */
    private function read_while($target = ""){
        $result_string = "";
        $the_char = $this->current();
        if($target === "\r\n"){
            while($the_char !== "\r" && $the_char !== "\n" && !$this->eof()){
                $result_string .= $the_char;
                $the_char = $this->next(1);
            }
        }else{
            while(!preg_match("/" . preg_quote($target,"/") .  "$/", $result_string) && !$this->eof()){
                $result_string .= $the_char;
                $the_char = $this->next(1);
            }
        }
        return $result_string;
    }

    /**
     * 读取数字，包括B、O、D、H四种进制
     * @param type $prefix 
     */
    private function read_number($prefix = ""){
        $has_e = false;
        $after_e = false;
        $has_x = false;
        $has_dot = ($prefix === ".");
        
        $i = 0;
        $result_string = "";
        while(!$this->eof()){
            $the_char = $this->current();
            $i++;
            if($the_char === "x" || $the_char === "X"){
                if($has_x)  break;
                $has_x = true;
                $result_string .= $the_char;
                $this->next(1);
                continue;
            }
            if (!$has_x && ($the_char === "E" || $the_char === "e")) {
                if ($has_e) break;
                $has_e = $after_e = true;
                $result_string .= $the_char;
                $this->next(1);
                continue;
            }
            if ($the_char === "-") {
                if ($after_e || ($i === 1 && !$prefix)) {
                    $result_string .= $the_char;
                    $this->next(1);
                    continue;
                }
                break;
            }
            if ($the_char === "+") {
                if($after_e){
                    $result_string .= $the_char;
                    $this->next(1);
                    continue;
                }
                break;
            }
            $after_e = false;
            if ($the_char === ".") {
                //i===1的情况下，处理小数：".5"的情况
                if (!$has_dot && !$has_x || $i === 1){
                    $has_dot = true;
                    $result_string .= $the_char;
                    $this->next(1);
                    continue;
                }
                break;
            }
            if($this->is_alphanumeric_char($the_char)){
                $result_string .= $the_char;
                $this->next(1);
                continue;
            }
            break;
        }
        
        $num = $result_string;
        if ($prefix){
            $num = $prefix . $num;
        }
        //尝试将数字正确的解析
        $valid = $this->parse_js_number($num);

        
        //错误信息记录
        if ($valid === null) {
            $errMsg = array(FL::MSG_ERROR,"Invalid syntax: " . $num);
        }
        return $this->token(FL::JS_NUMBER, $valid,$errMsg);
    }

    /**
     * 读取十六进制数字
     * @param int $input 分两种，2：\x20和4:\u025A
     * @return int 
     */
    private function read_hex_num($input){
        $num = 0;
        $errMsg = array();
        for (; $input > 0; --$input) {
                $the_char = $this->next(1);
                $digit = intval($the_char, 16);
                if (count($errMsg) === 0 && $the_char != "0" && $digit === 0){
                    $errMsg = array(FL::MSG_ERROR,"Invalid hex-character pattern in string");
                }
                $num = ($num << 4) | $digit;
        }
        return array($num,$errMsg);
    }
    
    /**
     * 读取转义字符
     */
    private function read_escaped_char(){
        $result_string = "";
        $errMsg = array();
        $the_char = $this->current();
        
        switch (strtolower($the_char)) {
            case "n" : $result_string = "\\n";break;
            case "r" : $result_string = "\\r";break;
            case "t" : $result_string = "\\t";break;
            case "b" : $result_string = "\\b";break;
            case "v" : $result_string = "\\v";break;
            case "f" : $result_string = "\\f";break;
            case "0" : $result_string = "\\0";break;
            case "x" : 
                $the_num = $this->read_hex_num(2);
                $result_string = chr($the_num[0]);
                $errMsg = $the_num[1];
                break;
            case "u" : 
                $the_num = $this->read_hex_num(4);
                $result_string = chr($the_num[0]);
                $errMsg = $the_num[1];
                break;
            case "\r": $result_string = "\r";break;
            case "\n": $result_string = "\n";break;
            default  : $result_string = "\\" . $the_char;break;
        }
        return array($result_string,$errMsg);
    }
    
    /**
     * 读取字符串
     */
    private function read_string(){
        $quote = $this->current();
        $result_string = $quote;
        $errMsg = array();
        while(!$this->eof()){
            $the_char = $this->next(1);
            if($the_char === "\\"){
                $octal_len = 0;
                $first = null;
                while(true){
                    if(ord($the_char) >= 48 && ord($the_char) <= 55){
                        if($first !== null){
                            $first = $the_char;
                            ++$octal_len;
                        }elseif(ord($first) <= 51 && $octal_len <= 2){
                            ++$octal_len;
                        }elseif(ord($first) >= 52 && $octal_len <= 1){
                            ++$octal_len;
                        }
                        $the_char = $this->next(1);
                        continue;
                    }
                    break;
                }
                if($octal_len > 0) {
                    $the_char = chr(intval($the_char, 8));
                }else{
                    $this->next(1);
                    $the_result = $this->read_escaped_char();
                    $the_char = $the_result[0];
                    if(count($errMsg) === 0) {
                        $errMsg = $the_result[1];
                    }
                }
            }elseif($the_char === $quote){
                $result_string .= $the_char;
                $this->next(1);
                break;
            }
            $result_string .= $the_char;
        }
        return $this->token(FL::JS_STRING, $result_string,$errMsg);
    }
    
    /**
     * 读取行注释
     */
    private function read_inline_comment(){
        $result_string = $this->current() . $this->next();
        $this->next(2);
        if(($ipos = $this->find("\n")) === false){
            $result_string .= substr($this->content, $this->cur_pos,$this->length_of_content - $this->cur_pos);
            $this->cur_pos = $this->length_of_content;
        }else{
            $result_string .= $this->read_while("\r\n");
            $result_string = str_replace(array("\r","\n"), "", $result_string);
        }
        return $this->token(FL::JS_INLINE_COMMENT,$result_string);
    }
    
    /**
     * 读取块注释
     */
    private function read_block_comment(){
        $result_string = $this->current() . $this->next();
        $this->next(2);
        //如果在注释中没有找到"*/"，则将后续的所有内容都当成注释
        if(($ipos = $this->find("*/")) === false){
            $result_string .= substr($this->content, $this->cur_pos,$this->length_of_content - $this->cur_pos);
            $this->cur_pos = $this->length_of_content;
        }else{
            $result_string .= $this->read_while("*/");
        }
 
        //判断是否存在ie条件注释
        if(preg_match("/(^\/\*@cc_on)|(^\/\*@.*@\*\/$)/i", $result_string)){
            $errMsg = array(FL::MSG_WARN, "Found \"conditional comment\": " . $result_string);
        }
        return $this->token(FL::JS_BLOCK_COMMENT,$result_string,$errMsg);
    }
    
    /**
     * 读取标识符
     */
    private function read_identifier(){
        $backslash = false;
        $identifier = "";
        $errMsg = array();
        while(!$this->eof()){
            $the_char = $this->current();
            if(!$backslash) {
                if($the_char === "\\") {
                    $backslash = true;
                    $the_char = $this->next(1);
                }elseif($this->is_identifier_char($the_char)){
                    $identifier .= $the_char;
                    $the_char = $this->next(1);
                }else{
                    break;
                }
            }else{
                if(count($errMsg) === 0 && strtolower($the_char) !== "u") {
                    $errMsg = array(FL::MSG_ERROR,"Expecting UnicodeEscapeSequence -- uXXXX");
                }
                $the_result = $this->read_escaped_char();
                $the_char = $the_result[0];
                $the_errMsg = $the_result[1];
                if(count($errMsg) === 0 && count($the_errMsg) !== 0) {
                    $errMsg = $the_errMsg;
                }
                if(count($errMsg) === 0 && !$this->is_identifier_char($the_char)){
                    $errMsg = array(FL::MSG_ERROR,"Unicode char: " . ord($the_char) . " is not valid in identifier");
                }
                $identifier .= $the_char;
                $backslash = false;
                $the_char = $this->next(1);
            }
        }
        return array($identifier,$errMsg);
    }
    
    /**
     * 读取正则表达式
     */
    private function read_regexp(){
        $prev_backslash = false;
        $regexp = "/";
        $in_class = false;
        //正则内容
        while(!$this->eof()){
            $the_char = $this->next(1);
            if($prev_backslash) {
                $regexp .= "\\" . $the_char;
                $prev_backslash = false;
            }elseif($the_char === "["){
                $in_class = true;
                $regexp .= $the_char;
            }elseif($the_char === "]" && $in_class){
                $in_class = false;
                $regexp .= $the_char;
            }elseif($the_char === "/" && !$in_class){
                $regexp .= $the_char;
                $this->next(1);
                break;
            }elseif($the_char === "\\"){
                $prev_backslash = true;
            }else {
                $regexp .= $the_char;
            }
        }
        
        //正则修饰符
        $modifiers = $this->read_identifier();
        $mod_str = $modifiers[0];
        $errMsg = $modifiers[1];
        $regexp .= $mod_str;

        //如果修饰符不符合js规范，则报error
        if($mod_str !== "" && preg_match("/[^" . join("", self::$_REGEXP_MODIFIERS) . "]/ie",$mod_str)){
            $errMsg = array(FL::MSG_ERROR,"Invalid regexp modifier: " . $regexp);
        }
        return $this->token(FL::JS_REGEXP, $regexp,$errMsg);
    }
    
    /**
     * 增进式读取操作符
     * @param type $op
     * @return type 
     */
    private function operator_grow($op){
        if(!($the_char = $this->next(1))){
            return $op;
        }
        $bigger = $op . $the_char;
        if(in_array($bigger, self::$_OPERATORS)){
            return $this->operator_grow($bigger);
        }else{
            return $op;
        }
    }
    
    /**
     * 读取操作符
     */
    private function read_operator($prefix = ""){
        $the_char = $this->current();
        return $this->token(FL::JS_OPERATOR,$this->operator_grow($prefix ? $prefix : $the_char));
    }
    
    /**
     * 读取斜杠“/”
     * @return type 
     */
    private function read_slash(){
        switch($the_char = $this->next()){
            case "/" : 
                return $this->read_inline_comment();
            case "*" :
                return $this->read_block_comment();
        }
        return $this->is_allow_regexp ? $this->read_regexp() : $this->read_operator("/");
    }
    
    /**
     * 读取点“.”
     */
    private function read_dot(){
        return $this->is_digit($this->next()) ? $this->read_number() : $this->read_punc(".");
    }
    
    /**
     * 读取标点符号
     */
    private function read_punc($punc = ""){
        $the_char = "";
        if($punc !== ""){
            $the_char = $punc;
            $this->next(strlen($punc));
        }else{
            $the_char = $this->current();
            $this->next(1);
        }
        return $this->token(FL::JS_PUNCTUATION,$the_char);
    }
    
    /**
     * 读取单词（包括keyword、reserved word、identifier……）
     */
    private function  read_word(){
        $the_token = $this->read_identifier();
        $the_word = $the_token[0];
        $errMsg = $the_token[1];
     
        //如果不是关键字，也不是保留字，则是普通标识符
        if(!in_array($the_word, self::$_KEYWORDS) && !in_array($the_word, self::$_RESERVED_WORDS)){
            return $this->token(FL::JS_IDENTIFIER,$the_word,$errMsg);
        }
        //保留字
        elseif(in_array($the_word, self::$_RESERVED_WORDS)){
            return $this->token(FL::JS_RESERVED_WORDS,$the_word,$errMsg);
        }
        //关键字中的操作符
/*        elseif(in_array($the_word, self::$_OPERATORS)){
            return $this->token(FL::JS_OPERATOR,$the_word,$errMsg);
        }
*/        //原子量
        elseif(in_array($the_word, self::$_KEYWORDS_ATOM)){
            return $this->token(FL::JS_KEYWORDS_ATOM,$the_word,$errMsg);
        }
        //普通关键字
        else{
            return $this->token(FL::JS_KEYWORDS,$the_word,$errMsg);
        }
    }
    
    /**
     * 读取其他普通字符
     */
    private function read_normal(){
        $result_string = $this->current();
        while(!$this->eof()){
            $the_char = $this->next(1);
            if($the_char !== null
                  && !in_array($the_char, self::$_WHITESPACE_CHARS)
                  && $the_char !== "/"
                  && $the_char !== "."
                  && !$this->is_digit($the_char)
                  && !($the_char === "'" || $the_char === '"')
                  && !in_array($the_char, self::$_PUNC_CHARS)
                  && !in_array($the_char, self::$_OPERATOR_CHARS)
                  && !($the_char === "\\" || $this->is_identifier_start($the_char))){
                $result_string .= $the_char;
            }else{
                break;
            }
        }
        $errMsg = array(FL::MSG_ERROR,"Unexpected character [" . $result_string . "]");
        return $this->token(FL::FL_NORMAL, $result_string, $errMsg);
    }
    
    /**
     * 读取下一个待解析的token
     * @return array token 
     */
    private function read_next_token(){
        //获取当前需要解析的字符
        $the_char = $this->current();

        //已经读取到文件结束
        if($the_char === null) return $this->token (FL::FL_EOF,"");
        //读取空白字符
        if(in_array($the_char, self::$_WHITESPACE_CHARS)) return $this->read_whitespace ();
        //读取slash，可能是注释，也可能是正则或操作符
        if($the_char === "/") return $this->read_slash();
        //读取小数点
        if($the_char === ".") return $this->read_dot();
        //读取数字
        if($this->is_digit($the_char)) return $this->read_number ();
        //读取字符串
        if($the_char === "'" || $the_char === '"') return $this->read_string ();
        //读取标点符号
        if(in_array($the_char, self::$_PUNC_CHARS)) return $this->read_punc();
        //读取操作符
        if(in_array($the_char, self::$_OPERATOR_CHARS)) return $this->read_operator ();
        //读取标识符
        if($the_char === "\\" || $this->is_identifier_start($the_char)) return $this->read_word ();
        //其他普通的token
        return $this->read_normal();
    }
}
