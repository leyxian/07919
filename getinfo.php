<?php
error_reporting(E_ALL ^ E_NOTICE);
require 'config.php';
define('FROMEWORK',false);
require 'framwork/MooPHP.php';

class GetInfo {
	
	private $_functions = array(1=>'index',2=>'getUserPhoto',3=>'mcUnset');
	
	function __construct() {
		$f = empty($_GET['f'])?1:trim($_GET['f']);
		$fun = $this->_functions[$f];
		if(!empty($fun))
			$this->$fun();
		else
			echo 'has no method!';
	}
	
	function index(){
		echo "class : getInfo -> function : index!";
	}
	
	function getUserPhoto(){
		$uid = trim($_GET['uid']);
		$style = empty($_GET['type'])?'mid':trim($_GET['type']);
		if(empty($uid))
			$img = 'public/system/images/service_nopic_man.gif';
		else{
			$user = MooMembersData($uid);
			if(array_empty($user))
				$img = 'public/system/images/service_nopic_man.gif';
			else{
				if($user['images_ischeck']==1 && !empty($user['mainimg'])){
					$img = MooGetphoto($uid,$style);
					if(!$img)
						$img = $user['mainimg'];
				}elseif(!empty($user['mainimg'])){
					$img = $user['gender']==1?'public/system/images/woman.gif':'public/system/images/man.gif';
				}else{
					$img = $user['gender']==1?'public/system/images/service_nopic_woman.gif':'public/system/images/service_nopic_man.gif';
				}
			}
		}
		$path_parts = pathinfo($img);
		switch(strtolower($path_parts['extension'])){
			case 'png':
				header('Content-Type: image/png');
				$im = @imagecreatefrompng('http://'.$_SERVER['SERVER_NAME'].'/'.$img);
				imagepng($im);
			break;
			case 'jpg':
				header('Content-Type: image/jpeg');
				$im = @imagecreatefromjpeg('http://'.$_SERVER['SERVER_NAME'].'/'.$img);
				imagejpeg($im);
			break;
			case 'jpeg':
				header('Content-Type: image/jpeg');
				$im = @imagecreatefromjpeg('http://'.$_SERVER['SERVER_NAME'].'/'.$img);
				imagejpeg($im);
			break;
			case 'gif':
				header('Content-Type: image/gif');
				$im = @imagecreatefromgif('http://'.$_SERVER['SERVER_NAME'].'/'.$img);
				
				imagegif($im);
			break;
			default:
				header('Content-Type: image/png');
				 /* Create a blank image */
				$im  = imagecreatetruecolor(150, 30);
				$bgc = imagecolorallocate($im, 255, 255, 255);
				$tc  = imagecolorallocate($im, 0, 0, 0);
				imagefilledrectangle($im, 0, 0, 150, 30, $bgc);
				/* Output an error message */
				imagestring($im, 1, 5, 5, 'Error loading ' . $imgname, $tc);
				imagepng($im);
		}
		imagedestroy($im);
	}
	
	function mcUnset(){
		$key = $_GET['key'];
		if(!empty($key))
			mc_unset($key);
		else
			echo 'error!';
	}
}

$gt = new GetInfo();
?>