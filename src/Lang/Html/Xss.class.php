<?php
Fl::loadClass ( 'Fl_Base' );
Fl::loadClass ( 'Fl_Html_Static' );
Fl::loadClass ( 'Fl_Tpl' );
/**
 * 
 * XSS check & auto fixed
 * @author welefen
 *
 */
class Fl_Html_Xss extends Fl_Base {

	/**
	 * 
	 * auto fixed
	 * @var boolean
	 */
	public $auto_fixed = true;

	/**
	 * 
	 * safe vars
	 * @var array
	 */
	public $safe_vars = array ();

	/**
	 * 
	 * xml document ?
	 * @var boolean
	 */
	public $isXml = false;

	/**
	 * 
	 * identify function
	 * for check current value escape type, eg:
	 * $spCallback is alias for $smarty.get.callback, but default escape is html, so identify function will be return callback escape type
	 * @var string
	 */
	public $identifyFn = '';

	/**
	 * 
	 * options
	 * @var array
	 */
	public $options = array (
		"url" => "sp_path", 
		"html" => "sp_escape_html", 
		"js" => "sp_escape_js", 
		"callback" => "sp_escape_callback", 
		"data" => "sp_escape_data", 
		"event" => "sp_escape_event", 
		"noescape" => "sp_no_escape", 
		"xml" => "sp_escape_xml" 
	);

	/**
	 * 
	 * escape level
	 * @var array
	 */
	public $escapeLevel = array (
		"event" => 10, 
		"data" => 9, 
		"url" => 1, 
		"html" => 1, 
		"js" => 1, 
		"callback" => 11, 
		"xml" => 1 
	);

	/**
	 * 
	 * output
	 * @var array
	 */
	protected $output = array ();

	/**
	 * 
	 * xss log
	 * @var array
	 */
	protected $log = array ();

	/**
	 * run
	 * @see Fl_Base::run()
	 */
	public function run($options = array()) {
		$this->options = array_merge ( $this->options, $options );
		if (! $this->checkHasTplToken ()) {
			return $this->auto_fixed ? $this->text : array ();
		}
		if ($this->guessIsHtml ()) {
			$this->checkHtmlTokens ();
		} else {
			$this->checkJsTokens ();
		}
		if ($this->auto_fixed) {
			return $this->tokensToText ();
		}
		return $this->log;
	}

	/**
	 * 
	 * tokens to text
	 */
	private function tokensToText() {
		$result = '';
		foreach ( $this->output as $item ) {
			if ($item ['newlineBefore']) {
				$result .= FL_NEWLINE;
			}
			if (! empty ( $item ['commentBefore'] )) {
				$result .= join ( '', $item ['commentBefore'] );
			}
			$result .= $item ['value'];
		}
		return $result;
	}

	/**
	 * 
	 * get all tokens
	 * @param array $type
	 */
	public function getTokens($type = 'html') {
		$type = ucfirst ( strtolower ( $type ) );
		if (isset ( $this->tokens [$type] )) {
			return $this->tokens [$type];
		}
		$instance = $this->getInstance ( 'Fl_' . $type . '_Token' );
		#$instance->validate = false;
		return $this->tokens [$type] = $instance->run ();
	}

	/**
	 * 
	 * @param array $token
	 */
	public function checkHtmlTokens() {
		$tokens = $this->getTokens ( 'html' );
		foreach ( $tokens as $item ) {
			if ($item ['type'] === FL_TOKEN_HTML_TAG_START) {
				$attrTokens = $this->getInstance ( "Fl_Html_TagToken", $item ['value'] )->run ();
				$tagName = strtolower ( $attrTokens ['tag'] );
				$tag = '<' . $attrTokens ['tag'] . FL_SPACE;
				$attrTokens = $attrTokens ['attrs'];
				foreach ( $attrTokens as $attrItem ) {
					$count = count ( $attrItem );
					$attr = strtolower ( $attrItem [0] );
					if ($count == 1) {
						$tag .= $this->checkIt ( array_merge ( $item, array (
							'value' => $attrItem [0] 
						) ), 'html' ) . FL_SPACE;
					} elseif ($count === 3) {
						if ($attr && strpos ( $attr, 'on' ) === 0) {
							$type = 'event';
						} elseif ($attr === 'src' || $attr === 'href' || ($tagName === 'form' && $attr === 'action')) {
							$type = 'url';
						} else {
							$type = 'html';
						}
						$value = $this->checkIt ( array_merge ( $item, array (
							'value' => $attrItem [2] 
						) ), $type );
						$tag .= $attrItem [0] . '=' . $value . FL_SPACE;
					}
				}
				$tag = trim ( $tag ) . ">";
				$item ['value'] = $tag;
			} else if ($item ['type'] === FL_TOKEN_HTML_SCRIPT_TAG) {
				$item ['value'] = $this->checkIt ( $item, 'js' );
			} else {
				$item ['value'] = $this->checkIt ( $item, 'html' );
			}
			$this->addOutput ( $item );
		}
	}

	/**
	 * 
	 * js tokens
	 */
	public function checkJsTokens() {
		$tokens = $this->getTokens ( 'js' );
		foreach ( $tokens as $item ) {
			if ($item ['type'] === FL_TOKEN_TPL || $item ['type'] === FL_TOKEN_JS_STRING) {
				$item ['value'] = $this->checkIt ( $item, 'data' );
			}
			$this->addOutput ( $item );
		}
	}

	/**
	 * 
	 * add value to output
	 * @param string or array $value
	 */
	public function addOutput($value) {
		if (empty ( $value )) {
			return true;
		}
		$this->output [] = $value;
	}

	/**
	 * 
	 * check it
	 * @param array $value
	 * @param string $type
	 */
	public function checkIt($token = array(), $type = '') {
		if ($this->isXml && $type == 'html') {
			$type = 'xml';
		}
		if (! $this->containTpl ( $token ['value'] )) {
			return $token ['value'];
		}
		$return = Fl_Tpl::factory ( $this )->xss ( $token, $type, $this );
		if ($return ['log']) {
			$this->log = array_merge ( $this->log, $return ['log'] );
		}
		return $return ['value'];
	}

	/**
	 * 
	 * get xss log
	 */
	public function getLog() {
		return $this->log;
	}

	/**
	 * 
	 * guess is html
	 */
	public function guessIsHtml() {
		$tag = array ();
		$tokens = $this->getTokens ( 'html' );
		$length = count ( $tokens );
		$htmlTags = array (
			FL_TOKEN_HTML_SCRIPT_TAG => 1, 
			FL_TOKEN_HTML_STYLE_TAG => 1, 
			FL_TOKEN_HTML_TEXTAREA_TAG => 1, 
			FL_TOKEN_HTML_PRE_TAG => 1, 
			FL_TOKEN_HTML_STATUS => 1, 
			FL_TOKEN_HTML_IE_HACK => 1 
		);
		for($i = 0; $i < $length; $i ++) {
			$item = $tokens [$i];
			if ($item ['type'] === FL_TOKEN_XML_HEAD) {
				return $this->isXml = true;
			} elseif (isset ( $htmlTags [$item ['type']] )) {
				return true;
			} else if ($item ['type'] === FL_TOKEN_HTML_TAG_START) {
				if ($i > 0) {
					$preItem = $tokens [$i - 1];
					if ($preItem ['type'] === FL_TOKEN_HTML_TEXT) {
						$preString = $preItem ['value'];
						$preLastChar = $preString {strlen ( $preString ) - 1};
						if ($preLastChar === '"' || $preLastChar === "'") {
							continue;
						}
					}
				}
				$tag [] = $item ['value'];
				//if more than 5 tags, it's like html
				if (count ( $tag ) > 5) {
					return true;
				}
			}
		}
		//if have html tags but less than 5
		if (count ( $tag ) > 0) {
			//if a error have occured when tokenzier by js, it will be html
			try {
				$tokens = $this->getTokens ( 'js' );
			} catch ( Fl_Exception $e ) {
				return true;
			}
			foreach ( $tokens as $item ) {
				if (count ( $tag ) === 0) {
					return false;
				}
				if ($item ['type'] === FL_TOKEN_JS_STRING) {
					$notFilter = $findPos = array ();
					foreach ( $tag as $t ) {
						$pos = 0;
						while ( true ) {
							$pos = strpos ( $item ['value'], $t, $pos );
							if ($pos === false) {
								$notFilter [] = $t;
								break;
							} else {
								if (! in_array ( $pos, $findPos )) {
									$findPos [] = $pos;
									break;
								} else {
									$pos += strlen ( $t );
								}
							}
						}
					}
					$tag = $notFilter;
				}
			}
			if (count ( $tag ) > 0) {
				return true;
			}
		}
		return false;
	}
}