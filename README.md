hi,all


Fl(Font-end Language Helper)是一个支持smarty语法的前端语言(html、css、js)词法分析、编码检查、美化、压缩的工具。


使用方式：


requirce_once "Fl/Fl.class.php";
$flInstance = Fl::getInstance();

//压缩html代码
$compressOutput = $flInstance->compress_html($html_content);

//压缩css代码
$compressOutput = $flInstance->compress_css($css_content);


