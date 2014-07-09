<?php
/*
	Copyright (c) 2009 

	$Id: index.php 399 2009-10-14 07:11:04Z techice $
*/
$name = empty($_GET['n'])?'index':$_GET['n'];

//note ���ؿ�����ò���
require 'config.php';
//定义FROMEWORK，为false表示从前台来，true表示从后台来
define('FROMEWORK',false);
//note ���ؿ��
require 'framwork/MooPHP.php';

//����ķ���
$names = array('login', 'index', 'register', 'lostpasswd', 'inputpwd','sns', 'viewspace', 'relatekw', 'ajax', 'seccode', 'sendmail', 'myaccount','service','lovestyle','loveing','payment','space','activity','chat','material');

if(!in_array($name, $names)) {
	echo 'noaccess';
}
//用户信息
$MooUid = 0;
MooUserInfo();
$user_arr=UserInfo();
$userid =$MooUid;//=$user_arr['uid'];

include  "module/".strtolower($name)."/ajax.php"; 

$_MooClass['MooMySQL']->close();
@ $memcached->close();
@ $fastdb->close();