<?php
error_reporting(0);
ignore_user_abort(true);
set_time_limit(21600);

date_default_timezone_set('Asia/Shanghai');
define("FROMEWORK",false);

require 'framwork/MooPHP.php';
require 'config_list2.php';
require 'framwork/libraries/ImageCrop.class.php';
require 'MyCollection.class.php';

register_shutdown_function('PageOnShutdown');
function PageOnShutdown(){
	$msg = error_get_last();
	if(!array_empty($msg)){
		$str ='';
		foreach($msg as $k=>$v){
			$str.= "[{$k}]=>{$v} ";
		}
		MooWriteFile(dirname(__file__)."/data/zha_collect_auto.txt",$str,"a+");
		
	}
	MooWriteFile(dirname(__file__)."/data/zha_collect_auto.txt",'[script shutdown'.date('Y-m-d H:i:s').']',"a+");
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

run('zha_collect_auto');

global $_MooClass,$dbTablePre;
$selSql = "select nickname from {$dbTablePre}members_search where usertype=1 and regdate>1364745600 and nickname!='' ";
$nickData[] = $_MooClass['MooMySQL']->getAll($selSql." and gender=0");
$nickData[] = $_MooClass['MooMySQL']->getAll($selSql." and gender=1");
unset($selSql);

$cookie_file = tempnam('/temp','cookie');
$mc = new MyCollection($cookie_file,$proxyIp,array('X-FORWARDED-FOR:211.71.95.158', 'CLIENT-IP:211.71.95.158'));
$loginInfo['url'] = 'http://profile.zhenai.com/login/loginactionindex.jsps';
$loginInfo['peferer'] = 'http://profile.zhenai.com/';
$loginInfo['loginData']['loginInfo'] = '13696545523';
$loginInfo['loginData']['password'] = '120120';
$loginInfo['loginData']['loginmode'] = 2;
$loginInfo['loginData']['rememberpassword'] = 1;

MooWriteFile("data/zha_collect_auto.txt","script auto start,","a+");

$num = mc_get('zha_collect_auto_page');
if(!is_numeric($num) || $num <0 ){
	$num = 461;
}
if(isset($_GET['num']) && is_numeric($_GET['num']) ){
	$num = trim($_GET['num']);
}
if(!is_numeric($num)) exit("参数错误");
while(!isset($collectList[$num])){
	if($num<0) break;
	$num--;
}
mc_set('zha_collect_auto_page',--$num);
if($num<0) exit("script end");
if(isset($collectList[$num])){
	MooWriteFile("data/zha_collect_auto.txt","loading {$num}[".date("Y-m-d H:i:s")."],","a+");
	foreach($collectList[$num] as $pc_k => $pc_v){
		run('zha_collect_auto');
		$pcs = explode(',', $pc_k);
		$url = 'http://search.zhenai.com/search/getfastmdata.jsps?condition=2&photo=1&agebegin=18&ageend=26&workcityprovince='.$pcs[0].'&workcitycity='.$pcs[1];
		$mc->webLogin($loginInfo);
		foreach($pc_v as $ke => $va){
			run('zha_collect_auto');
			$gender = $ke;
			$mc_num = $_MooClass['MooMySQL']->getOne("select count(*) as c from {$dbTablePre}members_collect where province = {$pcs[0]} and city= {$pcs[1]} and gender = {$ke}");
			if($ke==0 && $mc_num['c']>200){continue;}
			elseif($ke==1 && $mc_num['c']>600){continue;}
			$b = 0;
			for ($c=1; $c <=ceil($va/10) ; $c++) {
				run('zha_collect_auto');
				$listUrl = $url.'&gender='.$ke.'&currentpage='.$c;
				$contents = $mc->webVisit(array('url'=>$listUrl));
				$i = 0;
				while(!$contents){
					if($i>2){
						MooWriteFile("data/zha_collect_auto.txt","{$listUrl}['false'],","a+");
						continue 2;
					}
					$contents = $mc->webVisit(array('url'=>$listUrl));$i++;
				}
				preg_match_all('/http:\/\/([a-z\.]+\/)+getmemberdata.jsps\?memberid=[0-9]+/',$contents,$list,PREG_SET_ORDER);
				if(array_empty($list)){
					MooWriteFile("data/zha_collect_auto.txt","{$listUrl}no list,","a+");
					continue 2;
				}unset($listUrl);
				foreach($list as $val){
					$lisArr[] = $val[0];
				}unset($list);
				$lisArr = array_unique($lisArr);
				foreach($lisArr as $key=>$val){
					//脚本控制器
					run('zha_collect_auto');
					//判断是否存在
					if(dbHas('members_collect',array('source' => $val) ) ){continue;}
					$time = time();
					$contents = $mc->webVisit(array('url' => $val));
					$i = 0;
					while(!$contents){
						if($i>2){MooWriteFile("data/zha_collect_auto.txt","{$val}['false'],","a+");
							continue 2;
						}
						$contents = $mc->webVisit(array('source' => $val));
						$i++;
					}unset($i);
					//无形象照跳过
					preg_match('/objDefalutPhoto(.*)/',$contents,$str);
					preg_match('/http:\/\/(.*)(jpg|gif|png|jpeg|JPG|GIF|PNG|JPEG)/',$str[0],$str);
					if(empty($str[0]) || !exif_imagetype($str[0])){continue;}
					$mbData['source'] = $val;
					//职业
					foreach($occupationList as $k=>$v2){
						if(preg_match('/<dd>'.iconv('utf-8','gbk',$k).'/',$contents,$str))
							$data['occupation'] = $v2;
					}
					//单位
					foreach($corptypeList as $k=>$v2){
						if(preg_match('/<dd>'.iconv('utf-8','gbk',$v2).'/',$contents,$str))
							$data['corptype'] = $k;
					}
					//购车
					foreach($vehicleList as $k=>$v2){
						if(preg_match('/<dd>'.iconv('utf-8','gbk',$v2).'/',$contents,$str))
							$data['vehicle'] = $k;
					}
					//宗教
					foreach($religionList as $k=>$v2){
						if(preg_match('/<dd>'.iconv('utf-8','gbk',$k).'/',$contents,$str))
							$data['religion'] = $v2;
					}
					//兄弟姐妹
					preg_match('/'.iconv('utf-8','gbk','兄弟姐妹：').'<\/strong>[^<]*<\/dt>[^<]*<dd>[0-9]+/',$contents,$str);
					preg_match('/[0-9]+/',$str[0],$str);
					$data['family'] = $str[0];
					//吸烟
					foreach($smokingList as $k=>$v2){
						if(preg_match('/<dd>'.iconv('utf-8','gbk',$k).'/',$contents,$str))
							$data['smoking'] = $v2;
					}
					//喝酒
					foreach($drinkingList as $k=>$v2){
						if(preg_match('/<dd>'.iconv('utf-8','gbk',$k).'/',$contents,$str))
							$data['drinking'] = $v2;
					}
					//想要孩子
					foreach($wantchildrenList as $k=>$v2){
						if(preg_match('/<dd>'.iconv('utf-8','gbk',$k).'/',$contents,$str))
							$data['wantchildren'] = $v2;
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
					$mcoData['gender'] = $data['gender'];
					//出生年份
					preg_match('/[0-9]+'.iconv('UTF-8','GBK','岁').'/',$contents,$str);
					preg_match('/[0-9]+/',$str[0],$str);
					if(is_numeric($str[0]) && $str[0]>0){
						$data['birthyear'] = date('Y',strtotime('-'.$str[0].'year'));
						$mcoData['age'] = (int)$str[0];
					}
					//地区
					preg_match('/'.iconv('UTF-8','GBK','住在').'[^<]*<strong[^>]+>[^<]+/',$contents,$str);
					$str = str_replace('住在','',iconv('GBK','UTF-8',$str[0]));
					$str = preg_replace('/<strong[^>]+>/','',$str);
					$pc = getProvinceCity($str,$provinceCityList);
					$data['province'] = getProviceNo($pc['provice']);
					$data['city'] = getCityNo($pc['city']);
					$mcoData['province'] = $data['province'];
					$mcoData['city'] = $data['city'];
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
					$pc = getProvinceCity($str[1],$provinceCityList);
					$data['hometownprovince'] = getProviceNo($pc['provice']);
					$data['hometowncity'] = getCityNo($pc['city']);
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
					
					//members_search
					$mcoData['source'] = $mbData['source'];
					$mcoData['web_members_search'] = serialize($data);
					$uid = dbInsert('members_collect',$mcoData);unset($mcoData);
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
					$mbData['callno'] = 0;
					$mbData['regip'] = $ip;
					//形象照		
					preg_match('/objDefalutPhoto(.*)/',$contents,$str);
					preg_match('/http:\/\/(.*)(jpg|gif|png|jpeg|JPG|GIF|PNG|JPEG)/',$str[0],$str);
					if($str[0] && @exif_imagetype($str[0])){
						$info = pathinfo($str[0]);
						$photo = 'pic_collect/'.$info['basename'];
						if(!is_dir($photo))
							MooMakeDir(dirname($photo));
						$tmp_url = str_replace('_3', '_2', $str[0]);
						$str[0] = @exif_imagetype($tmp_url)?$tmp_url:$str[0];unset($tmp_url);
						$i = 1;
						while(!copy($str[0], $photo)){
							if($i>3){
								break;
							}
							$i++;
						}unset($i);
						if(is_file($photo)){
							cropImg($photo,$photo);
							$pic['imgurl'] = $photo;
							$pic['pic_date'] = date('Y/m/d',$_SERVER['REQUEST_TIME']);
							$pic['pic_name'] = $info['basename'];
							$pic['syscheck'] = 1;
							$pic['isimage'] = 1;
							$pData[] = $pic;unset($pic);
						}unset($info);
					}
					if($data['province'])
						$mbData['currentprovince'] = $data['province'];
					if($data['city'])
						$mbData['currentcity'] = $data['city'];
					if($data['province'] && $data['city']){
						$fp = 'a:3:{i:0;a:1:{i:'.$data['province'].';s:8:"'.$data['city'].'";}i:1;a:1:{i:0;s:1:"0";}i:2;a:1:{i:0;s:1:"0";}}';
						$mbData['friendprovince'] = $fp;
					}
					$mcoData['web_members_base'] = serialize($mbData);
					//web_members_choice 择偶表
					//理想对象
					//$mcData['uid'] = $uid;
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
					foreach($bodyList as $k=>$v2){
						if(preg_match('/<dd>'.iconv('utf-8','gbk',$v2).'/',$friendStr[0],$str))
							$mcData['body'] = $k;
					}
					$mcData['hasphoto']=1;
					foreach($marriageList as $k=>$v2){
						if(strpos($friendStr[0],iconv('UTF-8','GBK',$v2))!==false)
							$mcData['marriage'] = $k;
					}
					foreach($educationList as $k=>$v2){
						if(strpos($friendStr[0],iconv('UTF-8','GBK',$v2))!==false)
							$mcData['education'] = $k;
					}
					foreach($occupationList as $k=>$v2){
						if(preg_match('/<dd>'.iconv('utf-8','gbk',$k).'/',$friendStr[0],$str))
							$mcData['occupation'] = $v2;
					}
					preg_match('/'.iconv('UTF-8','GBK','工作地区：').'(.*)/',$friendStr[0],$str);
					preg_match('/title="(.*)"/',$str[0],$str);
					$pc = getProvinceCity($str[1],$provinceCityList);
					$mcData['workprovince'] = getProviceNo($pc['provice']);
					$mcData['workcity'] = getCityNo($pc['city']);
					$mcData['smoking'] = rand(1,10)>9?1:0;
					$mcData['drinking'] = rand(1,10)>9?1:0;
					foreach($wantchildrenList as $k=>$v2){
						if(preg_match('/<dd>'.iconv('utf-8','gbk',$k).'/',$friendStr[0],$str))
							$mcData['wantchildren'] = $v2;
					}
					
					foreach($salaryList as $k=>$v2){
						if(strpos($v2,'-')!==false){
							$a = explode('-',$v2);
							$v2 = ($a[0]-1).'&nbsp;-&nbsp;'.$a[1];
						}
						if(preg_match('/<dd>'.iconv('utf-8','gbk',$v2).'/',$friendStr[0],$str))
							$mcData['salary'] = $k;
					}
					$mcoData['web_members_choice'] = serialize($mcData);unset($mcData);
					
					//web_members_introduce 独白
					preg_match('/<li[^>]+folded(\sunfolded)?[^>]+>[^<]+</',$contents,$str);
					$introduce = strip_tags($str[0]);
					$miData['introduce'] = empty($introduce)?'':iconv('GBK','UTF-8',$introduce);
					$miData['introduce_pass'] = empty($introduce)?0:1;
					$miData['introduce_check'] = 1;
					$mcoData['web_members_introduce'] = serialize($miData);unset($miData);
					//web_member_admininfo
					
					$maData['real_lastvisit'] = $time;
					$maData['finally_ip'] = $ip;
					$mcoData['web_member_admininfo'] = serialize($maData);unset($maData);
					//web_members_login
					$mlData['lastip'] = $ip;
					$mlData['lastvisit'] = $time;
					$mlData['last_login_time'] = $time;
					$mlData['login_meb'] = 1;
					$mcoData['web_members_login'] = serialize($mlData);unset($mlData);
					//用户相册
					preg_match_all('/<li>\s*<p>\s*<img[^>]+>/',$contents,$str);
					if(!array_empty($str[0])){
						$i=0;
						foreach($str[0] as $v2){
							preg_match('/data-big-img="[^"]+"/',$v2,$str);
							preg_match('/http:\/\/.+\.(jpg|gif|png|jpeg|JPG|GIF|PNG|JPEG)/',$str[0],$str);
							$photos = 'pic_collect/';
							if($str[0] && exif_imagetype($str[0])){
								if(!is_dir($photos))
									MooMakeDir($photos);
								$pathParts = pathinfo($str[0]);
								$photosname= $pathParts['basename'];
								$date = date('Y/m/d');
								$imgurl = $photos.$photosname;
								if(!@copy($str[0],$imgurl)){
									continue;
								}else{
									cropImg($imgurl,$imgurl);
								}
								$i++;
								$pic['imgurl'] = $imgurl;
								$pic['pic_date'] = $date;
								$pic['pic_name'] = $photosname;
								$pic['syscheck'] = 1;
								$pData[] = $pic;unset($pic);
							}unset($v2);
						}
					}
					$mcoData['web_pic'] = serialize($pData);unset($pData);
					//诚信认证
					$cData['uid'] = $uid;
					$cData['email'] = 'yes';
					if(rand(0,9)>=5){
						$cData['identity_check'] = 3;
					}
					$cData['telphone'] = '12345678900';
					$mcoData['web_certification'] = serialize($cData);unset($cData);
					dbUpdate('members_collect',$mcoData,array('id'=>$uid));
					$b++;
					if($gender==0 && $mc_num['c']+$b > 200) continue 3;
					if($gender==1 && $mc_num['c']+$b >600) continue 3;
					//日志
					MooWriteFile(dirname(__file__)."/data/zha_collect_auto.txt","{$uid}[".($mc_num['c']+$b)."],","a+");
					if($_SERVER['HTTP_HOST']!='www.07919.com'){pr($uid,1);}
					unset($time,$contents,$str,$data,$mbData,$pc,$mz,$ip,$res,$imgurl,$photosname,$pathParts,$photos,$date,$uid,$introduce,$mcoData);
				}unset($lisArr);
			}
			unset($mc_num,$gender);
		}
		unset($url,$pcs);
	}
	MooWriteFile("data/zha_collect_auto.txt","endding {$num}[".date("Y-m-d H:i:s")."],","a+");
}
$num--;

MooWriteFile("data/zha_collect_auto.txt","script auto end,","a+");
unlink($cookie_file);
$_MooClass['MooMySQL']->close();
unset($_MooClass,$dbTablePre,$nickData,$info,$loginInfo);
//header("location:".$_SERVER['PHP_SELF']."?num=".$num);
exit('script auto end');

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
?>