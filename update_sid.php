<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
	if($_GET['test']!=123){
		header("Location: http://www.772500.com");
	}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>更新sid</title>
</head>
<body>
<form method='post'>
index:<input type='text' name='index'/><br/>
会员id:<input type='text' name='uid'/><br/>
客服sid:<input type='text' name='sid'/><input type='submit' name='show' value='查看' /><br/>
</form>

<hr/>
<form method='post'>
index:<input type='text' name='index'/><br/>
会员id:<input type='text' name='uid'/><br/>
客服sid:<input type='text' name='sid'/><input type='submit' name='update' value='更新' /><br/>
</form>
<hr/>
<form method='post'>
index:<input type='text' name='index'/><br/>
会员id:<input type='text' name='uid'/><br/>
客服sid:<input type='text' name='sid'/><input type='submit' name='update2' value='也更新增量索引' /><br/>
</form>
</body>
</html>
<?php
error_reporting(0);
require 'config.php';
//note 加载框架
require 'framwork/MooPHP.php';
if (isset($_POST['uid'])){
	echo '<pre>';
	var_dump($_POST);
	echo '</pre>';
	$sp = searchApi($_POST['index']);
	echo $sp->index."</br>";
	if (isset($_POST['update'])){
		$fields = array('sid');
		$values = array(intval($_POST['uid'])=>array(intval($_POST['sid'])));
		$res = $sp->updateAttr($fields,$values);
			echo '<pre>';
	var_dump($res);
	echo '</pre>';
	}elseif(isset($_POST['update2'])){
		$fields = array('sid');
		$values = array(intval($_POST['uid'])=>array(intval($_POST['sid'])));
		$res = $sp->updateAttr2($fields,$values);
		echo '<pre>';
		var_dump($res);
		echo '</pre>';
	}else {
		$arr = array();
		if (!empty($_POST['uid'])) $arr[] = array('@id',intval($_POST['uid']));
		if (!empty($_POST['sid'])) $arr[] = array('sid',intval($_POST['sid']));
		$res = $sp->getResult($arr);
		echo '<pre>';
		var_dump($res['matches']);
		echo '</pre>';
	}
}
?>

