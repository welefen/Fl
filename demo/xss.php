<?php
include_once '../src/Fl.class.php';
Fl::loadClass ( 'fl_html_xss' );
$content = '<div>html content {{a}} ddd {{{safe_a}}} {{{unsafe_var}}}</div>';
$instance = new Fl_Html_Xss ( $content, 'utf8' );
$instance->tpl = 'XTemplate';
$instance->ld = '{{';
$instance->rd = '}}';
//安全变量列表，支持正则
$instance->safe_vars = array (
	'safe_a' 
);
$result = $instance->run ( array (
	'url' => 'escape_url',  //URL转义
	'html' => 'escape_html',  //HTML 转义
	'js' => 'escape_js',  //JS 转义
	'callback' => 'escape_callback',  //callback 转义
	'data' => 'escape_data',  //数据转义，最终 innerHTML 到 DOM 中
	'event' => 'escape_event',  //事件参数转义，如： <div onclick="click(\"{{a}}\")"></div>
	'noescape' => 'no_escape',  //不需要转义
	'xml' => 'escape_xml'  //XML 转义
) );
echo $result . "\n";