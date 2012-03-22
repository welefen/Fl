<?php
/**
 * Smarty模板编码规范检测
 * @author 赵先烈
 */
class Fl_Validate_Smartytpl{
	/**
     * 待分析的Smarty模板内容
     * @var type 
     */
	private $analyticContent = array();
	/**
     * 总共需要分析的token数
     * @var type 
     */
	private $analyticCount = 0;
	/**
     * 已经验证的token数
     * @var type 
     */
	private $validateCount = 0;
    /**
     * 当前文件名
     * @var type 
     */
    private $_cur_file = '';
    /**
     * 相关的检测计数器
     * @var type 
     */
    private $detect_recorder = array();
    /**
     * 检测结果
     * @var type 
     */
    private $validateResult = array(
        'warning' => array(),
        'error' => array()
    );
    /**
     * 对Smarty模板内容进行规范检测
     * @param type $content
     * @param type $validateInstance 
     */
	public function run($content = '',$file_name = ''){
        //初始化
        $this->init($file_name);
        //js词法分析
		$this->analyticContent = $this->fl_instance->analytic_html($content);
		$this->analyticCount = count($this->analyticContent);
    
        //检测
        $token = $this->get_current();
		while ($token !== null){
            $this->deal_token($token);
            $token = $this->get_next(1);
		}
        
        //最后需要检测的内容
        $this->detect_at_last();
        
        //返回检测结果
        return $this->validateResult;
	}
        
    /**
     * 初始化
     */
    private function init($file_name = ''){
        $this->analyticContent = array();
        $this->analyticCount = 0;
        $this->validateCount = 0;
        $this->_cur_file = $file_name;
        $this->detect_recorder = array(
            "html_comment"  => 0
        );
        $this->validateResult = array(
            'warning' => array(),
            'error' => array()
        );
    }
    
    /**
     * 处理token
     * @return type 
     */
    private function deal_token($token = null) {
        $value = $token[0];
        $type = $token[1];

        switch ($type){
            //Smarty模板语法
            case FL::FL_TPL_DELIMITER:
                $this->deal_smarty_tag($value);
                break;
            //HTML注释
            case FL::HTML_COMMENT:
                $this->mark_htmlcomment($token);
                break;
            default:
                break;
        }
    }
    
    /**
     * 处理smarty标签
     * @param type $value 
     */
    private function deal_smarty_tag($value = ''){
        //smarty标签词法分析
        $tag_info = $this->fl_instance->analytic_smartytpl($value);
        $type = $tag_info['type'];
        $name = $tag_info['name'];
        $attrs = $tag_info['attrs'];
     
        //根据标签类型进行单独处理
        switch ($type) {
            //普通模板变量
            case FL::SMARTY_TPL_VAR:
                break;
            //一般smary标签
            case FL::SMARTY_TAG_START:
                if($name === "assign") {
                    //判断当前标签是否为assign标签
                    $this->detect_assign($tag_info);
                }elseif($name === "block"){
                    //判断当前标签是否为block标签
                    $this->detect_block($tag_info);
                }elseif($name === "extends"){
                    //判断当前标签是否为extends标签
                    $this->detect_extends($tag_info);
                }elseif($name === "function"){
                    //判断当前标签是否为function标签
                    $this->detect_function_define($tag_info);
                }elseif($name === "include"){
                    //判断当前标签是否为include标签
                    $this->detect_include($tag_info);
                }elseif($name === "for"){
                    //判断当前标签是否为for标签
                    $this->detect_for($tag_info);
                }elseif($name === "foreach"){
                    //判断当前标签是否为foreach标签
                    $this->detect_foreach($tag_info);
                }
                break;
            //自定义function的调用
            case FL::SMARTY_FUNCTION_CALL:
                $this->detect_function_call($tag_info);
                break;

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
     * 生成一个高亮的msg
     * @param type $msg 
     */
    private function generate_hilight_msg($msg = ''){
        return  "\033[31m" . $msg . "\033[0m";
    }
    
    /**
     * 最后需要检测的内容
     */
    private function detect_at_last(){
        //检测HTML注释
        if($this->detect_recorder['html_comment']) {
            $this->detect_htmlcommoent();
        }
    }
    
    /**
     * 统计模板中HTML注释的数量
     * @param type $token 
     */
    private function mark_htmlcomment($token = null){
        $this->detect_recorder["html_comment"]++;
    }
    
    /**
     * 检测data模板，看是否包含HTML的注释
     * 如果有，抛出警告，建议换成Smarty自带的注释方式
     */
    private function detect_htmlcommoent(){
        $num = "[count: " . $this->generate_hilight_msg($this->detect_recorder["html_comment"]) . "] ";
        if(preg_match("/[\da-zA-Z]+_data_[\da-zA-Z]+/", $this->_cur_file)){
            //后续会将这个错误级别提高至：MSG_ERROR
            $this->set_msg(FL::MSG_WARN, $num . "Don't use HTML Comment in a data template!");
        }else{
            $this->set_msg(FL::MSG_WARN, $num . "Don't use HTML Comment,you can use Smarty Comment instead,eg:<&* ... *&>");
        }
    }
    
    /**
     * 检测assign标签
     * @param type $tag_info 
     */
    private function detect_assign($tag_info = array()){
        $attrs = $tag_info['attrs'];
        $has_var = false;
        $has_value = false;
        foreach($attrs as $i => $item){
            if($item[0] === "var"){
                $has_var = true;
            }elseif($item[0] === "value"){
                $has_value = true;
            }
        }
        //assign标签中未定义var
        if(!$has_var){
            $this->set_msg(FL::MSG_WARN, "Missing the \"var\" attribute in [" . $this->generate_hilight_msg("assign") . "] tag.");
        }
        //assign标签中未定义value
        if(!$has_value){
            $this->set_msg(FL::MSG_WARN, "Missing the \"value\" attribute in [" . $this->generate_hilight_msg("assign") . "] tag.");
        }
    }
    
    /**
     * 检测自定义function的调用
     * @param type $tag_info 
     */
    private function detect_function_call($tag_info = array()){
        
    }
    
    /**
     * 检测自定义function的定义
     * @param type $tag_info 
     */
    private function detect_function_define($tag_info = array()){
        $attrs = $tag_info['attrs'];
        foreach($attrs as $i => $item){
            if($item[0] === "name"){
                //校验自定义function的命名
                if(!preg_match("/^fn_/", preg_replace("/[\"\']/", "", $item[1]))) {
                    $this->set_msg(FL::MSG_WARN, "Custom smarty function must be start of \"fn_\" in [" . 
                            $this->generate_hilight_msg("function name=" . $item[1]) . "] tag.");
                }
            }
        }
    }
    
    /**
     * 检测自定义block
     * @param type $tag_info 
     */
    private function detect_block($tag_info = array()){
        $attrs = $tag_info['attrs'];
        foreach($attrs as $i => $item){
            if($item[0] === "name"){
                //校验自定义block的命名
                if(!preg_match("/^block_/", preg_replace("/[\"\']/", "", $item[1]))) {
                    $this->set_msg(FL::MSG_WARN, "Custom smarty block must be start of \"block_\" in [" . 
                            $this->generate_hilight_msg("block name=" . $item[1]) . "] tag.");
                }
            }
        }
    }
    
    /**
     * 检测自定义extends
     * @param type $tag_info 
     */
    private function detect_extends($tag_info = array()){
        $attrs = $tag_info['attrs'];
        $has_file = false;

        //先判断extends标签是不是在文件的第一行
        if($this->validateCount > 0) {
            $this->set_msg(FL::MSG_WARN, "The [" . $this->generate_hilight_msg("extends") . "] must be the first line in the file.");
        }
        
        foreach($attrs as $i => $item){
            //检测extends标签是否指定了file属性
            if($item[0] === "file"){
                $has_file = true;
                //file不能为空
                if(trim(preg_replace("/[\"\']/", "", $item[1])) === '') {
                    $this->set_msg(FL::MSG_WARN, "The \"file\" attribute can not be empty in [" . 
                            $this->generate_hilight_msg("extends") . "] tag.");
                }
            }
        }
        //extends标签中未定义file
        if(!$has_file){
            $this->set_msg(FL::MSG_WARN, "Missing the \"file\" attribute in [" . $this->generate_hilight_msg("extends") . "] tag.");
        }
    }
    
    /**
     * 检测自定义include
     * @param type $tag_info 
     */
    private function detect_include($tag_info = array()){
        $attrs = $tag_info['attrs'];
        $has_file = false;
        
        foreach($attrs as $i => $item){
            //检测extends标签是否指定了file属性
            if($item[0] === "file"){
                $has_file = true;
                //file不能为空
                if(trim(preg_replace("/[\"\']/", "", $item[1])) === '') {
                    $this->set_msg(FL::MSG_WARN, "The \"file\" attribute can not be empty in [" . 
                            $this->generate_hilight_msg("include") . "] tag.");
                }
            }
        }
        //extends标签中未定义file
        if(!$has_file){
            $this->set_msg(FL::MSG_WARN, "Missing the \"file\" attribute in [" . $this->generate_hilight_msg("include") . "] tag.");
        }
    }
    
    /**
     * 检测自定义for
     * @param type $tag_info 
     */
    private function detect_for($tag_info = array()){
        
    }
    
    /**
     * 检测自定义foreach
     * @param type $tag_info 
     */
    private function detect_foreach($tag_info = array()){
        
    }
}