<?php
/*
 * 情缘网&相亲网发送手机验证到mycount先查询对方手机号有没有成功验证过
 *
 * 如果对方站成功验证过不发送到本站mycount统计
 *
 * author:weelion
 *
 * 2011-06-23
 *
 */

require dirname(__FILE__).'/./framwork/MooPHP.php';

if($_GET['key'] != 'david') // note 密钥
    exit('error');

$mobile = MooGetGPC('mobile','string','G');

$sql = "select `telphone` from `web_certification` where `telphone` = '{$mobile}'";
$rs = $GLOBALS['_MooClass']['MooMySQL']->getOne($sql,true);

if(!empty($rs)){ // note 如果没验证正常发送
    exit('checked');
}





