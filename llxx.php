<?php
header('Content-Type: text/html; charset=utf-8'); 
$_GET['ENTER'] = empty($_GET['ENTER']) ? "请改变ENTER值" : trim(urldecode($_GET['ENTER']));
$im = imagecreatefromjpeg('./mg.jpg');
$black = imagecolorallocate($im, 255, 000, 000);
$_GET['ENTER'] = 'ENTER : '.$_GET['ENTER'];
imagefttext($im, 14, 0, 20, 20, $black, './fonts/simyahei.ttf',substr($_GET['ENTER'], 0, 74));
$len = strlen($_GET['ENTER']);
$i = 20;
while ($len > 75) {
	$i += 25;
	imagefttext($im, 14, 0, 20, $i, $black, './fonts/simyahei.ttf',substr($_GET['ENTER'], 74, 75));
	$_GET['ENTER'] = substr($_GET['ENTER'], 74);
	$len = strlen($_GET['ENTER']);
}
header('Content-Type: image/gif');
imagejpeg($im);
imagedestroy($im);
?>