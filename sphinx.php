<?php
error_reporting(0);
require 'config.php';
//note 加载框架
require 'framwork/MooPHP.php';
$filter = array(array('gender',0));
$result = searchApi('members_man')->getResultOfReset($filter,array("offset"=>0,"limit"=>6));
var_dump($result);
?>