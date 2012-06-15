<?php
Fl::loadClass ( 'Fl_Base' );
/**
 * 
 * Css Beautify
 * @author welefen
 *
 */
class Fl_Css_Beautify extends Fl_Base {

	/**
	 * 
	 * Css Beautify options
	 * @var array
	 */
	public $options = array (
		"indent" => "\t", 
		"space_after_colon" => true, 
		"beautify_selector" => true 
	);

	/**
	 * 
	 * css token instance
	 * @var object
	 */
	protected $tokenInstnace = null;

	/**
	 * 
	 * newline pre value
	 * @var array
	 */
	protected static $newLinePreValue = array (
		"{" => 1, 
		"}" => 1 
	);

	/**
	 * 
	 * current indent number, default is 0
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
	 * next token
	 * @var array
	 */
	protected $nextToken = array ();

	/**
	 * 
	 * beautify output, return by join ''
	 * @var array
	 */
	protected $output = array ();

	/**
	 * run
	 * @see Fl_Base::run()
	 */
	public function run($options = array()) {
		$this->options = array_merge ( $this->options, $options );
		$this->tokenInstnace = $this->getInstance ( 'Fl_Css_Token' );
		$this->nextToken = $this->tokenInstnace->getNextToken ();
		$token = array ();
		$end = false;
		while ( true ) {
			$this->preToken = $token;
			$token = $this->nextToken;
			$this->nextToken = $this->tokenInstnace->getNextToken ();
			if (! $this->nextToken) {
				$end = true;
				$this->nextToken = array ();
			}
			$this->output [] = $this->beautifyToken ( $token );
			if ($end) {
				break;
			}
		}
		return join ( '', $this->output );
	}

	/**
	 * 
	 * beautify current token
	 * @param array $token
	 */
	public function beautifyToken($token) {
		$result = $this->beautifyComment ( $token );
		switch ($token ['type']) {
			case FL_TOKEN_CSS_SELECTOR :
				return $result . $this->beautifySelector ( $token );
			case FL_TOKEN_CSS_BRACES_ONE_START :
			case FL_TOKEN_CSS_BRACES_TWO_START :
				return $result . $this->beautifyStartBrace ( $token );
			case FL_TOKEN_CSS_BRACES_ONE_END :
			case FL_TOKEN_CSS_BRACES_TWO_END :
				return $result . $this->beautifyEndBrace ( $token );
			case FL_TOKEN_CSS_PROPERTY :
				return $result . $this->beautifyProperty ( $token );
			case FL_TOKEN_CSS_COLON :
				return $result . $this->beautifyColon ( $token );
			default :
				return $result . $this->beautifyDefault ( $token );
		}
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
		if (isset ( self::$newLinePreValue [$this->preToken ['value']] )) {
			$newline = true;
		}
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
	 * beautify colon
	 * @param array $token
	 */
	public function beautifyColon($token) {
		$result = $token ['value'];
		if ($this->options ['space_after_colon']) {
			$result .= FL_SPACE;
		}
		return $result;
	}

	/**
	 * 
	 * beautify default token
	 * @param array $token
	 */
	public function beautifyDefault($token) {
		//for ;;;
		if ($token ['type'] === FL_TOKEN_CSS_SEMICOLON && $this->preToken ['type'] === FL_TOKEN_CSS_SEMICOLON) {
			return '';
		}
		if (isset ( $this->preToken ['type'] )) {
			//for @ type
			if ($token ['type'] === FL_TOKEN_CSS_AT_KEYFRAMES || $token ['type'] === FL_TOKEN_CSS_AT_MOZILLA) {
				return FL_NEWLINE . $this->getIndentString () . $token ['value'];
			} else if (Fl_Css_Static::isAtType ( $token ['type'] )) {
				return FL_NEWLINE . $token ['value'];
			}
		}
		if ($token ['type'] === FL_TOKEN_CSS_HACK) {
			return FL_NEWLINE . $this->getIndentString () . $token ['value'];
		}
		return $token ['value'];
	}

	/**
	 * 
	 * beautify property
	 * @param array $token
	 */
	public function beautifyProperty($token) {
		return FL_NEWLINE . $this->getIndentString () . $token ['value'];
	}

	/**
	 * 
	 * beautify selector token
	 * @param array $token
	 */
	public function beautifySelector($token) {
		$result = '';
		if (isset ( self::$newLinePreValue [$this->preToken ['value']] )) {
			$result .= FL_NEWLINE;
		}
		if (! $this->options ['beautify_selector']) {
			return $result . $this->getIndentString () . $token ['value'];
		}
		$output = $this->getInstance ( "Fl_Css_SelectorToken", $token ['value'] )->run ();
		$return = array ();
		$result .= $this->getIndentString ();
		$items = array ();
		foreach ( $output as $item ) {
			$first = true;
			$str = '';
			foreach ( $item as $token ) {
				if (! $first && $token ['spaceBefore']) {
					$str .= FL_SPACE;
				}
				$str .= $token ['value'];
				$first = false;
			}
			$items [] = $str;
		}
		$result .= join ( "," . FL_NEWLINE, $items );
		$return [] = $result;
		return join ( '', $return );
	}

	/**
	 * 
	 * beautify start bracket
	 * @param array $token
	 */
	public function beautifyStartBrace($token) {
		$this->indent ++;
		return FL_SPACE . "{";
	}

	/**
	 * 
	 * beautify end brackets
	 * @param array $token
	 */
	public function beautifyEndBrace($token) {
		$this->indent --;
		$result = '';
		if ($this->preToken ['type'] == FL_TOKEN_CSS_VALUE) {
			$result .= ';';
		}
		return $result . FL_NEWLINE . $this->getIndentString () . "}";
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