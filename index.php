<?php
/*  
    Copyright (c) 2009 

    $Id: index.php 399 2009-10-14 07:11:04Z techice $

	header("Expires: Mon, 26 Jul 1970 05:00:00  GMT");  
	header("Cache-Control:no-cache, must-revalidate");  
	header("Pragma:no-cache");  */
$name = isset($_GET['n']) ? $_GET['n'] : 'index';
$_GET['h'] = isset($_GET['h']) ? $_GET['h'] : '';

if($_SERVER['HTTP_HOST'] == 'www.07919.dev')
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
else
    error_reporting(0);

//360防护脚本
if(is_file($_SERVER['DOCUMENT_ROOT'].'/framwork/plugins/360webscan.php') && $_SERVER['HTTP_HOST'] !='www.07919.dev'){
    require_once($_SERVER['DOCUMENT_ROOT'].'/framwork/plugins/360webscan.php');
}

//note 加载框架配置参数
require 'config.php';

defined('MOOPHP_COOKIE_DOMAIN') && MOOPHP_COOKIE_DOMAIN && ini_set('session.cookie_domain', MOOPHP_COOKIE_DOMAIN);

//定义FROMEWORK，为false表示从前台来，true表示从后台来
define("FROMEWORK",false);

//note 加载框架

require 'framwork/MooPHP.php';


//如果脚本中断，关闭数据库连接
register_shutdown_function(array($_MooClass['MooMySQL'],'close'));

//允许的方法
$names = array('login', 'index', 'register', 'lostpasswd', 'inputpwd','myaccount', 'viewspace', 'relatekw', 'ajax', 'seccode', 'sendmail', 'material', 'search', 'service', 'payment','safetyguide','lovestyle','loveing','story', 'about','return','invite','vote','profile','recommend', 'crontab', 'pop','clinic','space','hnintro','paymenttest','cooperation','video','activity','chat',
    'andriod','test','lovestation','spread', 'confession', 'flowershop', 'play', 'company', 
);

//获取推广参数
MooGetFromwhere();
$MooUid = 0;
//用户信息
MooUserInfo();
$user_arr=$user=UserInfo();

$uid = $userid =$MooUid;

MooWebLogs(true,true);

//模块判断
if( !in_array($name, $names) ){
    MooMessage('没有这个页面', 'index.php','01');
}
//普通用户统一跳转WWW
if(strpos($_SERVER['HTTP_HOST'],'www')===false && !Moo_is_kefu() && $_SERVER['SERVER_ADDR']!='127.0.0.1'){
    Header("HTTP/1.1 301 Moved Permanently");
	header('Location: http://'.$_SERVER['SERVER_NAME']);
}

//伪造游客用户数据
if(empty($user_arr)){
	$user_arr['uid'] = 0;
	$user_arr['gender'] = 0;
	$user_arr['birthyear'] = date("Y")-26;//默认26岁
	$user_arr['province'] = 0;
	$user_arr['city']  =  0;
}

//时间相关
if($uid){    
    //更新COOKIE 成活时间
    MooUpateCookie($uid);
    $new_email_num=header_show_total($uid);

    // 判断是不是已经升高级付款
    $sql = "select id from {$dbTablePre}payment_new where status = 1 and pay_type = 2 and pay_service = 1 and uid = {$uid}";
    $h_pay = $_MooClass['MooMySQL']->getOne($sql,true);
}
//获取皮肤名称
$style_uid = MooGetGPC('uid', 'integer', 'G');
$skiname = MooGetGPC('skiname','string','G');
//新邮件数


if( !empty($style_uid) && $style_uid != $uid ){ //采用他人的样式
	$style_user_arr = array();
    if(MooMembersData($style_uid, 'is_lock') == 1) $style_user_arr = MooMembersData($style_uid);
}else{
    $style_uid = $uid;
    $style_user_arr = $user_arr;
}
$style_name = 'default';
include_once("module/".strtolower($name)."/index.php");

//$_MooClass['MooMySQL']->close();

@ $memcached->close();
@ $fastdb->close();
?>
