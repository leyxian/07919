<?php
error_reporting(0);
set_time_limit(0);
ignore_user_abort(true);
define("FROMEWORK",false);

require 'framwork/MooPHP.php';
require 'config_list2.php';
require 'framwork/libraries/ImageCrop.class.php';
require 'MyCollection.class.php';

register_shutdown_function('PageOnShutdown');
function PageOnShutdown(){
	$msg = error_get_last();
	if(!array_empty($msg)){
		foreach($msg as $k=>$v){
			$str.= "[{$k}]=>{$v} ";
		}
		MooWriteFile(dirname(__file__)."/data/shj_collect.txt",$str,"a+");
		
	}
	MooWriteFile(dirname(__file__)."/data/shj_collect.txt",'[script shutdown]',"a+");
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

if ( ! function_exists( 'exif_imagetype' ) ) {
	function exif_imagetype ( $filename ) {
	    if ( ( list($width, $height, $type, $attr) = getimagesize( $filename ) ) !== false ) {
	        return $type;
	    }
	   return false;
	}
}

$key = basename($_SERVER['PHP_SELF'],'.php');
$status = mc_get($key);
if(!empty($status)){
	pr($status,1);
}else{
	mc_set($key,'shj_collect ing!',86400);
}unset($status);

MooWriteFile(dirname(__file__)."/data/shj_collect.txt","start-->,","a+");

$cookie_file = tempnam('/temp','cookie');
$mc = new MyCollection($cookie_file,'http://110.173.0.18:80',array('X-FORWARDED-FOR:211.71.95.158', 'CLIENT-IP:211.71.95.158'));

$info['url'] = 'http://login.jiayuan.com/dologin.php';
$info['peferer'] = 'http://login.jiayuan.com/';
$info['loginData']['name'] = '13696545523@qq.com';
$info['loginData']['password'] = 'nn2006313';
$info['loginData']['ljg_login'] = 1;
$info['loginData']['channel'] = 0;
$info['loginData']['position'] = 0;
$contents = $mc->webLogin($info);
$i = 0;
while(!$contents){
	$i++;
	if($i>2) break;
	$contents = $mc->webLogin($info);
}
unset($info,$contents,$i);
foreach($sj_caiji as $v){
	for($i=1;$i<=$v['num'];$i++){
		$url = str_replace('p=1', 'p='.$i, $v['url']);
		$info['url'] = $url;
		$contents = $mc->webVisit($info);unset($info);
		$contents = json_decode($contents);
		if($contents)
		foreach($contents->userInfo as $va){
			if(empty($va->image) || !exif_imagetype($va->image) ){continue;}
			$info['url'] = 'http://www.jiayuan.com/'.$va->realUid;		
			//是否存在
			$res = $GLOBALS['_MooClass']['MooMySQL']->getOne("select id from {$dbTablePre}members_collect where source = '{$info['url']}'");
			if($res){continue;}
			$html = $mc->webVisit($info);
			while(!$html){
				$i++;
				if($i>2) break;
				$html = $mc->webVisit($info);
			}
			if(!$html){continue;}
			
			$mbData['source'] = $info['url'];
			//职业
			preg_match('/<b>职业：<\/b>[^<]+/', $html,$str);
			$data['occupation'] = getOccupation($str[0],  $occupationList);
			//单位
			preg_match('/<span>公司类型：<\/span>[^<]+/', $html,$str);
			$data['corptype'] = getCorptype($str[0], $corptypeList);
			//购车
			preg_match('/<b>购车：<\/b>[^<]+/', $html,$str);
			$data['vehicle'] = getVeicle($str[0], $vehicleList);
			//宗教
			preg_match('/<span>宗教信仰：<\/span>[^<]+/', $html,$str);
			$data['religion'] = getReligion($str[0], $religionList);
			//兄弟姐妹

			//是否吸烟
			preg_match('/<span>是否吸烟：<\/span>[^<]+/', $html,$str);
			$data['smoking'] = getSmoking($str[0], $smokingList);
			//是否饮酒
			preg_match('/<span>是否饮酒：<\/span>[^<]+/', $html,$str);
			$data['smoking'] = getDrinking($str[0], $drinkingList);
			//是否想要孩子
			preg_match('/<span>愿意要孩子：<\/span>[^<]+/', $html,$str);
			$data['wantchildren'] = getWantchildren($str[0], $wantchildrenList);
			//昵称
			$data['nickname'] = $va->nickname;
			//用户名
			$data['username'] = getUsername();
			$data['telphone'] = 0;
			$data['password'] = md5('qingyuan07919');
			$data['gender'] = getGender($va->sex, $genderList);
			$mcoData['gender'] = $data['gender'];
			//出生年份
			$data['birthyear'] = 2013-$va->age-1;
			$mcoData['web_members_search'] = $va->age+1;
			//地区
			$data['province'] = $v['province'];
			$mcoData['province'] = $data['province'];
			$data['city'] = $v['city'];
			$mcoData['city'] = $data['city'];
			//婚姻状况
			preg_match('/<b>婚姻：<\/b>[^<]+/', $html,$str);
			if(strpos($str[0], ',')!==false){
				$list = explode(',', $str[0]);
				$marriage = $list[0];
				$children = $list[1];
			}else{
				$marriage = $str[0];
				$children = null;
			}

			$data['marriage'] = getMarriage($marriage,$marriageList);
			//孩子
			$data['children'] = getChildren($children, $childrenList);unset($list,$marriage,$children);
			//学历
			//preg_match('/<b>学历：<\/b>[^<]+/', $html,$str);
			$data['education'] = getEducation($va->education,$educationList);
			//月收入
			preg_match('/<b>月薪：<\/b>[^<]+/', $html,$str);
			if(strpos($str[0], '～')!==false){
				$list = explode('～', $str[0]);
				preg_match('/[0-9]+/', $list[0],$begin);
				preg_match('/[0-9]+/', $list[1],$end);
				$num = ((int)$begin[0]+(int)$end[0])/2;
			}else{
				preg_match('/[0-9]+/', $str[0],$begin);
				$num = $begin[0];
			}
			$data['salary'] = getSalary($num, $salaryList);unset($num,$begin,$end,$list);
			//住房条件
			preg_match('/<b>住房：<\/b>[^<]+/', $html,$str);
			$data['house'] = getHouse($str[0], $houseList);
			//身高
			$data['height'] = $va->height;
			//体重
			preg_match('/<span>体　　重：<\/span>[^<]+/', $html,$str);
			preg_match('/[0-9]+/', $str[0], $str);
			if(isset($str[0]))
			$data['weight'] = $str[0];
			preg_match('/<span>体　　型：<\/span>[^<]+/', $html,$str);
			$data['house'] = getHouse($str[0], $bodyList);
			//生肖
			preg_match('/<span>生　　肖：<\/span>[^<]+/', $html,$str);
			$data['animalyear'] = getAnimailyear($str[0], $animalyearList);
			//星座
			$con_key = array_keys($constellationList);
			foreach($constellationList as $key=>$val){
				if(strpos($html, $key)!==false){
					$data['constellation'] = array_search($key, $con_key) + 1;
					if($data['birthyear']){
						$list = explode(',', $val);
						$mbData['birth'] = rand(strtotime($data['birthyear'].'-'.$list[1]),strtotime($data['birthyear'].'-'.$list[0]));unset($list);
					}
				}
			}
			//籍贯
			preg_match('/<span>籍　　贯：<\/span>[^<]+/', $html,$str);
			$pc = getProivceAndCity($str[0],$provinceCityList);
			$data['hometownprovince'] = getProviceNo($pc['provice']);
			$data['hometowncity'] = getCityNo($pc['city']);unset($pc);
			$data['regdate'] = time();
			$data['updatetime'] = time();
			//民族
			preg_match('/<b>民族：<\/b>[^<]+/', $html,$str);
			$data['nation'] = getStock($str[0],$stockList);

			$mcoData['source'] = $mbData['source'];
			$mcoData['web_members_search'] = serialize($data);
			$uid = dbInsert('members_collect',$mcoData);unset($mcoData);
			if(empty($uid)){
				exit("insert members_collect error!");
			}
			//$mbData['uid'] = $uid;
			$mbData['callno'] = 0;
			$mbData['regip'] = getUserIp();

			//形象照
			$info = pathinfo($va->image);
			$photo = dirname(__file__).'/pic_collect/'.$info['basename'];
			if(!is_dir($photo))
				MooMakeDir(dirname($photo));
			$i = 1;
			while(!copy($va->image, $photo)){
				if($i>3){
					break;
				}
				$i++;
			}unset($i);
			if(is_file($photo)){
				$pic['imgurl'] = $photo;
				$pic['pic_date'] = date('Y/m/d',$_SERVER['REQUEST_TIME']);
				$pic['pic_name'] = $info['basename'];
				$pic['syscheck'] = 1;
				$pic['isimage'] = 1;
				$pData[] = $pic;unset($pic);
			}unset($info);

			$mbData['currentprovince'] = $data['province'];
			$mbData['currentcity'] = $data['city'];
			$fp = 'a:3:{i:0;a:1:{i:'.$data['province'].';s:8:"'.$data['city'].'";}i:1;a:1:{i:0;s:1:"0";}i:2;a:1:{i:0;s:1:"0";}}';
			$mbData['friendprovince'] = $fp;unset($fp);
			$mcoData['web_members_base'] = serialize($mbData);
			dbUpdate('members_collect',$mcoData,array('id'=>$uid));unset($mcoData);
			//择偶表
			//$mcData['uid'] = $uid;
			$mcData['gender'] = $data['gender']==1?0:1;
			preg_match('/[0-9]+-[0-9]+岁/', $va->matchCondition,$str);
			if($str[0])
			preg_match_all('/[0-9]+/', $str[0], $str);
			if(!array_empty($str)){
				$mcData['age1'] = $str[0][0];
				$mcData['age2'] = $str[0][1];
			}
			preg_match('/[0-9]+-[0-9]+cm/', $va->matchCondition,$str);
			if($str[0])
			preg_match_all('/[0-9]+/', $str[0], $str);
			if(!array_empty($str)){
				$mcData['height1'] = $str[0][0];
				$mcData['height2'] = $str[0][1];
			}
			$mcData['hasphoto']=1;
			$mcData['marriage'] = getMarriage($va->matchCondition,$marriageList);
			$mcData['education'] = getEducation($va->matchCondition,$educationList);
			$pc = getProivceAndCity($va->matchCondition,$provinceCityList);
			$mcData['workprovince'] = getProviceNo($pc['provice']);
			$mcData['workcity'] = getCityNo($pc['city']);unset($pc);
			$mcData['smoking'] = rand(1,10)>9?1:0;
			$mcData['drinking'] = rand(1,10)>9?1:0;
			$mcoData['web_members_choice'] = serialize($mcData);unset($mcData);
			dbUpdate('members_collect',$mcoData,array('id'=>$uid));unset($mcoData);
			//内心独白
			//$miData['uid'] = $uid;
			$miData['introduce'] = $va->shortnote;
			$miData['introduce_check'] = 1;
			$miData['introduce_pass'] = 1;
			$mcoData['web_members_introduce'] = serialize($miData);unset($miData);
			dbUpdate('members_collect',$mcoData,array('id'=>$uid));unset($mcoData);
			//登录
			//$maData['uid'] = $uid;
			$maData['real_lastvisit'] = $_SERVER['REQUEST_TIME'];
			$maData['ip'] = $mbData['regip'];
			$mcoData['web_member_admininfo'] = serialize($maData);unset($maData);
			dbUpdate('members_collect',$mcoData,array('id'=>$uid));unset($mcoData);
			//web_members_login
			$mlData['lastip'] = $mbData['regip'];
			$mlData['lastvisit'] = $_SERVER['REQUEST_TIME'];
			$mlData['last_login_time'] = $_SERVER['REQUEST_TIME'];
			$mlData['login_meb'] = 1;
			$mcoData['web_members_login'] = serialize($mlData);unset($mlData);
			dbUpdate('members_collect',$mcoData,array('id'=>$uid));unset($mcoData);
			//相册
			preg_match_all('/<li><div class="img_box">[\s\S]*?<\/li>/', $html,$str);
			if(!array_empty($str[0])){
				$photos = dirname(__file__).'/pic_collect/';
				if(!is_dir($photos))
					MooMakeDir($photos);
				foreach ($str[0] as $key => $val) {
					preg_match('/<img(.*)src="([^"]+)"[^>]+>/isU', $val, $pics);
					if($pics[2] && exif_imagetype($pics[2])){
						$pathParts = pathinfo($pics[2]);
						$pathParts['filename'] = substr($pathParts['filename'], 0,-1).'d';
						$photosname= $pathParts['filename'].'.'.$pathParts['extension'];
						$date = date('Y/m/d',$_SERVER['REQUEST_TIME']);
						$imgurl = $photos.$photosname;
						$b = 1;
						$pics[2] = str_replace($pathParts['basename'], $photosname, $pics[2]);
						while(!copy($pics[2],$imgurl)){
							if($b>3){continue 2;}
							$b++;
						}unset($b);
						cropImage($imgurl,$imgurl);
						//$pic['uid'] = $uid;
						$pic['imgurl'] = $imgurl;
						$pic['pic_date'] = $date;
						$pic['pic_name'] = $photosname;
						$pic['syscheck'] = 1;
						$pData[] = $pic;unset($pic);
					}
				}unset($photos);
				$mcoData['web_pic'] = serialize($pData);unset($pData);
				dbUpdate('members_collect',$mcoData,array('id'=>$uid));unset($mcoData);
			}
			//诚信
			$cData['uid'] = $uid;
			$cData['email'] = 'yes';
			if(rand(0,9)>=5){
				$cData['identity_check'] = 3;
			}
			$cData['telphone'] = '12345678900';
			$mcoData['web_certification'] = serialize($cData);unset($cData);
			dbUpdate('members_collect',$mcoData,array('id'=>$uid));
			//积分
			MooWriteFile(dirname(__file__)."/data/shj_collect.txt","{$uid},","a+");
			if($_SERVER['HTTP_HOST']!='www.07919.com') {pr($uid,1);}
			unset($html,$str,$uid,$data,$mbData);			
		}
		unset($info,$contents);
	}
}

mc_unset($key);
unset($urlArr,$key,$mc);
unlink($cookie_file);
MooWriteFile(dirname(__file__)."/data/shj_collect.txt","<--end,","a+");
exit();

function getUsername(){
	$len = rand(1,6);
	$str = "abcdefghijklmnopqrstuvwxyz1234567890";
	$proName = 'jy';
	for($i=0;$i<$len;$i++){
		$proName.= substr($str,rand(0,35),1);
	}
	$proName.= rand(0,9999);
	$mail = array('qq.com','foxmail.com','sina.com','sina.cn','126.com','163.com','yahoo.com','21cbh.com','gmail.com','hotmail.com');
	$username = $proName.'@'.$mail[rand(0,9)];
	return $username;
}
unset($mc);
unlink($cookie_file);

function getProivceAndCity($str,$provinceCityList){
	$provices = array_keys($provinceCityList);
	foreach($provices as $v){
		if(strpos($str,$v)!==false){
			$data['provice'] = $v;
		}
	}
	if(isset($data['provice'])){
		$city = $provinceCityList[$data['provice']];
		$str = str_replace_occurance($data['provice'],'',$str,1);
	}
	
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

function str_replace_occurance($search, $replace, $subject, $occurance) {
   $pos = 0;
   for ($i = 0; $i <= $occurance; $i++) {
       $pos = strpos($subject, $search, $pos);
   }
   return substr_replace($subject, $replace, $pos, strlen($search));
}

function getUserIp(){
	//生成随机IP地址  //start
	$ip2id= round(rand(600000, 2550000) / 10000); //第一种方法，直接生成 
	$ip3id= round(rand(600000, 2550000) / 10000); 
	$ip4id= round(rand(600000, 2550000) / 10000); 
	//下面是第二种方法，在以下数据中随机抽取 
	$arr_1 = array("218","218","66","66","218","218","60","60","202","204","66","66","66","59","61","60","222","221","66","59","60","60","66","218","218","62","63","64","66","66","122","211"); 
	$randarr= mt_rand(0,count($arr_1)-1); 
	$ip1id = $arr_1[$randarr];
	$ip = $ip1id.".".$ip2id.".".$ip3id.".".$ip4id;
	unset($ip2id,$ip3id,$ip4id,$arr_1,$randarr,$ip1id);
	return $ip;
}
?>