<?php
/*
 *更新用户的诚信值
 * //reset_integrity_all.php?s=启动记录行&end=结束记录行
 */
require "./framwork/MooPHP.php";
if(!isset($_GET['s'])) exit;
$start = (int)$_GET['s'];
$end = isset($_GET['end']) ? (int)$_GET['end'] : 0 ;
//$length = 100;
//reset_integrity('100012');exit;
$sql = "select `uid`,`s_cid`,`images_ischeck` from web_members_search limit {$start},1000";
$re = $_MooClass['MooMySQL']->getAll($sql);

if($re){
	foreach($re as $r){
		reset_integrity($r);
	}
	$start += 1000;
	if($end && $start >= $end){
		exit("成功更新到$end条记录。");
	}
	exit('<html><head><meta http-equiv="refresh" content="2;url=?s='.$start.'&sid='.$sid.'&end='.$end.'"> </head><body></body></html>');
	
}else{
	exit("ok");
}

?>