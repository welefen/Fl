<?php
Fl::loadClass ( "Fl_Base" );
/**
 * 
 * html ast
 * @author welefen
 *
 */
class Fl_Html_Ast extends Fl_Base {

	/**
	 * 
	 * options
	 * @var array
	 */
	public $options = array (
		"embed_token" => false, 
		"remove_blank_text" => false, 
		"remove_blank_text_in_block_tag" => true 
	);

	/**
	 * html token instance
	 */
	protected $tokenInstance = null;

	/**
	 * 
	 * current token
	 * @var array
	 */
	protected $currentToken = array ();

	/**
	 * 
	 * peek token
	 * @var array
	 */
	protected $peekToken = array ();

	/**
	 * 
	 * in tags depth
	 * @var array
	 */
	protected $inTags = array ();

	/**
	 * 
	 * return data
	 * @var array
	 */
	protected $output = array ();

	/**
	 * run
	 * @see Fl_Base::run()
	 */
	public function run($options = array()) {
		if ($this->checkHasTplToken ()) {
			return false;
		}
		$this->options = array_merge ( $this->options, $options );
		$this->tokenInstance = $this->getInstance ( "Fl_Html_Token" );
		$this->getNextToken ();
		while ( $this->currentToken ) {
			$st = $this->statement ();
			if ($st) {
				$this->output [] = $st;
			}
			$this->getNextToken ();
		}
		return $this->output;
	}

	/**
	 * 
	 * get next token
	 */
	public function getNextToken() {
		if (! empty ( $this->peekToken )) {
			$this->currentToken = $this->peekToken;
			$this->peekToken = array ();
		} else {
			$this->currentToken = $this->tokenInstance->getNextToken ();
		}
		return $this->currentToken;
	}

	/**
	 * 
	 * statement
	 */
	public function statement() {
		if (empty ( $this->currentToken )) {
			return false;
		}
		switch ($this->currentToken ['type']) {
			case FL_TOKEN_HTML_TAG_START :
				return $this->tagStartStatement ();
			case FL_TOKEN_HTML_TAG_END :
				return false;
			case FL_TOKEN_HTML_PRE_TAG :
			case FL_TOKEN_HTML_SCRIPT_TAG :
			case FL_TOKEN_HTML_STYLE_TAG :
			case FL_TOKEN_HTML_TEXTAREA_TAG :
				return $this->specialStatement ();
			case FL_TOKEN_HTML_TEXT :
				if (preg_match ( FL_SPACE_ALL_PATTERN, $this->currentToken ['value'] )) {
					if ($this->options ['remove_blank_text']) {
						return false;
					}
					if (count ( $this->inTags ) && $this->options ['remove_blank_text_in_block_tag']) {
						$inTag = $this->inTags [count ( $this->inTags ) - 1];
						if (Fl_Html_Static::isBlockTag ( $inTag )) {
							return false;
						}
					}
				}
				return array (
					"type" => FL_TOKEN_HTML_TEXT, 
					"value" => $this->getValue ( $this->currentToken ) 
				);
			default :
				return array (
					"type" => $this->currentToken ['type'], 
					"value" => $this->getValue ( $this->currentToken ) 
				);
		}
	}

	/**
	 * 
	 * get token value
	 * @param array $token
	 */
	public function getValue($token) {
		if ($this->options ['embed_token']) {
			return $token;
		}
		return $token ['value'];
	}

	/**
	 * 
	 * tag start statement
	 */
	public function tagStartStatement() {
		$token = $this->currentToken;
		$tag = strtolower ( Fl_Html_Static::getTagName ( $token ['value'], $this ) );
		if (Fl_Html_Static::isSingleTag ( $tag )) {
			return array (
				"type" => FL_TOKEN_HTML_SINGLE_TAG, 
				"value" => $this->getValue ( $token ) 
			);
		}
		$result = array ();
		$this->inTags [] = $tag;
		$comment = array ();
		while ( $this->currentToken ) {
			$this->getNextToken ();
			if (Fl_Html_Static::isTag ( $this->currentToken )) {
				if (Fl_Html_Static::optionalTagUntil ( $tag, $this->currentToken, $this )) {
					$this->peekToken = $this->currentToken;
					if ($this->currentToken ['type'] === FL_TOKEN_HTML_TAG_END) {
						$tagEnd = $this->currentToken;
						if ($tag === Fl_Html_Static::getTagName ( $this->currentToken ['value'], $this )) {
							$this->getNextToken ();
						}
					}
					break;
				}
			}
			$re = $this->statement ();
			if ($re) {
				$result [] = $re;
			}
		}
		array_pop ( $this->inTags );
		return array (
			"type" => FL_TOKEN_HTML_TAG, 
			"tag" => $tag, 
			"value" => $this->getValue ( $token ), 
			"children" => $result, 
			"end" => $this->getValue ( $tagEnd ) 
		);
	}

	/**
	 * 
	 * special
	 */
	public function specialStatement() {
		$tag = strtolower ( Fl_Html_Static::getTagName ( $this->currentToken ['value'], $this ) );
		$special = Fl_Html_Static::splitSpecialValue ( $this->currentToken ['value'], $tag, $this );
		$this->currentToken ['value'] = $special ['tag_start'];
		return array (
			"type" => $this->currentToken ['type'], 
			"tag" => $tag, 
			"value" => $this->getValue ( $this->currentToken ), 
			"children" => array (
				array (
					"type" => FL_TOKEN_HTML_TEXT, 
					"value" => $this->getValue ( array_merge ( $this->currentToken, array (
						"value" => $special ["content"] 
					) ) ) 
				) 
			) 
		);
	}
}