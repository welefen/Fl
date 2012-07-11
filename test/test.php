<?php
try {
	require 'util.php';
	$type = $_GET ['type'];
	$cate = get_par ( "cate" );
	$list = get_test_cate_list ();
	if ($type == 'addTest') {
		$text = get_par ( 'text' );
		$ld = get_par ( 'ld' );
		$rd = get_par ( 'rd' );
		$tpl = get_par ( 'tpl' );
		$class = $list [$cate];
		$add = get_par ( 'add' );
		$clsOptions = get_cls_options ( $cate );
		$properties = array (
			"ld" => $ld, 
			"rd" => $rd, 
			"tpl" => $tpl 
		);
		$options = array ();
		if ($clsOptions ['properties']) {
			foreach ( $clsOptions ['properties'] as $name => $value ) {
				if ($value === true || $value === false) {
					$properties [$name] = ! ! get_par ( $name );
				} else {
					$properties [$name] = get_par ( $name );
				}
			}
		}
		if ($clsOptions ['options']) {
			foreach ( $clsOptions ['options'] as $name => $value ) {
				if ($value === true || $value === false) {
					$options [$name] = ! ! get_par ( $name );
				} else {
					$options [$name] = get_par ( $name );
				}
			}
		}
		$result = get_test_result ( $class, $text, $properties, $options );
		if ($add && isLocal ()) {
			add_test_case ( $cate, array (
				"text" => $text, 
				"result" => $result, 
				"options" => $options, 
				"properties" => $properties 
			) );
		} else {
			if (is_array ( $result )) {
				print_r ( $result );
			} else {
				echo $result;
			}
		}
	} elseif ($type == 'delTest') {
		$item = get_par ( 'item' );
		$md5 = get_par ( 'md5' );
		del_test_case ( $cate, $md5 );
	} elseif ($type == 'getTest') {
		$md5 = get_par ( 'md5' );
		echo json_encode ( get_test_case ( $cate, $md5 ) );
	} elseif ($type == 'retest') {
		$md5 = get_par ( 'md5' );
		print_r ( retest_case ( $cate, $md5 ) );
	} elseif ($type == 'welefen') {
		foreach ( $list as $cate => $class ) {
			$file = dirname ( __FILE__ ) . '/Case/' . $cate . '.json';
			if (file_exists ( $file )) {
				$content = file_get_contents ( $file );
				$content = json_decode ( $content, true );
				$result = array ();
				foreach ( $content as $item ) {
					$item ['text'] = trim ( $item ['text'] );
					$result [md5 ( $item ['text'] )] = $item;
				}
				file_put_contents ( $file, json_encode ( $result ) );
			}
		}
	} else {
		$class = $list [$cate];
		$result = test_file ( $cate, $class );
		echo json_encode ( $result );
	}
} catch ( Fl_Exception $e ) {
	if (isLocal ()) {
		print_r ( $e );
	} else {
		echo "Fatal Error: " . $e->message;
	}
}
