<?php
error_reporting(0);

ignore_user_abort(true);
set_time_limit(0);

require 'framwork/MooPHP.php';


$file = fopen('file/qqmail/mails1.txt', 'r');
while(!feof($file)){
	$str = fgets($file);
	$wStr = '';
	$str = strtolower(trim($str));
	if(preg_match('/[\w\.\-]+@(sina|126|163).(com|cn)/', $str, $matches)){
		$wStr = $str;
	}else{
		$wStr = false;
	}
	if($wStr)
		MooWriteFile('file/sina_wangyi.txt',"{$wStr}\r\n","a+");
}
fclose($file);
MooWriteFile('file/Qmail2.txt',"end\r\n","a+");
exit;
function get_line($file,$line) {    
    $fp = fopen($file,'r');    
    $i = 0;    
    while(!feof($fp)) {    
        $i++;    
        $c = fgets($fp);    
        if($i==$line) {    
            return $c;    
            break;    
        }
    }
    fclose($fp);
    return false;
} 
exit;

//
$file = $files[$_GET['index']];
if($file){
	if($file == 'qq.txt'){
		$file = 'file/'.$file;
	}else{
		$file = 'file/qqmail/'.$file;
	}
	if(is_file($file)){
		pr($file);
	}else{
		exit('no file');
	}
}else{
	exit('no index file');
}

exit;
$s_dir = "file/";
//foreach($files as $v){
	$s_file = $s_dir.$v;
	if(!is_file($s_file)) exit("is not a file!");
	$file = fopen($s_file, 'r');
	while(!feof($file)){
		$str = fgets($file);
		if(preg_match('/@(qq|QQ).(com|COM)/', $str)){
			$data = explode(' ', $str);
			MooWriteFile('file/qq.txt',$data[0],"a+");
		}unset($str);
	}
	fclose($file);
	unset($file);
//}
MooWriteFile('file/qq.txt',"end \r\n","a+");
?>