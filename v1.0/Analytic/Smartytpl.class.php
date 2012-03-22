<?php
/**
 * 
 * html词法分析类
 * 对原内容进行分析，不做任何trim处理
 * 
 * @author lichengyin
 *
 */
class Fl_Analytic_Smartytpl{
	
	/**
	 * 当前解析到的位置
	 * @var int
	 */
	public $parsePos = 0;
	
	/**
	 * 要解析的内容
	 * @var string
	 */
	public $content = '';
	/**
	 * 要解析的内容长度
	 * @var int
	 */
	public $contentLength = 0;
    
    /**
     * 构造器
     */
	public function __construct(){
		
	}
	/**
	 * 
	 * 默认是进行html分析
	 * type不为1的时候进行tag属性分析
	 * @param string $content
	 * @param int $type
	 */
	public function run($content = ''){
		$this->content = $content;
		$this->contentLength = strlen($this->content);
        return $this->deal_smarty_tag($content);
	}
    
    /**
	 * 
	 * 分析Smarty tag标签的属性名和属性值
	 * 一个完整的tag里可能包含换行符
	 * @param string $tagContent
	 */
	public function deal_smarty_tag($tagContent = ''){
        //smarty定界符
        $this->len_ld = strlen($this->fl_instance->left_delimiter);
        $this->len_rd = strlen($this->fl_instance->right_delimiter);
  
		//tag end
		$tagContent = trim($tagContent);
		//将换行符替换为空格
		$tagContent = str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $tagContent);
		//将最后的>和/去掉, 最多只能去一个，因为smarty的右定界符可能含有/和>
		$tagContent = trim(substr($tagContent, 0, strlen($tagContent) - $this->len_ld));

		if ($tagContent[strlen($tagContent) - $this->len_ld - 1] === '/'){
			$lastChars = substr($tagContent, strlen($tagContent) - $this->len_rd - 1);
			if ($this->fl_instance->right_delimiter != $lastChars){
				$tagContent = trim(substr($tagContent, 0, strlen($tagContent) - 1));
			}
		}
		$this->parsePos = 0;
        $this->raw_content = $tagContent;
		$this->content = trim(substr($tagContent, $this->len_ld));
		$this->contentLength = strlen($tagContent);
        
		//tag start
		$result = array(
            "type"      => FL::SMARTY_TAG_START,  
            "name"      => null,
            "attrs"      => array()
        );
        
        //解析smarty标签
        $tagResult = $this->read_smarty_tagname();
        $type = $tagResult['type'];
        switch ($type) {
            //判断当前标签是否为一个smarty的结束标签
            case FL::SMARTY_TAG_END:
                return $tagResult;
                break;
            //判断当前标签是否为一个独立的模板变量
            case FL::SMARTY_TPL_VAR:
                return $tagResult;
                break;
            //判断当前标签是否为一个自定义function的调用
            case FL::SMARTY_FUNCTION_CALL:
                return $tagResult;
                break;

            default:
                break;
        }
        
        //解析smarty属性
        $attrResult = $this->read_smarty_attrs($type);
        $result = array_merge($result, $tagResult);
        $result['attrs'] = $attrResult;

        return $result;
	}
    
    /**
     * 读取smarty标签
     * @return type 
     */
    private function read_smarty_tagname(){
		$tagName = '';
        $isTagName = false;
        $result = array(
            'type'=>FL::SMARTY_TAG_START,
            'name'=>'',
            'attrs'=>array()
        );
		if (substr($this->raw_content, 0, $this->len_ld + 1) === $this->fl_instance->left_delimiter . '/') {
            $result['type'] = FL::SMARTY_TAG_END;
            $result['name'] = trim(substr($this->raw_content, $this->len_ld + 1, 
                    strlen($this->raw_content) - ($this->len_ld + 1)));
			return $result;
		}
		while (true){
			if ($this->parsePos >= $this->contentLength){
				break;
			}
			$char = $this->content[$this->parsePos];
			$this->parsePos++;
            //过滤"<&"和tagName之间的空白符，如："<& include"
            if(preg_match("/\s+/", $char) && !$isTagName){
                continue;
            }
            //非tagName的字符，结束while
            elseif (!preg_match("/^[a-z0-9_]{1}$/i", $char)){
                //smarty模板变量
                if(preg_match("/\$/", $char) && !$isTagName){
                    $result["type"] = FL::SMARTY_TPL_VAR;
                    $result["name"] = substr($this->content, $this->parsePos - 1);
                    return $result;
                }elseif(preg_match("/\(/", $char)){ //这种情况下，视为自定义方法的调用
                    $result['type'] = FL::SMARTY_FUNCTION_CALL;
                    $result['name'] = $tagName;
                    $result['attrs'] = substr($this->content, $this->parsePos - 1);
                    return $result;
                    break;
                }else{
                    $this->parsePos--;
                    break;
                }
			}
            //tagName
            else{
                $isTagName = true;
				$tagName .= $char;
			}
		}
        $result['name'] = $tagName;
        return $result;
    }
    
    /**
     * 读取smarty的属性列表
	 * @param string $type 当前tag的类型
     * @return type 
     */
    private function read_smarty_attrs($type = 0){
        $attr = $name = '';
        $result = array();
        
		while (true){
			if ($this->parsePos >= $this->contentLength){
				break;
			}
			$char = $this->content[$this->parsePos];
			$this->parsePos++;
			if ($char === '"' || $char === "'"){
				//处理value="<&if $test=""&>1<&else&>0<&/if&>"的情况
				$this->parsePos++;
				$o = $this->fl_instance->getTplDelimiterToken($char, $this);
				$re = $char;
				if ($o){
					$re .= $o[0];
				}else{
					$this->parsePos--;
				}
				$re .=  $this->_getUnformated($char);
				$result[] = array($name, $re);
				$name = $re = '';
			}else if ($char === '='){
				if ($attr !== ''){
					$name = $attr;
				}
				$attr = '';
			}else if ($char === ' '){
				if ($attr !== ''){
					if ($name){
						$result[] = array($name, $attr);
					}else{
                        //过滤属性名后的空格
                        $curPos = $this->parsePos;
                        while($curPos < $this->contentLength && preg_match("/\s+/",$this->content[$curPos])){
                            $curPos++;
                        }
                        if($this->content[$curPos] === "=") {
                            $this->parsePos = $curPos;
                            continue;
                        }
						$result[] = array($attr, '');
					}
				}
				$name = $attr = '';
			}else{
				if ($char !== ' ') $attr .= $char;
			}
		}
		if ($attr !== ''){
			if ($name){
				$result[] = array($name, $attr);
			}else{
				$result[] = array($attr, '');
			}
			$name = $attr = '';
		}
        return $result;
    }
    
    /**
     * 获取需要的字符
     * @param type $char
     * @param type $orign
     * @return type 
     */
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
}
