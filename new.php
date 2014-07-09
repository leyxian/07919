<?php
ignore_user_abort(true);
set_time_limit(0);

date_default_timezone_set('Asia/Shanghai');
define('FROMEWORK', false);

require 'framwork/libraries/ImageCrop.class.php';

$data['gender'] = 1;
$data['uid'] = 20525531;
$data['occupation'] = 17;
$data['corptype'] = 7;
$data['vehicle'] = 3;
$data['religion'] = 15;
$data['family'] = 2;
$data['nickname'] = '爱幻想的梦';
$data['username'] = 'aihuangxiang@qq.com';
$data['password'] = md5('qingyuan07919');
$data['birthyear'] = 1991;
$data['province'] = 10117000;
$data['marriage'] = 1;
$data['education'] = 3;
$data['salary'] = 3;
$data['house'] = 5;
$data['children'] = 1;
$data['height'] = 162;
$data['weight'] = 48;
$data['animalyear'] = 8;
$data['hometownprovince'] = 10117000;
$data['regdate'] = time();
$data['updatetime'] = time();
$data['nation'] = 1;
$data['usertype'] = 3;
$data['sid'] = 1;
$data['images_ischeck'] = 1;
$data['pic_num'] = 4;
//dbInsert($data);

$photo = getPhoto($uid,'big');
if(!is_dir($photo))	MooMakeDir(dirname($photo));
copy('http://www.772500.com/data/upload/images/photo/2013/02/19/orgin/201302191520070315889655.jpg',$photo);
cropImg($photo,$photo);
$mbData['mainimg'] = $photo;
$mbData['uid'] = $data['uid'];
$mbData['regip'] = '127.0.0.1';
$mbData['source'] = 'http://www.772500.com/';
$mbData['currentprovince'] = $data['province'];
$mbData['friendprovince'] = 'a:3:{i:0;a:1:{i:'.$data['province'].';s:8:"'.0.'";}i:1;a:1:{i:0;s:1:"0";}i:2;a:1:{i:0;s:1:"0";}}';
//dbInsert($mbData);

$mcData['uid'] = $data['uid'];
$mcData['gender'] = 0;
$mcData['workprovince'] = '10117000';
dbInsert($mcData);

$maData['uid'] = $data['uid'];
$maData['real_lastvisit'] = time();
$maData['finally_ip'] = '127.0.0.1';
//dbInsert($maData);

$mlData['uid'] = $data['uid'];
$mlData['lastip'] = '127.0.0.1';
$mlData['lastvisit'] = time();
$mlData['last_login_time'] = time();
$mlData['login_meb'] = 1;
//dbInsert($mlData);

$pics = array('http://www.772500.com/data/upload/images/photo/2013/02/19/orgin/201302191520070315889655.jpg','http://www.772500.com/data/upload/images/photo/2013/02/19/orgin/201302191520320405277595.jpg','http://www.772500.com/data/upload/images/photo/2013/02/19/orgin/201302191520530121961445.jpg','http://www.772500.com/data/upload/images/photo/2013/02/19/orgin/201302191520421337944776.jpg');
foreach($pics as $v){
	$photos = PIC_PATH.'/'.date("Y")."/".date("m")."/".date("d").'/orgin/';
	if(!is_dir($photos)) MooMakeDir($photos);
	$pathParts = pathinfo($v);
	$photosname= $pathParts['basename'];
	$date = date('Y/m/d');
	$imgurl = $photos.$photosname;
	copy($v,$imgurl);
	cropImg($imgurl,$imgurl);
	$pData['uid'] = $data['uid'];
	$pData['imgurl'] = $imgurl;
	$pData['pic_date'] = $date;
	$pData['pic_name'] = $photosname;
	$pData['syscheck'] = 1;
	//dbInsert($pData);
	unset($pData,$imgurl,$photos,$pathParts,$date);
}

$cData['uid'] = $data['uid'];
$cData['telphone'] = 1;
//dbInsert($cData);

function getPhoto($uid, $style = "mid", $jpg="") {
	$dir = FROMEWORK===true?'../':'';
	$first_dir=substr($uid,-1,1);
	$secend_dir=substr($uid,-2,1);
	$third_dir=substr($uid,-3,1);
	$forth_dir=substr($uid,-4,1);
	$new_filedir=$dir."data/upload/userimg/".$first_dir."/".$secend_dir."/".$third_dir."/".$forth_dir."/";

	$uidmd5 = $uid * 3;
		$imgurl ='sfsdf';
	if($jpg != ""){
		$imgurl = $new_filedir . $uidmd5 . '_' . $style . '.' . $jpg;
	}else{
		$imgurl = $new_filedir . $uidmd5 . '_' . $style . '.jpg';
	}
	return $imgurl;
}
//剪裁水印
function cropImg($src,$dst){
	if(!is_file($src)) return null;
	list($width,$height,$type) = getimagesize($src);
	$oldheight = $height-36;
	$new = imagecreatetruecolor($width, $height);
	switch($type){
		case IMAGETYPE_JPEG:
			$old = imagecreatefromjpeg($src);
			break;
		case IMAGETYPE_PNG :
			$old = imagecreatefrompng($src);
			break;
		case IMAGETYPE_GIF :
			$old = imagecreatefromgif($src);
			break;
	}
	imagecopyresized($new,$old,0,0,0,0,$width,$height,$width,$oldheight);
	switch($type){
		case IMAGETYPE_JPEG:
			imagejpeg($new,$dst);
			break;
		case IMAGETYPE_PNG :
			imagepng($src,$dst);
			break;
		case IMAGETYPE_GIF :
			imagegif($src,$dst);
			break;
	}
	imagedestroy($new);
	imagedestroy($old);
}
function dbInsert($table,$data){
    if(empty($table) || array_empty($data)) return false;
    global $_MooClass,$dbTablePre;
    $fields = implode(',', array_keys($data));
    $values = '';
    foreach($data as $v){
        $values .= is_numeric($v)?$v.',':"'".$v."',";
    }
    $values = substr($values, 0, -1);
    $_MooClass['MooMySQL']->query("insert into {$dbTablePre}{$table} ({$fields}) values ($values)");
    $id = $_MooClass['MooMySQL']->insertId();
    $_MooClass['MooMySQL']->close();
    unset($table,$data,$fields,$values,$_MooClass,$dbTablePre);
    return $id;
}
?>