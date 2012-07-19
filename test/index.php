<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Fl Test Platform</title>
<style rel="stylesheet">
@charset "utf-8";

* {
	padding: 0;
	margin: 0;
	outline: 0;
}

body {
	font-size: 14px;
	font-family: arial;
}

*::-webkit-scrollbar {
	width: 5px;
}

*::-webkit-scrollbar-track {
	-webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
	-webkit-border-radius: 10px;
	border-radius: 10px;
}

*::-webkit-scrollbar-thumb {
	-webkit-border-radius: 10px;
	border-radius: 10px;
	background: rgba(0, 0, 0, 0.8);
	-webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.5);
}

*::-webkit-scrollbar-thumb:window-inactive {
	background: rgba(0, 0, 0, 0.4);
}

header {
	height: 60px;
	line-height: 60px;
	background: #2D303B;
	background-image: -webkit-gradient(linear, 0 0, 0 100%, from(rgba(81, 86, 100, .95)
		), color-stop(0.88, rgba(57, 61, 71, .95) ), to(rgba(51, 54, 63, .95)
		) );
	-webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, .6), inset 0 1px 0
		rgba(255, 255, 255, .1), inset 0 -1px 0 rgba(0, 0, 0, .2);
	opacity: .90;
	-webkit-transform: translate3d(0, 0, 0);
	transform: translate3d(0, 0, 0);
	position: relative;
	z-index: 10;
	-webkit-box-sizing: border-box;
	display: block;
	color: #ccc;
}

html,body {
	height: 100%;
	width: 100%;
	overflow: hidden;
}

header h1 {
	padding-left: 10px;
}

a {
	text-decoration: none;
}

a:hover {
	text-decoration: underline;
}
h1 a{color:#ccc}
.wrapper {
	position: absolute;
	top: 60px;
	left: 0;
	right: 0;
	bottom: 0;
}

.cate {
	position: absolute;
	top: 0px;
	left: 0;
	width: 190px;
	bottom: 0;
	padding: 5px;
	background: #2D303B;
	-webkit-box-sizing: border-box;
	border-right: 1px solid #161820;
}

.cate h2 {
	color: #AEAFBB;
	text-shadow: 0 1px 0 black;
	height: 30px;
	font-size: 15px;
	line-height: 30px;
	cursor: pointer;
}

.content {
	position: absolute;
	top: 0px;
	left: 190px;
	right: 0;
	bottom: 0;
	padding: 10px;
	line-height: 20px;
	overflow-y: auto;
	overflow-x: hidden;
	background: #363946;
}

.cate li {
	line-height: 24px;
	list-style-type: circle;
	margin-left: 15px;
}

.cate li a {
	color: #D8DAE3;
	height: 24px;
	line-height: 24px;
	cursor: pointer;
	display: block;
	padding-left: 5px;
	font-size: 16px;
}

.cate li a.active {
	background: -webkit-gradient(linear, 0 0, 0 100%, from(rgba(27, 30, 37, .5)
		), to(rgba(27, 30, 37, .35) ) );
	border-color: #1D1F26;
	-webkit-box-shadow: #3B3F48 0 1px 0, rgba(0, 0, 0, .25) 0 1px 2px inset;
	-moz-box-shadow: #3B3F48 0 1px 0, rgba(0, 0, 0, .25) 0 1px 2px inset;
	box-shadow: #3B3F48 0 1px 0, rgba(0, 0, 0, .25) 0 1px 2px inset;
	border-radius: 3px;
	color: #fff;
	font-weight: bold;
}

.content h2 {
	-webkit-box-shadow: inset 1px 0 0 rgba(255, 255, 255, .075);
	box-shadow: inset 1px 0 0 rgba(255, 255, 255, .075);
	background: -webkit-gradient(linear, 0 0, 0 100%, from(rgba(255, 255, 255, .1)
		), to(rgba(255, 255, 255, .02) ) );
	border-bottom: 1px solid #252830;
	height: 24px;
	line-height: 24px;
	padding-left: 10px;
	color: #888;
	font-size: 14px;
	margin: -10px -10px 0 -10px;
}

.add {
	position: fixed;
	bottom: 0;
	background: #fff;
	right: 10px;
	left: 200px;
	-webkit-border-top-left-radius: 5px;
	-webkit-border-top-right-radius: 5px;
	padding: 0 5px 5px 5px;
	opacity: 0.85;
}

textarea {
	width: 98%;
	display: block;
	height: 120px;
	border: 1px solid #ccc;
	-webkit-border-radius: 5px;
	padding: 5px;
	font-size: 13px;
	line-height: 16px;
}

input[type=text] {
	height: 18px;
	border: 1px solid #ccc;
	-webkit-border-radius: 3px;
	width: 60px;
}

button {
	padding: 3px 5px;
}

.result {
	position: absolute;
	top: 10px;
	left: 10px;
	right: 5px;
	bottom: 0px;
}

.list {
	position: absolute;
	top: 15px;
	left: 0px;
	right: 0px;
	bottom: 115px;
	overflow-x: hidden;
	padding-right: 10px;
	overflow-y: auto;
}

.result li {
	line-height: 22px;
	margin: 5px 0;
	width: 100%;
	padding-left: 5px;
	-webkit-border-radius: 5px;
	opacity: 0.7;
	font-size: 14px;
	display: block;
	position: relative;
	cursor: pointer;
	padding-bottom: 1px;
}

.result li span {
	display: block;
	height: 22px;
	overflow: hidden;
}

.result li .info {
	display: none;
	background: #ccc;
	margin: 5px;
	-webkit-border-radius: 5px;
	padding: 3px;
	line-height: 18px;
	opacity: 0.75;
}

.result li b.del {
	position: absolute;
	top: 0;
	right: 10px;
	cursor: pointer;
	font-size: 16px;
	display: none;
	opacity: 0.5;
}

.result li b.refresh {
	position: absolute;
	top: 5px;
	right: 25px;
	cursor: pointer;
	background: url("refresh.png") no-repeat;
	height: 12px;
	width: 12px;
	display: none;
	opacity: 0.5;
}

.result li:hover b {
	display: block;
}

.result li.correct {
	background: green
}

.result li.failure {
	background: red;
}

.add h2 {
	margin: 0;
	padding-left: 0;
	border-bottom: 0;
}

#testResultCon {
	-webkit-transition: all 0.3s ease-out;
}
</style>
<script src="jquery.js" type="text/javascript"></script>
</head>
<body id="bd">
<?php
require 'util.php';
?>
<header>
<h1><a href="/">Fl测试平台</a></h1>
</header>
<div class="wrapper">
<nav class="cate">
		<?php
		$list = get_test_cate_list ();
		?>
		<h2>￥测试分类</h2>
<ul>
			<?php
			foreach ( $list as $name => $class ) :
				?>
				<li><a class="item <?php
				echo $name?>"
		href="test.php?cate=<?php
				echo $name?>"><?php
				echo $name?></a></li>
			<?php
			endforeach
			;
			;
			?>
		</ul>
</nav>
<div class="content">
<div class="result"></div>
<div class="add">
<h2>添加测试</h2>
<textarea name="text" id="text" style="height: 40px"></textarea>
<div style="line-height: 30px;">模版语言：<select name="tpl" id="tpl">
	<option value="Smarty">Smarty</option>
</select> 左定界符:<input type="text" name="ld" id="ld" value="<&" /> 右定界符：<input
	name="rd" id="rd" type="text" value="&>" /> <span id="otherOptions"></span>
<button class="testBtn" style="margin-left: 20px; font-weight: bold;">测试</button>
</div>
<div id="testResultCon" style="display: none">
<h2>测试结果</h2>
<textarea id="testResult" style="height: 200px">
				
				</textarea>
				<?php
				if (isLocal ()) :
					?>
<button class="testBtn" data-add="1" id="addToTestCase"
	style="margin-top: 10px;">测试结果正确，添加到测试用例</button>
	
				
				<?php endif;
				?>
				<button class="preview">预览</button>
<button class="cancel">取消</button>
</div>

</div>
</div>
</div>
<script>
var clsOptions = <?php
echo json_encode ( get_cls_options () );
?>;
</script>
<script>
$(function(){
	function escape_js(str){
		if(!str || 'string' != typeof str) return str;
		return str.replace(/["'<>\\\/`]/g, function($0){
		   return '&#'+ $0.charCodeAt(0) +';';
		});
	}
	var current = '';
	$('.cate a.item').click(function(event){
		event.preventDefault();
		var url = $(this).attr("href");
		$.getJSON(url, function(data){
			var result = ['<h2>测试用例和结果(NUMBER)</h2><ul class="list">'], j= 0, correct = [], failure = [];
			$.each(data, function(i, item){
				//console.log(i);
				if(item['test_result']){
					correct.push('<li data-md5="'+i+'" class="correct"><span>'+escape_js(item['text'])+'</span><b class="refresh" title="重新检测"></b><b class="del" title="删除">x</b>');
					correct.push('<div class="info" style="display:none">模版引擎：'+escape_js(item['tpl'])+' 左定界符：'+escape_js(item['ld'])+' 右定界符：'+escape_js(item['rd'])+'<br />测试用例：'+escape_js(item['text'])+'</div>')
					correct.push('</li>');
				}else{
					failure.push('<li data-md5="'+i+'" class="failure"><span>'+escape_js(item['text'])+'</span><b class="refresh" title="重新检测"></b><b class="del" title="删除">x</b>');
					failure.push('<div class="info" style="display:none">模版引擎：'+escape_js(item['tpl'])+' 左定界符：'+escape_js(item['ld'])+' 右定界符：'+escape_js(item['rd'])+'<br />测试用例：'+escape_js(item['text'])+'</div>')
					failure.push('</li>');
				}
				j++;
			})
			result.push(failure.join(''));
			result.push(correct.join(''));
			result.push('</ul>');
			$(".result").html(result.join('').replace('NUMBER', j));
			showOptions();
		})
		var html = $(this).html();
		if(current){
			$('.'+current).removeClass('active');
		}
		current = html;
		location.hash = current;
		$('.'+current).addClass('active');
		
	})
	function showOptions(){
		var options = clsOptions[current];
		if(!options) {
			return $('#otherOptions').html('');
		}
		var result = ["其他选项："];
		if(options.properties){
			for(var name in options.properties){
				var value = options.properties[name];
				result.push("<label style='margin-left:10px;'>"+name+":</label>");
				if(value === true || value === false){
					result.push('<input type="checkbox" id="'+name+'" name="'+name+'"'+ (value ? " checked" : "")+'>')
				}else{
					result.push('<input type="text" id="'+name+'" name="'+name+'" value="'+value+'">')
				}
			}
		}
		if(options.options){
			for(var name in options.options){
				var value = options.options[name];
				result.push("<label style='margin-left:10px;'>"+name+":</label>");
				if(value === true || value === false){
					result.push('<input type="checkbox" id="'+name+'" name="'+name+'"'+ (value ? " checked" : "")+'>')
				}else{
					result.push('<input type="text" id="'+name+'" name="'+name+'" value="'+value+'">')
				}
			}
		}
		$('#otherOptions').html(result.join(''));
		$('ul.list').css("bottom", $('div.add').height()+10);
	}
	var hash = (location.hash || '#').substr(1);
	if(hash && $('.cate li a.'+hash).length){
		$('.cate li a.'+hash).click();
	}else{
		$('.cate li a').first().click();
	}
	
	$('.testBtn').click(function(){
		var text = $('#text').val();
		if(!text){
			return $('#text').focus();
		}
		var ld = $('#ld').val();
		var rd = $('#rd').val();
		var tpl = $('#tpl').val();
		var add = $(this).attr('data-add');
		var pars = {
			cate: current,
			text: text,
			ld: ld,
			rd: rd,
			tpl: tpl,
			add: add
		};
		var options = clsOptions[current];
		if(options){
			if(options.properties){
				for(var name in options.properties){
					var defaultValue = options.properties[name];
					if(defaultValue === true || defaultValue === false){
						pars[name] = $('#'+name)[0].checked ? 1 : 0;
					}else{
						pars[name] = $('#'+name).val();
					}
				}
			}
			if(options.options){
				for(var name in options.options){
					var defaultValue = options.options[name];
					if(defaultValue === true || defaultValue === false){
						pars[name] = $('#'+name)[0].checked ? 1 : 0;
					}else{
						pars[name] = $('#'+name).val();
					}
				}
			}
		}
		$.post('test.php?type=addTest', pars, function(result){
			if(add){
				if(!result){
					var hash = (location.hash || '#').substr(1);
					if(hash && $('.cate li a.'+hash).length){
						$('.cate li a.'+hash).click();
					}else{
						$('.cate li a').first().click();
					}
					$('#testResultCon').hide();
					//return location.reload();
				}
			}
			$('#testResult').val(result).show();
			$('#testResultCon').show();
			if(result.indexOf('Fatal error') > -1 || result.indexOf('throwException') > -1){
				$('#addToTestCase').hide();
			}else{
				$('#addToTestCase').show();
			}
		});
	})
	$('.result').delegate('b.del', 'click', function(event){
		event.stopPropagation();
		if(confirm("确认删除？")){
			var item = $(this.parentNode).find('span').html();
			$.post('test.php?type=delTest', {
				cate: current,
				//item: item,
				md5:$(this).parent().attr('data-md5')
			}, function(result){
				//location.reload();
				var hash = (location.hash || '#').substr(1);
				if(hash && $('.cate li a.'+hash).length){
					$('.cate li a.'+hash).click();
				}else{
					$('.cate li a').first().click();
				}
				$('#testResultCon').hide();
			})
		}
	})
	$('.result').delegate('b.refresh', 'click', function(event){
		event.stopPropagation();
		$.getJSON('test.php?type=getTest', {
			cate: current,
			md5: $(this).parent().attr('data-md5')
		},function(data){
			$('#text').val(data.text);
			$('#ld').val(data.ld||data.properties.ld);
			$('#rd').val(data.rd||data.properties.rd);
		})
		$.post('test.php?type=retest', {
			cate: current,
			md5:$(this).parent().attr('data-md5')
		}, function(result){
			$('#testResultCon').show();
			$('#testResult').val(result);
		})
	})
	$('.cancel').click(function(){
		$('#testResultCon').hide();
	})
	$('.result').delegate('li', 'click', function(event){
		$(this).find('.info').toggle();
	})
	$('.preview').click(function(){
		var a = window.open("about:blank");
		a.focus();
		a.document.write($('#testResult').val());
	})
})
</script>
</body>
</html>