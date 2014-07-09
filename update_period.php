<?php
require dirname(__FILE__).'/./framwork/MooPHP.php';

$start = intval($_GET['s']);
echo $start;
$sql = "select uid,bgtime from web_members_search limit $start,200";
$res = $GLOBALS['_MooClass']['MooMySQL']->getAll($sql);
if($res){	
	$start += 200;
	foreach($res as $k=>$v){
		$uid = $v['uid'];
		$bgtime = $v['bgtime'];
		
		$sql1 = "select period from web_member_admininfo where uid=".$uid;
		$res1 = $GLOBALS['_MooClass']['MooMySQL']->getOne($sql1);
		$period = $res1['period'];

		if($period>$bgtime){
			$thistime = $period-$bgtime;
			$sql2 = "update web_member_admininfo set period=".$thistime." where uid=".$uid;
			$res2 = $GLOBALS['_MooClass']['MooMySQL']->query($sql2);
			unset($res1);
		}
	}
	unset($res);
	exit('<html><head><meta http-equiv="refresh" content="2;url=?s='.$start.'"> </head><body></body></html>');
}else{
		exit("ok");
}

?>