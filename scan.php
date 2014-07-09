<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
require 'config.php';
require 'framwork/MooPHP.php';
//fetchArray insertId numRows getOne getAll query
$db = $_MooClass['MooMySQL'];
$size = 500;
if (isset($_GET['page']) && intval($_GET['page']) > 1) {
	$page = intval($_GET['page']);
} else {
	$page = 1;
}
$end = $page++ * $size;
$start = $end - $size;
$sql = 'select uid, images_ischeck from web_members_search order by uid limit ' . $start . ', ' . $size;
$rs = $db->query($sql);
if (!$rs) {
	echo '处理完成！';
} else {
	while ($info = $db->fetchArray($rs)) {
		if ($info['images_ischeck'] != 0) {
			$uid = $info['uid'];
			$first_dir=substr($uid,-1,1);
			$secend_dir=substr($uid,-2,1);
			$third_dir=substr($uid,-3,1);
			$forth_dir=substr($uid,-4,1);
			$newfile = "data/upload/userimg/".$first_dir."/";
			$newfile .= $secend_dir."/";
			$newfile .= $third_dir."/";
			$newfile .= $forth_dir."/";
			$newfile .= ($uid * 3) . '_mid.jpg';
			if (!is_file($newfile)) {
				$sql = 'update web_members_search set images_ischeck = 0 where uid=' . $uid;
				$db->query($sql);
			}
		}
	}
	$url = './scan.php?page=' . $page;
	//echo '<a href="' . $url . '">next</a>';exit();
	echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
	echo '<script type="text/javascript">';
	echo 'window.location.href="' . $url . '";';
	echo '</script>';
	echo '<noscript>';
	echo '<meta http-equiv="refresh" content="0;url=' . $url . '" />';
	echo '</noscript>';
	echo '正在处理第' . ($start + 1) . '-' . $end . '个用户！';
}