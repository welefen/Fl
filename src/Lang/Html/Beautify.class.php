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
		"indent" => "    " 
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
			"remove_blank_text" => true 
		) );
		return $this->beautifyAst ( $ast );
	}

	/**
	 * 
	 * beautify
	 * @param array $ast
	 */
	public function beautifyAst($ast, $childTag = '') {
		$result = '';
		$first = true;
		foreach ( $ast as $item ) {
			if (! $first) {
				$result = rtrim ( $result, FL_NEWLINE ) . FL_NEWLINE;
			}
			if ($first) {
				$first = false;
			}
			$result .= $this->beautifyComment ( $item ['value'] );
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
			}
			if ($item ['type'] !== FL_TOKEN_HTML_TEXT) {
				$result .= $this->getIndentString ();
			} else {
				if (count ( $ast ) > 1) {
					$result .= $this->getIndentString ();
				}
			}
			$result .= $item ['value'] ['value'];
			if ($newline) {
				$result .= FL_NEWLINE;
			}
			if ($indent) {
				$this->indent ++;
			}
			if (count ( $item ['children'] )) {
				$result .= $this->beautifyAst ( $item ['children'] );
			}
			if ($item ['type'] === FL_TOKEN_HTML_TAG) {
				if ($newline) {
					$result .= FL_NEWLINE;
				}
				if ($indent) {
					$this->indent --;
					$result .= $this->getIndentString ();
				}
				$result .= '</' . $item ['tag'] . '>';
			}
		}
		return rtrim ( $result, FL_NEWLINE );
	}

	/**
	 * 
	 * beautify comment before token
	 * @param array $token
	 */
	public function beautifyComment($token) {
		if (count ( $token ['commentBefore'] ) == 0) {
			return '';
		}
		$comments = $token ['commentBefore'];
		foreach ( $comments as $comment ) {
			$result .= $comment ['text'];
		}
		return $result;
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