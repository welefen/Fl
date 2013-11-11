<?php
$text = file_get_contents ( "1.text" );
require_once (dirname ( dirname ( __FILE__ ) )) . '/src/Fl.class.php';
Fl::loadClass ( 'Fl_Html_Compress' );
$instance = new Fl_Html_Compress ( $text );
$instance->tpl = '';
$instance->ld = '';
$instance->rd = '';
$startTime = microtime ( true );
#xhprof_enable ( XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY );
xhprof_enable ();
$options = array (
	"compress_tag" => true 
);
$output = $instance->run ( $options );
$xhprof_data = xhprof_disable ();
$endTime = microtime ( true );
$path = "/home/welefen/Documents/www/";
include_once $path . "xhprof_lib/utils/xhprof_lib.php";
include_once $path . "xhprof_lib/utils/xhprof_runs.php";
$xhprof_runs = new XHProfRuns_Default ();
echo '<div>Time: ' . ($endTime - $startTime) . 's</div>';
$rate = number_format ( (strlen ( $text ) - strlen ( $output )) * 100 / strlen ( $text ), 2 );
echo strlen ( $text ) . "/" . strlen ( $output ) . "<br/>" . $rate . "%<br>";
$run_id = $xhprof_runs->save_run ( $xhprof_data, "sourcejoy" );
echo '<iframe src="http://www/xhprof_html/?run=' . $run_id . '&source=sourcejoy" frameborder="0" width="100%" height="950px" border="0"></iframe>';
?>
