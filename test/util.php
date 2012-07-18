<?php

function isLocal() {
	return $_SERVER ['HTTP_HOST'] === 'www';
}

function get_par($name) {
	$value = $_GET [$name];
	$value1 = $_POST [$name];
	$val = $value1 ? $value1 : $value;
	//$val = trim ( $val );
	if (get_magic_quotes_gpc ()) {
		return stripslashes ( $val );
	}
	return $val;
}

function get_test_cate_list() {
	$list = array (
		'CssToken' => 'Fl_Css_Token', 
		'CssSelectorToken' => 'Fl_Css_SelectorToken', 
		'CssValidate' => "Fl_Css_Validate", 
		'CssCompress' => 'Fl_Css_Compress', 
		'CssBeautify' => 'Fl_Css_Beautify', 
		'CssAutoComplete' => 'Fl_Css_AutoComplete', 
		'HtmlToken' => "Fl_Html_Token", 
		'HtmlTagToken' => 'Fl_Html_TagToken', 
		'HtmlCompress' => 'Fl_Html_Compress', 
		'HtmlAst' => 'Fl_Html_Ast', 
		'HtmlDom' => 'Fl_Html_Dom', 
		'HtmlJson' => 'Fl_Html_Json', 
		'HtmlValidate' => 'Fl_Html_Validate', 
		'HtmlXss' => 'Fl_Html_Xss', 
		'HtmlBeautify' => 'Fl_Html_Beautify', 
		'JsToken' => 'Fl_Js_Token', 
		'JsAst' => 'Fl_Js_Ast', 
		'JsValidate' => 'Fl_Js_Validate', 
		'JsCompress' => 'Fl_Js_Compress', 
		'JsBeautify' => 'Fl_Js_Beautify' 
	);
	return $list;
}

function get_cls_options($cate = '') {
	$options = array (
		"CssToken" => array (
			"properties" => array (
				"validate" => true 
			) 
		),  ///"options" => array () 
		"CssSelectorToken" => array (
			"properties" => array (
				"validate" => true 
			) 
		),  //"options" => array () 
		"CssValidate" => array (
			"properties" => array (), 
			"options" => array (
				"expression" => true, 
				"important" => true, 
				"properties_hack" => true, 
				"other_hack" => true, 
				"filter" => true, 
				"selector_max_level" => 4 
			) 
		), 
		"CssCompress" => array (
			"options" => array (
				"remove_last_semicolon" => true, 
				"override_same_property" => true, 
				"short_value" => true, 
				"merge_property" => true, 
				"sort_property" => true, 
				"sort_selector" => true, 
				"merge_selector" => true, 
				"property_to_lower" => true 
			) 
		), 
		"CssBeautify" => array (
			"properties" => array (), 
			"options" => array (
				"indent" => "\t", 
				"space_after_colon" => true, 
				"beautify_selector" => true 
			) 
		), 
		"JsAst" => array (
			"properties" => array (
				"embedToken" => false 
			) 
		), 
		"HtmlToken" => array (
			"properties" => array (
				"validate" => true, 
				"isXML" => false 
			) 
		), 
		"HtmlCompress" => array (
			"options" => array (
				"remove_comment" => true, 
				"simple_doctype" => true, 
				"newline_to_space" => true, 
				"tag_to_lower" => true, 
				"remove_inter_tag_space" => false,  //not safe
				"remove_inter_block_tag_space" => true,  //safe
				"replace_multi_space" => " ", 
				"remove_empty_script" => true, 
				"remove_empty_style" => true, 
				"remove_optional_attrs" => true, 
				"remove_attrs_quote" => true, 
				"remove_attrs_optional_value" => true, 
				"remove_http_protocal" => true, 
				"remove_https_protocal" => true, 
				"remove_optional_end_tag" => true, 
				"remove_optional_end_tag_list" => array (), 
				"chars_line_break" => 8000, 
				"compress_style_value" => true, 
				"compress_inline_css" => true, 
				"compress_inline_js" => true, 
				"compress_tag" => true, 
				"merge_adjacent_css" => true, 
				"merge_adjacent_js" => true 
			) 
		), 
		"HtmlAst" => array (
			"options" => array (
				"embed_token" => false, 
				"remove_blank_text" => false, 
				"remove_blank_text_in_block_tag" => true 
			) 
		), 
		"HtmlXss" => array (
			"properties" => array (
				"auto_fixed" => true, 
				"isXml" => false, 
				"identifyFn" => "" 
			), 
			"options" => array (
				"url" => "sp_path", 
				"html" => "sp_escape_html", 
				"js" => "sp_escape_js", 
				"callback" => "sp_escape_callback", 
				"data" => "sp_escape_data", 
				"event" => "sp_escape_event", 
				"noescape" => "sp_no_escape", 
				"xml" => "sp_escape_xml" 
			) 
		) 
	);
	if (isset ( $options [$cate] )) {
		return $options [$cate];
	}
	return $options;
}

function test_file($name, $class) {
	require_once dirname ( dirname ( __FILE__ ) ) . "/src/Fl.class.php";
	Fl::loadClass ( $class );
	$file = dirname ( __FILE__ ) . "/Case/" . $name . ".json";
	if (file_exists ( $file )) {
		$content = file_get_contents ( $file );
		if ($content) {
			$json = json_decode ( $content, true );
			$json_result = array ();
			foreach ( $json as $md5 => $item ) {
				$text = $item ['text'];
				$result = $item ['result'];
				$properties = $item ['properties'];
				$options = $item ['options'];
				if (! $options) {
					$options = array ();
				}
				$instance = new $class ( $text );
				if ($properties) {
					foreach ( $properties as $name => $value ) {
						$instance->$name = $value;
					}
				}
				foreach ( array (
					'tpl', 
					'ld', 
					'rd' 
				) as $name ) {
					if ($item [$name]) {
						$instance->$name = $item [$name];
					}
				}
				$output = $instance->run ( $options );
				$item ['test_result'] = ($result === $output);
				if ($item ['test_result']) {
					unset ( $item ['result'] );
				}
				$json_result [$md5] = $item;
			}
			return $json_result;
		}
	}
	return array ();
}

function add_test_case($name, $data) {
	$file = dirname ( __FILE__ ) . "/Case/" . $name . ".json";
	$result = array ();
	if (file_exists ( $file )) {
		$result = file_get_contents ( $file );
		$result = json_decode ( $result, true );
	}
	$result [md5 ( $data ['text'] )] = $data;
	file_put_contents ( $file, json_encode ( $result ) );
}

function del_test_case($name, $md5) {
	$file = dirname ( __FILE__ ) . "/Case/" . $name . ".json";
	$json = array ();
	if (file_exists ( $file )) {
		$result = file_get_contents ( $file );
		$result = json_decode ( $result, true );
		if (is_array ( $result )) {
			foreach ( $result as $m => $item ) {
				if ($m !== $md5) {
					$json [$m] = $item;
				}
			}
		}
	}
	file_put_contents ( $file, json_encode ( $json ) );
}

function get_test_case($cate, $md5) {
	$list = get_test_cate_list ();
	$class = $list [$cate];
	$file = dirname ( __FILE__ ) . "/Case/" . $cate . ".json";
	if (file_exists ( $file )) {
		$content = file_get_contents ( $file );
		$json = json_decode ( $content, true );
		$item = $json [$md5];
		return $item;
	}
	return array ();
}

function retest_case($cate, $md5) {
	$list = get_test_cate_list ();
	$class = $list [$cate];
	$file = dirname ( __FILE__ ) . "/Case/" . $cate . ".json";
	if (file_exists ( $file )) {
		$content = file_get_contents ( $file );
		$json = json_decode ( $content, true );
		$item = $json [$md5];
		if ($item) {
			require_once dirname ( dirname ( __FILE__ ) ) . "/src/Fl.class.php";
			Fl::loadClass ( $class );
			$instance = new $class ( $item ['text'] );
			$properties = $item ['properties'];
			$options = $item ['options'];
			if (! $options) {
				$options = array ();
			}
			if ($properties) {
				foreach ( $properties as $name => $value ) {
					$instance->$name = $value;
				}
			}
			foreach ( array (
				'tpl', 
				'ld', 
				'rd' 
			) as $name ) {
				if ($item [$name]) {
					$instance->$name = $item [$name];
				}
			}
			$output = $instance->run ( $options );
			return $output;
		}
	}
	return '';
}

function get_test_result($class, $text, $properties = array(), $options = array()) {
	require_once dirname ( dirname ( __FILE__ ) ) . "/src/Fl.class.php";
	Fl::loadClass ( $class );
	$instance = new $class ( $text );
	foreach ( $properties as $name => $value ) {
		$instance->$name = $value;
	}
	$output = $instance->run ( $options );
	return $output;
}