<?php
Fl::loadClass ( "Fl_Base" );
Fl::loadClass ( "Fl_Static" );
/**
 * 
 * html filter
 * @author welefen
 *
 */
class Fl_Html_Filter extends Fl_Base {

	/**
	 * 
	 * 标签白名单
	 * @var array
	 */
	public $blankTagList = array (
		'div', 
		'span', 
		'a', 
		'img', 
		'p', 
		'ul', 
		'ol', 
		'li', 
		'em', 
		'strong', 
		'html', 
		'head', 
		'body', 
		'dd', 
		'dl', 
		'dt', 
		'center', 
		'h1', 
		'h2', 
		'h3', 
		'h4', 
		'h5', 
		'h6', 
		'header', 
		'footer', 
		'nav', 
		'article', 
		'section', 
		'address', 
		'table', 
		'tr', 
		'td', 
		'th', 
		'style', 
		'link', 
		'title', 
		'meta' 
	);

	/**
	 * 
	 * 标签属性白名单
	 * @var array
	 */
	public $blankTagPropertyList = array (
		'id', 
		'class', 
		'style', 
		'name', 
		'align', 
		'type', 
		'src', 
		'rel', 
		'href', 
		'dir', 
		'hidden', 
		'tabindex', 
		'title', 
		'accesskey' 
	);

	/**
	 * 
	 * 当前内容对应的页面地址，需要根据这个替换页面资源的相对地址
	 * @var string
	 */
	public $url = '';

	/**
	 * 获取外部资源的回调方法,外部提供
	 */
	public $getResourceContentFn = '';

	/**
	 * 
	 * 过滤选项
	 * @var array
	 */
	public $options = array (
		'remove_js' => true,  //移除js
		'remove_tag_event' => true,  //去除标签的事件
		'use_blank_tag_filter' => true,  //标签使用白名单
		'use_blank_tag_property_filter' => true,  //标签属性使用白名单
		'url_max_length' => 100, 
		'filter_tag_style_value' => true,  //过滤标签的style值
		'filter_a_href_value' => true,  //过滤a标签的href值
		'filter_img_src_value' => true,  //过滤img标签的src值
		'remove_css' => false,  //移除css
		'external_css_to_inline' => true  //将外链的css变成内联,在不移除css下有效
	);

	/**
	 * 
	 * html token instance
	 * @var object
	 */
	private $tokenInstance = null;

	/**
	 * 
	 * 输出内容
	 * @var array
	 */
	public $output = array ();

	/**
	 * (non-PHPdoc)
	 * @see Fl_Base::run()
	 */
	public function run($options = array()) {
		$this->options = array_merge ( $this->options, $options );
		$this->tokenInstance = $this->getInstance ( "Fl_Html_Token" );
		while ( true ) {
			$token = $this->tokenInstance->getNextToken ();
			if (empty ( $token )) {
				break;
			}
			switch ($token ['type']) {
				case FL_TOKEN_HTML_TAG_START :
					$this->output [] = $this->filterTag ( $token );
					break;
				case FL_TOKEN_HTML_SCRIPT_TAG :
					$this->output [] = $this->filterScript ( $token );
					break;
				case FL_TOKEN_HTML_STYLE_TAG :
					$this->output [] = $this->filterStyle ( $token );
					break;
				case FL_TOKEN_HTML_PRE_TAG :
					$this->output [] = $this->filterPre ( $token );
					break;
				case FL_TOKEN_HTML_TEXTAREA_TAG :
					$this->output [] = $this->filterTextarea ( $token );
					break;
				default :
					$this->output [] = $token ['value'];
			}
		}
		return join ( '', $this->output );
	}

	/**
	 * 
	 * 过滤style
	 * @param array $token
	 */
	public function filterStyle($token = array()) {
		if ($this->options ['remove_css']) {
			return false;
		}
		$detail = Fl_Html_Static::splitSpecialValue ( $token ['value'], 'style', $this );
		$tagStart = $detail ['tag_start'];
		$tagStart = $this->filterTag ( array (
			'value' => $tagStart 
		) );
		$content = $detail ['content'];
		if ($content) {
			$instance = $this->getInstance ( "Fl_Css_Filter", $content );
			$instance->url = $this->url;
			$instance->getResourceContentFn = $this->getResourceContentFn;
			$content = $instance->run ( $this->options );
		}
		return $tagStart . $content . $detail ['tag_end'];
	}

	/**
	 * 
	 * 过滤pre
	 * @param array $token
	 */
	public function filterPre($token = array()) {
		if ($this->options ['use_blank_tag_filter']) {
			if (! in_array ( 'pre', $this->blankTagList )) {
				return false;
			}
		}
		$detail = Fl_Html_Static::splitSpecialValue ( $token ['value'], 'pre', $this );
		$tagStart = $this->filterTag ( array (
			'value' => $detail ['tag_start'] 
		) );
		//pre的内容是可以执行的，所以要通过html_filter来过滤
		$instance = $this->getInstance ( "Fl_Html_Filter", $detail ['content'] );
		$content = $instance->run ( $this->options );
		return $tagStart . $content . $detail ['tag_end'];
	}

	/**
	 * 
	 * 过滤textarea
	 * @param array $token
	 */
	public function filterTextarea($token = array()) {
		if ($this->options ['use_blank_tag_filter']) {
			if (! in_array ( 'textarea', $this->blankTagList )) {
				return '';
			}
		}
		//如果允许textarea标签的话，也要过滤textarea的属性
		$detail = Fl_Html_Static::splitSpecialValue ( $token ['value'], 'textarea', $this );
		$tagStart = $this->filterTag ( array (
			'value' => $detail ['tag_start'] 
		) );
		return $tagStart . $detail ['content'] . $detail ['tag_end'];
	}

	/**
	 * 
	 * 过滤script
	 * @param array $token
	 */
	public function filterScript($token) {
		if ($this->options ['remove_js']) {
			return '';
		}
		return $token ['value'];
	}

	/**
	 * 
	 * 过滤远程的css
	 */
	public function filterExternalCss($tagDetail = array()) {
		if ($this->options ['remove_css']) {
			return '';
		}
		$hrefValue = Fl_Html_Static::getAttrValue ( $tagDetail ['attrs'], 'href' );
		$hrefValue = Fl_Static::getFixedUrl ( $hrefValue, $this->url );
		if ($this->options ['external_css_to_inline']) {
			//如果没有提供抓取外部资源的方法，则直接过滤掉
			if ($this->getResourceContentFn) {
				$content = call_user_func ( $this->getResourceContentFn, $hrefValue, $this );
				if (! empty ( $content )) {
					$instance = $this->getInstance ( "Fl_Css_Filter", $content );
					$instance->url = $hrefValue;
					$instance->getResourceContentFn = $this->getResourceContentFn;
					try {
						$content = $instance->run ( $this->options );
					} catch ( Fl_Exception $e ) {
						$this->throwException ( $e->message . ' in `' . $hrefValue . '`' );
					}
					return '<style type="text/css">' . $content . '</style>';
				}
			} else {
				return '';
			}
		}
		$result = $this->filterTag ( array (
			'value' => '<link rel="stylesheet" href="' . $hrefValue . '">' 
		), true );
		return $result;
	}

	/**
	 * 
	 * 是否是远程的css
	 * @param array $tagDetail
	 */
	protected function isExternalCss($tagDetail = array()) {
		if (strtolower ( $tagDetail ['tag'] ) != 'link') {
			return false;
		}
		$attrs = $tagDetail ['attrs'];
		$relValue = Fl_Html_Static::getAttrValue ( $attrs, 'rel' );
		if (strpos ( $relValue, 'stylesheet' ) !== false) {
			$hrefValue = Fl_Html_Static::getAttrValue ( $attrs, 'href' );
			if ($hrefValue) {
				return true;
			}
			return false;
		}
		return false;
	}

	/**
	 * 
	 * 过滤开始标签
	 * @param array $token
	 */
	public function filterTag($token, $notCheckECss = false) {
		$value = $token ['value'];
		if (! empty ( $value )) {
			$instance = $this->getInstance ( 'Fl_Html_TagToken', $value );
			$result = $instance->run ();
		} else {
			$result = $token;
		}
		$tag = strtolower ( $result ['tag'] );
		//外链的css
		if (! $notCheckECss && $this->isExternalCss ( $result )) {
			return $this->filterExternalCss ( $result );
		}
		if ($this->options ['use_blank_tag_filter']) {
			if (! in_array ( $tag, $this->blankTagList )) {
				return false;
			}
		}
		$attrs = $result ['attrs'];
		$attrResult = array ();
		foreach ( $attrs as $item ) {
			$name = strtolower ( $item [0] );
			//过滤事件
			if ($this->options ['remove_tag_event']) {
				if (strpos ( $name, 'on' ) === 0) {
					continue;
				}
			}
			//标签属性白名单
			if ($this->options ['use_blank_tag_property_filter']) {
				if (! in_array ( $name, $this->blankTagPropertyList )) {
					continue;
				}
			}
			//a链接修复和过滤
			if ($tag == 'a' && $this->options ['filter_a_href_value'] && $name == 'href') {
				if (count ( $item ) == 3 && $item [1] == '=') {
					$values = Fl_Html_Static::getUnquoteText ( $item [2] );
					$url = Fl_Static::getFixedUrl ( $values ['text'], $this->url );
					if ($this->options ['url_max_length']) {
						$url = substr ( $url, 0, $this->options ['url_max_length'] );
					}
					$item [2] = $values ['quote'] . $url . $values ['quote'];
				} else {
					continue;
				}
			}
			//图片连接的修复和过滤
			if ($tag == 'img' && $this->options ['filter_img_src_value'] && $name == 'src') {
				if (count ( $item ) == 3 && $item [1] == '=') {
					$values = Fl_Html_Static::getUnquoteText ( $item [2] );
					$url = Fl_Static::getFixedUrl ( $values ['text'], $this->url );
					if ($this->options ['url_max_length']) {
						$url = substr ( $url, 0, $this->options ['url_max_length'] );
					}
					$item [2] = $values ['quote'] . $url . $values ['quote'];
				} else {
					continue;
				}
			}
			//style value
			if ($this->options ['filter_tag_style_value'] && $name == 'style') {
				if (count ( $item ) == 3 && $item [1] == '=') {
					$values = Fl_Html_Static::getUnquoteText ( $item [2] );
					$text = 'a{' . $values ['text'] . '}';
					$instance = $this->getInstance ( "Fl_Css_Filter", $text );
					$instance->url = $this->url;
					$instance->getResourceContentFn = $this->getResourceContentFn;
					$text = $instance->run ( $this->options );
					$item [2] = $values ['quote'] . substr ( $text, 2, strlen ( $text ) - 3 ) . $values ['quote'];
				} else {
					continue;
				}
			}
			$attrResult [] = $item;
		}
		$attrsJoin = array ();
		foreach ( $attrResult as $item ) {
			$attrsJoin [] = join ( "", $item );
		}
		if (empty ( $attrsJoin )) {
			return '<' . $tag . '>';
		}
		return '<' . $tag . ' ' . join ( " ", $attrsJoin ) . ">";
	}
}