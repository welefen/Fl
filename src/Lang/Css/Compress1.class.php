<?php
/**
 * 
 * CSS Compress Class
 * @author welefen
 *
 */
Fl::loadClass ( "Fl_Base" );
Fl::loadClass ( "Fl_Css_Static" );
class Fl_Css_Compress extends Fl_Base {

	/**
	 * 
	 * compress options
	 * @var array
	 */
	public $options = array (
		'short_value' => true, 
		'sort_property' => true, 
		'sort_selector' => true, 
		'combine_value' => true, 
		'combine_selector' => true 
	);

	/**
	 * tpl options
	 */
	protected $tplOptions = array (
		"sort_selector" => false, 
		"combine_value" => false, 
		"combine_selector" => false 
	);

	/**
	 * 
	 *remove unuse css class
	 * @var boolean
	 */
	public $removeUselessClass = false;

	/**
	 * 
	 * use class list
	 * @var array
	 */
	public $useClassList = array ();

	/**
	 * 
	 * prev token
	 * @var array
	 */
	protected $preToken = array ();

	/**
	 * 
	 * current token
	 * @var array
	 */
	protected $curToken = array ();

	/**
	 * 
	 * next token
	 * @var array
	 */
	protected $nextToken = array ();

	/**
	 * 
	 * css token instance
	 * @var object
	 */
	protected $tokenInstance = null;

	/**
	 * 
	 * output text
	 * @var array
	 */
	protected $output = array ();

	/**
	 * 
	 * current selector
	 * @var string
	 */
	protected $selectors = array ();

	/**
	 * 
	 * single tag, such as: div{color}
	 * @var array
	 */
	protected $singleTagSelectors = array ();

	/**
	 * 
	 * single tag in mutli selectors, such as: #id, div{color:red}
	 * @var array
	 */
	protected $singleTagInMultiSelectors = array ();

	/**
	 * 
	 * output tmp
	 * @var array
	 */
	protected $outputTmp = array ();

	/**
	 * 
	 * first selectors in media
	 * @var array
	 */
	protected $firstSelectors = array ();

	/**
	 * 
	 * first selector
	 * @var boolean
	 */
	protected $firstSelectored = true;

	/**
	 * 
	 * selector position
	 * @var int
	 */
	protected $selectorPos = 0;

	/**
	 * 
	 * @var boolean
	 */
	protected $sortSelector = true;

	/**
	 * run
	 * @see Fl_Base::run()
	 */
	public function run($options = array()) {
		$this->options = array_merge ( $this->options, $options );
		$this->tokenInstance = $this->getInstance ( 'Fl_Css_Token' );
		if ($this->checkHasTplToken ()) {
			$this->options = array_merge ( $this->options, $this->tplOptions );
		}
		$this->nextToken = $this->tokenInstance->getNextToken ();
		$end = false;
		while ( true ) {
			$token = $this->getToken ();
			$this->compressToken ( $this->curToken );
			if (empty ( $token )) {
				break;
			}
		}
		$this->combineSelector ();
		$this->combineSingleTagSelectors ();
		$result = join ( '', $this->output );
		return Fl_Css_Static::compressCssValue ( $result );
	}

	/**
	 * 
	 * get current token
	 */
	public function getToken() {
		$this->preToken = $this->curToken;
		$this->curToken = $this->nextToken;
		$this->nextToken = $this->tokenInstance->getNextToken ();
		if (! $this->nextToken) {
			$this->nextToken = array ();
		}
		return $this->curToken;
	}

	/**
	 * 
	 * compress token
	 * @param array $token
	 */
	public function compressToken($token) {
		switch ($token ['type']) {
			case FL_TOKEN_CSS_SELECTOR :
				$this->compressSingleSelector ( $token );
				break;
			case FL_TOKEN_CSS_AT_KEYFRAMES :
				$this->sortSelector = false;
				$this->output [] = trim ( $token ['value'] );
				break;
			case FL_TOKEN_CSS_BRACES_TWO_END :
				$this->combineSelector ();
				$this->combineSingleTagSelectors ();
			default :
				if (Fl_Css_Static::isAtType ( $token ['type'] )) {
					$this->combineSelector ( false, false );
				}
				$this->output [] = trim ( $token ['value'] );
		}
	}

	/**
	 * 
	 * combine single tag selectors
	 */
	public function combineSingleTagSelectors() {
		$result = array_merge ( array_values ( $this->singleTagSelectors ), $this->firstSelectors );
		$this->firstSelectors = array ();
		$this->singleTagSelectors = array ();
		$this->singleTagInMultiSelectors = array ();
		$this->selectors = $result;
		$result = $this->combineSelector ( true );
		if ($result !== true) {
			$this->output [] = $result;
		}
		$this->output [] = join ( "", $this->outputTmp );
		$this->outputTmp = array ();
		$this->sortSelector = true;
		$this->firstSelectored = true;
	}

	/**
	 * 
	 * combine selector in some media
	 */
	public function combineSelector($return = false) {
		if (empty ( $this->selectors )) {
			return true;
		}
		$selectors = Fl_Css_Static::sortSelectors ( $this->selectors );
		$selectors = array_values ( $selectors );
		$this->selectors = array ();
		while ( true ) {
			$length = count ( $selectors );
			if ($length < 2) {
				break;
			}
			$result = array ();
			$flag = false;
			$pos = 0;
			for($i = 0; $i < $length - 1; $i ++) {
				$assoc = self::getAttrsIntersect ( $selectors [$i], $selectors [$i + 1] );
				if (! empty ( $assoc )) {
					foreach ( $assoc as $name => $item ) {
						unset ( $selectors [$i] ['attrs'] [$name] );
						unset ( $selectors [$i + 1] ['attrs'] [$name] );
					}
					$equal = array_merge ( array (
						$selectors [$i + 1] ['selector'] 
					), $selectors [$i] ['equal'], $selectors [$i + 1] ['equal'] );
					$selectors [$i] ['pos'] = $pos ++;
					$result [] = $selectors [$i];
					$result [] = array (
						"attrs" => $assoc, 
						"selector" => $selectors [$i] ['selector'], 
						'equal' => $equal, 
						'pos' => $pos ++ 
					);
					$flag = true;
				} elseif (! empty ( $selectors [$i] ['attrs'] )) {
					$result [] = array_merge ( $selectors [$i], array (
						'pos' => $pos ++ 
					) );
				}
			}
			if (! empty ( $selectors [$length - 1] )) {
				$result [] = array_merge ( $selectors [$length - 1], array (
					'pos' => $pos ++ 
				) );
			}
			$selectors = $result;
			$selectors = Fl_Css_Static::sortSelectors ( $selectors );
			if (! $flag) {
				break;
			}
		}
		if ($return) {
			return self::selectorToText ( $selectors );
		}
		if ($this->firstSelectored) {
			$this->firstSelectors = $selectors;
		} else {
			$this->outputTmp [] = self::selectorToText ( $selectors );
		}
	}

	/**
	 * 
	 * selector to text
	 * @param array $selectors
	 */
	public static function selectorToText($selectors = array()) {
		$output = '';
		foreach ( $selectors as $item ) {
			if (empty ( $item ['equal'] )) {
				$selector = $item ['selector'];
			} else {
				$item ['equal'] [] = $item ['selector'];
				$selector = join ( ",", $item ['equal'] );
			}
			$attrsText = self::attrsToText ( $item ['attrs'] );
			if ($attrsText) {
				$output .= $selector . "{" . $attrsText . "}";
			}
		}
		return $output;
	}

	/**
	 * 
	 * serialize attrs that interect
	 * @param array $attrs1
	 * @param array $attrs2
	 */
	public static function serializeAttrs($attrs1, $attrs2) {
		if ($attrs1 ['prefix'] || $attrs1 ['suffix'] || $attrs2 ['prefix'] || $attrs2 ['suffix'] || $attrs1 ['type'] === FL_TOKEN_CSS_HACK || $attrs2 ['type'] === FL_TOKEN_CSS_HACK) {
			return - 1;
		}
		unset ( $attrs1 ['pos'], $attrs2 ['pos'], $attrs1 ['equal'], $attrs2 ['equal'] );
		#return strcmp ( json_encode ( $attrs1 ), json_encode ( $attrs2 ) );
		return strcmp ( serialize ( $attrs1 ), serialize ( $attrs2 ) );
	}

	/**
	 * 
	 * get intersect of two attrs
	 * @param array $se1
	 * @param array $se2
	 */
	public static function getAttrsIntersect($se1 = array(), $se2 = array()) {
		$attrs1 = $se1 ['attrs'];
		$attrs2 = $se2 ['attrs'];
		$assoc = array_uintersect_assoc ( $attrs1, $attrs2, "Fl_Css_Compress::serializeAttrs" );
		//if intersect attrs has hack attr, remove it 
		foreach ( $assoc as $name => $item ) {
			foreach ( $attrs1 as $n1 => $i1 ) {
				if ($i1 ['property'] == $item ['property'] && ($i1 ['prefix'] || $i1 ['suffix'])) {
					unset ( $assoc [$name] );
				}
				if ($i1 ['type'] === FL_TOKEN_CSS_HACK || $i1 ['type'] === FL_TOKEN_CSS_MULTI_PROPERTY) {
					return false;
				}
			}
			foreach ( $attrs2 as $n1 => $i1 ) {
				if ($i1 ['property'] == $item ['property'] && ($i1 ['prefix'] || $i1 ['suffix'])) {
					unset ( $assoc [$name] );
				}
				if ($i1 ['type'] === FL_TOKEN_CSS_HACK || $i1 ['type'] === FL_TOKEN_CSS_MULTI_PROPERTY) {
					return false;
				}
			}
		}
		if (empty ( $assoc )) {
			return false;
		}
		$assCount = count ( $assoc );
		if (count ( $attrs1 ) != $assCount && count ( $attrs2 ) !== $assCount) {
			// 3 chars is `, { }`
			$seLen = strlen ( $se1 ['selector'] ) + strlen ( $se2 ['selector'] ) + 3;
			$se1Equal = strlen ( join ( ',', $se1 ['equal'] ) );
			$se2Equal = strlen ( join ( ',', $se2 ['equal'] ) );
			if ($se1Equal) {
				$seLen += $se1Equal + 1;
			}
			if ($se2Equal) {
				$seLen += $se2Equal + 1;
			}
			$assLen = 0;
			foreach ( $assoc as $item ) {
				//2 chars is : and ;
				$assLen += strlen ( $item ['prefix'] . $item ['property'] . $item ['value'] . $item ['suffix'] ) + 2;
				//if have important in value, add `!important` length(10)
				if ($item ['important']) {
					$assLen += 10;
				}
			}
			$assLen --;
			//if combine selector length more than combine attrs, can't not combine them
			if ($seLen >= $assLen) {
				return false;
			}
		}
		return $assoc;
	}

	/**
	 * 
	 * css attrs to text
	 * @param array $attrs
	 */
	public static function attrsToText($attrs = array()) {
		$result = array ();
		foreach ( $attrs as $name => $item ) {
			$result [] = $item ['prefix'] . $item ['property'];
			$result [] = ':';
			$result [] = $item ['value'];
			if ($item ['important']) {
				$result [] = '!important';
			}
			$result [] = $item ['suffix'] . ';';
		}
		$result = join ( '', $result );
		$result = rtrim ( $result, ';' );
		return $result;
	}

	/**
	 * 
	 * merge selector attrs
	 * @param array $attrs1
	 * @param array $attrs2
	 */
	public static function mergeAttrs($attrs1 = array(), $attrs2 = array()) {
		foreach ( $attrs2 as $name => $item ) {
			if (isset ( $attrs1 [$name] )) {
				if (! $attrs1 [$name] ['important'] || $item ['important']) {
					//can't not replace it
					unset ( $attrs1 [$name] );
					$attrs1 [$name] = $item;
				}
			} else {
				$attrs1 [$name] = $item;
			}
		}
		return $attrs1;
	}

	/**
	 * 
	 * compress single selector
	 */
	public function compressSingleSelector($token = array()) {
		$attrs = $this->getSelectorAttrs ();
		$attrs = $this->combineCssValue ( $attrs );
		//if not property in selector, remove it
		if (empty ( $attrs )) {
			return true;
		}
		$detail = array (
			'attrs' => $attrs, 
			'score' => 0, 
			'equal' => array (), 
			'pos' => $this->selectorPos ++ 
		);
		$value = $token ['value'];
		$result = $this->getInstance ( 'Fl_Css_SelectorToken', $value )->run ();
		//remove useless css class
		if ($this->removeUselessClass) {
			$removeResult = array ();
			foreach ( $result as $item ) {
				$last = $item [count ( $item ) - 1];
				if ($last ['type'] === FL_TOKEN_CSS_SELECTOR_CLASS) {
					$classValue = substr ( $last ['value'], 1 );
					if (in_array ( $classValue, $this->useClassList )) {
						$removeResult [] = $item;
					}
				} else {
					$removeResult [] = $item;
				}
			}
			if (empty ( $removeResult )) {
				return true;
			}
			$result = $removeResult;
		}
		if (count ( $result ) > 1) {
			$every = true;
			$sts = array ();
			foreach ( $result as $item ) {
				if (count ( $item ) === 1 && $item [0] ['type'] === FL_TOKEN_CSS_SELECTOR_TYPE) {
					$sts [strtolower ( $item [0] ['value'] )] = 1;
				} else {
					$every = false;
				}
			}
			$selectors = array ();
			foreach ( $result as $item ) {
				$selectors [] = Fl_Css_Static::selectorTokenToText ( $item, false );
			}
			$selector = join ( ",", $selectors );
			if ($every && $this->sortSelector) {
				if (isset ( $this->singleTagSelectors [$selector] )) {
					$this->singleTagSelectors [$selector] ["attrs"] = self::mergeAttrs ( $this->singleTagSelectors [$selector] ["attrs"], $attrs );
				} else {
					array_pop ( $selectors );
					$this->singleTagSelectors [$selector] = array (
						"attrs" => $attrs, 
						"selector" => $selectors [0], 
						"equal" => $selectors 
					);
				}
				return true;
			} else {
				$this->singleTagInMultiSelectors = array_merge ( $this->singleTagInMultiSelectors, $sts );
			}
			$this->combineSelector ();
			$this->outputTmp [] = $selector . "{";
			$this->outputTmp [] = self::attrsToText ( $attrs ) . "}";
			$this->firstSelectored = false;
		} else {
			//div{color}, only one tag
			if ($this->sortSelector && count ( $result [0] ) === 1 && $result [0] [0] ['type'] === FL_TOKEN_CSS_SELECTOR_TYPE) {
				$value = strtolower ( $result [0] [0] ['value'] );
				if (! isset ( $this->singleTagInMultiSelectors [$value] )) {
					if (isset ( $this->singleTagSelectors [$value] )) {
						$this->singleTagSelectors [$value] ["attrs"] = self::mergeAttrs ( $this->singleTagSelectors [$value] ["attrs"], $attrs );
					} else {
						$this->singleTagSelectors [$value] = array (
							"attrs" => $attrs, 
							"selector" => $value, 
							"equal" => array () 
						);
					}
					return true;
				}
			}
			$selector = Fl_Css_Static::selectorTokenToText ( $result [0], false );
			$detail ['score'] = Fl_Css_Static::getSelectorSpecificity ( $result [0], true );
			$detail ['selector'] = $selector;
			if (isset ( $this->selectors [$selector] )) {
				$this->selectors [$selector] ['attrs'] = self::mergeAttrs ( $this->selectors [$selector] ['attrs'], $attrs );
			} else {
				$this->selectors [$selector] = $detail;
			}
		}
	}

	/**
	 * 
	 * combine css value
	 * @param array $attrs
	 */
	public function combineCssValue($attrs) {
		$paddingList = array (
			'padding-top', 
			'padding-right', 
			'padding-bottom', 
			'padding-left' 
		);
		$marginList = array (
			'margin-top', 
			'margin-right', 
			'margin-bottom', 
			'margin-left' 
		);
		$attrs = Fl_Css_Static::combineProperty ( $attrs, 'padding', $paddingList );
		$attrs = Fl_Css_Static::combineProperty ( $attrs, 'margin', $marginList );
		return $attrs;
	}

	/**
	 * 
	 * compress single selector, property and value
	 */
	public function getSelectorAttrs() {
		$braces = $this->getToken ();
		if ($braces ['type'] !== FL_TOKEN_CSS_BRACES_ONE_START) {
			$this->throwException ( "after selector must be a {" );
		}
		$attrs = array ();
		$attr = '';
		$pos = 0;
		//css hack in attrs
		$hack = false;
		while ( true ) {
			$token = $this->getToken ();
			if (empty ( $token ) || $token ['type'] === FL_TOKEN_CSS_BRACES_ONE_END) {
				break;
			} elseif ($token ['type'] === FL_TOKEN_CSS_PROPERTY) {
				$attr = strtolower ( $token ['value'] );
			} elseif ($token ['type'] === FL_TOKEN_CSS_VALUE) {
				if (! $attr) {
					$this->throwException ( 'only css value is not valid.' );
				}
				if (! isset ( $token ['value'] )) {
					$attr = '';
					continue;
				}
				$propertyDetail = Fl_Css_Static::getPropertyDetail ( $attr );
				//if property is filter, can't replace `, ` to `,`
				//see http://www.imququ.com/post/the_bug_of_ie-matrix-filter.html
				if ($propertyDetail ['property'] != 'filter') {
					$token ['value'] = preg_replace ( "/,\s+/", ",", $token ['value'] );
				}
				$valueDetail = Fl_Css_Static::getValueDetail ( $token ['value'] );
				//get short value
				$valueDetail ['value'] = Fl_Css_Static::getShortValue ( $valueDetail ['value'], $propertyDetail ['property'] );
				//merge them with `+`
				$detail = $propertyDetail + $valueDetail;
				//add pos key for property sort
				$detail ['pos'] = $pos ++;
				/**
				 * for {color:red;color:blue\9;}
				 * if suffix in css value, can not override property.
				 */
				$attr .= $valueDetail ['suffix'];
				//multi same property
				//background:red;background:url(xx.png)
				if (Fl_Css_Static::isMultiSameProperty ( $attr )) {
					$attr .= $pos;
					$detail ['type'] = FL_TOKEN_CSS_MULTI_PROPERTY;
				}
				$attrs = self::mergeAttrs ( $attrs, array (
					$attr => $detail 
				) );
				$attr = '';
			} elseif ($token ['type'] === FL_TOKEN_CSS_HACK) {
				//for css hack [;color:red;]
				if (strpos ( $token ['value'], ":" ) != false) {
					$vs = explode ( ":", $token ['value'], 2 );
					$attrs [$vs [0] . strval ( $pos ++ )] = array (
						'value' => $vs [1], 
						'property' => $vs [0], 
						'type' => FL_TOKEN_CSS_HACK 
					);
					$hack = true;
				}
			}
		}
		//if hack in attrs, can't sort it
		if (! $hack) {
			$attrs = Fl_Css_Static::sortProperties ( $attrs );
		}
		return $attrs;
	}
}