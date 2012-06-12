<?php
Fl::loadClass ( 'Fl_Base' );
/**
 * 
 * Css Validate class
 * @author welefen
 *
 */
class Fl_Css_Validate extends Fl_Base {

	/**
	 * 
	 * validate options
	 * @var array
	 */
	public $options = array (
		"expression" => true, 
		"important" => true, 
		"property_hack" => true, 
		"other_hack" => true, 
		"filter" => true, 
		"selector_max_level" => 4, 
		"class_pattern" => "/^\.[a-z][a-z0-9\-]+$/s", 
		"id_pattern" => "/^\#[a-zA-Z0-9]+$/i" 
	);

	/**
	 * 
	 * output message
	 * @var array
	 */
	protected $output = array ();

	/**
	 * run
	 * @see Fl_Base::run()
	 */
	public function run($options = array()) {
		$this->options = array_merge ( $this->options, $options );
		$tokenInstance = $this->getInstance ( 'Fl_Css_Token' );
		while ( $token = $tokenInstance->getNextToken () ) {
			$this->validateToken ( $token );
		}
		return $this->output;
	}

	/**
	 * 
	 * check every tokens
	 * @param array $token
	 */
	public function validateToken($token) {
		switch ($token ['type']) {
			case FL_TOKEN_CSS_SELECTOR :
				return $this->validateSelector ( $token );
			case FL_TOKEN_CSS_PROPERTY :
				return $this->validateProperty ( $token );
			case FL_TOKEN_CSS_VALUE :
				return $this->validateValue ( $token );
			case FL_TOKEN_CSS_HACK :
				return $this->validateHack ( $token );
			default :
				return $this->ValidateDefault ( $token );
		}
	}

	/**
	 * 
	 * validate css selector
	 * @param array $token
	 */
	public function validateSelector($token) {
		$tokens = $this->getInstance ( "Fl_Css_SelectorToken", $token ['value'] )->run ();
		$maxSelectorLevel = intval ( $this->options ['selector_max_level'] );
		foreach ( $tokens as $selectorItem ) {
			if ($maxSelectorLevel && count ( $selectorItem ) > $maxSelectorLevel) {
				$selector = Fl_Css_Static::selectorTokenToText ( $selectorItem );
				$this->addMessage ( 'selector `' . $selector ['text'] . '` level can not max ' . $maxSelectorLevel, 'selector_max_level', array (
					'line' => $token ['line'] + $selector ['line'], 
					'col' => $selector ['col'] 
				) );
			}
			foreach ( $selectorItem as $item ) {
				if ($item ['type'] === FL_TOKEN_CSS_SELECTOR_CLASS) {
					if (! preg_match ( $this->options ['class_pattern'], $item ['value'] )) {
						$this->addMessage ( '`' . $item ['value'] . '` is not valid', 'class_pattern', array (
							'line' => $token ['line'] + $item ['line'], 
							'col' => $item ['col'] 
						) );
					}
				} else if ($item ['type'] === FL_TOKEN_CSS_SELECTOR_ID) {
					if (! preg_match ( $this->options ['id_pattern'], $item ['value'] )) {
						$this->addMessage ( '`' . $item ['value'] . '` is not valid', 'id_pattern', array (
							'line' => $token ['line'] + $item ['line'], 
							'col' => $item ['col'] 
						) );
					}
				}
			}
		}
	}

	/**
	 * 
	 * validate css property
	 * @param array $token
	 */
	public function validateProperty($token) {
		if (! $this->options ['property_hack']) {
			return true;
		}
		if (! Fl_Css_Static::checkPropertyPattern ( $token ['value'] )) {
			return $this->addMessage ( "property `" . $token ['value'] . "` is not valid", 'property_hack', $token );
		}
		if (strpos ( $token ['value'], 'filter' ) !== false) {
			return $this->addMessage ( "property `" . $token ['value'] . "` is not valid", 'filter', $token );
		}
	}

	/**
	 * 
	 * validate css value
	 * @param array $token
	 */
	public function validateValue($token) {
		if ($this->options ['important']) {
			if (strpos ( $token ['value'], '!important' ) != false) {
				return $this->addMessage ( '`' . $token ['value'] . '` has !important', 'important', $token );
			}
		}
		if ($this->options ['expression']) {
			if (strpos ( $token ['value'], 'expression' ) != false) {
				return $this->addMessage ( '`' . $token ['value'] . '` has expression', 'expression', $token );
			}
		}
	}

	/**
	 * 
	 * validate css hack, such as [;color:red;]
	 * @param array $token
	 */
	public function validateHack($token) {
		if (! $this->options ['other_hack']) {
			return true;
		}
		$this->addMessage ( '`' . $token ['value'] . '`' . ' is not valid', 'other_hack', $token );
	}

	/**
	 * 
	 * validate default
	 * @param array $token
	 */
	public function ValidateDefault($token) {
		return true;
	}

	/**
	 * 
	 * add validate message
	 * @param string $message
	 * @param string $cate
	 */
	public function addMessage($message, $cate = '', $token = array()) {
		if (! isset ( $this->output [$cate] )) {
			$this->output [$cate] = array ();
		}
		$message .= " at line:" . $token ['line'] . ", col:" . $token ['col'];
		$this->output [$cate] [] = $message;
	}
}