<?php
error_reporting(0);
set_time_limit(0);
ignore_user_abort(true);
define("FROMEWORK",false);
date_default_timezone_set('Asia/Shanghai');

require 'framwork/MooPHP.php';
require 'config_list2.php';
require 'framwork/libraries/ImageCrop.class.php';

if(trim($_GET['pwd']!=='2006313')){
	pr('this is a hello p world!',1);
}
$key = basename($_SERVER['PHP_SELF'],'.php');
$ip = GetIP();
MooWriteFile('data/caiji.txt',"[{$ip}]caiji start>>",'a+');unset($ip);

register_shutdown_function('PageOnShutdown');
function PageOnShutdown(){
	$msg = error_get_last();
	if(!array_empty($msg)){
		foreach($msg as $k=>$v){
			$str.= "[{$k}]=>{$v} ";
		}
		clearstatcache();
		MooWriteFile(dirname(__file__)."/data/caiji.txt",$str,"a+");
		
	}
	MooWriteFile(dirname(__file__)."/data/caiji.txt",'[script shutdown]',"a+");
}
if( !function_exists('error_get_last') ) {
    set_error_handler(
        create_function(
            '$errno,$errstr,$errfile,$errline,$errcontext',
            '
                global $__error_get_last_retval__;
                $__error_get_last_retval__ = array(
                    \'type\'        => $errno,
                    \'message\'        => $errstr,
                    \'file\'        => $errfile,
                    \'line\'        => $errline
                );
                return false;
            '
        )
    );
    function error_get_last() {
        global $__error_get_last_retval__;
        if( !isset($__error_get_last_retval__) ) {
            return null;
        }
        return $__error_get_last_retval__;
    }
}
$status = mc_get($key);
if($status){
	pr($status,1);
}else{
	mc_set($key,'hello world,this is a m testing!',86400);
}unset($status);

if ( ! function_exists( 'exif_imagetype' ) ) {
	function exif_imagetype ( $filename ) {
	    if ( ( list($width, $height, $type, $attr) = getimagesize( $filename ) ) !== false ) {
	        return $type;
	    }
	   return false;
	}
}

global $dbTablePre,$_MooClass;

MooAutoLoad('ImageInfo');
$im = new ImageInfo();


$sql = "select count(*) as c,province,city from {$dbTablePre}members_search where usertype=1 and gender=1 and province>10109999 and city>0 group by province,city having c>0 order by city asc";
$wms = $GLOBALS['_MooClass']['MooMySQL']->getAll($sql);
if($wms)
foreach($wms as $k =>$v){
	$num = $v['c']*2>600?600:$v['c']*2;
	$manNum = $GLOBALS['_MooClass']['MooMySQL']->getOne("select count(*) as c from {$dbTablePre}members_search where usertype=1 and gender=0 and province={$v['province']} and city={$v['city']}");
	if($manNum['c']>=600) continue;
	$num = $num>600-$manNum['c']?600-$manNum['c']:$num;
	$caiji["{$v['province']},{$v['city']}"] = array($num);
}unset($wms,$manNum,$num);
foreach($caiji as $k=>$v){
	$a = explode(',',$k);
	$sql = "select max(birthyear) as maxb,min(birthyear) as minb from {$dbTablePre}members_search where usertype=1 and gender=1 and province = {$a[0]} and city = {$a[1]} and birthyear>1970";
	$birth = $GLOBALS['_MooClass']['MooMySQL']->getOne($sql);
	$url = 'http://search.zhenai.com/search/getfastmdata.jsps?condition=2&photo=1&agebegin='.(date('Y')-$birth['maxb']).'&ageend='.(date('Y')-$birth['maxb']).'&workcityprovince='.$a[0].'&workcitycity='.$a[1];
	foreach($v as $ke=>$va){
		for($i=1;$i<=ceil($va/10);$i++){
			$urlArr[] = $url.'&gender='.$ke.'&currentpage='.$i;
		}
	}
}unset($caiji,$sql,$birth);
$selSql = "select nickname from {$dbTablePre}members_search where usertype=1 and regdate>1364745600 and nickname!='' ";
$nickData[] = $GLOBALS['_MooClass']['MooMySQL']->getAll($selSql." and gender=0");
$nickData[] = $GLOBALS['_MooClass']['MooMySQL']->getAll($selSql." and gender=1");
unset($selSql);

$url = "http://profile.zhenai.com/login/loginactionindex.jsps";
$logData['loginInfo'] = '13696545523';
$logData['password'] = '120120';
$logData['whereLogin'] = 'login_page';
$logData['loginmode'] = 2;
$logData['rememberpassword'] = 1;
$cookie_file = tempnam('/temp','cookie');

$ch= curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $logData);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
curl_setopt($ch, CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11");
curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:211.71.95.158', 'CLIENT-IP:211.71.95.158'));
curl_setopt($ch, CURLOPT_PROXY, $proxyIp);
curl_setopt ($ch, CURLOPT_REFERER, "http://profile.zhenai.com/login/login.jsp");
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
$info = curl_exec($ch);
curl_close($ch);unset($logData);
foreach($urlArr as $k=>$v){
	//脚本控制器
	$configs = parse_ini_file('script_config.ini',true);
	if(empty($configs['scripts']['zacollect_encrypt'])){
		MooWriteFile('data/caiji.txt',"caiji stop,",'a+');
		mc_unset($key);
		unlink($cookie_file);
		exit;
	}
	$arr = parse_url($v);
	parse_str($arr['query']);
	$ch = curl_init($v);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:211.71.95.158', 'CLIENT-IP:211.71.95.158'));
	curl_setopt($ch, CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11");
	curl_setopt($ch, CURLOPT_PROXY, $proxyIp);
	curl_setopt ($ch, CURLOPT_REFERER, "http://profile.zhenai.com");
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
	$contents = curl_exec($ch);
	if(empty($contents)){
		MooWriteFile('data/caiji.txt',"{$v}['read false'],",'a+');
		continue;
	}
	if(curl_errno($ch)){
		curl_close($ch);
		MooWriteFile('data/caiji.txt',"{$v}['load false'],",'a+');
		continue;
	}
	curl_close($ch);
	preg_match_all('/http:\/\/([a-z\.]+\/)+getmemberdata.jsps\?memberid=[0-9]+/',$contents,$list,PREG_SET_ORDER);unset($contents);
	foreach($list as $v){
		$lisArr[] = $v[0];
	}unset($list);
	if(array_empty($lisArr)){
		MooWriteFile('data/caiji.txt',"no lisArr,",'a+');
		continue;
	}
	$lisArr = array_unique($lisArr);
	foreach($lisArr as $k=>$v){
		//脚本控制器
		$configs = parse_ini_file('script_config.ini',true);
		if(empty($configs['scripts']['zacollect_encrypt'])){
			MooWriteFile('data/caiji.txt',"caiji stop,",'a+');
			mc_unset($key);
			unlink($cookie_file);
			exit;
		}
		
		//判断是否存在
		$res = $GLOBALS['_MooClass']['MooMySQL']->getOne("select uid from {$dbTablePre}members_base where source = '{$v}'");
		if(!array_empty($res)){
			$GLOBALS['_MooClass']['MooMySQL']->close();
			preg_match('/[0-9]+/',$v,$str);
			MooWriteFile('data/caiji.txt',"{$str[0]}[having],",'a+');
			continue;
		}
		
		$time = time();
		$ch = curl_init($v);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:211.71.95.158', 'CLIENT-IP:211.71.95.158'));
		curl_setopt($ch, CURLOPT_PROXY, $proxyIp);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
		$contents = curl_exec($ch);
		if(curl_errno($ch)){
			curl_close($ch);
			MooWriteFile('data/caiji.txt',"{$v}	,",'a+');unset($msg);
			continue;
		}
		curl_close($ch);
		//无形象照跳过
		preg_match('/objDefalutPhoto(.*)/',$contents,$str);
		preg_match('/http:\/\/(.*)(jpg|gif|png|jpeg|JPG|GIF|PNG|JPEG)/',$str[0],$str);
		if(empty($str[0]) || !exif_imagetype($str[0])){
			$GLOBALS['_MooClass']['MooMySQL']->close();
			MooWriteFile('data/caiji.txt',"{$v}['no pic'],",'a+');
			continue;
		}
		$mbData['source'] = $v;
		//职业
		foreach($occupationList as $k=>$v){
			if(preg_match('/<dd>'.iconv('utf-8','gbk',$k).'/',$contents,$str))
				$data['occupation'] = $v;
		}
		//单位
		foreach($corptypeList as $k=>$v){
			if(preg_match('/<dd>'.iconv('utf-8','gbk',$v).'/',$contents,$str))
				$data['corptype'] = $k;
		}
		//购车
		foreach($vehicleList as $k=>$v){
			if(preg_match('/<dd>'.iconv('utf-8','gbk',$v).'/',$contents,$str))
				$data['vehicle'] = $k;
		}
		//宗教
		foreach($religionList as $k=>$v){
			if(preg_match('/<dd>'.iconv('utf-8','gbk',$k).'/',$contents,$str))
				$data['religion'] = $v;
		}
		//兄弟姐妹
		preg_match('/'.iconv('utf-8','gbk','兄弟姐妹：').'<\/strong>[^<]*<\/dt>[^<]*<dd>[0-9]+/',$contents,$str);
		preg_match('/[0-9]+/',$str[0],$str);
		$data['family'] = $str[0];
		//吸烟
		foreach($smokingList as $k=>$v){
			if(preg_match('/<dd>'.iconv('utf-8','gbk',$k).'/',$contents,$str))
				$data['smoking'] = $v;
		}
		//喝酒
		foreach($drinkingList as $k=>$v){
			if(preg_match('/<dd>'.iconv('utf-8','gbk',$k).'/',$contents,$str))
				$data['drinking'] = $v;
		}
		//想要孩子
		foreach($wantchildrenList as $k=>$v){
			if(preg_match('/<dd>'.iconv('utf-8','gbk',$k).'/',$contents,$str))
				$data['wantchildren'] = $v;
		}
		//昵称
		preg_match('/<li[^>]+main[^>]+><h1><strong>[^>]+/',$contents,$str);
		preg_match('/<strong>(.*)<\//',$str[0],$str);
		$data['nickname'] = iconv('GBK','utf-8',$str[1]);
		if(preg_match('/会员[0-9]+/',$data['nickname'])){
			$data['nickname'] = $nickData[$gender][rand(0,(count($nickData[$gender])-1))]['nickname'];
		}
		//用户名
		$data['username'] = getUsername();
		$data['telphone'] = 0;
		$data['password'] = md5('qingyuan07919');
		$data['truename'] = '';
		//性别
		$data['gender'] = $gender;
		//出生年份
		preg_match('/[0-9]+'.iconv('UTF-8','GBK','岁').'/',$contents,$str);
		preg_match('/[0-9]+/',$str[0],$str);
		if(is_numeric($str[0]) && $str[0]>0)
			$data['birthyear'] = date('Y',strtotime('-'.$str[0].'year'));
		//地区
		preg_match('/'.iconv('UTF-8','GBK','住在').'[^<]*<strong[^>]+>[^<]+/',$contents,$str);
		$str = str_replace('住在','',iconv('GBK','UTF-8',$str[0]));
		$str = preg_replace('/<strong[^>]+>/','',$str);
		$pc = getProivceAndCity($str,$provinceCityList);
		$data['province'] = getProvice($pc['provice']);
		$data['city'] = getCity($pc['city']);
		//婚姻状况
		if(strpos($contents,iconv('UTF-8','GBK','离异'))){
			$data['marriage'] = 3;
		}elseif(strpos($contents,iconv('UTF-8','GBK','丧偶'))){
			$data['marriage'] = 4;
		}else{
			$data['marriage'] = 1;
		}
		//学历
		if(strpos($contents,iconv('UTF-8','GBK','高中及以下'))!==false){
			$data['education'] = 3;
		}elseif(strpos($contents,iconv('UTF-8','GBK','中专'))!==false){
			$data['education'] = 2;
		}elseif(strpos($contents,iconv('UTF-8','GBK','大学本科'))!==false){
			$data['education'] = 5;
		}elseif(strpos($contents,iconv('UTF-8','GBK','硕士'))!==false){
			$data['education'] = 6;
		}elseif(strpos($contents,iconv('UTF-8','GBK','博士'))!==false){
			$data['education'] = 7;
		}else{
			$data['education'] = 4;
		}
		//月收入
		preg_match('/[0-9]+-[0-9]+'.iconv('UTF-8','GBK','元').'/',$contents,$str);
		$str[0] = empty($str[0])?'':iconv('gbk','utf-8',$str[0]);
		if($str[0]=='1000以下'){
			$data['salary'] = 1;	
		}elseif($str[0]=='1001-2000元'){
			$data['salary'] = 2;
		}elseif($str[0]=='3001-5000元'){
			$data['salary'] = 4;
		}elseif($str[0]=='5001-8000元'){
			$data['salary'] = 5;
		}elseif($str[0]=='8001-10000元'){
			$data['salary'] = 6;
		}elseif($str[0]=='10001-20000元'){
			$data['salary'] = 7;
		}elseif($str[0]=='20001-50000元'){
			$data['salary'] = 8;
		}elseif($str[0]=='50000以上元'){
			$data['salary'] = 9;
		}else{
			$data['salary'] = 3;
		}
		//住房条件
		if(strpos($contents,iconv('UTF-8','GBK','和父母家人同住'))!==false){
			$data['house'] = 1;
		}elseif(strpos($contents,iconv('UTF-8','GBK','自有物业'))!==false){
			$data['house'] = 2;
		}elseif(strpos($contents,iconv('UTF-8','GBK','婚后有房'))!==false){
			$data['house'] = 4;
		}else{
			$data['house'] = 3;
		}
		//孩子
		if(strpos($contents,iconv('UTF-8','GBK','有孩子，我们住在一起'))!==false){
			$data['children'] = 3;
		}elseif(strpos($contents,iconv('UTF-8','GBK','有孩子，我们偶尔一起住'))!==false){
			$data['children'] = 4;
		}elseif(strpos($contents,iconv('UTF-8','GBK','有孩子，但不在身边'))!==false){
			$data['children'] = 5;
		}else{
			$data['children'] = 1;
		}
		//身高
		preg_match('/[0-9]+'.iconv('UTF-8','GBK','厘米').'/',$contents,$str);
		preg_match('/[0-9]+/',$str[0],$str);
		$data['height'] = $str[0];
		//体重
		preg_match('/[0-9]+'.iconv('UTF-8','GBK','公斤').'/',$contents,$str);
		preg_match('/[0-9]+/',$str[0],$str);
		$data['weight'] = $str[0];
		//体形
		if(strpos($contents,iconv('UTF-8','GBK','瘦长'))!==false){
			$data['body'] = 2;
		}elseif(strpos($contents,iconv('UTF-8','GBK','苗条'))!==false){
			$data['body'] = 6;
		}elseif(strpos($contents,iconv('UTF-8','GBK','高大美丽'))!==false){
			$data['body'] = 7;
		}elseif(strpos($contents,iconv('UTF-8','GBK','丰满'))!==false){
			$data['body'] = 8;
		}elseif(strpos($contents,iconv('UTF-8','GBK','富线条美'))!==false){
			$data['body'] = 9;
		}elseif(strpos($contents,iconv('UTF-8','GBK','皮肤白皙'))!==false){
			$data['body'] = 11;
		}elseif(strpos($contents,iconv('UTF-8','GBK','长发飘飘'))!==false){
			$data['body'] = 12;
		}elseif(strpos($contents,iconv('UTF-8','GBK','时尚卷发'))!==false){
			$data['body'] = 13;
		}elseif(strpos($contents,iconv('UTF-8','GBK','干练短发'))!==false){
			$data['body'] = 14;
		}else{
			$data['body'] = 1;
		}
		//生肖
		if(strpos($contents,iconv('UTF-8','GBK','<dd>鼠</dd>'))!==false){
			$data['animalyear'] = 1;
		}elseif(strpos($contents,iconv('UTF-8','GBK','<dd>牛</dd>'))!==false){
			$data['animalyear'] = 2;
		}elseif(strpos($contents,iconv('UTF-8','GBK','<dd>虎</dd>'))!==false){
			$data['animalyear'] = 3;
		}elseif(strpos($contents,iconv('UTF-8','GBK','<dd>兔</dd>'))!==false){
			$data['animalyear'] = 4;
		}elseif(strpos($contents,iconv('UTF-8','GBK','<dd>龙</dd>'))!==false){
			$data['animalyear'] = 5;
		}elseif(strpos($contents,iconv('UTF-8','GBK','<dd>蛇</dd>'))!==false){
			$data['animalyear'] = 6;
		}elseif(strpos($contents,iconv('UTF-8','GBK','<dd>马</dd>'))!==false){
			$data['animalyear'] = 7;
		}elseif(strpos($contents,iconv('UTF-8','GBK','<dd>羊</dd>'))!==false){
			$data['animalyear'] = 8;
		}elseif(strpos($contents,iconv('UTF-8','GBK','<dd>猴</dd>'))!==false){
			$data['animalyear'] = 9;
		}elseif(strpos($contents,iconv('UTF-8','GBK','<dd>鸡</dd>'))!==false){
			$data['animalyear'] = 10;
		}elseif(strpos($contents,iconv('UTF-8','GBK','<dd>狗</dd>'))!==false){
			$data['animalyear'] = 11;
		}elseif(strpos($contents,iconv('UTF-8','GBK','<dd>猪</dd>'))!==false){
			$data['animalyear'] = 12;
		}
		//constellation
		if(strpos($contents,iconv('UTF-8','GBK','牡羊座(03.21-04.20)'))!==false){
			$data['constellation'] = 1;
			if($data['birthyear']){
				$mbData['birth'] = rand(strtotime($data['birthyear'].'-03-21'),strtotime($data['birthyear'].'-04-20'));
			}
		}elseif(strpos($contents,iconv('UTF-8','GBK','金牛座(04.21-05.20)'))!==false){
			$data['constellation'] = 2;
			if($data['birthyear']){
				$mbData['birth'] = rand(strtotime($data['birthyear'].'-04-21'),strtotime($data['birthyear'].'-05-20'));
			}
		}elseif(strpos($contents,iconv('UTF-8','GBK','双子座(05.21-06.21)'))!==false){
			$data['constellation'] = 3;
			if($data['birthyear']){
				$mbData['birth'] = rand(strtotime($data['birthyear'].'-05-21'),strtotime($data['birthyear'].'-06-21'));
			}
		}elseif(strpos($contents,iconv('UTF-8','GBK','巨蟹座(06.22-07.22)'))!==false){
			$data['constellation'] = 4;
			if($data['birthyear']){
				$mbData['birth'] = rand(strtotime($data['birthyear'].'-06-22'),strtotime($data['birthyear'].'-07-22'));
			}
		}elseif(strpos($contents,iconv('UTF-8','GBK','狮子座(07.23-08.22)'))!==false){
			$data['constellation'] = 5;
			if($data['birthyear']){
				$mbData['birth'] = rand(strtotime($data['birthyear'].'-07-23'),strtotime($data['birthyear'].'-07-22'));
			}
		}elseif(strpos($contents,iconv('UTF-8','GBK','处女座(08.23-09.22)'))!==false){
			$data['constellation'] = 6;
			if($data['birthyear']){
				$mbData['birth'] = rand(strtotime($data['birthyear'].'-08-23'),strtotime($data['birthyear'].'-09-22'));
			}
		}elseif(strpos($contents,iconv('UTF-8','GBK','天秤座(09.23-10.22)'))!==false){
			$data['constellation'] = 7;
			if($data['birthyear']){
				$mbData['birth'] = rand(strtotime($data['birthyear'].'-09-23'),strtotime($data['birthyear'].'-10-22'));
			}
		}elseif(strpos($contents,iconv('UTF-8','GBK','天蝎座(10.23-11.21)'))!==false){
			$data['constellation'] = 8;
			if($data['birthyear']){
				$mbData['birth'] = rand(strtotime($data['birthyear'].'-10-23'),strtotime($data['birthyear'].'-11-21'));
			}
		}elseif(strpos($contents,iconv('UTF-8','GBK','射手座(11.22-12.21)'))!==false){
			$data['constellation'] = 9;
			if($data['birthyear']){
				$mbData['birth'] = rand(strtotime($data['birthyear'].'-11-22'),strtotime($data['birthyear'].'-12-21'));
			}
		}elseif(strpos($contents,iconv('UTF-8','GBK','魔羯座(12.22-01.19)'))!==false){
			$data['constellation'] = 10;
			if($data['birthyear']){
				$mbData['birth'] = rand(strtotime($data['birthyear'].'-12-22'),strtotime($data['birthyear'].'-01-19'));
			}
		}elseif(strpos($contents,iconv('UTF-8','GBK','水瓶座(01.20-02.19)'))!==false){
			$data['constellation'] = 11;
			if($data['birthyear']){
				$mbData['birth'] = rand(strtotime($data['birthyear'].'-01-20'),strtotime($data['birthyear'].'-02-19'));
			}
		}elseif(strpos($contents,iconv('UTF-8','GBK','双鱼座(02.20-03.20)'))!==false){
			$data['constellation'] = 12;
			if($data['birthyear']){
				$mbData['birth'] = rand(strtotime($data['birthyear'].'-02-20'),strtotime($data['birthyear'].'-03-20'));
			}
		}
		//bloodtype
		if(strpos($contents,iconv('UTF-8','GBK','<dd>A型</dd>'))!==false){
			$data['bloodtype'] = 1;
		}elseif(strpos($contents,iconv('UTF-8','GBK','<dd>B型</dd>'))!==false){
			$data['bloodtype'] = 2;
		}elseif(strpos($contents,iconv('UTF-8','GBK','<dd>AB型</dd>'))!==false){
			$data['bloodtype'] = 3;
		}elseif(strpos($contents,iconv('UTF-8','GBK','<dd>O型</dd>'))!==false){
			$data['bloodtype'] = 4;
		}
		//籍贯
		preg_match('/'.iconv('UTF-8','GBK','籍贯：').'(.*)/',$contents,$str);
		preg_match('/title="(.*)"/',$str[0],$str);
		if($str[1])$str[1] = iconv('GBK','utf-8',$str[1]);
		$pc = getProivceAndCity($str[1],$provinceCityList);
		$data['hometownprovince'] = getProvice($pc['provice']);
		$data['hometowncity'] = getCity($pc['city']);
		$data['regdate'] = time();
		$data['updatetime'] = time();

		//民族
		$mz = array("0,未填写","1,汉族","2,藏族","3,朝鲜族","4,蒙古族","5,回族","6,满族","7,维吾尔族","8,壮族","9,彝族","10,苗族","11,侗族","12,瑶族","13,白族","14,布依族","15,傣族","16,京族","17,黎族","18,羌族","19,怒族","20,佤族","21,水族","22,畲族","23,土族","24,阿昌族","25,哈尼族","26,高山族","27,景颇族","28,珞巴族","29,锡伯族","30,德昂(崩龙)族","31,保安族","32,基诺族","33,门巴族","34,毛南族","35,赫哲族","36,裕固族","37,撒拉族","38,独龙族","39,普米族","40,仫佬族","41,仡佬族","42,东乡族","43,拉祜族","44,土家族","45,纳西族","46,傈僳族","47,布朗族","48,哈萨克族","49,达斡尔族","50,鄂伦春族","51,鄂温克族","52,俄罗斯族","53,塔塔尔族","54,塔吉克族","55,柯尔克孜族","56,乌兹别克族","57,国外");
		foreach($mz as $k=>$v){
			if($k>0){
				$a = split(',',$v);
				if(strpos($contents,iconv('UTF-8','GBK','<dd>'.$a['1'].'</dd>'))!==false){
					$data['nation'] = $a[0];
				}
			}
		}
		$data['usertype'] = 3;
		$data['sid'] = 1;

		//数据不全
		if(empty($data['province'])||empty($data['birthyear'])) continue;
		
		$fields = implode(',',array_keys($data));
		$values = '';
		foreach(array_values($data) as $k=>$v){
			$values.= "'".$v."',";
		}
		$values = substr($values,0,-1);
		//members_search
		$insert_sql = "insert into {$dbTablePre}members_search ({$fields}) values ({$values})";unset($values);
		$GLOBALS['_MooClass']['MooMySQL']->query($insert_sql);
		$uid = $GLOBALS['_MooClass']['MooMySQL']->insertId();
		if(empty($uid)) continue;
		
		//生成随机IP地址  //start
		$ip2id= round(rand(600000, 2550000) / 10000); //第一种方法，直接生成 
		$ip3id= round(rand(600000, 2550000) / 10000); 
		$ip4id= round(rand(600000, 2550000) / 10000); 
		//下面是第二种方法，在以下数据中随机抽取 
		$arr_1 = array("218","218","66","66","218","218","60","60","202","204","66","66","66","59","61","60","222","221","66","59","60","60","66","218","218","62","63","64","66","66","122","211"); 
		$randarr= mt_rand(0,count($arr_1)-1); 
		$ip1id = $arr_1[$randarr];
		$ip = $ip1id.".".$ip2id.".".$ip3id.".".$ip4id;
		unset($ip2id);unset($ip3id);unset($ip4id);unset($arr_1);unset($randarr);unset($ip1id);
		//end
		
		$mbData['uid'] = $uid;
		//$mbData['nature'] = $uid;
		$mbData['callno'] = 0;
		$mbData['regip'] = $ip;
		//形象照		
		//preg_match('/<li[^>]*list-item[^>]*>[^<]*<img[^>]+>/',$contents,$str);
		//preg_match('/rel="[^"]+"/',$str[0],$str);
		preg_match('/objDefalutPhoto(.*)/',$contents,$str);
		preg_match('/http:\/\/(.*)(jpg|gif|png|jpeg|JPG|GIF|PNG|JPEG)/',$str[0],$str);
		if($str[0] && @exif_imagetype($str[0])){
			$photo = getPhoto($uid,'big');
			MooMakeDir(dirname($photo));
			$tmp_url = str_replace('_3', '_2', $str[0]);
			$str[0] = @exif_imagetype($tmp_url)?$tmp_url:$str[0];unset($tmp_url);
			@copy($str[0],$photo);
			cropImg($photo,$photo);
			$ic=new ImageCrop($photo,$photo);
			$ic->Crop(171,212,2);
			$ic->SaveImage();
			$ic->destory();unset($ic);
			$GLOBALS['_MooClass']['MooMySQL']->query("update {$dbTablePre}members_search set images_ischeck=1 where uid={$uid}");
			$mbData['mainimg'] = $photo;
		}
		if($data['province'])
			$mbData['currentprovince'] = $data['province'];
		if($data['city'])
			$mbData['currentcity'] = $data['city'];
		if($data['province'] && $data['city']){
			$fp = 'a:3:{i:0;a:1:{i:'.$data['province'].';s:8:"'.$data['city'].'";}i:1;a:1:{i:0;s:1:"0";}i:2;a:1:{i:0;s:1:"0";}}';
			$mbData['friendprovince'] = $fp;
		}
		$mb_fields = implode(',',array_keys($mbData));
		$mb_values='';
		foreach($mbData as $v){
			$mb_values.= "'".$v."',";
		}unset($mbData);
		$mb_values = substr($mb_values,0,-1);
		$mb_insert ="insert into {$dbTablePre}members_base ({$mb_fields}) values ({$mb_values})";
		if(!$GLOBALS['_MooClass']['MooMySQL']->getOne("select uid from {$dbTablePre}members_base where uid={$uid}"))
			$GLOBALS['_MooClass']['MooMySQL']->query($mb_insert);
		//web_members_choice 择偶表
		//理想对象
		$mcData['uid'] = $uid;
		$mcData['gender'] = $gender==1?0:1;
		preg_match('/<div class="tricol"[^>]*>[^<]*<ul class="clearfix">[^<]*(<li[^>]*>[^<]*<dt>(.*)<\/dt>[^<]*<dd[^>]*>[^<]*<\/dd><\/li>[^<]*)+/',$contents,$friendStr);
		preg_match('/[0-9]+&nbsp;-&nbsp;[0-9]+'.iconv('utf-8','GBK','岁').'/',$friendStr[0],$str);
		if($str[0]){
			$mcArr = explode('-',$str[0]);
			preg_match('/[0-9]+/',$mcArr[0],$str);
			$mcData['age1'] = $str[0];
			preg_match('/[0-9]+/',$mcArr[1],$str);
			$mcData['age2'] = $str[0];
		}	
		preg_match('/[0-9]+&nbsp;-&nbsp;[0-9]+'.iconv('utf-8','GBK','厘米').'/',$friendStr[0],$str);
		if($str[0]){
			$mcArr = explode('-',$str[0]);
			preg_match('/[0-9]+/',$mcArr[0],$str);
			$mcData['height1'] = $str[0];
			preg_match('/[0-9]+/',$mcArr[1],$str);
			$mcData['height2'] = $str[0];
		}
		foreach($bodyList as $k=>$v){
			if(preg_match('/<dd>'.iconv('utf-8','gbk',$v).'/',$friendStr[0],$str))
				$mcData['body'] = $k;
		}
		$mcData['hasphoto']=1;
		foreach($marriageList as $k=>$v){
			if(strpos($friendStr[0],iconv('UTF-8','GBK',$v))!==false)
				$mcData['marriage'] = $k;
		}
		foreach($educationList as $k=>$v){
			if(strpos($friendStr[0],iconv('UTF-8','GBK',$v))!==false)
				$mcData['education'] = $k;
		}
		foreach($occupationList as $k=>$v){
			if(preg_match('/<dd>'.iconv('utf-8','gbk',$k).'/',$friendStr[0],$str))
				$mcData['occupation'] = $v;
		}
		preg_match('/'.iconv('UTF-8','GBK','工作地区：').'(.*)/',$friendStr[0],$str);
		preg_match('/title="(.*)"/',$str[0],$str);
		$pc = getProivceAndCity($str[1],$provinceCityList);
		$mcData['workprovince'] = getProvice($pc['provice']);
		$mcData['workcity'] = getCity($pc['city']);
		$mcData['smoking'] = rand(1,10)>9?1:0;
		$mcData['drinking'] = rand(1,10)>9?1:0;
		foreach($wantchildrenList as $k=>$v){
			if(preg_match('/<dd>'.iconv('utf-8','gbk',$k).'/',$friendStr[0],$str))
				$mcData['wantchildren'] = $v;
		}
		
		foreach($salaryList as $k=>$v){
			if(strpos($v,'-')!==false){
				$a = explode('-',$v);
				$v = ($a[0]-1).'&nbsp;-&nbsp;'.$a[1];
			}
			if(preg_match('/<dd>'.iconv('utf-8','gbk',$v).'/',$friendStr[0],$str))
				$mcData['salary'] = $k;
		}
		$mc_values = '';
		foreach($mcData as $v){
			$mc_values.= "'".$v."',";
		}
		$mc_values = substr($mc_values,0,-1);
		$mc_fields = implode(',',array_keys($mcData));
		$mc_insert = "insert into {$dbTablePre}members_choice ({$mc_fields}) values ({$mc_values})";
		if(!$GLOBALS['_MooClass']['MooMySQL']->getOne("select uid from {$dbTablePre}members_choice where uid={$uid}"))
		$GLOBALS['_MooClass']['MooMySQL']->query($mc_insert);unset($mcData);
		
		//web_members_introduce 独白
		preg_match('/<li[^>]+folded(\sunfolded)?[^>]+>[^<]+</',$contents,$str);
		$introduce = strip_tags($str[0]);
		$introduce = empty($introduce)?'':iconv('GBK','UTF-8',$introduce);
		$introduce_pass = empty($introduce)?0:1;
		if(!$GLOBALS['_MooClass']['MooMySQL']->getOne("select uid from {$dbTablePre}members_introduce where uid={$uid}"))
		$GLOBALS['_MooClass']['MooMySQL']->query("insert into {$dbTablePre}members_introduce (uid,introduce,introduce_check,introduce_pass) values ({$uid},'{$introduce}',1,{$introduce_pass})");
		//web_member_admininfo
		if(!$GLOBALS['_MooClass']['MooMySQL']->getOne("select uid from {$dbTablePre}member_admininfo where uid={$uid}"))
		$GLOBALS['_MooClass']['MooMySQL']->query("insert into {$dbTablePre}member_admininfo (uid,real_lastvisit,finally_ip) values ({$uid},{$time},{$ip})");
		//web_members_login
		if(!$GLOBALS['_MooClass']['MooMySQL']->getOne("select uid from {$dbTablePre}members_login where uid={$uid}"))
		$GLOBALS['_MooClass']['MooMySQL']->query("insert into {$dbTablePre}members_login (uid,lastip,lastvisit,last_login_time,login_meb) values ({$uid},'{$ip}',{$time},{$time},1)");
		//用户相册
		preg_match_all('/<li>\s*<p>\s*<img[^>]+>/',$contents,$str);
		if(!array_empty($str[0])){
			$i=0;
			foreach($str[0] as $v){
				preg_match('/data-big-img="[^"]+"/',$v,$str);
				preg_match('/http:\/\/.+\.(jpg|gif|png|jpeg|JPG|GIF|PNG|JPEG)/',$str[0],$str);
				$photos = PIC_PATH.'/'.date("Y")."/".date("m")."/".date("d").'/orgin/';
				if($str[0] && exif_imagetype($str[0])){
					MooMakeDir($photos);
					$pathParts = pathinfo($str[0]);
					$photosname= $pathParts['basename'];
					$date = date('Y/m/d');
					$imgurl = $photos.$photosname;
					if(!@copy($str[0],$imgurl)){
						MooWriteFile('data/caiji.txt',"copy:{$str[0]}[false],",'a+');
						continue;
					}else{
						cropImg($imgurl,$imgurl);
					}
					$i++;
					$GLOBALS['_MooClass']['MooMySQL']->query("insert into {$dbTablePre}pic (uid,imgurl,pic_date,pic_name,syscheck) values ({$uid},'{$imgurl}','{$date}','{$photosname}',1)");
				}unset($v);
			}
			$GLOBALS['_MooClass']['MooMySQL']->query("update {$dbTablePre}members_search set pic_num=".$i." where uid={$uid}");unset($i);
		}
		@$im->resetPicNew($uid,'public/system/images/logo_original.png');
		@$im->createPhoto($uid,null,'public/system/images/logo2.png');
		//诚信认证
		$cData['uid'] = $uid;
		$cData['email'] = 'yes';
		if(rand(0,9)>=5){
			$cData['identity_check'] = 3;
		}
		$cData['telphone'] = '12345678900';
		$res = $GLOBALS['_MooClass']['MooMySQL']->getOne("select uid from {$dbTablePre}certification where uid={$uid}");
		if(array_empty($res)){
			$GLOBALS['_MooClass']['MooMySQL']->query("insert into {$dbTablePre}certification (".implode(',',array_keys($cData)).") values ('".implode("','",array_values($cData))."')");
		}
		@reset_integrity($uid);unset($cData);
		//日志
		MooWriteFile('data/caiji.txt',$uid.",",'a+');
		if($_SERVER['HTTP_HOST']!='www.07919.com') {mc_unset($key);pr($uid,1);}
		unset($time,$contents,$str,$data,$mbData,$v,$pc,$mz,$fields,$values,$ip,$res,$insert_sql,$imgurl,$photosname,$pathParts,$photos,$date,$uid,$introduce,$introduce_pass,$mc_values,$mc_fields,$mc_insert,$mb_values,$mb_fields);
		$GLOBALS['_MooClass']['MooMySQL']->close();
	}unset($lisArr,$gender);
}
mc_unset($key);
unset($urlArr,$nickData,$im,$key,$dbTablePre,$_MooClass);
unlink($cookie_file);
MooWriteFile('data/caiji.txt',"caiji end,",'a+');
exit();

//剪裁水印
function cropImg($src,$dst){
	if(!is_file($src)) return null;
	list($width,$height,$type) = getimagesize($src);
	$oldheight = $height-36;
	$new = imagecreatetruecolor($width, $height);
	switch($type){
		case IMAGETYPE_JPEG:
			$old = imagecreatefromjpeg($src);
			break;
		case IMAGETYPE_PNG :
			$old = imagecreatefrompng($src);
			break;
		case IMAGETYPE_GIF :
			$old = imagecreatefromgif($src);
			break;
	}
	imagecopyresized($new,$old,0,0,0,0,$width,$height,$width,$oldheight);
	switch($type){
		case IMAGETYPE_JPEG:
			imagejpeg($new,$dst);
			break;
		case IMAGETYPE_PNG :
			imagepng($src,$dst);
			break;
		case IMAGETYPE_GIF :
			imagegif($src,$dst);
			break;
	}
	imagedestroy($new);
	imagedestroy($old);
}

function str_replace_occurance($search, $replace, $subject, $occurance) {
   $pos = 0;
   for ($i = 0; $i <= $occurance; $i++) {
       $pos = strpos($subject, $search, $pos);
   }
   return substr_replace($subject, $replace, $pos, strlen($search));
}

function getUsername(){
	$len = rand(1,6);
	$str = "abcdefghijklmnopqrstuvwxyz1234567890";
	$proName = 'zha';
	for($i=0;$i<$len;$i++){
		$proName.= substr($str,rand(0,35),1);
	}
	$proName.= rand(0,9999);
	$mail = array('qq.com','foxmail.com','sina.com','sina.cn','126.com','163.com','yahoo.com','21cbh.com','gmail.com','hotmail.com');
	$username = $proName.'@'.$mail[rand(0,9)];
	return $username;
}

function getPhoto($uid, $style = "mid", $jpg="") {
		$dir = FROMEWORK===true?'../':'';
		$first_dir=substr($uid,-1,1);
		$secend_dir=substr($uid,-2,1);
		$third_dir=substr($uid,-3,1);
		$forth_dir=substr($uid,-4,1);
		$new_filedir=$dir."data/upload/userimg/".$first_dir."/".$secend_dir."/".$third_dir."/".$forth_dir."/";
	
		$uidmd5 = $uid * 3;
			$imgurl ='sfsdf';
		if($jpg != ""){
			$imgurl = $new_filedir . $uidmd5 . '_' . $style . '.' . $jpg;
		}else{
			$imgurl = $new_filedir . $uidmd5 . '_' . $style . '.jpg';
		}
		return $imgurl;
	}
function getProivceAndCity($str,$provinceCityList){
	$provices = array_keys($provinceCityList);
	foreach($provices as $v){
		if(strpos($str,$v)!==false){
			$data['provice'] = $v;
		}
	}
	if($data['provice']){
		$city = $provinceCityList[$data['provice']];
	}
	$str = str_replace_occurance($data['provice'],'',$str,1);
	if($city)
	foreach($city as $v){
		if(strpos($str,$v)!==false){
			if($v=='襄阳') $v='襄樊';
			if($v=='淮安') $v='淮阴';
			$data['city'] = $v;	
		}
	}
	$data['provice'] = isset($data['provice'])?$data['provice']:0;
	$data['city'] = isset($data['city'])?$data['city']:0;
	return $data;
}

function getProvice($name){
    $provice = array("10102000,北京","10103000,上海","10101201,深圳","10101002,广州","10101000,广东","10104000,天津","10105000,重庆","10106000,安徽","10107000,福建","10108000,甘肃","10109000,广西","10110000,贵州","10111000,海南","10112000,河北","10113000,河南","10114000,黑龙江","10115000,湖北","10116000,湖南","10117000,吉林","10118000,江苏","10119000,江西","10120000,辽宁","10121000,内蒙古","10122000,宁夏","10123000,青海","10124000,山东","10125000,山西","10126000,陕西","10127000,四川","10128000,西藏","10129000,新疆","10130000,云南","10131000,浙江","10132000,澳门","10133000,香港","10134000,台湾","2,国外");
    $no = '';
    $p = $name;
    $arr = array();
    foreach ($provice as $v) {
        $a = split(',', $v);
        $arr[$a[1]] = $a[0];
    }
    $pro = $arr[$p];
    if(empty($arr[$p])) {
        $pp = trim(substr($name, 0,9));
        if($pp=='黑龙江'){
            $pro = '10114000';
        }elseif ($pp=='内蒙古') {
            $pro = '10121000';
        }
    }
    return  $pro;
}

function  getCity($name) {
    $city = array("10101201,深圳","10101002,广州","10101003,佛山","10101004,湛江","10101005,珠海","10101006,肇庆","10101007,东莞","10101008,惠州","10101009,海丰","10101010,顺德","10101011,中山","10101012,茂名","10101013,汕头","10101014,梅州","10101015,韶关","10101016,江门","10101017,南海","10101018,清远","10101019,英德","10101020,潮州","10101021,番禺","10101022,阳江","10101023,河源","10101024,高州","10101025,兴宁","10101026,揭阳","10101027,化州","10101028,汕尾","10101029,廉江","10101030,阳春","10101031,三水","10101032,信宜","10101033,乐昌","10101034,普宁","10101035,博罗","10101036,徐闻","10101037,恩平","10101038,罗定","10101039,阳东","10101040,鹤山","10101041,高明","10101042,新兴","10101043,蕉岭","10101044,连平","10101045,曲江","10101046,潮安","10101047,惠来","10101048,丰顺","10101049,龙川","10101050,新丰","10101051,四会","10101052,从化","10101053,仁化","10101054,封开","10101055,乳源","10101056,广宁","10101057,南澳","10101058,阳山","10101059,陆河","10101060,遂溪","10101061,宝安","10101062,连州","10101063,潮阳","10101064,惠阳","10101065,吴川","10101066,开平","10101067,斗门","10101068,云浮","10101069,龙门","10101070,雷州","10101071,新会","10101072,郁南","10101073,阳西","10101074,梅县","10101075,揭东","10101076,大埔","10101077,和平","10101078,翁源","10101079,高要","10101080,饶平","10101081,揭西","10101082,五华","10101083,紫金","10101084,花都","10101085,始兴","10101086,怀集","10101087,平远","10101088,佛冈","10101089,增城","10101090,南雄","10101091,德庆","10101092,澄海","10101093,连山","10101094,电白","10101095,连南","10101096,陆丰","10101097,惠东","10101098,台山","10102001,东城","10102002,西城","10102003,崇文","10102004,宣武","10102005,朝阳","10102006,丰台","10102007,石景山","10102008,海淀","10102009,门头沟","10102010,房山","10102011,通州","10102012,顺义","10102013,昌平","10102014,大兴","10102015,怀柔","10102016,平谷","10102017,密云","10102018,延庆","10103001,浦东","10103002,徐汇","10103003,长宁","10103004,普陀","10103005,闸北","10103006,虹口","10103007,杨浦","10103008,黄浦","10103009,卢湾","10103010,静安","10103011,宝山","10103012,闵行","10103013,嘉定","10103014,金山","10103015,松江","10103016,青浦","10103017,崇明","10103018,奉贤","10103019,南汇","10103020,川沙","10104002,武清","10104003,宁河","10104004,天津","10104005,宝坻","10104006,静海","10104007,蓟县","10104008,和平","10104009,河东","10104010,河西","10104011,南开","10104012,河北","10104013,红桥","10104014,塘沽","10104015,汉沽","10104016,大港","10104017,东丽","10104018,西青","10104019,北辰","10104020,津南","10105001,重庆","10105002,奉节","10105003,武隆","10105004,忠县","10105005,巫山","10105006,开县","10105007,永川","10105008,荣昌","10105009,巴县","10105010,铜梁","10105012,石柱","10105013,合川","10105014,双桥","10105015,南川","10105016,长寿","10105017,巫溪","10105018,黔江","10105019,云阳","10105020,垫江","10105021,梁平","10105022,万县","10105023,大足","10105024,北碚","10105025,壁山","10105026,江北","10105027,潼南","10105028,涪陵","10105029,江津","10105030,丰都","10105031,南桐","10105032,城口","10105033,綦江","10105034,渝中","10105035,大渡口","10105036,沙坪坝","10105037,九龙坡","10105038,南岸","10105039,万盛","10105040,渝北","10105041,巴南","10105042,万州","10105043,秀山","10105044,酉阳","10105045,彭水","10106001,合肥","10106002,淮南","10106003,蚌埠","10106004,宿州","10106005,阜阳","10106006,六安","10106007,巢湖","10106008,滁州","10106009,芜湖","10106010,屯溪","10106011,安庆","10106012,黄山","10106013,铜陵","10106014,黟县","10106015,砀山","10106016,全椒","10106017,祁门","10106018,繁昌","10106019,舒城","10106020,绩溪","10106021,怀宁","10106022,东至","10106023,临泉","10106024,怀远","10106025,旌德","10106026,潜山","10106027,贵池","10106028,颍上","10106029,郎溪","10106030,桐城","10106031,和县","10106032,涡阳","10106033,肥西","10106034,宣州","10106035,太湖","10106036,无为","10106037,阜南","10106038,明光","10106039,当涂","10106040,金寨","10106041,来安","10106042,淮北","10106043,凤台","10106044,霍邱","10106045,灵壁","10106046,凤阳","10106047,休宁","10106048,南陵","10106049,寿县","10106050,萧县","10106051,歙县","10106052,芜湖县","10106053,岳西","10106054,石台","10106055,利辛","10106056,固镇","10106057,广德","10106058,宿松","10106059,青阳","10106060,太和","10106061,五河","10106062,宁国","10106063,望江","10106064,含山","10106065,界首","10106066,长丰","10106067,泾县","10106068,枞阳","10106069,庐江","10106070,蒙城","10106071,肥东","10106072,毫州","10106073,定远","10106074,濉溪","10106075,马鞍山","10106076,霍山","10106077,泗县","10106078,天长","10106079,池州","10106080,宣城","10107001,福州","10107002,厦门","10107003,泉州","10107004,南平","10107005,邵武","10107006,福安","10107007,漳州","10107008,龙岩","10107009,三明","10107010,莆田","10107011,晋江","10107012,石狮","10107013,宁德","10107014,华安","10107015,松溪","10107016,古田","10107017,尤溪","10107018,连江","10107019,龙海","10107020,光泽","10107021,周宁","10107022,永安","10107023,福清","10107024,云霄","10107025,仙游","10107026,宁化","10107027,永泰","10107028,诏安","10107029,建宁","10107030,罗源","10107031,安溪","10107032,将乐","10107033,同安","10107034,永定","10107035,德化","10107036,建瓯","10107037,武平","10107038,建阳","10107039,福鼎","10107040,连城","10107041,南靖","10107042,浦城","10107043,寿宁","10107044,沙县","10107045,闽候","10107046,长泰","10107047,政和","10107048,屏南","10107049,大田","10107050,长乐","10107051,漳浦","10107052,武夷山","10107053,清流","10107054,平潭","10107055,东山","10107056,明溪","10107057,闽清","10107058,平和","10107059,南安","10107060,泰宁","10107061,漳平","10107062,永春","10107063,上杭","10107064,惠安","10107065,顺昌","10107066,柘荣","10107067,长汀","10107068,霞浦","10108001,兰州","10108002,张掖","10108003,武威","10108004,酒泉","10108005,安西","10108006,金昌","10108007,天水","10108008,定西","10108009,平凉","10108010,西峰","10108011,陇西","10108012,甘南","10108013,迭部","10108014,会宁","10108015,宕昌","10108016,漳县","10108017,金塔","10108018,永靖","10108019,礼县","10108020,民勤","10108021,岷县","10108022,临夏市","10108023,嘉峪关","10108024,东乡","10108025,两当","10108026,古浪","10108027,渭源","10108028,红古","10108029,广河","10108030,永昌","10108031,宁县","10108032,榆中","10108033,武山","10108034,山丹","10108035,环县","10108036,张家川","10108037,临潭","10108038,民乐","10108039,武都","10108040,正宁","10108041,泾川","10108042,碌曲","10108043,萧南","10108044,靖远","10108045,成县","10108046,崇信","10108047,旧尼","10108048,玉门","10108049,景泰","10108050,文县","10108051,庄浪","10108052,敦煌","10108053,临夏县","10108054,西和","10108055,通渭","10108056,阿克塞","10108057,和政","10108058,徽县","10108059,天祝","10108060,临洮","10108061,肃北","10108062,康乐","10108063,金川","10108064,庆阳","10108065,皋兰","10108066,甘谷","10108067,积石山","10108068,镇原","10108069,永登","10108070,秦安","10108071,夏河","10108072,高台","10108073,合水","10108074,静宁","10108075,清水","10108076,舟曲","10108077,临泽","10108078,白银","10108079,华池","10108080,灵台","10108081,玛曲","10108082,康县","10108083,华亭","10108084,陇南","10109001,南宁","10109002,柳州","10109003,钦州","10109004,百色","10109005,玉林","10109006,防城港","10109007,桂林","10109008,梧州","10109009,河池","10109010,北海","10109011,武鸣","10109012,龙胜","10109013,合山","10109014,贵港","10109015,龙州","10109016,资源","10109017,浦北","10109018,来宾","10109019,平南","10109020,大新","10109021,苍梧","10109022,宜州","10109023,象州","10109024,田林","10109025,横县","10109026,岑溪","10109027,环江","10109028,阳朔","10109029,西林","10109030,扶绥","10109031,贺县","10109032,凤山","10109033,上思","10109034,荔浦","10109035,田阳","10109036,柳江","10109037,钟山","10109038,南丹","10109039,上林","10109040,平乐","10109041,田东","10109042,柳城","10109043,都安","10109044,邕宁","10109045,灵川","10109046,平果","10109047,融安","10109048,北流","10109049,宁明","10109050,永福","10109051,那坡","10109052,金秀","10109053,博白","10109054,马山","10109055,恭城","10109056,灵山","10109057,忻城","10109058,桂平","10109059,宾阳","10109060,武宣","10109061,天等","10109062,藤县","10109063,罗城","10109064,隆林","10109065,隆安","10109066,昭平","10109067,天峨","10109068,临桂","10109069,凌云","10109070,蒙山","10109071,东兰","10109072,灌阳","10109073,靖西","10109074,鹿寨","10109075,富川","10109076,巴马","10109077,凭祥","10109078,兴安","10109079,乐业","10109080,三江","10109081,容县","10109082,大化","10109083,崇左","10109084,全州","10109085,德保","10109086,融水","10109087,陆川","10109088,合浦","10109089,贺州","10110001,贵阳","10110002,六盘水","10110003,玉屏","10110004,凯里","10110005,都匀","10110006,安顺","10110007,遵义","10110008,台江","10110009,清镇","10110010,紫云","10110011,德江","10110012,镇远","10110013,务川","10110014,黄平","10110015,息烽","10110016,镇宁","10110017,威宁","10110018,三穗","10110019,贵定","10110020,纳雍","10110021,毕节","10110022,锦屏","10110023,习水","10110024,瓮安","10110025,大方","10110026,兴义","10110027,从江","10110028,荔波","10110029,织金","10110030,望谟","10110031,松桃","10110032,绥阳","10110033,平塘","10110034,六枝","10110035,兴仁","10110036,道真","10110037,惠水","10110038,雷山","10110039,普安","10110040,石阡","10110041,凤冈","10110042,榕江","10110043,麻江","10110044,平坝","10110045,印江","10110046,余庆","10110047,施秉","10110048,修文","10110049,关岭","10110050,长顺","10110051,岑巩","10110052,开阳","10110053,普定","10110054,赫章","10110055,铜仁","10110056,天柱","10110057,赤水","10110058,福泉","10110059,黔西","10110060,黎平","10110061,仁怀","10110062,三都","10110063,金沙","10110064,册亨","10110065,沿河","10110066,桐梓","10110067,独山","10110068,剑河","10110069,安龙","10110070,万山","10110071,正安","10110072,罗甸","10110073,盘县","10110074,贞丰","10110075,江口","10110076,湄潭","10110077,龙里","10110078,丹寨","10110079,晴隆","10110080,思南","10110081,铜仁地","10110082,黔西南","10110083,黔东南","10110084,黔南","10111001,海口","10111002,三亚","10111003,洋浦","10111004,澄迈","10111005,昌江","10111006,文昌","10111007,琼海","10111008,琼山","10111009,通什","10111010,保亭","10111011,琼中","10111012,白沙","10111013,东方","10111014,定安","10111015,临高","10111016,屯昌","10111017,万宁","10111018,乐东","10111019,陵水","10111020,儋州","10111021,五指山","10112001,石家庄","10112002,衡水","10112003,邢台","10112004,邯郸","10112005,沧州","10112006,唐山","10112007,廊坊","10112008,秦皇岛","10112009,承德","10112010,保定","10112011,张家口","10112012,赵县","10112013,万全","10112014,霸州","10112015,安平","10112016,肥乡","10112017,安国","10112018,宽城","10112019,泊头","10112020,沙河","10112021,高邑","10112022,崇礼","10112023,大城","10112024,临西","10112025,昌黎","10112026,曲阳","10112027,滦平","10112028,肃宁","10112029,武安","10112030,灵寿","10112031,尚义","10112032,固安","10112033,临城","10112034,抚宁","10112035,高阳","10112036,玉田","10112037,献县","10112038,磁县","10112039,赤城","10112040,大厂","10112041,宁晋","10112042,正定","10112043,容城","10112044,遵化","10112045,铙阳","10112046,成安","10112047,清苑","10112048,涿鹿","10112049,巨鹿","10112050,井陉","10112051,安新","10112052,唐海","10112053,武邑","10112054,鸡泽","10112055,易县","10112056,阳原","10112057,海兴","10112058,南宫","10112059,新乐","10112060,蠡县","10112061,乐亭","10112062,景县","10112063,丘县","10112064,唐县","10112065,孟村","10112066,威县","10112067,深泽","10112068,迁安","10112069,枣强","10112070,大名","10112071,涿州","10112072,隆化","10112073,南皮","10112074,平乡","10112075,晋州","10112076,怀安","10112077,深州","10112078,广平","10112079,博野","10112080,平泉","10112081,吴桥","10112082,任县","10112083,赞皇","10112084,张北","10112085,永清","10112086,定州","10112087,丰宁","10112088,河间","10112089,平山","10112090,沽源","10112091,文安","10112092,内丘","10112093,卢龙","10112094,阜平","10112095,任丘","10112096,临漳","10112097,行唐","10112098,康保","10112099,香河","10112100,柏乡","10112101,青龙","10112102,获鹿","10112103,徐水","10112104,滦县","10112105,涉县","10112106,潢城","10112107,怀来","10112108,三河","10112109,隆尧","10112110,栾城","10112111,雄县","10112112,滦南","10112113,武强","10112114,永年","10112115,涞水","10112116,蔚县","10112117,黄骅","10112118,新河","10112119,元氏","10112120,望都","10112121,丰南","10112122,阜城","10112123,曲周","10112124,涞源","10112125,盐山","10112126,清河","10112127,无极","10112128,顺平","10112129,丰润","10112130,故城","10112131,馆陶","10112132,定兴","10112133,兴隆","10112134,青县","10112135,广宗","10112136,辛集","10112137,宣化","10112138,迁西","10112139,冀县","10112140,魏县","10112141,高碑店","10112142,围场","10112143,东光","10112144,南和","10113001,郑州","10113002,新乡","10113003,安阳","10113004,许昌","10113005,驻马店","10113006,漯河","10113007,信阳","10113008,周口","10113009,洛阳","10113010,平顶山","10113011,三门峡","10113012,南阳","10113013,开封","10113014,商丘","10113015,鹤壁","10113016,濮阳","10113017,焦作","10113018,原阳","10113019,内乡","10113020,南乐","10113021,息县","10113022,荥阳","10113023,泌阳","10113024,长垣","10113025,卢氏","10113026,中牟","10113027,罗山","10113028,武陟","10113029,汝南","10113030,辉县","10113031,杞县","10113032,淮阳","10113033,义马","10113034,登封","10113035,宛城","10113036,孟县","10113037,西平","10113038,长葛","10113039,尉氏","10113040,鹿邑","10113041,陕县","10113042,西峡","10113043,沁阳","10113044,平舆","10113045,禹州","10113046,洛宁","10113047,项城","10113048,虞城","10113049,汤阴","10113050,方城","10113051,正阳","10113052,舞钢","10113053,偃师","10113054,太康","10113055,永城","10113056,内黄","10113057,唐河","10113058,淇县","10113059,光山","10113060,襄城","10113061,宜阳","10113062,宁陵","10113063,镇平","10113064,淮滨","10113065,民权","10113066,鲁山","10113067,汝阳","10113068,舞阳","10113069,延津","10113070,桐柏","10113071,范县","10113072,商城","10113073,上街","10113074,汝州","10113075,栾川","10113076,获嘉","10113077,清丰","10113078,新郑","10113079,修武","10113080,遂平","10113081,封丘","10113082,兰考","10113083,西华","10113084,渑池","10113085,新密","10113086,卧龙","10113087,温县","10113088,确山","10113089,通许","10113090,郸城","10113091,灵宝","10113092,巩义","10113093,邓州","10113094,博爱","10113095,上蔡","10113096,鄢陵","10113097,沈丘","10113098,淅川","10113099,济源","10113100,新蔡","10113101,孟津","10113102,扶沟","10113103,夏邑","10113104,林州","10113105,社旗","10113106,浚县","10113107,潢川","10113108,郏县","10113109,伊川","10113110,商水","10113111,柘城","10113112,滑县","10113113,新野","10113114,新县","10113115,叶县","10113116,新安","10113117,临颖","10113118,睢县","10113119,卫辉","10113120,南召","10113121,台前","10113122,固始","10113123,宝丰","10113124,嵩县","10113125,郾城","10114001,哈尔滨","10114002,绥化","10114003,佳木斯","10114004,牡丹江","10114005,齐齐哈尔","10114006,北安","10114007,大庆","10114008,大兴安岭","10114009,鸡西","10114010,穆棱","10114011,望奎","10114012,五常","10114013,同江","10114014,海林","10114015,青冈","10114016,呼兰","10114017,饶河","10114018,绥芬河","10114019,克山","10114020,鹤岗","10114021,安达","10114022,逊克","10114023,依安","10114024,双鸭山","10114025,杜尔伯特","10114026,阿城","10114027,密山","10114028,嫩江","10114029,讷河","10114030,宝清","10114031,方正","10114032,勃利","10114033,加格达奇","10114034,富裕","10114035,延寿","10114036,依兰","10114037,呼玛","10114038,泰来","10114039,庆安","10114040,肇东","10114041,桦南","10114042,伊春","10114043,林口","10114044,绥棱","10114045,木兰","10114046,抚远","10114047,东宁","10114048,兰西","10114049,巴彦","10114050,桦川","10114051,铁力","10114052,宁安","10114053,黑河","10114054,萝北","10114055,林甸","10114056,虎林","10114057,孙吴","10114058,拜泉","10114059,绥滨","10114060,肇州","10114061,鸡东","10114062,德都","10114063,龙江","10114064,友谊","10114065,肇源","10114066,通河","10114067,七台河","10114068,五大连池","10114070,集贤","10114071,尚志","10114072,塔河","10114073,克东","10114074,明水","10114075,双成","10114076,汤原","10114077,漠河","10114078,海伦","10114079,宾县","10114080,富锦","10114081,嘉荫","10115001,武汉","10115002,黄石","10115003,沙市","10115004,鄂州","10115005,襄樊","10115006,咸宁","10115007,十堰","10115008,宜昌","10115009,恩施","10115010,荆州","10115011,黄冈","10115012,荆门","10115013,孝感","10115014,襄阳","10115015,蒲圻","10115016,神农架","10115017,麻城","10115018,五峰","10115019,南漳","10115020,荆沙","10115021,竹溪","10115022,浠水","10115023,兴山","10115024,枣阳","10115025,监利","10115026,随枣","10115027,英山","10115028,建始","10115029,保康","10115030,公安","10115031,黄梅","10115032,鹤峰","10115033,钟祥","10115034,天门","10115035,来凤","10115036,汉川","10115037,洪湖","10115038,武昌县","10115039,阳新","10115040,利川","10115041,云梦","10115042,远安","10115043,黄陂","10115044,通山","10115045,郧县","10115046,广水","10115047,枝江","10115048,通城","10115049,房县","10115050,黄州","10115051,长阳","10115052,宜城","10115053,嘉鱼","10115054,竹山","10115055,红安","10115056,秭归","10115057,谷城","10115058,郧西","10115059,罗田","10115060,老河口","10115061,石首","10115062,随州","10115063,蕲春","10115064,巴东","10115065,松滋","10115066,仙桃","10115067,武穴","10115068,宣恩","10115069,大悟","10115070,京山","10115071,潜江","10115072,武昌","10115073,大冶","10115074,咸丰","10115075,应城","10115076,温泉","10115077,蔡甸","10115078,安陆","10115079,当阳","10115080,新洲","10115081,崇阳","10115082,丹江口","10115083,枝城","10115084,恩施土家族苗族自治州","10116001,长沙","10116002,株洲","10116003,益阳","10116004,岳阳","10116005,常德","10116006,吉首","10116007,娄底","10116008,怀化","10116009,衡阳","10116010,邵阳","10116011,郴州","10116012,零陵","10116013,张家界","10116014,湘潭","10116015,醴陵","10116016,沅江","10116017,通道","10116018,平江","10116019,桂阳","10116020,凤凰","10116021,双峰","10116022,会同","10116023,宜章","10116024,永顺","10116025,耒阳","10116026,新化","10116027,辰溪","10116028,宁乡","10116029,桃源","10116030,花垣","10116031,衡南","10116032,洪江","10116033,浏阳","10116034,石门","10116035,泸溪","10116036,衡山","10116037,新宁","10116038,东安","10116039,津市","10116040,桑植","10116041,新邵","10116042,新田","10116043,湘乡","10116044,临澧","10116045,桂东","10116046,武岗","10116047,蓝山","10116048,桃江","10116049,芷江","10116050,湘阴","10116051,临武","10116052,邵东","10116053,江永","10116054,茶陵","10116055,南县","10116056,溆浦","10116057,临湘","10116058,安仁","10116059,双牌","10116060,炎陵","10116061,靖州","10116062,汨罗","10116063,永兴","10116064,龙山","10116065,冷水江","10116066,新晃","10116067,保靖","10116068,常宁","10116069,涟源","10116070,沅陵","10116071,望城","10116072,汉寿","10116073,古丈","10116074,衡东","10116075,永州","10116076,澧县","10116077,祁东","10116078,城步","10116079,祁阳","10116080,韶山","10116081,安乡","10116082,慈利","10116083,资兴","10116084,绥宁","10116085,宁远","10116086,麻阳","10116087,汝城","10116088,洞口","10116089,江华","10116090,攸县","10116091,安化","10116092,黔阳","10116093,华容","10116094,嘉禾","10116095,隆回","10116096,道县","10116097,湘西土家族苗族自治州","10117001,长春","10117002,吉林","10117003,延吉","10117004,通化","10117005,梅河口","10117006,四平","10117007,白城","10117008,松原","10117009,安图","10117010,辉南","10117011,东辽","10117012,图们","10117013,农安","10117014,德惠","10117015,前郭","10117016,犁树","10117017,乾安","10117018,磬石","10117019,靖宇","10117020,集安","10117021,蛟河","10117022,抚松","10117023,通榆","10117024,珲春","10117025,洮南","10117026,和龙","10117027,柳河","10117028,辽源","10117029,敦化","10117030,双阳","10117031,东丰","10117032,龙井","10117033,九台","10117034,扶余","10117035,双辽","10117036,榆树","10117037,长岭","10117038,公主岭","10117039,永吉","10117040,临江","10117041,桦甸","10117042,长白","10117043,舒兰","10117044,江源","10117045,大安","10117046,汪清","10117047,镇赉","10117048,白山","10117049,延边朝鲜族自治州","10118001,南京","10118002,苏州","10118003,无锡","10118004,徐州","10118005,常州","10118006,镇江","10118007,连云港","10118008,淮阴","10118009,盐城","10118010,扬州","10118011,南通","10118012,昆山","10118013,张家港","10118014,宜兴","10118015,江阴","10118016,淮安","10118017,常熟","10118018,泰兴","10118019,吴江","10118020,太仓","10118021,江浦","10118022,滨海","10118023,金湖","10118024,靖江","10118025,江宁","10118026,射阳","10118027,盱眙","10118028,如皋","10118029,姜堰","10118030,溧水","10118031,大丰","10118032,东海","10118033,海门","10118034,沭阳","10118035,灌云","10118036,海安","10118037,泗阳","10118038,锡山","10118039,丰县","10118040,江都","10118041,丹徒","10118042,邳县","10118043,金坛","10118044,仪征","10118045,睢宁","10118046,句容","10118047,宝应","10118048,响水","10118049,洪泽","10118050,吴县","10118051,泰州","10118052,六合","10118053,阜宁","10118054,涟水","10118055,如东","10118056,高淳","10118057,建湖","10118058,通州市","10118059,兴化","10118060,东台","10118061,赣榆","10118062,启东","10118063,宿迁","10118064,铜山","10118065,灌南","10118066,泗洪","10118067,沛县","10118068,武进","10118069,邗江","10118070,丹阳","10118071,新沂","10118072,溧阳","10118073,高邮","10118074,扬中","10119001,南昌","10119002,九江","10119003,景德镇","10119004,上饶","10119005,鹰潭","10119006,宜春","10119007,萍乡","10119008,赣州","10119009,吉安","10119010,抚州","10119011,铜鼓","10119012,进贤","10119013,临川","10119014,兴国","10119015,余干","10119016,浮梁","10119017,乐安","10119018,石城","10119019,波阳","10119020,永丰","10119021,莲花","10119022,庐山","10119023,南丰","10119024,南康","10119025,万年","10119026,峡江","10119027,贵溪","10119028,湖口","10119029,大余","10119030,横峰","10119031,万安","10119032,瑞昌","10119033,上高","10119034,崇义","10119035,德兴","10119036,宁冈","10119037,彭泽","10119038,靖安","10119039,龙南","10119040,新余","10119041,资溪","10119042,井岗山","10119043,永修","10119044,丰城","10119045,全南","10119046,东乡","10119047,万载","10119048,会昌","10119049,新建","10119050,崇仁","10119051,于都","10119052,玉山","10119053,乐平","10119054,安义","10119055,宜黄","10119056,宁都","10119057,弋阳","10119058,新干","10119059,南城","10119060,寻乌","10119061,广丰","10119062,吉水","10119063,修水","10119064,黎川","10119065,赣县","10119066,铅山","10119067,泰和","10119068,余江","10119069,星子","10119070,宜丰","10119071,上犹","10119072,婺源","10119073,遂川","10119074,德安","10119075,奉新","10119076,信丰","10119077,永新","10119078,都昌","10119079,高安","10119080,定南","10119081,分宜","10119082,广昌","10119083,安福","10119084,武宁","10119085,樟树","10119086,安远","10119087,金溪","10119088,瑞金","10120001,沈阳","10120002,铁岭","10120003,抚顺","10120004,鞍山","10120005,营口","10120006,大连","10120007,本溪","10120008,丹东","10120009,锦州","10120010,朝阳市","10120011,阜新","10120012,盘锦","10120013,辽阳","10120014,葫芦岛","10120015,清原","10120016,西丰","10120017,建昌","10120018,彰武","10120019,桓仁","10120020,长海","10120021,东港","10120022,普兰店","10120023,宽甸","10120024,新金","10120025,建平","10120026,辽中","10120027,凌海","10120028,海城","10120029,凌源","10120030,法库","10120031,义县","10120032,岫岩","10120033,大洼","10120034,开原","10120035,大石桥","10120036,新宾","10120037,绥中","10120038,铁法","10120039,兴城","10120040,庄河","10120041,瓦房店","10120042,灯塔","10120043,凤城","10120044,金县","10120045,新民","10120046,北票","10120047,康平","10120048,黑山","10120049,台安","10120050,喀喇沁左翼","10120051,北宁","10120052,盘山","10120053,昌图","10120054,盖州","10121001,呼和浩特","10121002,集宁","10121003,包头","10121004,临河","10121005,乌海","10121006,东胜","10121007,海拉尔","10121008,赤峰","10121009,锡林浩特","10121010,太仆寺旗","10121011,通辽","10121012,丰镇","10121013,鄂托克旗","10121014,阿鲁科尔沁","10121015,西乌珠穆沁旗","10121016,商都","10121017,土默特左旗","10121018,敖汉旗","10121019,正镶白旗","10121020,凉城","10121021,磴口","10121022,武川","10121023,巴林左旗","10121024,乌兰浩特","10121025,阿荣旗","10121026,察哈尔右翼中旗","10121027,乌拉特中镇","10121028,林西","10121029,突泉","10121030,扎兰屯","10121031,杭锦后旗","10121032,土默特右旗","10121033,喀喇沁旗","10121034,阿拉善左旗","10121035,陈巴尔虎旗","10121036,库伦旗","10121037,二连浩特","10121038,达茂旗","10121039,达拉特旗","10121040,额济纳旗","10121041,新巴尔虎右旗","10121042,扎鲁特旗","10121043,准格尔旗","10121044,额尔古纳右旗","10121045,科尔沁左翼后旗","10121046,苏尼特右旗","10121047,化德","10121048,乌审","10121049,满洲里","10121050,东乌珠穆沁旗","10121051,卓资","10121052,鄂托克前旗","10121053,托克托","10121054,宁城","10121055,阿马嗄旗","10121056,兴和","10121057,五原","10121058,和林格尔","10121059,翁牛特旗","10121060,正蓝旗","10121061,鄂温克","10121062,察哈尔右翼前旗","10121063,乌拉特前镇","10121064,清水河","10121065,巴林右旗","10121066,扎赉特旗","10121067,牙克石","10121068,察哈尔右翼后旗","10121069,乌拉特后镇","10121070,固阳","10121071,克什克腾旗","10121072,科右中旗","10121073,鄂伦春","10121074,开鲁","10121075,白云鄂博","10121076,阿拉善右旗","10121077,新巴尔虎左旗","10121078,奈曼旗","10121079,多伦","10121080,伊金霍洛旗","10121081,根河","10121082,科尔沁左翼中旗","10121083,苏尼特左旗","10121084,四子王旗","10121085,杭棉旗","10121086,莫力达瓦旗","10121087,霍林郭勒","10121088,镶黄旗","10121089,锡林郭勒盟","10121090,阿拉善盟","10121091,兴安","10121092,鄂尔多斯","10121093,呼伦贝尔","10121094,巴彦淖尔","10121095,乌兰察布","10122001,银川","10122002,石嘴山","10122003,固原","10122004,平罗","10122005,陶乐","10122006,中宁","10122007,青铜峡","10122008,西吉","10122009,贺兰","10122010,吴忠","10122011,中卫","10122012,灵武","10122013,盐池","10122014,海源","10122015,隆德","10122016,永宁","10122017,彭阳","10122018,同心","10122019,泾源","10122020,惠农","10122021,海原","10123001,西宁","10123002,果洛","10123003,玉树","10123004,格尔木","10123005,海西","10123006,同德","10123007,尖扎","10123008,乐都","10123009,刚察","10123010,乌兰","10123011,龙羊峡","10123012,门源","10123013,兴海","10123014,互助","10123015,冷湖","10123016,天峻","10123017,民和","10123018,玛多","10123019,称多","10123020,化隆","10123021,甘德","10123022,杂多","10123023,共和","10123024,贵德","10123025,海晏","10123026,河南","10123027,大通","10123028,祁连","10123029,泽库","10123030,湟中","10123031,大柴旦","10123032,都兰","10123033,湟源","10123034,达日","10123035,治多","10123036,循化","10123037,班玛","10123038,囊谦","10123039,同仁","10123040,曲麻莱","10123041,玛沁","10123042,茫崖","10123043,德令哈","10123044,贵南","10123045,海东地区","10123046,海北藏族自治州","10123047,黄南藏族自治州","10123048,海南藏族自治州","10124001,青岛","10124002,威海","10124003,济南","10124004,淄博","10124005,聊城","10124006,德州","10124007,东营","10124008,潍坊","10124009,烟台","10124010,兖州","10124011,泰安","10124012,菏泽","10124013,临沂","10124014,枣庄","10124015,济宁","10124016,日照","10124017,曲阜","10124018,滨州","10124019,东明","10124020,莱阳","10124021,肥城","10124022,高青","10124023,阳信","10124024,郓城","10124025,长岛","10124026,苍山","10124027,茌平","10124028,利津","10124029,长清","10124030,龙口","10124031,蒙阴","10124032,东阿","10124033,监邑","10124034,邹城","10124035,垦利","10124036,商河","10124037,栖霞","10124038,沂南","10124039,莘县","10124040,平原","10124041,鱼台","10124042,荣城","10124043,平阴","10124044,临沭","10124045,齐河","10124046,嘉祥","10124047,乳山","10124048,胶南","10124049,昌邑","10124050,费县","10124051,巨野","10124052,武城","10124053,汶上","10124054,滕州","10124055,平度","10124056,诸城","10124057,博兴","10124058,成武","10124059,陵县","10124060,新泰","10124061,五莲","10124062,即墨","10124063,临朐","10124064,惠民","10124065,曹县","10124066,招远","10124067,东平","10124068,莱芜","10124069,桓台","10124070,昌乐","10124071,沾化","10124072,鄄城","10124073,海阳","10124074,临清","10124075,沂源","10124076,梁山","10124077,莱州","10124078,平邑","10124079,高唐","10124080,庆云","10124081,广饶","10124082,章丘","10124083,蓬莱","10124084,沂水","10124085,阳谷","10124086,夏津","10124087,微山","10124088,济阳","10124089,牟平","10124090,莒南","10124091,冠县","10124092,宁津","10124093,金乡","10124094,文登","10124095,寿光","10124096,郯城","10124097,乐陵","10124098,泗水","10124099,胶州","10124100,高密","10124102,定陶","10124103,禹城","10124104,莱西","10124105,安丘","10124106,邹平","10124107,单县","10124108,宁阳","10124109,河口","10124110,青州","10124111,无棣","10125001,太原","10125002,离石","10125003,忻州","10125004,宁武","10125005,大同","10125006,临汾","10125007,侯马","10125008,运城","10125009,阳泉","10125010,长治","10125011,晋城","10125012,长子","10125013,交口","10125014,寿阳","10125015,隰县","10125016,垣曲","10125017,代县","10125018,柳林","10125019,天镇","10125020,祁县","10125021,乡宁","10125022,阳城","10125023,岚县","10125024,浑源","10125025,介休","10125026,安泽","10125027,五寨","10125028,陵川","10125029,芮城","10125030,灵丘","10125031,古县","10125032,河曲","10125033,临猗","10125034,朔州","10125035,平顺","10125036,石楼","10125037,偏关","10125038,曲沃","10125039,稷山","10125040,应县","10125041,平定","10125042,襄垣","10125043,临县","10125044,襄汾","10125045,新绛","10125046,山阴","10125047,灵石","10125048,沁县","10125049,文水","10125050,娄烦","10125051,霍州","10125052,夏县","10125053,原平","10125054,和顺","10125055,屯留","10125056,孝义","10125057,古交","10125058,榆社","10125059,蒲县","10125060,绛县","10125061,五台","10125062,潞城","10125063,中阳","10125064,大同县","10125065,太谷","10125066,永和","10125067,繁峙","10125068,高平","10125069,兴县","10125070,阳高","10125071,平遥","10125072,吉县","10125073,神池","10125074,沁水","10125075,广灵","10125077,浮山","10125078,岢岚","10125079,平陆","10125080,左云","10125081,壶关","10125082,保德","10125083,大宁","10125084,万荣","10125085,怀仁","10125086,孟县","10125087,黎城","10125088,方山","10125089,静乐","10125090,翼城","10125091,河津","10125092,右玉","10125093,榆次","10125094,武乡","10125095,汾阳","10125096,阳曲","10125097,洪洞","10125098,闻喜","10125099,昔阳","10125100,沁源","10125101,交城","10125102,清徐","10125103,汾西","10125104,永济","10125105,定襄","10125106,左权","10125107,晋中","10125108,吕梁","10126001,西安","10126002,渭南","10126003,延安","10126004,绥德","10126005,榆林","10126006,宝鸡","10126007,安康","10126008,汉中","10126009,商县","10126010,铜川","10126011,咸阳","10126012,洛川","10126013,留坝","10126014,镇巴","10126015,永寿","10126016,宜君","10126017,澄城","10126018,黄龙","10126019,镇坪","10126020,洋县","10126021,蒲城","10126022,武功","10126023,延长","10126024,镇安","10126025,南郑","10126026,富平","10126027,长安","10126028,太白","10126029,延川","10126030,清涧","10126031,紫阳","10126032,华阴","10126033,蓝田","10126034,陇县","10126035,靖边","10126036,石泉","10126037,华县","10126038,户县","10126039,眉县","10126040,乾县","10126041,汉阴","10126042,岐山","10126043,泾阳","10126044,横山","10126045,岚皋","10126046,安塞","10126047,略阳","10126048,淳化","10126049,佳县","10126050,洛南","10126051,志丹","10126052,佛坪","10126053,勉县","10126054,兴平","10126055,吴堡","10126056,商州","10126057,宜川","10126058,白河","10126059,西乡","10126060,合阳","10126061,长武","10126062,耀县","10126063,白水","10126064,吴旗","10126065,山阳","10126066,城固","10126067,韩城","10126068,麟游","10126069,子长","10126070,柞水","10126071,潼关","10126072,临潼","10126073,千阳","10126074,黄陵","10126075,神木","10126076,平利","10126077,大荔","10126078,周至","10126079,凤县","10126080,彬县","10126081,定边","10126082,旬阳","10126083,高陵","10126084,扶风","10126085,礼泉","10126086,米脂","10126087,宁陕","10126088,富县","10126089,商南","10126090,凤翔","10126091,三原","10126092,府谷","10126093,甘泉","10126094,宁强","10126095,旬邑","10126096,子洲","10126097,丹凤","10126098,商洛","10127001,成都","10127002,乐山","10127003,凉山","10127004,渡口","10127005,绵阳","10127006,汶川","10127007,阿坝","10127008,雅安","10127009,甘孜","10127010,广元","10127011,南充","10127012,达县","10127013,内江","10127014,自贡","10127015,宜宾","10127016,泸州","10127017,攀枝花","10127018,德阳","10127019,都江堰","10127020,江油","10127021,资阳","10127022,峨眉山","10127023,华蓥","10127024,青神","10127025,理县","10127026,营山","10127027,资中","10127028,旺苍","10127029,荥经","10127030,富顺","10127031,马边","10127032,甘洛","10127033,仪陇","10127034,简阳","10127035,苍溪","10127036,天全","10127037,纳溪","10127038,夹江","10127039,石棉","10127040,金堂","10127041,宣汉","10127042,隆昌","10127043,米易","10127044,安县","10127045,古蔺","10127046,西昌","10127047,会理","10127048,新津","10127049,万源","10127050,宁南","10127051,南溪","10127052,康定","10127053,冕宁","10127054,郫县","10127055,渠县","10127056,眉山","10127057,平昌","10127058,梓潼","10127059,兴文","10127060,雷波","10127061,彭州","10127062,蓬溪","10127063,仁寿","10127064,南江","10127065,盐亭","10127066,珙县","10127067,什邡","10127068,普格","10127069,崇州","10127070,广安","10127071,洪雅","10127072,酉阳","10127073,西充","10127074,筠连","10127075,中江","10127076,金阳","10127077,邛崃","10127078,武胜","10127079,彭山","10127080,彭水","10127081,阆中","10127082,剑阁","10127083,名山","10127084,邻水","10127085,丹梭","10127086,昭觉","10127087,蓬安","10127088,青川","10127089,汉源","10127090,荣县","10127091,合江","10127092,犍为","10127093,宝兴","10127094,温江","10127095,达川","10127096,威远","10127097,璧山","10127098,芦山","10127099,平武","10127100,叙永","10127101,金口河","10127102,木里","10127103,双流","10127104,开江","10127105,乐至","10127106,盐边","10127107,松潘","10127108,会东","10127109,蒲江","10127110,大竹","10127111,峨边","10127112,盐源","10127113,屏山","10127114,马尔康","10127115,越西","10127116,新都","10127117,遂宁","10127118,通江","10127119,三台","10127120,长宁","10127121,广汉","10127122,喜德","10127123,射洪","10127124,井研","10127125,巴中","10127126,高县","10127127,绵竹","10127128,布拖","10127129,大邑","10127130,岳池","10127131,沐川","10127132,秀山","10127133,南部","10127134,江安","10127135,美姑","10127136,广安","10127137,达州","10128001,拉萨","10128002,那曲","10128003,昌都","10128004,山南","10128005,日喀则","10128006,阿里","10128007,林芝","10128008,贡嘎","10128009,芒康","10128010,洛隆","10128011,巴青","10128012,措勤","10128013,堆龙德庆","10128014,索县","10128015,尼木","10128016,扎囊","10128017,江达","10128018,八宿","10128019,丁青","10128020,比如","10128021,班戈","10128022,曲水","10128023,尼玛","10128024,仁布","10129001,乌鲁木齐","10129002,石河子","10129003,乌苏","10129004,克拉玛依","10129005,伊宁","10129006,阿勒泰","10129007,巴音郭楞","10129008,哈密","10129009,吐鲁番","10129010,阿克苏","10129011,喀什","10129012,和田","10129013,哈巴河","10129014,和静","10129015,巩留","10129016,裕民","10129017,昌吉","10129018,疏附","10129019,福海","10129020,若羌","10129021,特克斯","10129022,米泉","10129023,巴楚","10129024,阿图什","10129025,且末","10129026,伊吾","10129027,玛纳斯","10129028,叶城","10129029,阿克陶","10129030,库车","10129031,皮山","10129032,阜康","10129033,莎车","10129034,精河","10129035,沙雅","10129036,洛浦","10129037,英吉沙","10129038,温宿","10129039,于田","10129040,托克逊","10129041,疏勒","10129042,阿拉尔","10129043,焉耆","10129044,察布查尔","10129045,柯坪","10129046,布尔律","10129047,轮台","10129048,新源","10129049,额敏","10129050,沙湾","10129051,咯什","10129052,富蕴","10129053,和硕","10129054,尼勒克","10129055,托里","10129056,奇台","10129057,伽师","10129058,吉木乃","10129059,尉犁","10129060,昭苏","10129061,马里坤","10129062,呼图壁","10129063,麦盖提","10129064,乌恰","10129065,吉木萨尔","10129066,泽普","10129067,博乐","10129068,新和","10129069,墨玉","10129070,木垒","10129071,岳普湖","10129072,温泉","10129073,拜城","10129074,策勒","10129075,鄯善","10129076,塔什库尔干","10129077,布克赛尔","10129078,乌什","10129079,民丰","10129080,库尔勒","10129081,奎屯","10129082,阿瓦提","10129083,青河","10129084,博湖","10129085,霍城","10129086,塔城","10129087,阿合奇","10129088,克孜勒苏柯尔克孜自治州","10129089,博尔塔拉蒙古自治州","10129090,伊犁","10130001,昆明","10130002,曲靖","10130003,昭通","10130004,开远","10130005,文山","10130006,思茅","10130007,大理","10130008,楚雄","10130009,临沧","10130010,保山","10130011,玉溪","10130012,西盟","10130013,盈江","10130014,洱源","10130015,砚山","10130016,兰坪","10130017,嵩明","10130018,禄丰","10130019,镇沅","10130020,巍山","10130021,广南","10130022,大姚","10130023,维西","10130024,呈贡","10130025,马龙","10130026,东川","10130027,个旧","10130028,富宁","10130029,南华","10130030,丽江","10130031,安宁","10130032,师宗","10130033,耿马","10130034,永善","10130035,弥勒","10130036,麻栗坡","10130037,元谋","10130038,华坪","10130039,禄劝","10130040,富源","10130041,沧源","10130042,大关","10130043,华宁","10130044,武定","10130045,景洪","10130046,鹤庆","10130047,罗平","10130048,凤庆","10130049,盐津","10130050,建水","10130051,新平","10130052,普洱","10130053,孟腊","10130054,祥云","10130055,腾冲","10130056,双江","10130057,绥江","10130058,泸西","10130059,通海","10130060,景谷","10130061,畹町","10130062,宾川","10130063,龙陵","10130064,泸水","10130065,水富","10130066,绿春","10130067,澄江","10130068,澜沧","10130069,陇川","10130070,弥渡","10130071,贡山","10130072,富民","10130073,屏边","10130074,江城","10130075,梁河","10130076,永平","10130077,丘北","10130078,中甸","10130079,宜良","10130080,寻甸","10130081,牟定","10130082,孟连","10130083,漾濞","10130084,马关","10130085,永仁","10130086,德钦","10130087,晋宁","10130088,会泽","10130089,镇雄","10130090,石屏","10130091,西畴","10130092,姚安","10130093,宁蒗","10130094,路南","10130095,陆良","10130096,镇康","10130097,彝良","10130098,红河","10130099,双柏","10130100,永胜","10130101,宣威","10130102,永德","10130103,威信","10130104,蒙自","10130105,江川","10130106,孟海","10130107,剑川","10130108,云县","10130109,巧家","10130110,河口","10130111,元江","10130112,墨江","10130113,潞西","10130114,南涧","10130115,昌宁","10130116,六库","10130117,鲁甸","10130118,金平","10130119,易门","10130120,景东","10130121,瑞丽","10130122,云龙","10130123,施甸","10130124,福贡","10130125,元阳","10130126,峨山","10130127,西双版纳傣族自治州","10130128,德宏傣族景颇族自治州","10130129,怒江傈傈族自治州","10130130,迪庆藏族自治州","10131001,杭州","10131002,温州","10131003,宁波","10131004,绍兴","10131005,湖州","10131006,嘉兴","10131007,临海","10131008,定海","10131009,金华","10131010,丽水","10131011,衢州","10131012,台州","10131013,义乌","10131014,温岭","10131015,舟山","10131016,永康","10131017,黄岩","10131018,兰溪","10131019,瑞安","10131020,富阳","10131021,宁海","10131022,龙泉","10131023,德清","10131024,松阳","10131025,长兴","10131026,永嘉","10131027,常山","10131028,上虞","10131029,桐乡","10131030,洞头","10131031,衢县","10131032,新昌","10131033,海盐","10131034,平阳","10131035,浦江","10131036,海宁","10131037,泰顺","10131038,武义","10131039,镇海","10131040,建德","10131041,磐安","10131042,慈溪","10131043,青田","10131044,临安","10131045,仙居","10131046,岱山","10131047,奉化","10131048,庆元","10131049,三门","10131050,普陀","10131051,象山","10131052,遂昌","10131053,安吉","10131054,瓯海","10131055,江山","10131056,景宁","10131057,乐清","10131058,开化","10131059,嵊县","10131060,金华县","10131061,嘉善","10131062,龙游","10131063,诸暨","10131064,东阳","10131065,平湖","10131066,苍南","10131067,余杭","10131068,文成","10131069,淳安","10131070,椒江","10131071,余姚","10131072,缙云","10131073,桐庐","10131074,玉环","10131075,鄞县","10131076,云和","10131077,萧山","10131078,天台","10131079,嵊泗","10132001,澳门","10133001,香港","10133002,九龙","10133003,新界","10133004,中西区","10133005,东区","10133006,观塘区","10133007,南区","10133008,深水埗区","10133009,黄大仙区","10133010,湾仔区","10133011,油尖旺区","10133012,离岛区","10133013,葵青区","10133014,北区","10133015,西贡区","10133016,沙田区","10133017,屯门区","10133018,大埔区","10133019,荃湾区","10133020,元朗区","10134001,台湾","10134002,卑南","10134003,刺桐","10134004,北港","10134005,北埔","10134006,补子","10134007,安定","10134008,草屯","10134009,八德","10134011,白河","10134012,车城","10134013,板桥","10134014,成功","10134015,宝山","10134016,春日","10134017,北斗","10134018,北门","10134019,北投","10134020,阿莲","10134021,布袋","10134022,安平","10134023,长宾","10134024,八里","10134025,潮州","10134026,白沙","10134027,城中区","10134028,褒忠","10134029,池上","10134030,台北","10134031,高雄","10134032,桃园","10134033,新竹","10134034,宜兰","10134035,苗栗","10134036,屏东","10134037,花莲","10134038,彰化","10134039,南投","10134040,台东","10134041,基隆","10134042,台中","10134043,台南","10134044,嘉义","10500000,美国","10600000,加拿大","10700000,日本","10800000,澳大利亚","10900000,英国","11000000,法国","11100000,德国","11200000,俄罗斯","11300000,新西兰","11400000,泰国","11500000,马来西亚","11600000,印度尼西亚","11700000,菲律宾","11800000,新加坡","11900000,韩国","12000000,缅甸","12100000,越南","12200000,柬埔寨","12300000,老挝","12400000,印度","12500000,文莱","12600000,巴基斯坦","12700000,朝鲜","12800000,尼泊尔","12900000,斯里兰卡","13000000,土耳其","13100000,乌克兰","13200000,意大利","13300000,芬兰","13400000,荷兰","13500000,挪威","13600000,葡萄牙","13700000,西班牙","13800000,瑞典","13900000,瑞士","14000000,阿根廷","14100000,巴西","14200000,智利","14300000,墨西哥","14400000,秘鲁","14500000,奥地利","14600000,比利时","14700000,丹麦","14800000,希腊","14900000,匈牙利","15000000,哥伦比亚","15100000,委内瑞拉","15200000,爱尔兰","15300000,保加利亚","15400000,冰岛","15500000,卢森堡","15600000,罗马尼亚","15700000,以色列","15800000,埃及","15900000,南非","16000000,奥克兰","16100000,喀麦隆","16200000,毛里求斯","16300000,马达加斯加","16400000,其它地区");
    $no = '';
    $arr = array();
    foreach ($city as $v){
        $a = split(',', $v);
        $arr[$a[1]] = $a[0];
    }
    $ci = $arr[$name];
    if(empty($ci)) {
        $ci = '';
    }
    return $ci;
}
?>