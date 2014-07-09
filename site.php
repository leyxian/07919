<?php
/*******************************************逻辑层(M)/表现层(V)*****************************************/
function site_index(){
		
	require_once(adminTemplate('site_member_recommend'));
}

//搜索地点查看站点首页
function site_search_index(){
	global $_MooClass,$dbTablePre;
	$province = MooGetGPC('workprovince','integer','P');
	$city = MooGetGPC('workcity','integer','P');
	if(in_array( $province, array(10101201,10101002))){
		//note 修正广东省深圳和广州的区域查询 
		$city = $province;
		$province = 10101000;
	}

	if(empty($province) || empty($city)){
		require_once(adminTemplate('site_member_recommend'));exit;
	}
	if($province == '-1'){
		salert('请选择省市查看');
	}
	//if($city == -1) $city='';
//	var_dump($province);
//	var_dump($city);
	//$userList = array();
	//获取该城市的首页展示

	$user_arr = array();
//	$sql = "SELECT * FROM {$dbTablePre}members_recommend WHERE city='$city' and province = '$province' order by uid desc"; // original file
	$sql = "SELECT uid, city_star, s_cid, sort FROM {$dbTablePre}members_recommend WHERE city='$city' and province = '$province' order by uid desc"; // updated file
	$user_arr = $_MooClass['MooMySQL']->getAll($sql);

	$uid_list = array();
	$user_arr1 = array();
	if($user_arr) {
		foreach($user_arr as $user) {
			$uid_list[] = $user['uid'];
		}
		$allow_uid = implode(',', $uid_list);
		$user_arr1 = $_MooClass['MooMySQL']->getAll("select nickname, birthyear, marriage, gender, s_cid from {$dbTablePre}members_search where uid in({$allow_uid}) order by uid desc");
		$count = count($uid_list);
		for($i = 0; $i < $count; $i++) {
		$user_arr[$i] = array_merge($user_arr[$i], $user_arr1[$i]);
			
		}
	}
	
	//查市城市之星
	//if(!empty($city)){
//		//$sql = "SELECT * FROM {$dbTablePre}members_recommend WHERE city='$city' AND is_lock=1 AND images_ischeck=1 AND showinformation=1 AND city_star>0 order by city_star desc,s_cid asc, pic_num desc";
//		$userList = $_MooClass['MooMySQL']->getAll($sql);
//	}
//	echo '$sql1'.$sql.'<br />';
	//市无城市之星，从省取城市之星
	//if(empty($userList)){
//		$sql="select * from {$dbTablePre}members WHERE province='$province' AND is_lock=1 AND images_ischeck = 1 AND showinformation=1 
//			  order by city_star desc,s_cid asc, pic_num desc LIMIT 12";
//		$userList = $_MooClass['MooMySQL']->getAll($sql);
//		
////		echo '$sq2'.$sql.'<br />';
//	}else{
		//市有城市之星时，如果不够12个，则从全国取钻石会员
		//$userlist_total = count($userList);
//		if($userlist_total<12){
//			//需从全国取几个钻石会员
//			$add_query_sum = 12 - $userlist_total;
//			$user_in= 0;
//			foreach($userList as $user){
//				$user_list[]=$user['uid'];
//			}
//			if(!empty($user_list)){
//				$user_in = implode(',',$user_list);
//			}
//			$user_in = $user_in.',1747188,2154375,1600591,2159633';
//			$sql = "select * from {$dbTablePre}members where uid not in({$user_in}) AND city_star=0 order by s_cid asc, pic_num desc limit $add_query_sum";
//			$userList_add_query_sum = $_MooClass['MooMySQL']->getAll($sql);
//			$userList = array_merge($userList , $userList_add_query_sum);
//			echo '$sq3'.$sql.'<br />';
		//}
	//}
	
	//推荐列表中如果存在，则以推荐列表中的指定的sort替换
//	$sql = "SELECT * FROM {$dbTablePre}members_recommend WHERE province='$province' AND (city=0 OR city='$city')";
//	$recommend_list = $_MooClass['MooMySQL']->getAll($sql);
//	if(!empty($recommend_list)){
//		foreach($recommend_list as $list){
//			$sort = $list['sort']-1;
//			if(isset($userList[$sort])){
//				
//				$sql = "SELECT * FROM {$dbTablePre}members WHERE uid='{$list['uid']}'";
//				$u = $_MooClass['MooMySQL']->getOne($sql);
//				$u['recommend'] = 1;
//				$u['sort'] = $list['sort'];
//				$userList[$sort] = $u;
//			}
//		}
//	}
//
//	$user_arr = $userList;
//	$index_userlist = $comma = '';
//	foreach($user_arr as $user){
//		$index_userlist .= $comma.$user['uid'];
//		$comma = ',';
//	}

	require_once(adminTemplate('site_member_recommend'));
}
/*
//note 首页管理
function site_index(){
    $page = max(1,MooGetGPC('page','integer'));
    $limit = 15;
    $offset = ($page-1)*$limit;
    
    $field = MooGetGPC('field','string','G');
    $order = MooGetGPC('order','string','G');
    $where = MooGetGPC('where','string','G');
    $value = MooGetGPC('value','string','G');
    
    $field = $field ? $field : 'r.dateline';
    $order = $order == '' ? 'desc' : $order;
    
    if( $where == 'workprovince') $where2 = "WHERE m.province = '{$value}'";
	elseif( $where == 'workcity') $where2 = "WHERE m.city = '{$value}'";
	elseif( $where == 'province') $where2 = "WHERE r.province = '{$value}'";
	elseif( $where == 'city') $where2 = "WHERE r.city = '{$value}'";
	elseif( $where == 'gender') $where2 = "WHERE m.gender = '{$value}'";
	
	$sql = "SELECT r.province,r.city,r.dateline,m.uid, m.nickname, m.gender, m.birthyear, m.province workprovince, m.city workcity, m.marriage FROM {$GLOBALS['dbTablePre']}members_recommend r
			LEFT JOIN {$GLOBALS['dbTablePre']}members m
			ON r.uid = m.uid {$where2}
			ORDER BY {$field} {$order} LIMIT {$offset},{$limit}";
	$user_arr = $GLOBALS['_MooClass']['MooMySQL']->getAll($sql);
	
	$total = getcount("members_recommend r LEFT JOIN {$GLOBALS['dbTablePre']}members m ON r.uid = m.uid",$where2);
	$currenturl = "index.php?action=site&h=index&field={$field}&order={$order}&where={$where}&value={$value}";
   	$pages = multipage( $total, $limit, $page, $currenturl );
   	$order = $order == 'desc' ? 'asc' : 'desc';
   	//note 插入日志
	serverlog(1,$GLOBALS['dbTablePre'].'members_recommend',"{$GLOBALS['adminid']}号客服{$GLOBALS['username']}查看首页推荐会员", $GLOBALS['adminid']);
	require_once(adminTemplate('site_member_recommend'));
}*/

//note 添加首页推荐会员
function site_add_recommend_member(){
	$uid = MooGetGPC('uid','integer','P');
	$province = MooGetGPC('province','integer','P');
	$city = MooGetGPC('city','integer','P');
	if(in_array( $province, array(10101201,10101002))){
		//note 修正广东省深圳和广州的区域查询 
		$city = $province;
		$province = 10101000;
	}

	$dateline = time();
	
	$sql = "SELECT COUNT(*) num FROM {$GLOBALS['dbTablePre']}members_recommend WHERE uid = {$uid}";
	$ret = $GLOBALS['_MooClass']['MooMySQL']->getOne($sql,true);
	if( $ret['num'] ){
		salert("该会员已被推荐过");
		exit;
	}
	
	$sql = "INSERT INTO {$GLOBALS['dbTablePre']}members_recommend(uid, province, city, dateline) values({$uid}, {$province}, {$city}, {$dateline})";
	$GLOBALS['_MooClass']['MooMySQL']->query($sql);
	
	//note 删除缓存文件
//	$sql = "SELECT * FROM {$GLOBALS['dbTablePre']}index_cachefile WHERE province='{$province}' OR province=''"; // original file
	$sql = "SELECT provincestarfile, citystarfile, provinceotherfile  FROM {$GLOBALS['dbTablePre']}index_cachefile WHERE province='{$province}' OR province=''"; // updated file
	$cache_file = $GLOBALS['_MooClass']['MooMySQL']->getOne($sql);
	
	if(file_exists('../data/block/'.$cache_file['provincestarfile'])){
		@unlink('../data/block/'.$cache_file['provincestarfile']);
	}
	if(file_exists('../data/block/'.$cache_file['citystarfile'])){
		@unlink('../data/block/'.$cache_file['citystarfile']);
	}
	if(file_exists('../data/block/'.$cache_file['provinceotherfile'])){
		@unlink('../data/block/'.$cache_file['provinceotherfile']);
	}	
	
	$sql = "SELECT COUNT(*) num FROM {$GLOBALS['dbTablePre']}members_recommend";
	$ret = $GLOBALS['_MooClass']['MooMySQL']->getOne($sql);
	if($ret['num']<12){
		$sql = "SELECT * FROM {$GLOBALS['dbTablePre']}index_cachefile";
		$cache_file = $GLOBALS['_MooClass']['MooMySQL']->getAll($sql);
		foreach($cache_file as $file){
			if(file_exists('../data/block/'.$file['provinceotherfile'])){
				@unlink('../data/block/'.$file['provinceotherfile']);
			}	
		}
	}
	
	//note 插入日志
	serverlog(3,$GLOBALS['dbTablePre'].'members_recommend','添加首页推荐会员uid='.$uid, $GLOBALS['adminid'],$uid);
        
	salert('添加成功','index.php?action=site&h=index');
}
//note 删除首页推荐会员
function site_del_recommend_member(){
	$uid = MooGetGPC('uid','integer','G');
	
	$sql = "SELECT * FROM {$GLOBALS['dbTablePre']}members_recommend WHERE uid = {$uid}";
	$ret = $GLOBALS['_MooClass']['MooMySQL']->getOne($sql);
	
	//note 删除缓存文件
//	$sql = "SELECT * FROM {$GLOBALS['dbTablePre']}index_cachefile WHERE province='{$ret['province']}' OR province=''"; // original file
	$sql = "SELECT provincestarfile, citystarfile, provinceotherfile FROM {$GLOBALS['dbTablePre']}index_cachefile WHERE province='{$ret['province']}' OR province=''"; // updated file
	$cache_file = $GLOBALS['_MooClass']['MooMySQL']->getOne($sql);

	if(file_exists('../data/block/'.$cache_file['provincestarfile'])){
		@unlink('../data/block/'.$cache_file['provincestarfile']);
	}
	if(file_exists('../data/block/'.$cache_file['citystarfile'])){
		@unlink('../data/block/'.$cache_file['citystarfile']);
	}
	if(file_exists('../data/block/'.$cache_file['provinceotherfile'])){
		@unlink('../data/block/'.$cache_file['provinceotherfile']);
	}
	
	$sql = "DELETE FROM {$GLOBALS['dbTablePre']}members_recommend WHERE uid = {$uid}";
	$GLOBALS['_MooClass']['MooMySQL']->query($sql);
	
	//note 插入日志
	serverlog(4,$GLOBALS['dbTablePre'].'members_recommend','删除首页推荐会员uid='.$uid, $GLOBALS['adminid'],$uid);
	
	salert('删除成功','index.php?action=site&h=index');
}

/***********************************************控制层(C)*****************************************/
$h=MooGetGPC('h','string')=='' ? 'index' : MooGetGPC('h','string');
//note 动作列表
$hlist = array('index','add_recommend_member','del_recommend_member','search_index');
//note 判断页面是否存在
if(!in_array($h,$hlist)){
    MooMessageAdmin('您要打开的页面不存在','index.php?action=system_admingroup&h=list');
}
//note 判断是否有权限
if(!checkGroup('site',$h)){
  salert('您没有修改此操作的权限');
}

switch($h){
    //note 欢迎界面
    case 'index':
    	site_index();
   	break;
   	//note 添加首页推荐会员
    case 'add_recommend_member':
    	site_add_recommend_member();
    break;
    //note 删除首页推荐会员
    case 'del_recommend_member':
    	site_del_recommend_member();
    break;
    case 'search_index':
    	site_search_index();
    break;
    
}
?>