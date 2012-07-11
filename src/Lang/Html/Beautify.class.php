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
		"indent" => "\t" 
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
		$instance->embedToken = true;
		$ast = $instance->run ();
		return $this->beautifyAst ( $ast );
	}

	/**
	 * 
	 * beautify
	 * @param array $ast
	 */
	public function beautifyAst($ast, $childTag = '') {
		$result = '';
		foreach ( $ast as $item ) {
			$result .= $this->beautifyComment ( $item ['value'] );
			$isTag = $item ['type'] === 'tag';
			if ($isTag) {
				$result .= $this->getIndentString ();
			}
			$result .= $item ['value'] ['value'];
			if ($isTag) {
				$this->indent ++;
			}
			if ($item ['type'] !== "text") {
				if ($isTag) {
					if (Fl_Html_Static::isBlockTag ( $item ['tag'] )) {
						$result .= FL_NEWLINE;
					}
				} else {
					$result .= FL_NEWLINE;
				}
			}
			$this->preToken = $item ['value'];
			if (! empty ( $item ['children'] )) {
				$result .= $this->beautifyAst ( $item ['children'] );
			}
			if ($isTag) {
				if (Fl_Html_Static::isBlockTag ( $item ['tag'] )) {
					$result .= FL_NEWLINE;
				}
				$result .= '</' . $item ['tag'] . '>';
				$this->indent --;
			}
		}
		return $result;
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
		$preLine = intval ( $this->preToken ['line'] );
		$result = '';
		$indent = $this->getIndentString ();
		$first = ! isset ( $this->preToken ['value'] );
		foreach ( $comments as $comment ) {
			if ($comment ['line'] > $preLine || $newline) {
				$result .= FL_NEWLINE . $indent;
				$result .= join ( FL_NEWLINE . $indent, explode ( FL_NEWLINE, $comment ['text'] ) );
			} else if ($first) {
				$result .= $comment ['text'] . FL_NEWLINE;
			} else {
				$result .= FL_SPACE;
				$result .= $comment ['text'];
			}
			$preLine = $comment ['line'];
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