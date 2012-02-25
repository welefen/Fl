<?php
/**
 * XSS检测：对源码进行XSS扫描
 * 如果在config.php文件中配置了XSS自动修复，则会对源码进行修改
 * @example
      	//引入核心文件
        $buildPath = dirname(__FILE__);	
        include_once($buildPath . '/vender/Fl/Fl.class.php');	
        //创建FL实例
        $flInstance = FL::getInstance();

        //Smarty模板变量左定界符，默认为<&
        $leftDelimiter = '<&';
        //Smarty模板变量右定界符，默认为&>
        $rightDelimiter = '&>';
        //xss安全变量
        $xssSafeVars = array(
                '/^(?:spDomain|spPager|spErrNo|spToken|spCallBackEscape)/ies',
            );
        //这是最大的转义组合，各产品线可各自配置
        $escapeMap = array(
                'js' => 'sp_escape_js',
                'html' => 'sp_escape_html',
                'event' => 'sp_escape_event',
                'data' => 'sp_escape_data',
                'path' => 'sp_path',
                'callback' => 'sp_escape_callback',
                'no_escape' => 'sp_no_escape'
            );
        //是否进行自动修复
        $isAutoFixed = true;

        //单个文件，像这样就可以了，如果是整个目录的话，遍历rootPath吧！
        $rootPath = str_replace('xss','',$buildPath);
        $fileName = 'app/template/test1.html';
        $fileContent = file_get_contents($rootPath . $fileName);

        //方法返回值类型：array('content' => '','error' => array())
        $result = $flInstance->validate_xss(
                $fileContent,	//需要进行检测的内容
                $xssSafeVars,	//XSS安全变量，不需要进行转义
                $isAutoFixed,	//是否进行自动修复
                $escapeMap,		//转义名称列表，各产品线可能不一样
                $leftDelimiter,	//Smarty模板变量左定界符，默认为:<&
                $rightDelimiter	//Smarty模板变量右定界符，默认为:&>
            );

        //下面这个就是自动修复后的结果
        echo "\n\n下面就是修复的结果了：\n" ;
        print_r($result);
 *
 * @author lichengyin
 * @author zhaoxianlie
 */
class Fl_Validate_Xss {
	
	private $_class = '';
	
	private $_pattern = '';
	
	private $_check_pattern = array();
	
	public $fl_instance = null;
	
	public $_left_delimiter = '<&';
	public $_right_delimiter = '&>';
		
	public $xssSafeVars = array();
    public $showMsgMethod = null;
	/**
	 * 是否进行XSS自动修复
	 * @var unknown_type
	 */
	private $is_xss_auto_fixed = true;
	
	/**
	 * XSS自动修复所需变量
	 * @var unknown_type
	 */
	private $xss_auto_fixed = array(
		//当前解析到的文件的内容
		'cur_file_content'	=> '',
		//当前正在解析的块儿内容（原内容）
		'cur_parse_raw_content'	=> '',
		//当前正在解析的块儿内容
		'cur_parse_content'	=> '',
		//当前正在解析的语句原内容
		'cur_parse_raw_sentence'	=> '',
		//当前正在解析的原内容
		'cur_parse_sentence'	=> '',
		//记录语句块儿被替换的次数
		'stt_replace_time'	=> 0,
		//语句在内容块中出现的次数
		'sentence_in_content'	=> array(),
		//内容在文件中出现的次数
		'content_in_file'	=> array(),
		//记录内容块儿被替换的次数
		'ctt_replace_time'	=> 0,
		//XSS UI变量
		'tpl_vars'			=> array(),
		//记录模板变量被替换的次数
		'tpl_replace_time'		=> 0,
		//记录当前UI变量需要进行的escapse方式，如"|sp_escapse_js"
		'tpl_replace_str'		=> '',
		//某模板中XSS漏洞的个数
		'xss_num'			=> 0,
		//模板变量的正则
		'tpl_var_pattern'	=> '',
		//标记当前解析到的内容中是否含有xss
		'has_xss_in_cur_content' => false,
        //转义中，重复的，应该被删掉的，不能共存的，比如js和data转义同时存在时，删掉js转义
        'duplicated_escape' => false
	);
	
    /**
     * XSS结果
     * @var type XSS结果
     */
    private $_xss_result = array(
        'error' => array(),
        'content' => ''
    );
    
    /**
     * 构造器
     */
	public function __construct(){
		$this->_class = get_class($this);
		$this->_left_delimiter = $leftDelimiter;
		$this->_right_delimiter = $rightDelimiter;
	}
	
    /**
     * 方法入口，检测及自动修复
     * @param type $fileContent 需要进行检测的内容
     * @param type $xssSafeVars XSS安全变量，不需要进行转义
     * @param type $isAutoFixed 是否进行自动修复，如果为true，方法返回修复后的结果
     * @param type $escapeMap 转义名称列表，各产品线可能不一样
     * @param type $leftDelimiter Smarty模板变量左定界符，默认为:<&
     * @param type $rightDelimiter Smarty模板变量右定界符，默认为:&>
     * @return type 
     */
	public function run($fileContent = '',$xssSafeVars = array(),$isAutoFixed = false , $escapeMap = array(), $leftDelimiter = '<&', $rightDelimiter = '&>' ){
		$this->_pattern = str_replace(
			array('LEFT', 'RIGHT'),
			array(preg_quote($leftDelimiter, '/'), preg_quote($rightDelimiter, '/')), 
			'/LEFT\s*\$(.*?)\s*RIGHT/ies'
		);
		$this->_check_pattern = $escapeMap ;
		$this->xss_auto_fixed['tpl_var_pattern'] = '/(\$[^\|]+)(.*?)/ies';
        $this->_xss_result = array(
            'error' => array(),
            'content' => ''
        );
		//xss安全变量
		$this->xssSafeVars = $xssSafeVars;
		//是否进行XSS自动修复
		$this->is_xss_auto_fixed = $isAutoFixed;
        //获取模板内容
        $this->xss_auto_fixed['cur_file_content'] = $fileContent;

		//XSS自动修复
		if($this->is_xss_auto_fixed) {
			//解析每个模板之前，需要重置一些变量
			$this->xss_auto_fixed['xss_num'] = 0;
			$this->check_single_file($fileContent);
		} else {
			$this->check_single_file($fileContent); 
		}
		//返回结果
        $this->_xss_result['content'] = $this->xss_auto_fixed['cur_file_content'];
        return $this->_xss_result;
	}
	
	/**
	 * 检测单一文件
	 * @param string $fileContent
	 */
	public function check_single_file($fileContent = ''){
		$content = $fileContent;
		$analytic_content = $this->fl_instance->analytic_html($content);

		if ($this->check_is_html($analytic_content, $content)){ //如果不是一个html文档，一般都是异步数据接口
			foreach ($analytic_content as $item){
				//记录当前正在处理的内容
				$this->_markCurParseContent($item[0]);
				if ($item[1] === FL::HTML_TAG_START){
					$tag_info = $this->fl_instance->analytic_html($item[0], 2);
					foreach ($tag_info[2] as $attrs){
						if ($attrs[1] && strpos($attrs[0], 'on') === 0){//event
							$this->_check_it($attrs[1], 'event');
						}else if($attrs[0] == 'src' || $attrs[0] == 'href' || 
                                (strtolower ($tag_info[1]) == "form" && $attrs[0] == 'action')){ //url
							$this->_check_it($attrs[1], 'path');
						}else{
							$this->_check_it($attrs[1] ? $attrs[1] : $attrs[0], 'html');
						}
					}
				}else if ($item[1] === FL::HTML_JS_CONTENT && trim($item[0])){
					$this->check_js_content(trim($item[0]), 'js');
				}else if ($item[1] !== FL::HTML_XML){
					$this->_check_it($item[0], 'html');
				}
			}
		}else{			
			return $this->check_js_content($content, '');
		}
	}
	/**
	 * 
	 * 检测JS的内容转义
	 * @param string $content
	 * @param string $type
	 */
	public function check_js_content($content = '', $type = ''){
		$analytic_content = $this->fl_instance->analytic_js($content);
		foreach ($analytic_content as $item){
			if ($item[1] === FL::FL_TPL_DELIMITER || $item[1] === FL::JS_STRING){
				//记录当前正在处理的内容
				$this->_markCurParseContent($item[0]);
				$this->_check_it($item[0], $type ? $type : 'js');
			}
		}
	}
	/**
	 * 
	 * 检测内容真的是html,主要是判断内容中是否至少含有一个标签
	 * 这种判断方式并不完全安全。（有可能异步接口里含有标签，但概率较小。没想到其他更好的判断方式）
	 * 
	 * XML也是一种特殊的HTML
	 * @param array $analytic_content
	 */
	public function check_is_html($analytic_content = array(), $content = ''){
		$tag = array();
		for ($i=0,$count=count($analytic_content);$i<$count;$i++){
			$item = $analytic_content[$i];
			if ($item[1] === FL::HTML_XML){
				return true;
			}
			//只检测tag_start即可
			if($item[1] === FL::HTML_TAG_START){
				if ($i > 0){
					$preItem = $analytic_content[$i-1];
					if ($preItem[1] === FL::HTML_CONTENT){
						$preString = trim($preItem[0]);
						//获取上一个特征值的最后一个字符
						$preLastChar = $preString[strlen($preString) - 1];
						//异步接口里可能也含有一些标签，但这些标签都用引号包含起来了。
						if ($preLastChar === '"' || $preLastChar === "'"){
							continue;
						}
					}
				}
				$tag[] = $item[0];
				//如果多余5个标签认为是html
				if (count($tag) >= 5){
					return true;
				}
			}
		}
		//检测有标签但小于5个，这时候用js类型分析
		if (count($tag) > 0){
			$analytic_content = $this->fl_instance->analytic_js($content);
			foreach ($analytic_content as $item){
				if (count($tag) === 0) return false;
				if ($item[1] === FL::JS_STRING){
					$notFilte = array();
					$findPos = array();
					foreach ($tag as $t){
						$pos = 0;
						while (true){
							$pos = strpos($item[0], $t, $pos);
							if ($pos === false){
								$notFilte[] = $t;
								break;
							}else{
								if (!in_array($pos, $findPos)){
									$findPos[] = $pos;
									break;
								}else {
									$pos += strlen($t);
								}
							}
						}
					}
					$tag = $notFilte;
				}
			}
			if (count($tag) > 0) return true;
		}
		return false;
	}
	
	/**
	 * 
	 * 检测每个smarty变量转义是否正确
	 * @param string $value
	 * @param string $type
	 */
	private function _check_it($content, $type = 'js'){
		$this->xss_auto_fixed['tpl_vars'] = array();
		$this->xss_auto_fixed['sentence_in_content'] = array();

		//记录当前解析的语句在内容块中出现的次数
		$this->_markSentenceInContent($content);

		//寻找模板变量
		preg_match_all($this->_pattern, $content, $matches);
		$content = $matches[1];
		$tpl_name = '';
	
		foreach ($content as $value){
			$value = trim($this->repairPregReplace($value));
			$lv = strtolower($value);
				
			//记录模板变量在句子中出现的次数
			$this->_markTplVarInSentence("$" . $value);
		
			if (strpos($lv, 'smarty.get.callback') !== false 
				|| strpos($lv, 'smarty.post.callback') !== false 
				|| strpos($lv, 'spcallback') !== false){
				
				$type = 'callback';
			}
			if (strpos($value, 'smarty.foreach') !== false //smarty本身的
				|| strpos($value, 'smarty.capture') !== false
				|| strpos($value, 'smarty.now') !== false
				|| strpos($value, 'smarty.section') !== false
				|| strpos($value, '+') !== false //+运算
				|| strpos($value, '=') !== false //赋值
				|| $this->_check_pattern['path'] 
                    && strpos($value, $this->_check_pattern['path']) !== false //已经使用了path进行了url转义
				|| $this->_check_pattern['no_escape'] 
                    && strpos($value, $this->_check_pattern['no_escape']) !== false //已经标示成了不需要转义
				|| strpos($value, '-') !== false ){
				continue;
			}else{
				//配置的安全变量
				$safe_var = $this->xssSafeVars;
				$flag = false;
				foreach($safe_var as $val){
					if(preg_match($val, $value)) {
						$flag = true;
						break;
					}
				}
				if ($flag) continue;
			}

            //下面开始判断某个模板变量是否进行了某种转义
            //当且仅当配置了该转义，才会进行检测和修复
			if ($this->_check_pattern[$type] && strpos($value, $this->_check_pattern[$type]) === false){
				//event为最高转义类型，用了这个转义，就不用其他方式的转义了。(除了callback)
				if ($type != 'callback' && $this->_check_pattern['event'] && strpos($value, $this->_check_pattern['event']) !== false){
					continue;
				}
				//data转义包含js，所以如果使用了data进行了转义，则不应该报错了。
				if ($type === 'js' || $type === 'html'){
					if ($this->_check_pattern['data'] && strpos($value, $this->_check_pattern['data']) !== false){
						continue;
					}
				}
				$str = '|' . $this->_check_pattern[$type];

				//XSS自动修复
                $this->_replaceTplVarInSentence("$" . $value,$str);
				//记录检测到的xss信息
                $this->_xss_result['error'][] = "[\033[31m $" . $value . " \033[0m] must be use \"" . $type . "\" escape.";
			}else{
                //js转义和data转义不能共存
                if($this->_check_pattern['js'] && strpos($value, $this->_check_pattern['js']) 
                        && $this->_check_pattern['data'] && strpos($value, $this->_check_pattern['data'])){
                    $str = '|' . $this->_check_pattern['js'];
                    //XSS自动修复
                    $this->_replaceTplVarInSentence("$" . $value,$str,-1);
                    $this->_xss_result['error'][] = "[\033[31m $" . $value . " \033[0m] can not be use \"" . 
                                $this->_check_pattern['js'] . "\" and \"" . 
                                $this->_check_pattern['data'] . "\" to escape at the same time.";
                }
            }
		}
	
		//替换当前语句
		$this->_replaceSentenceInContent();
	}

	/**
	 * 记录当前正在解析的语句
	 * @param unknown_type $content
	 */
	private function _markCurParseSentence($content){
		if($this->is_xss_auto_fixed){
			//保存当前解析的内容
			$this->xss_auto_fixed['cur_parse_raw_sentence'] = $content;
			//在当前解析到的内容中进行XSS修复
			$this->xss_auto_fixed['cur_parse_sentence'] = $content;
		}
	}
	
    /**
     * 
     * repair content from preg_replace
     * use preg_replace(PATTERN,"CLASS_METHOD('\\1')", $content)
     * then will replace '"' to '\\"' in \\1
     * @param string $content
     */
    public function repairPregReplace($content = ''){
        if (!is_string($content)) return $content;
        $content = str_replace('\\"', '"', $content);
        return $content;
    }
	
	/**
	 * 记录当前正在解析的内容
	 * @param unknown_type $content
	 */
	private function _markCurParseContent($content){
		if($this->is_xss_auto_fixed){
			//记录某段内容在文件中出现的次数
			$this->_markContentInFile($content);
			//保存当前解析的内容
			$this->xss_auto_fixed['cur_parse_raw_content'] = $content;
			//在当前解析到的内容中进行XSS修复
			$this->xss_auto_fixed['cur_parse_content'] = $content;
			
		}
	}
	
	/**
	 * 记录某段内容在文件中出现的次数
	 * @param unknown_type $content
	 */
	private function _markContentInFile($content){
		//在这里统计某段内容截止到解析时刻出现的次数
		if($this->is_xss_auto_fixed){
			//记录内容相同的块儿，避免在内容替换时弄错
			$not_in_arr = true;
			foreach($this->xss_auto_fixed['content_in_file'] as $cif_key => $cif_value) {
				if($cif_value['content'] === $content) {
					$cif_value['count']++;
					$not_in_arr = false;
					break;
				}
			}
			if($not_in_arr) {
				$this->xss_auto_fixed['content_in_file'][] = array(
					'content'	=> $content,
					'count'		=> 1
				);
			}
		}
	}

	/**
	 * 记录某条语句在代码中出现的次数
	 * @param $content
	 */
	private function _markSentenceInContent($content){
		if($this->is_xss_auto_fixed){
			//记录当前语句
			$this->_markCurParseSentence($content);

			//记录语句相同的代码行，避免在内容替换时弄错
			$not_in_arr = true;
			foreach($this->xss_auto_fixed['sentence_in_content'] as $sic_key => $sic_value) {
				if($sic_value['content'] === $content) {
					$sic_value['count']++;
					$not_in_arr = false;
					break;
				}
			}
			if($not_in_arr) {
				$this->xss_auto_fixed['sentence_in_content'][] = array(
					'content'	=> $content,
					'count'		=> 1
				);
			}
		}
	}
	
	/**
	 * 记录某个模板变量在内容中出现的次数
	 * @param $tpl_name
	 */
	private function _markTplVarInSentence($tpl_name){
		if($this->is_xss_auto_fixed){
			//记录同名的模板变量，避免在内容替换时弄错
			if(!$this->xss_auto_fixed['tpl_vars'][$tpl_name]) {
				$this->xss_auto_fixed['tpl_vars'][$tpl_name] = 1;
			} else {
				$this->xss_auto_fixed['tpl_vars'][$tpl_name]++;
			}
		}
	}
	
	/**
	 * 在语句中进行模板变量替换（XSS修复）
	 * @param $tpl_name 模板变量
	 * @param $str_suffix 转义
	 * @param $add 是否是增加转义，1表示是，-1表示不是，默认是
	 */
	private function _replaceTplVarInSentence($tpl_name,$str_suffix,$add = 1){
		if($this->is_xss_auto_fixed) {					
			//内容替换，进行XSS自动修复
			$this->xss_auto_fixed['tpl_replace_time'] = 0;
			$this->xss_auto_fixed['tpl_replace_str'] = $str_suffix;
			
            //设置标志位
            if($add === 1) {
                $this->xss_auto_fixed['duplicated_escape'] = false;
            }elseif($add === -1){
                $this->xss_auto_fixed['duplicated_escape'] = true;
            }
            
			//模板变量XSS修复
            $reg = str_replace(
                array('LEFT', 'RIGHT'),
                array(preg_quote($this->_left_delimiter, '/'), preg_quote($this->_right_delimiter, '/')), 
                "/LEFT\s*(" . preg_quote($tpl_name,"/") . ")\s*RIGHT/s"
            );
			$this->xss_auto_fixed['cur_parse_sentence'] = preg_replace_callback(
				$reg ,
				array(&$this,'replaceTplVarCallback'),
				$this->xss_auto_fixed['cur_parse_sentence']);
		}
	}
	
	/**
	 * 进行XSS自动修复时，替换相应的UI变量内容
	 * @param unknown_type $matches
	 */
	private function replaceTplVarCallback($matches){
		$this->xss_auto_fixed['tpl_replace_time']++;
		if($this->xss_auto_fixed['tpl_vars'][$matches[1]] === $this->xss_auto_fixed['tpl_replace_time']) {
			//XSS数量加1
			$this->xss_auto_fixed['xss_num']++;
			$this->xss_auto_fixed['has_xss_in_cur_content'] = true;
            
            //增加转义
            if($this->xss_auto_fixed['duplicated_escape'] === false) {
                return preg_replace("/" . preg_quote($matches[1],"/") . "/s", 
                        $matches[1] . $this->xss_auto_fixed['tpl_replace_str'], $matches[0]);
            }else{
                //去除重复的转义
                return preg_replace("/" . preg_quote($this->xss_auto_fixed['tpl_replace_str'],"/") . "/s", "" , $matches[0]);
            }
		}
		return $matches[0];
	}


	/**
	 * 将当前解析的语句替换到内容块中
	 */
	private function _replaceSentenceInContent(){
		if($this->xss_auto_fixed['has_xss_in_cur_content']) {
			$this->xss_auto_fixed['stt_replace_time'] = 0;
			
			$this->xss_auto_fixed['cur_parse_content'] = preg_replace_callback(
				'/' . preg_quote($this->xss_auto_fixed['cur_parse_raw_sentence'],'/') . '/s' ,
				array(&$this,'replaceSentenceCallback'),
				$this->xss_auto_fixed['cur_parse_content']);
				
			//重置原内容，为了解决解析标签属性的情况，可能一个标签含有多个属性，而且都需要进行xss修复
			$this->xss_auto_fixed['cur_parse_raw_sentence'] = $this->xss_auto_fixed['cur_parse_sentence'];
			//记录当前正在处理的内容
			$this->_markCurParseSentence($this->xss_auto_fixed['cur_parse_raw_sentence']);
				
			//文件内容保存
			$this->_replaceContentInFile();
			
			$this->xss_auto_fixed['has_xss_in_cur_content'] = false;
		}
	}
	
	/**
	 * 进行XSS自动修复时，替换相应的语句
	 * @param unknown_type $matches
	 */
	private function replaceSentenceCallback($matches){
		$this->xss_auto_fixed['stt_replace_time']++;

		//记录内容相同的块儿，避免在内容替换时弄错
		$not_in_arr = true;
		foreach($this->xss_auto_fixed['sentence_in_content'] as $sic_key => $sic_value) {
			if($sic_value['content'] === $matches[0] && $sic_value['count'] === $this->xss_auto_fixed['stt_replace_time']) {
				$not_in_arr = false;
				break;
			}
		}
		if(!$not_in_arr) {
			return $this->xss_auto_fixed['cur_parse_sentence'];
		}
		
		return $matches[0];
	}
	
	/**
	 * 将当前解析到的内容替换到源文件中
	 */
	private function _replaceContentInFile(){
		if($this->xss_auto_fixed['has_xss_in_cur_content']) {
			$this->xss_auto_fixed['ctt_replace_time'] = 0;
			$this->xss_auto_fixed['cur_file_content'] = preg_replace_callback(
				'/' . preg_quote($this->xss_auto_fixed['cur_parse_raw_content'],'/') . '/s' ,
				array(&$this,'replaceContentCallback'),
				$this->xss_auto_fixed['cur_file_content']);
			//重置原内容，为了解决解析标签属性的情况，可能一个标签含有多个属性，而且都需要进行xss修复
			$this->xss_auto_fixed['cur_parse_raw_content'] = $this->xss_auto_fixed['cur_parse_content'];
			
			//记录当前正在处理的内容
			$this->_markCurParseContent($this->xss_auto_fixed['cur_parse_raw_content']);
		}
	}

	/**
	 * 进行XSS自动修复时，替换相应的内容块儿
	 * @param unknown_type $matches
	 */
	private function replaceContentCallback($matches){
		$this->xss_auto_fixed['ctt_replace_time']++;
	
		//记录内容相同的块儿，避免在内容替换时弄错
		$not_in_arr = true;
		foreach($this->xss_auto_fixed['content_in_file'] as $cif_key => $cif_value) {
			if($cif_value['content'] === $matches[0] && $cif_value['count'] === $this->xss_auto_fixed['ctt_replace_time']) {
				$not_in_arr = false;
				break;
			}
		}
		
		if(!$not_in_arr) {
			return $this->xss_auto_fixed['cur_parse_content'];
		}
		return $matches[0];
	}
}
