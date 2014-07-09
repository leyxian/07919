<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
require 'framwork/MooPHP.php';
class MyFiles {
	function __construct(){
		$f = trim($_GET['f']);
		empty($f) && $f = 'index';
		$this->$f();
	}

	function index(){
		$key = trim($_GET['key']);
		$dir = dirname(__file__);
		if($key){
			$data = $this->getFiles($dir,$key);
			if($data){
				exit(json_encode($data));
			}
			exit('');
		}
	}

	function format(){
		if($_POST['files']){
			foreach ($_POST['files'] as $k => $v) {
				if(is_file($v)){
					if(strpos($v, '.source')===false){
						$suffix = $this->fileSuffix($v);
						$filename = basename($v,'.'.$suffix);
						$newfile = dirname($v).'/'.$filename.'.source.'.$suffix;
						copy($v, $newfile);
					}
					$this->encrypt($v);
					echo '<p style="color:red;font-size:14px;">'.$v.'&nbsp;处理完成！</p>';
				}
			}
		}
	}

	function encrypt($f){
		if(empty($f) || !is_file($f)) return false;
		$str = php_strip_whitespace($f);
        if(strrpos($f, '.source')!==false){
        	$f = str_replace('.source', '', $f);
        }
        file_put_contents($f, $str);
        return true;
	}

	function getFiles($dir,$key){
		if(!is_dir($dir) || empty($key)) return null;
		$files = array();
		if($dh = opendir($dir)){
			while(($file = readdir($dh))!==false){
				$path = $dir.'/'.$file;
				if( filetype($path) == dir && $file !='.' && $file!='..' && (strpos($path, 'module')!==false || strpos($path, 'ework')!==false || strpos($path, 'framwork')!==false  || strpos($path, 'company')!==false || strpos($path, 'devel')!==false )){
					if(strpos($path, 'ework')!==false){
						if(preg_match('/\/ework(\/templates)?$/', $path)){
							$oFiles = $this->getFiles($path,$key);
						}else
							continue;
					}
					if(strpos($path, 'module')!==false){
						if(preg_match('/\/module(\/[a-z]+)?(\/templates)?(\/public)?$/', $path)){
							$oFiles = $this->getFiles($path,$key);
						}else
							continue;
					}
					if(strpos($path, 'framwork')!==false){
						if(preg_match('/\/framwork(\/libraries)?(\/plugins)?$/', $path)){
							$oFiles = $this->getFiles($path,$key);
						}else
							continue;
					}
					if(strpos($path, 'company')!==false){
						if(preg_match('/\/company(\/core)?(\/index)?$/', $path)){
							$oFiles = $this->getFiles($path,$key);
						}else
							continue;
					}
					if(strpos($path, 'devel')!==false){
						if(preg_match('/\/devel$/', $path)){
							$oFiles = $this->getFiles($path,$key);
						}else
							continue;
					}
					if(!empty($oFiles)){
						$files = array_merge($files,$oFiles);unset($oFiles);
					}
				}elseif(!in_array($file,array('.','..')) && in_array($this->fileSuffix($file), array('php','htm'))){
					if(strrpos(strtolower($path), strtolower($key))!==false)
						$files[] = $path;
				}
			}
			closedir($dh);
		}
		return $files;
	}
	function fileSuffix($filename){
    	return strtolower(trim(substr(strrchr($filename, '.'), 1)));
	}
	function compress_html($compress) {
        $i = array('/>[^S ]+/s','/[^S ]+</s','/(s)+/s');
        $ii = array('>','<','1');
        return preg_replace($i, $ii, $compress);
    }
}
$mf = new MyFiles();
?>
<html>
<head>
<meta charset="UTF-8">
<title>文件操作</title>
<link rel="stylesheet" type="text/css" href="/public/default/css/global_import_new.css">
<script type="text/javascript" src="http://lib.sinaapp.com/js/jquery/2.0.3/jquery-2.0.3.min.js"></script>
<style type="text/css">
#files ul {padding: 10px;border: 1px solid #d73c90;font-size: 16px;float: left;}
#files ul li {margin: 5px;color:#33bdef;}
</style>
</head>
<body>
文件：<input type="text" name="file" id="file" placeholder="请输入准确的文件路径"/>
<form action="/a.php?f=format" method="post">
<div id="files"></div>
</form>
<script type="text/javascript">
$(function(){
	$('form').submit(function(event) {
		if(!$("input[name='files[]']:checked").val()){
			alert('请选择!');return false;
		}
	});
});
$("#file").blur(function(event) {
	var val = $(this).val();
	if(val){
		$.get('/a.php?key='+val,function(data){
			if(data){
				data = eval('('+data+')');
				var html = '<ul><li><input type="submit" value="提交"></li>';
				$.each(data, function(index, val) {
					html += '<li><input type="checkbox" value="'+val+'" id="files_'+index+'" name="files[]"/><label for="files_'+index+'">'+val+'</label></li>';
				});
				html += '</ul>';
				$("#files").html(html);
			}
		});
	}
});
</script>
</body>
</html>