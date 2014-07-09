<?php
error_reporting(0);
require 'config.php';
//note 加载框架
require 'framwork/MooPHP.php';
list($s,$m) = explode(' ', microtime());
$start = $s+$m;
pr(dirname(__file__));
//------------------------------------------------
// $index = empty($_GET['index'])?"choice":trim($_GET['index']);
// var_dump($index);echo '<br/>';
// $sp = searchApi($index);
// unset($_GET['index']);
// unset($_GET['debug']);
// $arrs = array_empty($_GET)?array(array('marriage','4')):$_GET;
// var_dump($arrs);echo '<br/>';
// foreach($arrs as $key=>$value){
	// if (strpos($key,'in')&&strpos($key,'(')){
		// list($key,$value) = explode('in',$key);
		// $key = trim($key);
		// $value = str_replace(',','|',str_replace(array('(',')'),'',$value));
		// $arr[] = array($key, $value);
	// }else {
		// $arr[] = array($key, $value);
	// }
// }
// var_dump($arr);echo '<br/>';
// $res = $sp->getResultOfReset($arr);
// echo '<br/>';
//----------------------------------------------------
//$strArr = array('31137200'=>array(0));
//searchApi('members_man members_women')->updateAttr(array('gender'),$strArr);

$filter = array(
					array("is_lock",1,''),
					array("images_ischeck",1,''),
					//array("@id",'1096625|1096626|1096627|1096631|1096635|1096636|1096637|1096638|1097098|1097108|1097111|1097122|1097131|1097139|20310462|20325561',1),
					array("birthyear", array(1986,1992),''),
					array("province",10106000,''),
					array("city",10106001,''),
				);
pr($filter);
$result = searchApi('members_man')->getResultOfReset($filter,array("offset"=>0,"limit"=>1000));
$ids = searchApi('members_man')->getIds();
foreach($result as $k => $v){
	echo $k.':';pr($v);
}
pr($ids);

//MooFastdbUpdate('members_search','uid',31358258,array('password'=>'e10adc3949ba59abbe56e057f20f883e'));echo "<br/>";
//pr(MooMembersData(31358258));
//var_dump($result);
//--------------------------------------------------
list($s,$m) = explode(' ', microtime());
$end = $s+$m;
echo $end-$start;
?>