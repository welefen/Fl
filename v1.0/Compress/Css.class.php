<?php
/**
 * compress css
 * regular from yui css compressor
 * @author lichengyin
 *
 */
class Fl_Compress_Css{
	
	private $_output = array();
	
	public $lineBreakPos = 8000;

	public function run($content = ''){
		$analyticContent = $this->fl_instance->analytic_css($content);
		$lineStringLength = 0;
		for ($i=0,$count=count($analyticContent);$i<$count;$i++){
			list($tokenText, $tokenType) = $analyticContent[$i];
			$tokenText = trim($tokenText);
			if ($tokenType === FL::FL_NEW_LINE || ($tokenType === FL::CSS_COMMENT && substr($tokenText, 0, 3) !== '/*!')){
				continue;
			}else if ($tokenType === FL::CSS_SELECTOER){
				$tokenText = $this->fl_instance->splitCssSelector($tokenText);
				$tokenText = join(',', $tokenText);
				//选择符里含有注释, 去除注释和换行符
				$tokenText = preg_replace("/(?:\/\*.*?\*\/|\n)/is", "", $tokenText);
				//$tokenText = str_replace("\n", "", $tokenText);
			}
			$lineStringLength += strlen($tokenText);
			$this->_output[] = $tokenText;
			if ($lineStringLength > $this->lineBreakPos && $tokenType == FL::CSS_SELECTOER_END){
				$this->_output[] = "\n";
				$lineStringLength = 0;
			}
			
		}
		$result = join('', $this->_output);
		//compress rule come from yui css compressor
		//Replace 0 0 0 0; with 0.
		$result = str_replace(":0 0 0 0;", ":0;", $result);
		$result = str_replace(":0 0 0;", ":0;", $result);
		$result = str_replace(":0 0;", ":0;", $result);
		//Replace background-position:0; with background-position:0 0;
		$result = str_replace("background-position:0;", "background-position:0 0;", $result);
		// Replace 0(px,em,%) with 0.
		$pattern = "/([\s:])(0)(px|em|%|in|cm|mm|pc|pt|ex)/is";
		$result = preg_replace($pattern, "$1$2", $result);
		//Replace 0.6 to .6, but only when preceded by : or a white-space
		$result = preg_replace("/(:|\s)0\.(\d+)/is", "$1.$2", $result);
		// Shorten colors from #AABBCC to #ABC. Note that we want to make sure
        // the color is not preceded by either ", " or =. Indeed, the property
        //     filter: chroma(color="#FFFFFF");
        // would become
        //     filter: chroma(color="#FFF");
        // which makes the filter break in IE.
        $pattern = "/([^\"'=\s])(\s*)#([0-9a-fA-F])\\3([0-9a-fA-F])\\4([0-9a-fA-F])\\5/is";
        $result = preg_replace($pattern, "$1$2#$3$4$5", $result);
        
		//Replace empty selector to ''
		//$result = preg_replace("/[^{}\/]+\{(?:;*)\}/is", "", $result);
		//remove last ;
		$result = str_replace(';}', '}', $result);
		$result = preg_replace("/\;+/is", ";", $result);
		return $result;
	}
}