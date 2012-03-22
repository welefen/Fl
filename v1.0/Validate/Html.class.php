<?php
class Fl_Validate_Html{
	
	public $analyticContent = array();
	
	public $analyticCount = 0;
	
	public $validateCount = 0;
	
	public $options = array();
	
	private $_tagList = array(
		'a', 'abbr', 'acronym', 'address', 'applet', 'area', 'audio', 'b', 'base',
		'basefont', 'bdo', 'big', 'blockquote', 'body', 'br', 'button', 'canvas', 
		'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'dd', 'del', 'dfn',
		'dir', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'font', 'form', 'frame',
		'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html', 'i', 'iframe',
		'img', 'input', 'ins', 'isindex', 'kbd', 'keygen', 'label', 'layer', 'legend', 'li',
		'link', 'listing', 'map', 'marquee', 'menu', 'meta', 'nobr', 'noembed', 'noframes',
		'nolayer', 'noscript', 'object', 'ol', 'optgroup', 'option', 'p', 'param', 'plaintext',
		'pre', 'q', 's', 'samp', 'script', 'select', 'small', 'source', 'span', 'strike', 'strong',
		'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead',
		'title', 'tr', 'tt', 'u', 'var', 'video', 'wbr', 'xmp',
		//html5 tag 
		'abbr', 'article', 'aside', 'audio', 'canvas', 'details', 'figcaption', 'figure', 'footer',
		'header', 'hgroup', 'mark', 'meter', 'nav', 'output', 'progress', 'section', 'summary', 'time', 'video'
	);
	
	private $_attrList = array(
		'abbr' 				=> array('td','th'),
		'accept' 			=> array('form', 'input'),
		'accept-charset' 	=> array('form'),
		'accesskey' 		=> array('a', 'area', 'button', 'input', 'label', 'legend', 'textarea'),
		'action' 			=> array('form'),
		'align' 			=> '*',
		'alink' 			=> array('body'),
		'alt' 				=> array('applet', 'area', 'img', 'input'),
		'archive' 			=> array('applet', 'object'),
		'aria-checked' 		=> array('div', 'span'),
		'aria-level' 		=> array('div', 'span'),
		'aria-pressed' 		=> array('div', 'span'),
		'aria-valuemax' 	=> array('div', 'span'),
		'aria-valuemin' 	=> array('div', 'span'),
		'aria-valuenow' 	=> array('div', 'span'),
		'autocapitalize' 	=> array('input'),
		'autocomplete' 		=> array('input'),
		//'autocorrect' 	=> array(''),
		'autoplay' 			=> array('audio', 'video'),
		'autosave' 			=> array('input'),
		'axis' 				=> array('td', 'th'),
		'background' 		=> array('body'),
		'behavior' 			=> array('marquee'),
		'bgcolor' 			=> array('body', 'table', 'td', 'th', 'tr'),
		'bgproperties' 		=> array('body'),
		'border' 			=> array('img', 'object'),
		'bordercolor' 		=> array('table'),
		'cellpadding' 		=> array('table'),
		'cellspacing' 		=> array('table'),
		'challenge' 		=> array('keygen'),
		'char' 				=> array('col', 'colgroup', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr'),
		'charoff' 			=> array('col', 'colgroup', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr'),
		'charset' 			=> array('a', 'link', 'script'),
		'checked' 			=> array('input'),
		'cellborder' 		=> '*',
		'cite' 				=> array('blockquote', 'q'),
		'class' 			=> '*',
		'classid' 			=> array('object'),
		'clear' 			=> array('br'),
		'code' 				=> array('applet'),
		'codebase' 			=> array('object'),
		'codetype' 			=> array('object'),
		'color' 			=> array('basefont', 'font'),
		'cols' 				=> array('frameset', 'textarea'),
		'colspan' 			=> array('td', 'th'),
		'compact' 			=> array('dir', 'dl', 'menu', 'ol', 'ul'),
		'composite' 		=> array('img'),
		'content'			=> array('meta'),
		'contenteditable' 	=> '*',
		'controls' 			=> array('audio', 'video'),
		'coords' 			=> array('area'),
		'data' 				=> array('object'),
		'datetime' 			=> array('del', 'ins'),
		'declare' 			=> array('object'),
		'defer' 			=> array('script'),
		'dir' 				=> '*',
		'direction' 		=> array('marquee'),
		'disabled' 			=> array('button', 'input', 'optgroup', 'option', 'select', 'textarea'),
		'enctype'			=> array('form'),
		'end' 				=> array('audio', 'video'),
		'face' 				=> array('basefont', 'font'),
		'for' 				=> array('label'),
		'frame' 			=> array('table'),
		'frameborder' 		=> array('frame','iframe'),
		'headers' 			=> array('td', 'th'),
		'height' 			=> array('applet', 'iframe', 'img', 'object', 'td', 'th' ,'video'),
		'hidden' 			=> array('embed'),
		'href' 				=> array('a', 'area', 'base', 'link'),
		'hreflang' 			=> array('a', 'link'),
		'hspace' 			=> array('applet', 'img', 'object'),
		'http-equiv' 		=> array('meta'),
		'id' 				=> '*',
		'incremental' 		=> array('input'),
		'ismap' 			=> array('img', 'input'),
		'keytype' 			=> array('keygen'),
		'label' 			=> array('optgroup', 'option'),
		'lang' 				=> '*',
		'language' 			=> array('script'),
		'leftmargin' 		=> array('body'),
		'link' 				=> array('link'),
		'longdesc' 			=> array('frame', 'iframe', 'img'),
		'loop' 				=> array('embed', 'marquee', 'object'),
		'loopend' 			=> array('audio', 'video'),
		'loopstart' 		=> array('audio', 'video'),
		'manifest' 			=> array('html'),
		'marginheight' 		=> array('frame', 'iframe'),
		'marginwidth' 		=> array('frame', 'iframe'),
		'max' 				=> array('input'),
		'maxlength' 		=> array('input'),
		'mayscript' 		=> array('applet'),
		'media' 			=> array('link', 'source', 'style'),
		'method' 			=> array('form'),
		'min' 				=> array('input'),
		'multiple' 			=> array('select'),
		'name' 				=> '*',
		'nohref' 			=> array('area'),
		'noresize' 			=> array('frame'),
		'nosave' 			=> '*',
		'noshade' 			=> array('hr'),
		'nowrap' 			=> array('td', 'th'),
		'object' 			=> array('applet'),
		'onabort' 			=> array('img'),
		'oversrc' 			=> array('img'),
		'placeholder' 		=> array('input'),
		'playcount' 		=> array('audio', 'video'),
		'pluginpage' 		=> array('embed'),
		'pluginspage' 		=> array('embed'),
		'pluginurl' 		=> array('embed'),
		'poster' 			=> array('video'),
		'precision' 		=> '*',
		'profile' 			=> array('head'),
		'prompt' 			=> array('isindex'),
		'readonly' 			=> array('textarea'),
		'rel' 				=> array('a', 'link'),
		'results' 			=> array('input'),
		'rev' 				=> array('a', 'link'),
		'role' 				=> array('div', 'span'),
		'rows' 				=> array('frameset', 'textarea'),
		'rowspan' 			=> array('td', 'th'),
		'rules' 			=> array('table'),
		'scheme' 			=> array('meta'),
		'scope' 			=> array('td', 'th'),
		'scrollamount' 		=> array('marquee'),
		'scrolldelay' 		=> array('marquee'),
		'scrolling' 		=> array('frame', 'iframe'),
		'selected' 			=> array('option'),
		'shape' 			=> array('a','area'),
		'size' 				=> array('basefont', 'font','hr','input', 'select'),
		'span' 				=> array('col', 'colgroup'),
		'src' 				=> array('audio', 'frame', 'iframe', 'img', 'input', 'script','video'),
		'standby'			=> array('object'),
		'start' 			=> array('audio','ol','video'),
		'style' 			=> '*',
		'summary' 			=> array('table'),
		'tabindex' 			=> '*',
		'tableborder' 		=> array('table'),
		'target' 			=> array('a', 'area', 'base','form','link'),
		'text' 				=> array('body'),
		'title' 			=> '*',
		'topmargin' 		=> array('body'),
		'truespeed' 		=> array('marquee'),
		'type' 				=> array('a', 'button','input','li','link','object','ol','param','script','style'),
		'usemap' 			=> array('img','input','object'),
		'valign' 			=> array('col','colgroup','tbody','td','tfoot','th','thead','tr'),
		'value' 			=> array('button','input','li','option','param'),
		'valuetype' 		=> array('param'),
		'version' 			=> array('html'),
		'vlink' 			=> array('body'),
		'vspace' 			=> array('applet','img','object'),
		'webkit-playsinline' => array('video'),
		'width' 			=> array('applet','iframe','img','object','td','th','video'),
		'wrap' 				=> array('textarea'),
	);
	
	public function setOptions($options){
		$options = array_merge(array(
			'tag' => '',  //检测标签
			'text' => '', //检测一般性内容
			'css' => '', //检测css的内容
			'js' => '', //检测js的内容
		), $options);
		$this->options = $options;
	}
	public function run($content = '', $validateInstance = null, $options = array()){
		$this->setOptions($options);
		$this->analyticContent = $this->fl_instance->analytic_html($content);
		$this->analyticCount = count($this->analyticContent);
		while ($this->validateCount < $this->analyticCount){
			list($tokenText, $tokenType) = $this->analyticContent[$this->validateCount];
			$this->validateCount++;
			$method = '';
			switch ($tokenType){
				case FL::HTML_TAG_START :
					$method = 'tag';
					break;
				case FL::HTML_CONTENT :
					$method = 'text';
					break;
			}
			if ($method){
				$method = $this->options[$method];
				if ($method){
					$validateInstance->$method($tokenText, $this);
				}
			}
		}
	}
	/**
	 * 
	 * 检测标签是否合法
	 * @param string $tag
	 */
	public function checkTagIsValid($tag){
		$tag = strtolower($tag);
		return !!in_array($tag, $this->_tagList);
	}
	/**
	 * 
	 * 检测属性名是不是浏览器默认支持的，自定义属性需要用data-打头，如：data-appid="1";
	 * @param string $attr
	 * @param string $tag
	 */
	public function checkAttrIsValid($attr, $tag){
		$attr = strtolower($attr);
		$tag = strtolower($tag);
		//onclick,onmouseover等事件相关的
		if (strpos($attr, 'on') === 0) return true;
		if (in_array($attr, $this->_attrList)){
			$tagList = $this->_attrList[$attr];
			if ($tagList === '*') return true;
			if (is_array($tagList) && in_array($tag, $tagList)) return true;
		}
		return false;
	}
}