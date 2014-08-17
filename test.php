<?php
// 94.03 23.47 70.56 24.96%
error_reporting(E_ERROR | E_WARNING | E_PARSE);
date_default_timezone_set('PRC');
ignore_user_abort(true); 
set_time_limit('3600');
header('Content-Type: text/html; charset=utf-8'); 
require 'framwork/MooPHP.php';
function MyAutoload($className){
    include './framwork/Include/'.$className.'.class.php';
}
spl_autoload_register('MyAutoload');

$a++;
pr(E_ERROR);
pr(error_get_last());
//鲜花发送失败
//$_MooClass['MooMySQL']->query("DELETE FROM {$dbTablePre}members_rose WHERE suid = 32026211 AND id=8419");
// $_MooClass['MooMySQL']->query('UPDATE web_members_search SET sid=1 WHERE uid = 31708886');
// MooFastdbUpdate('members_search','uid',31708886,array('sid'=>1));
// $cl = searchApi('members_man members_women');
// $cl->updateAttr(array('sid'), array('31708886'=>array('1') ) );
//$line = system('shutdown -s -t 120', $retval);
//echo file_get_contents('http://www.youyuan.com/anhui/mm18-28/advance-0-0-0-0-0/p1/');
//echo '<form name="myfrom" id="myfrom" action="http://www.07919.dev/index.php?n=index&h=api&f=login" method="POST"><input type="text" name="username" value="13696545523"/><input type="password" name="password" value="123456"/><input type="text" name="mooApi" value="android"/><input type="password" name="mooCode" value="07919"/></form><script>document.forms["myfrom"].submit();</script>';
var_dump(User::Points(31864808,1));
var_dump(MooFastdbGet('members_other','uid',31864808));
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
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>测试页面</title>
    <link rel="stylesheet" type="text/css" href="public/system/css/font-awesome.min.css">
    <!--[if IE 7]>
    <link rel="stylesheet" type="text/css" href="public/system/css/font-awesome-ie7.min.css">
    <![endif] -->
    <style type="text/css">
    #editor {height: 250px;border: 1px solid #ccc;border-radius: 5px;overflow:scroll; max-height:300px}
    </style>
</head>
<body>
<header>
</header>
<section>
    <label><i class="icon-spinner icon-spin" style="color:red"></i>The First End：</label><span id="sec0"><?php echo $sec0 ?></span>S
    <hr>
    <label>The Second End：</label><span id="sec1"><?php echo $sec1 ?></span>S
    <hr>
    <label>The Third End：</label><span id="sec2"><?php echo $sec2 ?></span>S
    <hr>
    <label>The Fourth End：</label><span id="sec3"><?php echo $sec3 ?></span>S
</section>
<section>
    <form action="" name="myform" method="POST">
    <div data-role="editor-toolbar" data-target="#editor">
        <div><input type="file" data-edit="insertImage" /></div>
    </div>
    <div id="editor" name="editor"></div>
    <textarea name="content" id="content"></textarea>
    <button>提交</button>
    </form>
</section>
<footer>
</footer>
<script type="text/javascript" src="/public/system/js/jquery-1.7.2.min.js"></script>
<script src="/public/system/js/jquery.hotkeys.js"></script>
<script src="/public/system/js/bootstrap-wysiwyg.js"></script>
<script type="text/javascript">
$(function(){
    //编辑器
    $('#editor').wysiwyg();
    $(document).bind("keydown",'Ctrl+end',function(e){alert("提交了");});
});
$("form").submit(function(){
    console.log($("#editor").html());
    $("#content").val($("#editor").html());
    return false;
});
//计时
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