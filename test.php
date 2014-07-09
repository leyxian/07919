<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
date_default_timezone_set('PRC');
ignore_user_abort(true); 
set_time_limit('3600');
header('Content-Type: text/html; charset=utf-8'); 
require 'framwork/MooPHP.php';
// $_MooClass['MooMySQL']->query('UPDATE web_members_search SET sid=1 WHERE uid = 31708886');
// MooFastdbUpdate('members_search','uid',31708886,array('sid'=>1));
// $cl = searchApi('members_man members_women');
// $cl->updateAttr(array('sid'), array('31708886'=>array('1') ) );
//$line = system('shutdown -s -t 120', $retval);
//echo file_get_contents('http://www.youyuan.com/anhui/mm18-28/advance-0-0-0-0-0/p1/');
//echo '<form name="myfrom" id="myfrom" action="http://www.07919.dev/index.php?n=index&h=api&f=login" method="POST"><input type="text" name="username" value="13696545523"/><input type="password" name="password" value="123456"/><input type="text" name="mooApi" value="android"/><input type="password" name="mooCode" value="07919"/></form><script>document.forms["myfrom"].submit();</script>';
$sec0 = strtotime('12:00:00') - time();
$sec1 = strtotime('18:00:00') - time();
$sec2 = strtotime('21:00:00') - time();
$sec3 = strtotime('2014-08-20 15:30:00') - time();
// $_MooClass['MooMySQL']->query("UPDATE members_search set sid = 665 where uid=31864808");
// MooFastdbUpdate('members_search','uid','31864808');
?>
<html>
<head>
	<meta charset="UTF-8">
	<title>测试页面</title>
	<script type="text/javascript" src="/public/system/js/jquery-1.7.2.min.js"></script>
</head>
<body>
<br>
<label>The First End：</label><span id="sec0"><?php echo $sec0 ?></span>S
<hr>
<label>The Second End：</label><span id="sec1"><?php echo $sec1 ?></span>S
<hr>
<label>The Third End：</label><span id="sec2"><?php echo $sec2 ?></span>S
<hr>
<label>The Fourth End：</label><span id="sec3"><?php echo $sec3 ?></span>S
<script type="text/javascript">
var t0 = <?php echo $sec0 ?>;
var t1 = <?php echo $sec1 ?>;
var t2 = <?php echo $sec2 ?>;
var t3 = <?php echo $sec3 ?>;
$(function(){
	minSec();
	clock();
});
function minSec(){
	$("#sec0").html(t0);
	$("#sec1").html(t1);
	$("#sec2").html(t2);
	$("#sec3").html(t3);
	t0 --;
	t1 --;
	t2 --;
	t3 --;
	setTimeout('minSec()',1000);
}
function clock(){
    var Digital = new Date();
    var year = Digital.getFullYear();
    var month = Digital.getMonth() + 1;
    var day = Digital.getDate();
    var hours = Digital.getHours();
    var minutes = Digital.getMinutes();
    var seconds=Digital.getSeconds();
    // var dn="AM";
    // if(hours>12){
    //     dn="PM";
    //     hours=hours-12;
    // }
    // if(hours==0){
    //     hours=12;
    // }
    //以上两个if让小时显示限制在1~12之间
    if(minutes<=9){
        minutes="0"+minutes;
    }
    if(seconds<=9){
        seconds="0"+seconds;
    }
    myclock = year+"-"+month+"-"+day+" "+hours+":"+minutes+":"+seconds;//+" "+dn2014-6-2 10:32
    document.title = myclock;
    setTimeout("clock()",1000);
}
</script>
</body>
</html>