<?php
Fl::loadClass ( 'Fl_Base' );
/**
 * 
 * html beautify, can not contain tpl
 * @author welefen
 *
 */
class Fl_Html_Beautify extends Fl_Base {

	/**
	 * 
	 * beautify options
	 * @var array
	 */
	public $options = array (
		"indent" => "    ", 
		"beautify_css" => true, 
		"beautify_js" => true 
	);

	/**
	 * 
	 * int num
	 * @var int
	 */
	protected $indent = 0;

	/**
	 * 
	 * prev token
	 * @var array
	 */
	protected $preToken = array ();

	/**
	 * 
	 * output
	 * @var array
	 */
	protected $output = array ();

	/**
	 * run
	 * @see Fl_Base::run()
	 */
	public function run($options = array()) {
		if ($this->checkHasTplToken ()) {
			return $this->text;
		}
		$this->options = array_merge ( $this->options, $options );
		$instance = $this->getInstance ( "Fl_Html_Ast" );
		$ast = $instance->run ( array (
			"embed_token" => true, 
			"remove_blank_text" => true, 
			"reserve_comments_before_end_tag" => true 
		) );
		return rtrim ( $this->beautifyAst ( $ast ), FL_NEWLINE );
	}

	/**
	 * 
	 * beautify
	 * @param array $ast
	 */
	public function beautifyAst($ast, $parentType = '') {
		$result = '';
		$first = true;
		foreach ( $ast as $item ) {
			if (! $first) {
				$result = rtrim ( $result, FL_NEWLINE ) . FL_NEWLINE;
			}
			$hasComment = false;
			if (count ( $item ['value'] ['commentBefore'] )) {
				$hasComment = true;
			}
			$comment = $this->beautifyComment ( $item ['value'] );
			if ($comment ['remove']) {
				$result = rtrim ( $result, FL_NEWLINE );
			}
			$result .= $comment ['value'];
			//
			if ($item ['type'] === FL_TOKEN_HTML_TAG_END) {
				continue;
			}
			if ($first) {
				$first = false;
			}
			$indent = $newline = false;
			if ($item ['type'] === FL_TOKEN_HTML_TAG) {
				$count = count ( $item ['children'] );
				if ($count > 1) {
					$indent = true;
					$newline = true;
				} elseif ($count === 1) {
					$c = $item ['children'] [$count - 1];
					if ($c ['type'] !== FL_TOKEN_HTML_TEXT) {
						$indent = true;
						$newline = true;
					}
				}
			} else {
				if (count ( $ast ) > 1) {
					$newline = true;
				}
				if ($item ['type'] === FL_TOKEN_HTML_DOCTYPE || $item ['type'] === FL_TOKEN_HTML_SINGLE_TAG) {
					$newline = true;
				}
				if ($item ['type'] === FL_TOKEN_HTML_STYLE_TAG || $item ['type'] === FL_TOKEN_HTML_SCRIPT_TAG) {
					$newline = true;
					$indent = true;
				}
			}
			if ($item ['type'] !== FL_TOKEN_HTML_TEXT) {
				$result .= $this->getIndentString ();
			} else {
				if (count ( $ast ) > 1) {
					$result .= $this->getIndentString ();
				}
			}
			if ($parentType === FL_TOKEN_HTML_STYLE_TAG) {
				Fl::loadClass ( "Fl_Css_Static" );
				$value = Fl_Css_Static::getStyleDetail ( $item ['value'] ['value'] );
				if ($value ['prefix']) {
					$result .= $value ['prefix'] . FL_NEWLINE;
				}
				$result .= $this->beautify_special ( $value ['value'], 'css' );
				if ($value ['suffix']) {
					$result .= FL_NEWLINE . $value ['suffix'] . FL_NEWLINE;
				}
			} elseif ($parentType === FL_TOKEN_HTML_SCRIPT_TAG) {
				$result .= $this->beautify_special ( $item ['value'] ['value'], 'js' );
			} else {
				$result .= $item ['value'] ['value'];
			}
			if ($newline) {
				$result .= FL_NEWLINE;
			}
			if ($indent) {
				$this->indent ++;
			}
			if (count ( $item ['children'] )) {
				$type = $item ['type'];
				if ($type === FL_TOKEN_HTML_SCRIPT_TAG) {
					$tagInfo = Fl_Html_Static::getScriptTagInfo ( $item ['value'] ['value'], $this );
					//虽然是script标签，但不一定是Js
					if (! $tagInfo ['script'] || $tagInfo ['external']) {
						$type = '';
					}
				}
				$children = $this->beautifyAst ( $item ['children'], $type );
				if ($tagInfo ['external'] && ! strlen ( trim ( $children ) )) {
					$result = rtrim ( $result, FL_NEWLINE );
					$newline = false;
					$indent = 2;
				} else {
					$result .= $children;
				}
			}
			$types = array (
				FL_TOKEN_HTML_TAG => 1, 
				FL_TOKEN_HTML_SCRIPT_TAG => 1, 
				FL_TOKEN_HTML_PRE_TAG => 1, 
				FL_TOKEN_HTML_STYLE_TAG => 1, 
				FL_TOKEN_HTML_TEXTAREA_TAG => 1 
			);
			$this->preToken = $item ['value'];
			if (isset ( $types [$item ['type']] )) {
				if ($newline) {
					$result = rtrim ( $result, FL_NEWLINE );
					$result .= FL_NEWLINE;
				}
				if ($indent) {
					$this->indent --;
					if ($indent === true) {
						$result .= $this->getIndentString ();
					}
				}
				if (! empty ( $item ['end'] )) {
					$this->preToken = $item ['end'];
					$comment = $this->beautifyComment ( $item ['end'] );
					$result .= rtrim ( $comment ['value'], FL_NEWLINE );
				}
				$result .= '</' . $item ['tag'] . '>';
			}
		}
		return $result;
	}

	/**
	 * 
	 * beautify style or script
	 * @param string $value
	 */
	public function beautify_special($value, $type = 'css', $indent = true) {
		if (! $this->options ['beautify_' . $type]) {
			return $value;
		}
		$options = array (
			'indent' => $this->options ['indent'] 
		);
		$type = ucfirst ( strtolower ( $type ) );
		$value = $this->getInstance ( "Fl_" . $type . "_beautify", $value )->run ( $options );
		$this->indent --;
		$indentString = $this->getIndentString ();
		if ($indent && strlen ( $indentString )) {
			$values = explode ( FL_NEWLINE, $value );
			$value = join ( FL_NEWLINE . $indentString, $values );
		}
		$this->indent ++;
		if (! $indent) {
			$indentString = '';
		}
		return $indentString . $value;
	}

	/**
	 * 
	 * beautify comment before token
	 * @param array $token
	 */
	public function beautifyComment($token) {
		if (empty ( $token ['commentBefore'] )) {
			return array (
				"remove" => false, 
				"value" => "" 
			);
		}
		$comments = $token ['commentBefore'];
		$preLine = intval ( $this->preToken ['line'] );
		$result = '';
		$indent = $this->getIndentString ();
		$first = ! isset ( $this->preToken ['value'] );
		foreach ( $comments as $comment ) {
			if ($comment ['line'] > $preLine) {
				$result .= FL_NEWLINE . $indent;
				$result .= join ( FL_NEWLINE . $indent, explode ( FL_NEWLINE, $comment ['text'] ) );
			} else if ($first) {
				$result .= $comment ['text'] . FL_NEWLINE;
			} else {
				$result .= $comment ['text'];
			}
			$preLine = $comment ['line'];
			$first = false;
		}
		return array (
			"remove" => true, 
			"value" => $result . FL_NEWLINE 
		);
	}

	/**
	 * 
	 * get indent string for token
	 */
	public function getIndentString() {
		if ($this->indent < 0) {
			$this->throwException ( "indent number error: " . $this->indent );
		}
		return str_repeat ( $this->options ['indent'], $this->indent );
	}
}