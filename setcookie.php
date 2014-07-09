<?php
$uid = $_POST['uid'];
$auth = $_POST['auth'];
$rand = $_POST['rand'];
require 'config.php';
require 'framwork/MooPHP.php';
MooSetCookie ( "uid", $uid, 3600);
MooSetCookie ( "rand", $rand, 3600);
MooSetCookie ( 'auth', $auth, 86400 * 7);
MooSetCookie ( "where_from", "");
MooSetCookie ( "puid", "");
MooSetCookie ( "website", "");
//header('Location:index.php?n=register&h=steptwo');
header('Location:index.php?n=spread&h=changpwd');