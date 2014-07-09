<?php
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(0);
ignore_user_abort(true);
set_time_limit(21600);

date_default_timezone_set('Asia/Shanghai');
define("FROMEWORK",false);

require 'framwork/MooPHP.php';
require 'config_list2.php';
require 'framwork/libraries/ImageCrop.class.php';
require 'MyCollection.class.php';

function PageOnShutdown(){
	$msg = error_get_last();
	if(!array_empty($msg)){
		$str ='';
		foreach($msg as $k=>$v){
			$str.= "[{$k}]=>{$v} ";
		}
        unlink($cookie_file);
		MooWriteFile(dirname(__file__)."/data/jia_collect_auto.txt",$str,"a+");
		
	}
	MooWriteFile(dirname(__file__)."/data/jia_collect_auto.txt",'[script shutdown'.date('Y-m-d H:i:s').']',"a+");
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
run('jia_collect_auto');
$logFile = 'data/jia_collect_auto.txt';

global $_MooClass,$dbTablePre;
$selSql = "select nickname from {$dbTablePre}members_search where usertype=1 and regdate>1364745600 and nickname!='' ";
$nickData[] = $_MooClass['MooMySQL']->getAll($selSql." and gender=0");
$nickData[] = $_MooClass['MooMySQL']->getAll($selSql." and gender=1");
unset($selSql);

$cookie_file = tempnam('/temp','cookie');
$mc = new MyCollection($cookie_file,$proxyIp,array('X-FORWARDED-FOR:211.71.95.158', 'CLIENT-IP:211.71.95.158'));

$loginInfo['url'] = 'http://login.jiayuan.com/dologin.php';
$loginInfo['peferer'] = 'http://login.jiayuan.com/';
$loginInfo['loginData']['name'] = '13696545523@qq.com';
$loginInfo['loginData']['password'] = 'nn2006313';
$loginInfo['loginData']['ljg_login'] = 1;
$loginInfo['loginData']['channel'] = 0;
$loginInfo['loginData']['position'] = 0;
$contents = $mc->webLogin($loginInfo);

$l = mc_get('jia_collect_auto_page');
if(isset($_GET['page']) && is_numeric($_GET['page'])){
    $l = (int)trim($_GET['page']);
}
$l = empty($l)?1:$l;

run('jia_collect_auto');
MooWriteFile($logFile,"loading {$l}[".date("Y-m-d H:i:s")."],",'a+');
$urls = (array('http://search.jiayuan.com/v2/search.php?sex=f&key=&stc=3%3A130.260%2C1%3A0%2C23%3A1%2C2%3A19.26&sn=default&sv=1&p='.$l.'&f=select','http://search.jiayuan.com/v2/search.php?sex=m&key=&stc=3%3A130.260%2C1%3A0%2C23%3A1%2C2%3A19.26&sn=default&sv=1&p='.$l.'&f=select'));
foreach($urls as $k=>$v){
    run('jia_collect_auto');
    $contents = $mc->webVisit(array('url'=>$v));
    $i=0;
    while(!$contents){
        if($i>2){
            MooWriteFile($logFile,$v.'[false],','a+'); continue 2;
        }
        $contents = $mc->webVisit(array('url'=>$v));$i++;
    }unset($i);
    $contents = json_decode($contents);
    if($l>$contents->pageTotal) {MooWriteFile($logFile, $l.'>'.$contents->pageTotal.',','a+');continue;}
    foreach($contents->userInfo as $va){
        run('jia_collect_auto');
        if(empty($va->image) || !exif_imagetype($va->image) ){MooWriteFile($logFile,$va->image.'[false],','a+');continue;}
        $info['url'] = 'http://www.jiayuan.com/'.$va->realUid;      
        //是否存在
        if(dbHas('members_collect',array('source' => $info['url']) ) ){continue;}
        $html = $mc->webVisit($info);
        $i = 0;
        while(!$html){
            $i++;
            if($i>2){
                MooWriteFile($logFile,$info['url'].'[false],','a+'); continue 2;
            }
            $html = $mc->webVisit($info);
        }unset($i);

        $time = time();
        $mbData['source'] = $info['url'];
        $mcoData['source'] = $info['url'];
        //地区
        preg_match_all('/<h2><a href="#detail"\s\w+[^<]+<\/a>(.*)<\/h2>/', $html, $str);
        if(isset($str[1][0])){
            $strs = explode('，', $str[1][0]);
            $str = $strs[3];unset($strs);
        }
        if(empty($str)){continue;}
        $w_pc = getProvinceCity($str,$provinceCityList);
        $data['province'] = getProviceNo($w_pc['provice']);
        $data['city'] = getCityNo($w_pc['city']);
        $data['city'] = empty($data['city'])?0:$data['city'];
        $mcoData['province'] = $data['province'];
        $mcoData['city'] = $data['city'];
        $data['gender'] = getGender($va->sex, $genderList);
        $mcoData['gender'] = $data['gender'];
        if(empty($data['province'])) { MooWriteFile($logFile,$str.'[false],','a+');continue;}
        $key = 'jia_'.$data['province'].'_'.$data['city'].'_'.$data['gender'];
        $count = mc_get($key);
        if(!$count){
            $count = $_MooClass['MooMySQL']->getOne("select count(*) as c from {$dbTablePre}members_collect where province = {$data['province']} and city = {$data['city']} and gender={$data['gender']} and sendNum=0");
            if($count['c']>600){mc_set($key,$count);}
        }
        if($count['c']>600){
            MooWriteFile($logFile,$w_pc['provice'].$w_pc['city'].'['.$count['c'].'],','a+');continue;
        }
        unset($count,$w_pc,$key);
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
        preg_match('/<span>家中排行：<\/span>[^<]+/', $html,$str);
        $data['family'] = getFamily($str[0], $familyList);
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
        if(checkNickName($va->nickname)){
            $data['nickname'] = $va->nickname;
        }else{
            $data['nickname'] = $nickData[$k][rand(0,(count($nickData[$gender])-1))]['nickname'];
        }
        preg_match('/<span>是否饮酒：<\/span>[^<]+/', $html,$str);
        //用户名
        $data['username'] = getUsername();
        $data['telphone'] = 0;
        $data['password'] = md5('qingyuan07919');
        //出生年份
        $data['birthyear'] = 2013-$va->age-1;
        $mcoData['age'] = $va->age+1;
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
        $data['body'] = getBody($str[0], $bodyList);
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
        $pc = getProvinceCity($str[0],$provinceCityList);
        $data['hometownprovince'] = getProviceNo($pc['provice']);
        $data['hometowncity'] = getCityNo($pc['city']);unset($pc);
        $data['regdate'] = time();
        $data['updatetime'] = time();

        $data['usertype'] = 3;
        $data['sid'] = 1;
        //民族
        preg_match('/<b>民族：<\/b>[^<]+/', $html,$str);
        $data['nation'] = getStock($str[0],$stockList);
        $mcoData['web_members_search'] = serialize($data);
        //$uid = dbInsert('members_collect',$data);
        preg_match('/<span>喜欢美食：<\/span>[^<]+/', $html,$str);
        $mbData['fondfood'] = getFondfood($str[0], $fondfoodList);
        preg_match('/<span>喜欢旅游：<\/span>[^<]+/', $html,$str);
        $mbData['fondplace'] = getFondplace($str[0], $fondplaceList);
        preg_match('/<span>业余爱好：<\/span>[^<]+/', $html,$str);
        $mbData['fondactivity'] = getFondactivity($str[0], $fondactivityList);
        preg_match('/<span>喜欢运动：<\/span>[^<]+/', $html,$str);
        $mbData['fondsport'] = getFondsport($str[0], $fondsportList);
        preg_match('/<span>喜爱的影片：<\/span>[^<]+/', $html,$str);
        $mbData['fondprogram'] = getFondprogram($str[0], $fondprogramList);
        $mbData['callno'] = 0;
        $mbData['regip'] = getUserIp();

        //形象照
        $img_info = pathinfo($va->image);
        $photo = 'pic_collect/'.$img_info['basename'];
        if(!is_dir($photo)) MooMakeDir(dirname($photo));
        $i = 1;
        while(!copy($va->image, $photo)){
            if($i>3){break;}
            $i++;
        }unset($i);
        if(is_file($photo)){
            $pic['imgurl'] = $photo;
            $pic['pic_date'] = date('Y/m/d');
            $pic['pic_name'] = $img_info['basename'];
            $pic['syscheck'] = 1;
            $pic['isimage'] = 1;
            $pData[] = $pic;unset($pic);
        }unset($img_info);
        $mbData['currentprovince'] = $data['province'];
        $mbData['currentcity'] = $data['city'];
        $fp = 'a:3:{i:0;a:1:{i:'.$data['province'].';s:8:"'.$data['city'].'";}i:1;a:1:{i:0;s:1:"0";}i:2;a:1:{i:0;s:1:"0";}}';
        $mbData['friendprovince'] = $fp;unset($fp);
        $mcoData['web_members_base'] = serialize($mbData);
        //择偶表
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
        $pc = getProvinceCity($va->matchCondition,$provinceCityList);
        $mcData['workprovince'] = getProviceNo($pc['provice']);
        $mcData['workcity'] = getCityNo($pc['city']);unset($pc);
        $mcData['smoking'] = rand(1,10)>9?1:0;
        $mcData['drinking'] = rand(1,10)>9?1:0;
        $mcoData['web_members_choice'] = serialize($mcData);unset($mcData);
        //内心独白
        $miData['introduce'] = $va->shortnote;
        $miData['introduce_check'] = 1;
        $miData['introduce_pass'] = 1;
        $mcoData['web_members_introduce'] = serialize($miData);unset($miData);
        //登录
        $maData['real_lastvisit'] = $_SERVER['REQUEST_TIME'];
        $maData['finally_ip'] = $mbData['regip'];
        $mcoData['web_member_admininfo'] = serialize($maData);unset($maData);
        //web_members_login
        $mlData['lastip'] = $ip;
        $mlData['lastvisit'] = $time;
        $mlData['last_login_time'] = $time;
        $mlData['login_meb'] = 1;
        $mcoData['web_members_login'] = serialize($mlData);unset($mlData);
        //相册
        preg_match_all('/<li><div class="img_box">[\s\S]*?<\/li>/', $html,$str);
        if(!array_empty($str[0])){
            $photos = 'pic_collect/';
            MooMakeDir($photos);
            $i = 0;
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
                    $i++;
                    $pic['imgurl'] = $imgurl;
                    $pic['pic_date'] = $date;
                    $pic['pic_name'] = $photosname;
                    $pic['syscheck'] = 1;
                    $pData[] = $pic;unset($pic);
                    unset($pathParts,$photosname,$imgurl,$pics);
                }
            }unset($photos);
        }
        $mcoData['web_pic'] = serialize($pData);unset($pData);
        //诚信
        $cData['email'] = 'yes';
        if(rand(0,9)>=5){
            $cData['identity_check'] = 3;
        }
        $cData['telphone'] = '12345678900';
        $mcoData['web_certification'] = serialize($cData);unset($cData);
        $id = dbInsert('members_collect',$mcoData);
        //日志
        MooWriteFile($logFile,"{$id},","a+");
        if($_SERVER['HTTP_HOST']!='www.07919.com'){pr($id,1);}
        unset($html,$str,$id,$data,$mbData,$mcoData,$time);
    }
}

mc_set('jia_collect_auto_page',++$l);
MooWriteFile($logFile,"script end[".date('Y-m-d H:i:s')."],",'a+');
unlink($cookie_file);
$_MooClass['MooMySQL']->close();
unset($l,$_MooClass,$dbTablePre,$page,$logFile,$nickData);

function getUsername(){
    $len = rand(1,6);
    $str = "abcdefghijklmnopqrstuvwxyz1234567890";
    $proName = 'jia';
    for($i=0;$i<$len;$i++){
        $proName.= substr($str,rand(0,35),1);
    }
    $proName.= rand(0,9999);
    $mail = array('qq.com','foxmail.com','sina.com','sina.cn','126.com','163.com','yahoo.com','21cbh.com','gmail.com','hotmail.com');
    $username = $proName.'@'.$mail[rand(0,9)];
    return $username;
}

function checkNickName($str){
    if(empty($str) || !is_string($str)) return false;
    $preg_keys = array('/会员[0-9]+/');
    foreach ($preg_keys as $v) {
        if(preg_match($v,$str)){
            return false;
        }
    }
    if(preg_match('/会员[0-9]+/',$str)){
        return false;
    }
    $keys  = array('佳缘','世纪');
    foreach($keys as $v){
        if(strpos($str,$v)!==false){
            return false;
        }
    }
    return true;
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