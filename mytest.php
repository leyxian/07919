<?php
//require 'config.php';
require 'config_list2.php';
require 'framwork/MooPHP.php';
header("Content-type: text/html; charset=utf-8");
define('FROMEWORK',false);

$p = empty($_GET['p'])?'605000':trim($_GET['p']);
$a = empty($_GET['a'])?'86':trim($_GET['a']);

$handle = fopen('data/house.log', 'r');
while(!feof($handle)){
	$data = fgets($handle);
	$data = explode(' ', $data);
	if($data[3]>$a && (float)str_replace(',', '', $data[7]) < $p){
		echo "<font style='color:red'>".implode('&nbsp;&nbsp;', $data)."</font><hr/>";
	}else{
		echo implode('&nbsp;&nbsp;', $data)."<hr/>";
	}
}

exit;

$key = isset($_GET['key'])?trim($_GET['key']):'';
mc_unset('zacollect_encrypt');var_dump(mc_get('zacollect_encrypt'));
mc_unset('zacollect');
mc_unset('zha_collect');
mc_unset('zha_collect_encrypt');
pr(mc_get('zha_collect_auto_page'));
if($key)
	mc_unset($key);
?>