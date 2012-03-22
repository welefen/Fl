<?php
/**
 * Javascript规范检测
 * 
 * @author zhaoxianlie
 */
class Fl_Validate_Js{
	/**
     * Javascript词法分析后的结果
     * @var array
     */
	private $analyticContent = array();
	/**
     * Javascript词法分析后的token数
     * @var int
     */
	private $analyticCount = 0;
	/**
     * 已经检测的数量
     * @var int
     */
	private $validateCount = 0;
    /**
     * validate结果
     * @var object
     */
    private $validateResult = array(
        'warning' => array(),
        'error'   => array()
    );

    /**
     * 对Javascript源码进行规范检测
     * @param type $content
     * @return Array 校验结果，数组形式的msg：array('warning'=>array(),'error'=>array())
     */
	public function run($content = '',$encoding = 'GBK'){
        //初始化
        $this->init();
        //js词法分析
		$this->analyticContent = $this->fl_instance->analytic_javascript($content,
                array(FL::JS_WHITESPACE,FL::JS_INLINE_COMMENT),$encoding);
		$this->analyticCount = count($this->analyticContent);
    
        //检测
        $token = $this->get_current();
		while ($token !== null){
            $this->deal_token($token);
            $token = $this->get_next(1);
		}
        
        //返回校验结果
        return $this->validateResult;
	}
    
    /**
     * 初始化
     */
    private function init(){
        $this->analyticContent = array();
        $this->analyticCount = 0;
        $this->validateCount = 0;
        $this->validateResult = array(
            'warning' => array(),
            'error'   => array()
        );
    }
        
    /**
     * 处理token
     * @return type 
     */
    private function deal_token($token = null) {
        $value = $token["value"];
        //根据上一个token的类型来读取下一个token
        switch ($token["type"]) {
            //数字
            case FL::JS_NUMBER:
            //正则表达式
            case FL::JS_REGEXP:
            //js操作符
            case FL::JS_OPERATOR:
            //js原生词：true、false等
            case FL::JS_KEYWORDS_ATOM:
                break;

            //字符串
            case FL::JS_STRING:
                $this->detect_string($token);
                break;

            //js标点符号或括号
            case FL::JS_PUNCTUATION:
                switch ($value) {
                    case ".":
                        //检测dot
                        $this->detect_dot($token);
                        break;
                    case "{":
                        //检测大括号，可能是block，也可能是json
                        $this->detect_braces($token);
                        break;
                    case "[":
                        //检测方括号：数组
                        $this->detect_square_brackets($token);
                        break;
                    default:
                }
                break;

            //关键字
            case FL::JS_KEYWORDS:
                if($value === "break"){
                    //检测break后是否有标签
                    $this->detect_label_after_kw($token);
                }elseif($value === "continue"){
                    //检测break后是否有标签
                    $this->detect_label_after_kw($token);
                }elseif($value === "debugger"){

                }elseif($value === "do"){

                }elseif($value === "for"){

                }elseif($value === "function"){
                     $this->detect_function($token);
                }elseif($value === "if"){

                }elseif($value === "return"){

                }elseif($value === "switch"){

                }elseif($value === "throw"){

                }elseif($value === "try"){

                }elseif($value === "var"){
                    //变量的命名不能是关键字
                    $this->detect_var();
                }elseif($value === "const"){
                    //IE中禁止用const定义常量
                    $this->detect_const($token);
                }elseif($value === "while"){

                }elseif($value === "with"){
                    //不推荐使用with块
                    $this->detect_block_with($token);
                }elseif($value === "void"){
                    //不推荐使用void
                    $this->detect_void($token);
                }else{

                }   
                break;

            //保留字
            case FL::JS_RESERVED_WORDS:
                //禁止使用js保留字
                $this->detect_reserved_word($token);
                break;
                
            //js标识符
            case FL::JS_IDENTIFIER:
                if($value === "console"){
                    //检测代码中使用到的console关键字，调试语句，建议删除
                    $this->detect_console($token);
                }
                break;

            //其他类型的token
            default:
                break;
        }
    }

    /**
     * 修正当前已经解析的数量
     * @param type $iOffset 偏移量
     */
    private function modify_validate_count($iOffset){
        $this->validateCount += $iOffset;
    }
    
    /**
     * 获取当前token
     * @return type 
     */
    private function get_current(){
        $token = $this->analyticContent[$this->validateCount];
        
        //先检测在词法分析阶段发现的问题
        $this->detect_anlaytic_error($token);
        
        return $token;
    }
    
    /**
     * 获取前面的第N个token
     * @param type $int
     * @param type $add
     * @return type 
     */
    private function get_prev(){
        $c = $this->validateCount - 1;
		if ($c >= $this->analyticCount) return null;

        $token = $this->analyticContent[$c];
        
        //先检测在词法分析阶段发现的问题
        if($bln_move_prev){
            $this->detect_anlaytic_error($token);
        }

        return $token;
	}
    
    /**
     * 获取后面的第N个token
     * @param type $int
     * @param type $add
     * @return type 
     */
    private function get_next($int = 0,$bln_move_next = true){
        if($int === 0){
            $int = 1;
            $bln_move_next = false;
        }
		if ($bln_move_next){
			$this->validateCount += $int;
			$c = $this->validateCount;
		}else{
			$c = $this->validateCount + $int;
		}
		if ($c >= $this->analyticCount) return null;

        $token = $this->analyticContent[$c];
        
        //先检测在词法分析阶段发现的问题
        if($bln_move_next){
            $this->detect_anlaytic_error($token);
        }

        return $token;
	}
    
    /**
     * 保存log信息
     * @param type $msg_type 消息类型
     * @param type $msg_info 消息内容
     */
    private function set_msg($msg_type = 0,$msg_info = ""){
        switch ($msg_type) {
            case FL::MSG_WARN:
                $this->validateResult['warning'][] = $msg_info;
                break;
            case FL::MSG_ERROR:
                $this->validateResult['error'][] = $msg_info;
                break;
            default:
                break;
        }
    }
    
    /**
     * 获得token在源码中的位置
     * @param type $token
     * @return string
     */
    private function get_token_pos($token = null){
        return "[\033[31mrow " . $token["row"] . ", col " . $token["column"] . "\033[0m] ";
    }
    
    /**
     * 对token进行错误探测：词法分析过程中发现的词法错误
     * @param type $token 待探测的token
     * @return boolean 有错误则返回false，无错误则返回true 
     */
    private function detect_anlaytic_error($token = null){
        if(count($token["errMsg"]) !== 0) {
            $this->set_msg($token["errMsg"][0], $this->get_token_pos($token) . $token["errMsg"][1]);
            return false;
        }
        return true;
    }
    
    /**
     * 探测token是否为Js的保留字
     * @param type $token 待探测的token
     * @return boolean 不是Js保留字则返回true，否则返回false
     */
    private function detect_reserved_word($token = null){
        $this->set_msg(FL::MSG_ERROR, $this->get_token_pos($token) . "\"" . $token["value"] . "\" is a Javascript Reserved word!");
        return false;
    }
    
    /**
     * 探测var后是否还有其他关键字，此种情况下，视为变量定义错误。
     */
    private function detect_var(){
        //对那些处于括号中的关键字，都不做处理
        //小括号
        $brackets = 0;
        //大括号
        $big_bracktes = 0;

        //这个地方应该用while而不是if，以后做升级
        if(($token_current = $this->get_next(1)) !== null) {
            //分号时，停止对var的解析
            if($token_current["value"] === ";") {
                //break;
            }
            $bln_rest = true;
            
            //小括号统计
            if($token_current["value"] === "("){
                $brackets++;
            }elseif($token_current["value"] === ")"){
                $brackets--;
            }
            //大括号统计
            elseif($token_current["value"] === "{"){
                $big_bracktes++;
            }elseif($token_current["value"] === "}"){
                $big_bracktes--;
            }
            
            
            //仅对不在括号中的内容进行检测
            if($brackets < 0 || $big_bracktes < 0) {
                //break;
            }elseif($brackets === 0){
                //判断当前token
                if($token_current["type"] === FL::JS_KEYWORDS 
                        || $token_current["type"] === FL::JS_RESERVED_WORDS 
                        || $token_current["type"] === FL::JS_KEYWORDS_ATOM){
                    //下一个token
                    $next2token = $this->get_next(1,false);
                    $prev_token = $this->get_prev();
                    
                    //遇到function则退出
                    if($prev_token["value"] === "=" &&  $token_current["value"] === "function" && 
                            $next2token["value"] === "("){
                        //break;
                    }
                    
                    //不合法的变量定义方式
                    if(($prev_token === null || $prev_token["type"] !== FL::JS_OPERATOR)){
                        if($next2token["type"] === FL::JS_PUNCTUATION) {
                            //关键字或保留字
                            $tips = ($token_current["type"] === FL::JS_KEYWORDS) ? "Javascript Keyword" : "Javascript Reserved word";
                            $this->set_msg(FL::MSG_ERROR, $this->get_token_pos($token_current) . "\"" 
                                    . $token_current["value"] . "\" is a $tips,but not a valid variable!");
                            $bln_rest = false;
                        }
                    }
                }
                //换行时，停止对var的解析
                if($token_current["next"]["type"] === FL::JS_WHITESPACE && 
                    preg_match("/\n/s", $token_current["next"]["value"])){
                    //break;
                }
                if(!$bln_rest){
                    //continue;
                }
            }
            $this->deal_token($token_current);
        }
        return $bln_rest;
    }
  
    /**
     * 检测void关键字。不推荐使用void关键字，来声明某个function或表达式无返回值
     * @param type $token 
     */
    private function detect_void($token = null){
        $this->set_msg(FL::MSG_WARN, $this->get_token_pos($token) . "Please don't use the \"void\" key word!");
    }
    
    /**
     * 检测const关键字。在IE中禁止使用const定义常量
     * @param type $token 
     */
    private function detect_const($token = null){
        $this->set_msg(FL::MSG_ERROR, $this->get_token_pos($token) . "\"const\" can not be supported in IE!");
    }
    
    /**
     * 检测代码中是否使用了with块
     * @param type $token 
     */
    private function detect_block_with($token = null){
        $this->set_msg(FL::MSG_WARN, $this->get_token_pos($token) . "Please don't use \"with\" block!");
        return false;
    }
    
    /**
     * 检测字符串是否用了“\”的方式进行拼接
     * @param type $token 
     */
    private function detect_string($token = null){
        if(preg_match("/\n/", $token["value"])){
            $this->set_msg(FL::MSG_WARN, $this->get_token_pos($token) . "Don't use the \"\\\" to concat string at the end of line!");
            return false;
        }
        return true;
    }
    
    /**
     * 在break和continue的后面，如果跟上了label，则认为不合法
     * @param type $token 
     */
    private function detect_label_after_kw($token = null) {
        $next_token = $this->get_next();
        if($next_token["type"] === FL::JS_IDENTIFIER) {
            $this->set_msg(FL::MSG_ERROR, $this->get_token_pos($token) . "\"" . $next_token["value"] . "\" after \"" 
                    . $token["value"] . "\" is a javascript label!");
            return false;
        }
        return true;
    }
    
    /**
     * 探测方法的定义
     * @param type $token 
     */
    private function detect_function($token = null){
        //通过function关键字的上一个token判断，当前是否为一个function表达式
        $token_prev = $this->get_prev();
        if($token_prev["value"] === "=") {
            $is_function_expression = true;
            //接着再看function的定义是否为这样的形式：var fun = function fun1(){};
            $token_next = $this->get_next(1);
            if($token_next["type"] === FL::JS_IDENTIFIER) {
                $this->set_msg(FL::MSG_ERROR, $this->get_token_pos($token_next) . 
                    "The function \"" . $token_next["value"] . "\" can be supported only in IE!");
            }
        }elseif($token_prev["value"] === ":") {
            // json中的function表达式，需要以'},'结尾
            $is_function_expression_in_json = true;
        }
        
        //从后面的token中，匹配右大括号，如果匹配上，该值递增1直到0停止，否则递减继续匹配
        $right_brace = 0;
        while(($token_current = $this->get_next(1)) !== null){
            if($token_current["type"] === FL::JS_PUNCTUATION){
                if($token_current["value"] === "}"){
                    $right_brace++;
                    if($right_brace === 0){
                        break;
                    }
                }elseif($token_current["value"] === "{"){
                    $right_brace--;
                    continue;
                }
            }
            $this->deal_token($token_current);
        }
        
        //闭包检测
        if(!$this->detect_closure($token_current)){
            //如果是function表达式，则需要以"};"结束
            if($is_function_expression){
                $token_next = $this->get_next();
                if($token_next["type"] !== FL::JS_PUNCTUATION){
                    $this->set_msg(FL::MSG_WARN, $this->get_token_pos($token_current) . 
                        "Here must have a \";\" after the function expression!");
                }
            }
            // json中的function表达式，需要以'},'结尾
            elseif($is_function_expression_in_json) {
                $token_next = $this->get_next();
                while($token_next["type"] === FL::JS_INLINE_COMMENT || $token_next["type"] === FL::JS_BLOCK_COMMENT) {
                    $token_next = $this->get_next(1);
                }
                if($token_next["type"] !== FL::JS_PUNCTUATION){
                    $this->set_msg(FL::MSG_WARN, $this->get_token_pos($token_current) . 
                        "Here must have a \",\" after the function expression in json!");
                }
            }
        }
    }
    
    /**
     * 闭包检测，主要检测如下两种闭包形式，后面必须加“;”结束
     * 1、function(args){...}();
     * 2、(function(args){...})();
     * @param type $token function结束标志："}"
     */
    private function detect_closure($token = null){
        $token_next = $this->get_next(1);
        if($token_next["value"] === ")"){
            $token_next = $this->get_next(1);
        }
        //如果紧接着的是“(”标识符，则表示这个地方的function是一个闭包
        if($token_next["value"] === "("){
            //从后面的token中，匹配右括号，如果匹配上，该值递增1直到0停止，否则递减继续匹配
            $right_brace = -1;
            while(($token_current = $this->get_next(1)) !== null){
                if($token_current["type"] === FL::JS_PUNCTUATION){
                    if($token_current["value"] === ")"){
                        $right_brace++;
                        if($right_brace === 0){
                            break;
                        }
                    }elseif($token_current["value"] === "("){
                        $right_brace--;
                        continue;
                    }
                }
                $this->deal_token($token_current);
            }
            //判断闭包后是否跟着分号“;”
            $token_next = $this->get_next(1);
            if($token_next["value"] === "("){
                $this->set_msg(FL::MSG_ERROR, $this->get_token_pos($token_current) . 
                    "Here must have a \";\" after the \")\"!");
            }else{
                //游标后退1
                $this->modify_validate_count(-1);
            }
            
            return true;
        }else{
            //游标后退1
            $this->modify_validate_count(-1);
            return false;
        }
    }
    
    /**
     * 检测操作符dot
     * @param type $token 
     */
    private function detect_dot($token = null){
        $token_prev = $this->get_prev();
        $token_next = $this->get_next(1);
        if($token_next && $token_next["type"] !== FL::JS_IDENTIFIER){
            $this->set_msg(FL::MSG_ERROR, $this->get_token_pos($token_next) . "\"" .
                    $token_next["value"]. "\" is not a valid member of \"" . $token_prev["value"] . "\"");
        }
    }
    
    /**
     * 检测方括号，数组的定义
     * @param type $token 
     */
    private function detect_square_brackets($token = null){
        //从后面的token中，匹配右方括号，如果匹配上，该值递增1直到0停止，否则递减继续匹配
        $right_brace = -1;
        while(($token_current = $this->get_next(1)) !== null){
            if($token_current["type"] === FL::JS_PUNCTUATION){
                if($token_current["value"] === "]"){
                    //数组的最后一项后，不能有多余的逗号或分号
                    $token_prev = $this->get_prev();
                    if($token_prev["value"] === "," || $token_prev["value"] === ";"){
                        $this->set_msg(FL::MSG_ERROR, $this->get_token_pos($token_prev) . "Unnecessary token before \"]\"!");
                    }
                    $right_brace++;
                    if($right_brace === 0){
                        break;
                    }
                }elseif($token_current["value"] === "["){
                    $right_brace--;
                    continue;
                }
            }
            $this->deal_token($token_current);
        }       
    }
    
    /**
     * 检测大括号，可能是block，也可能是json
     * @param type $token 
     */
    private function detect_braces($token = null){
        if($token !== null && $token["type"] === FL::JS_PUNCTUATION && $token["value"] === "{"){
            $token_prev = $this->get_prev();
            //如果在"{"的前面不是一个")"，则表示下面的内容不是一个block，而是json
            if($token_prev === null || $token_prev["value"] !== ")"){
                //从后面的token中，匹配右大括号，如果匹配上，该值递增1直到0停止，否则递减继续匹配
                $right_brace = -1;
                while(($token_current = $this->get_next(1)) !== null){
                    $token_prev = $this->get_prev();
                    if($token_current["type"] === FL::JS_PUNCTUATION){
                        if($token_current["value"] === "}"){
                            if($token_prev["value"] === ","){
                                $this->set_msg(FL::MSG_ERROR, $this->get_token_pos($token_prev) . "Unnecessary token before \"}\"!");
                            }
                            $right_brace++;
                            if($right_brace === 0){
                                break;
                            }
                        }elseif($token_current["value"] === "{"){
                            $right_brace--;
                            continue;
                        }
                    }
                    
                    //以下检查json的key是否为关键字或保留字
                    $token_next = $this->get_next(1, false);
                    if(($token_prev["value"] === "{" || $token_prev["value"] === ",") && $token_next["value"] === ":"){
                        if($token_current["type"] === FL::JS_KEYWORDS ||
                                $token_current["type"] === FL::JS_RESERVED_WORDS){
                            $this->set_msg(FL::MSG_ERROR, $this->get_token_pos($token_current) . "\"" 
                                    . $token_current["value"] . "\" is not a valid key of json data!");
                            continue;
                        }
                    }
                    $this->deal_token($token_current);
                }
            }
            //其他情况，都是block
            else{
                
            }
        }
    }
    
    /**
     * 检测代码中的console关键字，这是一个调试语句，建议从代码中删除
     * @param type $token 
     */
    private function detect_console($token = null){
        $this->set_msg(FL::MSG_WARN, $this->get_token_pos($token) . "\"" .
                $token["value"]. "\" is a debug key word,please remove it!");
    }
}
