<?php
/**
 * @author welefen
 * @license MIT
 * @copyright 2011 - 2012
 * @version 1.0
 * 这里主要就是定义宏常量，文件命名为.class.php，主要是方便使用Fl::loadClass进行加载
 * 
 */
//换行符
define ( 'FL_NEWLINE', "\n" );
//空格
define ( 'FL_SPACE', " " );
//是否含有空白字符的正则
define ( "FL_SPACE_PATTERN", "/\s+/" );
define ( "FL_SPACE_ALL_PATTERN", "/^\s+$/" );
//结束符
define ( 'FL_TOKEN_EOF', 'eof' );
//模版
define ( 'FL_TOKEN_TPL', 'tpl' );
//多行注释
define ( 'FL_COMMENT_MULTI', 'multi' );
//单行注释
define ( 'FL_COMMENT_INLINE', 'inline' );
//html注释
define ( 'FL_COMMENT_HTML', 'html' );
//最后一个token，如token值，主要是一些注释之类的
define ( 'FL_TOKEN_LAST', 'last' );
/**
 * HTML相关的TOKEN
 */
//pre标签
define ( 'FL_TOKEN_HTML_PRE_TAG', 'pre' );
//textarea标签
define ( 'FL_TOKEN_HTML_TEXTAREA_TAG', 'textarea' );
//html的开始标签
define ( 'FL_TOKEN_HTML_TAG_START', 'tag_start' );
//html的结束标签
define ( 'FL_TOKEN_HTML_TAG_END', 'tag_end' );
//script标签
define ( 'FL_TOKEN_HTML_SCRIPT_TAG', 'script' );
//style标签
define ( 'FL_TOKEN_HTML_STYLE_TAG', 'style' );
//IE hack
define ( 'FL_TOKEN_HTML_IE_HACK', 'ie hack' );
//static ok，用于服务器监控
define ( 'FL_TOKEN_HTML_STATUS', 'status' );
//text node
define ( 'FL_TOKEN_HTML_TEXT', 'text' );
//doc type
define ( 'FL_TOKEN_HTML_DOCTYPE', 'doc type' );
//xml head <?xml
define ( 'FL_TOKEN_XML_HEAD', 'xml head' );
//xml cdata
define ( 'FL_TOKEN_XML_CDATA', 'xml cdata' );
define ( 'FL_TOKEN_HTML_SINGLE_TAG', 'single tag' );
define ( 'FL_TOKEN_HTML_TAG', 'tag' );
/**
 * CSS相关的TOKEN
 */
define ( 'FL_TOKEN_CSS_AT', '@' );
//设备符号
define ( 'FL_TOKEN_CSS_AT_MEDIA', 'media' );
define ( 'FL_TOKEN_CSS_AT_NAMESPACE', 'namespace' );
//charset
define ( 'FL_TOKEN_CSS_AT_CHARSET', 'charset' );
//@import url
define ( 'FL_TOKEN_CSS_AT_IMPORT', 'import' );
define ( 'FL_TOKEN_CSS_AT_MOZILLA', 'mozilla' );
//font-face
define ( 'FL_TOKEN_CSS_AT_FONTFACE', 'font-face' );
//page
define ( 'FL_TOKEN_CSS_AT_PAGE', 'page' );
//keyframes
define ( 'FL_TOKEN_CSS_AT_KEYFRAMES', 'keyframes' );
//@ other
define ( 'FL_TOKEN_CSS_AT_OTHER', 'other' );
//一级{
define ( 'FL_TOKEN_CSS_BRACES_ONE_START', 'one grade { start' );
//一级}
define ( 'FL_TOKEN_CSS_BRACES_ONE_END', 'one grade } end' );
//二级{
define ( 'FL_TOKEN_CSS_BRACES_TWO_START', 'two grade { start' );
//二级}
define ( 'FL_TOKEN_CSS_BRACES_TWO_END', 'two grade } end' );
//选择器
define ( 'FL_TOKEN_CSS_SELECTOR', 'selector' );
define ( 'FL_TOKEN_CSS_KEYFRAMES_SELECTOR', 'keyframes selector' );
//属性
define ( 'FL_TOKEN_CSS_PROPERTY', 'property' );
//冒号
define ( 'FL_TOKEN_CSS_COLON', 'colon' );
//值
define ( 'FL_TOKEN_CSS_VALUE', 'value' );
//分号
define ( 'FL_TOKEN_CSS_SEMICOLON', 'semicolon' );
//hack
define ( 'FL_TOKEN_CSS_HACK', 'hack' );
//normal
define ( 'FL_TOKEN_CSS_NORMAL', 'normal' );
//id
define ( 'FL_TOKEN_CSS_SELECTOR_ID', '#' );
//class
define ( 'FL_TOKEN_CSS_SELECTOR_CLASS', '.' );
//attribute
define ( 'FL_TOKEN_CSS_SELECTOR_ATTRIBUTES', '[]' );
//pseudo class
define ( 'FL_TOKEN_CSS_SELECTOR_PSEUDO_CLASS', ':' );
//type
define ( 'FL_TOKEN_CSS_SELECTOR_TYPE', 'type' );
//pseudo element
define ( 'FL_TOKEN_CSS_SELECTOR_PSEUDO_ELEMENT', '::' );
//universal
define ( 'FL_TOKEN_CSS_SELECTOR_UNIVERSAL', '*' );
//combinator
define ( 'FL_TOKEN_CSS_SELECTOR_COMBINATOR', 'combinator' );
//namespace
define ( 'FL_TOKEN_CSS_SELECTOR_NAMESPACE', 'namespace' );
//comma
define ( 'FL_TOKEN_CSS_SELECTOR_COMMA', ',' );
define ( 'FL_TOKEN_CSS_MULTI_PROPERTY', 'multi property' );
/**
 * JS相关的TOKEN
 */
//数值
define ( 'FL_TOKEN_JS_NUMBER', 'number' );
//普通
define ( 'FL_TOKEN_JS_NORMAL', 'normal' );
define ( 'FL_TOKEN_JS_PUNC', 'punc' );
//字符串
define ( 'FL_TOKEN_JS_STRING', 'string' );
//操作符
define ( 'FL_TOKEN_JS_OPERATOR', 'operator' );
define ( 'FL_TOKEN_JS_NAME', 'name' );
//关键字
define ( 'FL_TOKEN_JS_KEYWORD', 'keyword' );
//单元字符
define ( 'FL_TOKEN_JS_ATOM', 'atom' );
//正则
define ( 'FL_TOKEN_JS_REGEXP', 'regexp' );
//ast
//block
define ( 'FL_TOKEN_JS_AST_BLOCK', 'block' );