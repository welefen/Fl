<?php
Fl::loadClass ( "Fl_Base" );
/**
 * 
 * html to json
 * @author welefen
 *
 */
class Fl_Html_Json extends Fl_Base {

	/**
	 * 
	 * return key options
	 * @var array
	 */
	public $options = array (
		"tag" => "tag", 
		"attrs" => "attrs", 
		"children" => "children", 
		"text" => "text", 
		"other" => "other", 
		"attrs_remove_quote" => true 
	);

	/**
	 * run
	 * @see Fl_Base::run()
	 */
	public function run($options = array()) {
		if ($this->checkHasTplToken ()) {
			return false;
		}
		$this->options = array_merge ( $this->options, $options );
		$ast = $this->getInstance ( 'Fl_Html_Ast' )->run ();
		if (! is_array ( $ast )) {
			return false;
		}
		return $this->astToJson ( $ast );
	}

	/**
	 * 
	 * ast to json
	 * @param array $ast
	 */
	public function astToJson($ast) {
		$result = array ();
		$tagType = array (
			FL_TOKEN_HTML_TAG => 1, 
			FL_TOKEN_HTML_SCRIPT_TAG => 1, 
			FL_TOKEN_HTML_STYLE_TAG => 1, 
			FL_TOKEN_HTML_PRE_TAG => 1, 
			FL_TOKEN_HTML_TEXTAREA_TAG => 1 
		);
		foreach ( $ast as $item ) {
			if (isset ( $tagType [$item ['type']] )) {
				$data = $this->getTagData ( $item ['value'] );
				if (! empty ( $item ['children'] )) {
					$children = $this->astToJson ( $item ['children'] );
					if (! empty ( $children )) {
						$data [$this->options ['children']] = $children;
					}
				}
				$result [] = $data;
			} elseif ($item ['type'] === FL_TOKEN_HTML_TEXT) {
				$result [] = array (
					$this->options ['text'] => $item ['value'] 
				);
			} elseif ($item ['type'] === FL_TOKEN_HTML_SINGLE_TAG) {
				$data = $this->getTagData ( $item ['value'] );
				$result [] = $data;
			} else {
				$result [] = array (
					$this->options ['other'] => $item ['value'] 
				);
			}
		}
		return $result;
	}

	/**
	 * 
	 * get tag and attrs
	 * @param string $value
	 */
	public function getTagData($value) {
		$mixed = $this->getInstance ( "Fl_Html_TagToken", $value )->run ();
		$attrs = $mixed ['attrs'];
		$data = array (
			$this->options ['tag'] => $mixed ['tag'] 
		);
		if (! empty ( $attrs )) {
			foreach ( $attrs as $aItem ) {
				$count = count ( $aItem );
				if ($count === 1) {
					$attrsData [$aItem [0]] = "";
				} elseif ($count === 3) {
					if ($this->options ['attrs_remove_quote']) {
						$valueDetail = Fl_Html_Static::getUnquoteText ( $aItem [2] );
						$attrsData [$aItem [0]] = $valueDetail ['text'];
					} else {
						$attrsData [$aItem [0]] = $aItem [2];
					}
				}
			}
			$data [$this->options ['attrs']] = $attrsData;
		}
		return $data;
	}
}