<?php
//$text = file_get_contents("douban_home.text");
//require_once dirname(dirname(dirname(__FILE__))) . '/src/Fl.class.php';
//Fl::loadClass('Fl_Html_Token');
//$instance = new Fl_Html_Token($text);
//$instance->tpl = 'smarty';
//$instance->ld = '<&';
//$instance->rd = '&>';

//xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
$startTime = microtime(true);
//$output = $instance->getAllTokens();
//$endTime = microtime(true);


/*require_once 'HtmlToken/HtmlTokenTest.class.php';
$test = new HtmlTokenTest();
$test->run(new HtmlReporter('utf-8'));
require_once 'HtmlTagToken/TagTokenTest.class.php';
$test = new TagTokenTest();
$test->run(new HtmlReporter('utf-8'));
require_once 'HtmlCompress/HtmlCompressTest.class.php';
$test = new HtmlCompressTest();
$test->run(new HtmlReporter('utf-8'));*/

//require_once 'CssToken/CssTokenTest.class.php';
//$test = new CssTokenTest();
//$test->run(new HtmlReporter('utf-8'));
require_once 'CssSelectorToken/CssSelectorTokenTest.class.php';
$test = new CssSelectorTokenTest();
$test->run(new HtmlReporter('utf-8'));


/*require_once 'JsToken/JsTokenTest.class.php';
$test = new JsTokenTest();
$test->run(new HtmlReporter('utf-8'));
require_once 'JsAst/JsAstTest.class.php';
$test = new JsAstTest();
$test->run(new HtmlReporter('utf-8'));*/


//echo ($endTime - $startTime);

//$xhprof_data = xhprof_disable();
#$path = "/home/welefen/Documents/www/";
#include_once $path . "xhprof_lib/utils/xhprof_lib.php";  
#include_once $path . "xhprof_lib/utils/xhprof_runs.php";  
//$xhprof_runs = new XHProfRuns_Default();  
//echo '<div>Time: '.($endTime - $startTime).'s</div>';
//$run_id = $xhprof_runs->save_run($xhprof_data, "sourcejoy"); 
//echo '<iframe src="http://www/xhprof_html/?run='.$run_id.'&source=sourcejoy" frameborder="0" width="100%" height="950px" border="0"></iframe>';

?>
