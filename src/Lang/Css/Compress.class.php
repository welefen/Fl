<?php
Fl::loadClass ( "Fl_Base" );
/**
 * 
 * CSS Compress
 * @author welefen
 *
 */
class Fl_Css_Compress extends Fl_Base {

	/**
	 * 
	 * compress options
	 * base compress is remove newline & comment
	 * @var array
	 */
	public $options = array (
		"remove_last_semicolon" => true, 
		"remove_empty_selector" => true, 
		"override_same_property" => true, 
		"short_value" => true, 
		"merge_property" => true, 
		"sort_property" => true, 
		"sort_selector" => true, 
		"merge_selector" => true, 
		"property_to_lower" => true 
	);

	/**
	 * 
	 * tpl options
	 * @var array
	 */
	protected $tplOptions = array (
		"override_same_property" => false, 
		"short_value" => false, 
		"merge_property" => false, 
		"sort_property" => false, 
		"sort_selector" => false, 
		"merge_selector" => false, 
		"property_to_lower" => false 
	);

	/**
	 * 
	 * remove useless class, used class will be set in $useClassList
	 * @var boolean
	 */
	public $removeUnusedClass = false;

	/**
	 * 
	 * use class in html or js. 
	 * is a array, not contain `.`
	 * @var array
	 */
	public $useClassList = array ();

	/**
	 * 
	 * token instance
	 * @var object
	 */
	protected $tokenInstance = null;

	/**
	 * 
	 * output array, with join '' to be returned
	 * @var array
	 */
	protected $output = array ();

	/**
	 * 
	 * selectors collection
	 * @var array
	 */
	protected $selectors = array ();

	/**
	 * 
	 * single tag in multi selectors, `div,h1,h2{}`
	 * @var array
	 */
	protected $tagMultis = array ();

	/**
	 * 
	 * if not in keyframes
	 * @var boolean
	 */
	protected $inKeyframes = false;

	/**
	 * run
	 * @see Fl_Base::run()
	 */
	public function run($options = array()) {
		$this->options = array_merge ( $this->options, $options );
		$this->tokenInstance = $this->getInstance ( "Fl_Css_Token" );
		$selectorPos = 0;
		$sortSelector = $this->options ['sort_selector'];
		while ( $token = $this->tokenInstance->getNextToken () ) {
			switch ($token ['type']) {
				case FL_TOKEN_CSS_SELECTOR :
					$this->collectionSelector ( $token, $selectorPos ++ );
					break;
				case FL_TOKEN_CSS_BRACES_TWO_END :
					$this->compressSelector ();
					$this->options ['sort_selector'] = $sortSelector;
					$this->output [] = trim ( $token ['value'] );
					$this->inKeyframes = false;
					break;
				case FL_TOKEN_CSS_AT_KEYFRAMES :
					$this->options ['sort_selector'] = false;
					$this->inKeyframes = true;
				default :
					if (Fl_Css_Static::isAtType ( $token ['type'] )) {
						$this->compressSelector ( false, false );
					}
					$this->output [] = trim ( $token ['value'] );
			}
		}
		$this->compressSelector ();
		$result = join ( '', $this->output );
		return Fl_Css_Static::compressCommon ( $result );
	}

	/**
	 * 
	 * compress selector
	 */
	public function compressSelector() {
		if (empty ( $this->selectors )) {
			return true;
		}
		$selectors = array_values ( $this->selectors );
		$this->selectors = array ();
		if (! $this->options ['merge_selector'] || count ( $selectors ) === 1) {
			return $this->output [] = $this->selectorToText ( $selectors );
		}
		$count = count ( $selectors );
		$result = array ();
		$se = array ();
		$flag = 0;
		for($i = 0; $i < $count; $i ++) {
			$item = $selectors [$i];
			if ($i === $count - 1) {
				$flag = 1;
			}
			if (empty ( $item ['equal'] ) || $item ['same_score']) {
				$se [] = $item;
			} else {
				$flag = 2;
			}
			if ($flag) {
				$se = $this->selectorsIntersect ( $se );
				if ($this->options ['sort_selector']) {
					$se = Fl_Css_Static::sortSelectors ( $se );
				}
				if ($flag === 2) {
					$se [] = $item;
				}
				if (! empty ( $result )) {
					array_unshift ( $se, array_pop ( $result ) );
				}
				$se = Fl_Css_Static::combineSameSelector ( $se );
				$se = $this->selectorsIntersect ( $se );
				$result = array_merge ( $result, $se );
				$se = array ();
				$flag = 0;
			}
		}
		$this->output [] = $this->selectorToText ( $result );
	}

	/**
	 * 
	 * selectors intersect
	 * @param array $selectors
	 */
	public function selectorsIntersect($selectors = array()) {
		while ( true ) {
			$length = count ( $selectors );
			if ($length < 2) {
				break;
			}
			$result = array ();
			$flag = false;
			$pos = 0;
			for($i = 0; $i < $length - 1; $i ++) {
				$assoc = Fl_Css_Static::getPropertiesIntersect ( $selectors [$i], $selectors [$i + 1] );
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
			if (! $flag) {
				break;
			}
		}
		return $selectors;
	}

	/**
	 * 
	 * collection selector
	 * @param array $token
	 * @param int $selectorPos
	 */
	public function collectionSelector($token, $selectorPos = 0) {
		$attrs = $this->getSelectorProperties ();
		if ($this->options ['remove_empty_selector'] && empty ( $attrs )) {
			return true;
		}
		$detail = array (
			'attrs' => $attrs, 
			'score' => 0, 
			'equal' => array (), 
			'pos' => $selectorPos ++ 
		);
		$result = $this->getInstance ( 'Fl_Css_SelectorToken', $token ['value'] )->run ();
		if ($this->removeUnusedClass) {
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
		if (count ( $result ) === 1) {
			//only a tag
			if (! $this->inKeyframes && count ( $result [0] ) === 1 && $result [0] [0] ['type'] === FL_TOKEN_CSS_SELECTOR_TYPE) {
				$value = strtolower ( $result [0] [0] ['value'] );
				$se = array_merge ( $detail, array (
					"selector" => $value, 
					"score" => 1 
				) );
				if (! isset ( $this->tagMultis [$value] )) {
					if (isset ( $this->selectors [$value] )) {
						$attrs = Fl_Css_Static::mergeProperties ( $this->selectors [$value] ['attrs'], $attrs );
						$this->selectors [$value] ['attrs'] = $attrs;
					} else {
						$this->selectors = array_merge ( array (
							$value => $se 
						), $this->selectors );
					}
				} else {
					$this->selectors [$value . "%" . ($selectorPos ++)] = $se;
				}
			} else {
				$selector = Fl_Css_Static::selectorTokenToText ( $result [0], false );
				$detail ['selector'] = $selector;
				$detail ['score'] = Fl_Css_Static::getSelectorSpecificity ( $result [0], true );
				$this->selectors [$selector . "%" . ($selectorPos ++)] = $detail;
			}
		} else {
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
			$score = - 1;
			$same = true;
			foreach ( $result as $item ) {
				$selectors [] = Fl_Css_Static::selectorTokenToText ( $item, false );
				$s = Fl_Css_Static::getSelectorSpecificity ( $item, true );
				if ($score != - 1 && $score !== $s) {
					$same = false;
				}
				if ($score === - 1) {
					$score = $s;
				}
			}
			$selector = join ( ",", $selectors );
			if ($every) {
				if (isset ( $this->selectors [$selector] )) {
					$attrs = Fl_Css_Static::mergeProperties ( $this->selectors [$selector] ['attrs'], $attrs );
					$this->selectors [$selector] ['attrs'] = $attrs;
				} else {
					$detail ['selector'] = array_shift ( $selectors );
					$detail ['score'] = 1;
					$detail ['same_score'] = true;
					$detail ['equal'] = $selectors;
					$this->selectors = array_merge ( array (
						$selector => $detail 
					), $this->selectors );
				}
			} else {
				$this->tagMultis = array_merge ( $this->tagMultis, $sts );
				$detail ['selector'] = array_shift ( $selectors );
				$detail ['equal'] = $selectors;
				$detail ['same_score'] = $same;
				$detail ['score'] = $score;
				$this->selectors [$selector . "%" . ($selectorPos ++)] = $detail;
			}
		}
	}

	/**
	 * 
	 * get propertis in a selector
	 */
	public function getSelectorProperties() {
		$braces = $this->tokenInstance->getNextToken ();
		if ($braces ['type'] !== FL_TOKEN_CSS_BRACES_ONE_START) {
			$this->throwException ( "after selector must be a {" );
		}
		$attrs = array ();
		$attr = $value = '';
		$pos = 0;
		$hack = $hasColon = $hasTpl = $tpl = false;
		while ( true ) {
			$token = $this->tokenInstance->getNextToken ();
			if (empty ( $token )) {
				break;
			} elseif ($token ['type'] === FL_TOKEN_CSS_PROPERTY) {
				$attr .= $this->options ['property_to_lower'] ? strtolower ( $token ['value'] ) : $token ['value'];
				if (! $this->options ['override_same_property'] && isset ( $attrs [$attr] )) {
					$attr .= $pos ++;
				}
			} elseif ($token ['type'] === FL_TOKEN_CSS_VALUE) {
				$value .= $token ['value'];
			} elseif ($token ['type'] === FL_TOKEN_CSS_SEMICOLON || $token ['type'] === FL_TOKEN_CSS_BRACES_ONE_END) {
				if (! strlen ( $value )) {
					if ($attr && $this->containTpl ( $attr )) {
						$attrs [$attr . '%' . $pos ++] = array (
							"type" => FL_TOKEN_TPL, 
							"value" => $attr 
						);
					}
					$attr = $value = '';
					$hasColon = $tpl = false;
					if ($token ['type'] === FL_TOKEN_CSS_SEMICOLON) {
						continue;
					} else {
						break;
					}
				}
				if ($tpl || $this->containTpl ( $attr )) {
					$hasTpl = true;
					$attrs [$attr . '%' . ($pos ++)] = array (
						"property" => $attr, 
						"value" => $value 
					);
				} else {
					$propertyDetail = Fl_Css_Static::getPropertyDetail ( $attr );
					//if property is filter, can't replace `, ` to `,`
					//see http://www.imququ.com/post/the_bug_of_ie-matrix-filter.html
					if (strtolower ( $propertyDetail ['property'] ) !== 'filter') {
						$value = preg_replace ( "/,\s+/", ",", $value );
					}
					$valueDetail = Fl_Css_Static::getValueDetail ( $value );
					//get short value
					if ($this->options ['short_value']) {
						$valueDetail ['value'] = Fl_Css_Static::getShortValue ( $valueDetail ['value'], $propertyDetail ['property'] );
					}
					//merge them with `+`
					$detail = $propertyDetail + $valueDetail;
					//add pos key for property sort
					$detail ['pos'] = $pos ++;
					/**
					 * for div{color:red;color:blue\9;}
					 * if suffix in css value, can not override property.
					 */
					$attr .= $valueDetail ['suffix'];
					//multi same property
					//background:red;background:url(xx.png)
					if (Fl_Css_Static::isMultiSameProperty ( $attr )) {
						$attr .= "%" . $pos;
						$detail ['type'] = FL_TOKEN_CSS_MULTI_PROPERTY;
					}
					if (! $this->options ['override_same_property']) {
						$attrs [$attr] = $detail;
					} else {
						$attrs = Fl_Css_Static::mergeProperties ( $attrs, array (
							$attr => $detail 
						) );
					}
				}
				$attr = $value = '';
				$hasColon = $tpl = false;
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
			} else if ($token ['type'] === FL_TOKEN_TPL) {
				if ($hasColon) {
					$value .= $token ['value'];
				} else {
					$attr .= $token ['value'];
				}
				$tpl = true;
			} elseif ($token ['type'] === FL_TOKEN_CSS_COLON) {
				if ($hasColon) {
					$value .= $token ['value'];
				} else {
					$hasColon = true;
				}
			}
			if ($token ['type'] === FL_TOKEN_CSS_BRACES_ONE_END) {
				break;
			}
		}
		if ($hasTpl) {
			$this->options = array_merge ( $this->options, $this->tplOptions );
		}
		if (! $hasTpl && ! $hack) {
			if ($this->options ['merge_property']) {
				$attrs = $this->shortProperties ( $attrs );
			}
			//if hack in attrs, can't sort it
			if ($this->options ['sort_property']) {
				$attrs = Fl_Css_Static::sortProperties ( $attrs );
			}
		}
		return $attrs;
	}

	/**
	 * 
	 * short padding,margin properties 
	 * @param array $attrs
	 */
	public function shortProperties($attrs = array()) {
		$attrs = Fl_Css_Static::combineProperty ( $attrs, 'padding', Fl_Css_Static::$paddingChildren );
		$attrs = Fl_Css_Static::combineProperty ( $attrs, 'margin', Fl_Css_Static::$marginChildren );
		return $attrs;
	}

	/**
	 * 
	 * css properties to text
	 * @param array $attrs
	 */
	public function propertiesToText($attrs = array()) {
		$result = array ();
		foreach ( $attrs as $name => $item ) {
			if ($item ['type'] === FL_TOKEN_TPL) {
				$result [] = $item ['value'];
			} else {
				$result [] = $item ['prefix'] . $item ['property'];
				$result [] = ':';
				$result [] = $item ['value'];
				if ($item ['important']) {
					$result [] = '!important';
				}
				$result [] = $item ['suffix'] . ';';
			}
		}
		$result = join ( '', $result );
		if ($this->options ['remove_last_semicolon']) {
			$result = rtrim ( $result, ';' );
		}
		return $result;
	}

	/**
	 * 
	 * selector to text
	 * @param array $selectors
	 */
	public function selectorToText($selectors = array()) {
		$output = '';
		foreach ( $selectors as $item ) {
			if (empty ( $item ['equal'] )) {
				$selector = $item ['selector'];
			} else {
				$item ['equal'] [] = $item ['selector'];
				$selector = join ( ",", $item ['equal'] );
			}
			$attrsText = $this->propertiesToText ( $item ['attrs'] );
			if ($attrsText) {
				$output .= $selector . "{" . $attrsText . "}";
			}
		}
		return $output;
	}
}