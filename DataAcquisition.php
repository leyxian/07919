<?php
//error_reporting(0); 
ignore_user_abort(true); 
set_time_limit(0); 
ini_set('memory_limit','1024M'); 
date_default_timezone_set('Asia/Shanghai'); 
define("FROMEWORK",false); 
require 'framwork/MooPHP.php'; 
require 'MyCollection.class.php'; 
require 'framwork/libraries/ImageCrop.class.php'; 
require 'config_list2.php'; 
register_shutdown_function('PageOnShutdown'); 
function PageOnShutdown(){ 
    $msg = error_get_last(); 
    if(!array_empty($msg)){ 
        $str =''; 
        foreach($msg as $k=>$v){ 
            $str.= "[{$k}]=>{$v} "; 
        } 
        $str.= "[f]=>{$_GET['f']}"; 
        ll("aataacquisition",$str); 
    } 
    ll($_GET['f'],$_GET['f']."[shutdown_".date('Y-m-d H:i:s')."]\r\n"); 
} 
if( !function_exists('error_get_last') ) { 
    set_error_handler( create_function( '$errno,$errstr,$errfile,$errline,$errcontext', '
            global $__error_get_last_retval__;
            $__error_get_last_retval__ = array(
                \'type\'        => $errno,
                \'message\'        => $errstr,
                \'file\'        => $errfile,
                \'line\'        => $errline
            );
            return false;
        ' ) ); 
    function error_get_last() { 
        global $__error_get_last_retval__; 
        if( !isset($__error_get_last_retval__) ) { return null; } 
        return $__error_get_last_retval__; 
    } 
} 
    if ( ! function_exists( 'exif_imagetype' ) ) { function exif_imagetype ( $filename ) { if ( ( list($width, $height, $type, $attr) = getimagesize( $filename ) ) !== false ) { return $type; } return false; } } 
class DataAcquisition { 
    public $_cookie_file = ''; 
    public $_mc =''; 
    function __construct(){ 
        global $proxyIp; 
        $this->_cookie_file = tempnam('/temp','cookie'); 
        $mc = new MyCollection($this->_cookie_file,$proxyIp,array('X-FORWARDED-FOR:8.8.8.81', 'CLIENT-IP:8.8.8.82')); $this->_mc = $mc; unset($proxyIp); 
    } 
    function mySelect(){ global $_MooClass,$dbTablePre; $sql = "select count(*) as c,province,city from {$dbTablePre}members_search where usertype=1 and gender=1 and province>0 and city>0 group by province,city having c>0 order by city asc"; $wms = $_MooClass['MooMySQL']->getAll($sql); if($wms) while (list($k,$v) = each($wms)) { $wms[$k][0] = $v['c']*2>600?600:$v['c']*2; unset($wms[$k]['c']); $sql = "select max(birthyear) as maxb from {$dbTablePre}members_search where usertype=1 and gender=1 and province = {$v['province']} and city = {$v['city']} and birthyear>1970"; $birth = $_MooClass['MooMySQL']->getOne($sql); $wms[$k]['maxAge'] = date('Y')-$birth['minb']; $wms[$k]['minAge'] = date('Y')-$birth['maxb']; } unset($_MooClass,$dbTablePre,$sql); return $wms; } function addOldMembers(){ global $collectList,$_MooClass,$dbTablePre; $no = mc_get(__class__.'_'.__function__); if(empty($no) && $no!== 0){ $no = 470; } while(!isset($collectList[$no]) ){ if($no<0){ ll(__function__,"please shutdown the script!"); exit; } $no--; } foreach ($collectList[$no] as $ke => $va) { run(__function__); $a = explode(',', $ke); $gender = array(0,1); foreach($gender as $val){ $num = $_MooClass['MooMySQL']->getOne("select count(*) as c,gender from {$dbTablePre}members_search where province = {$a[0]} ".($a[1]>0?" and city = {$a[1]}":'')." and gender = {$val} and birthyear<=".(date('Y')-($val==1?28:30))." and birthyear>=".(date('Y')-($val==1?40:42))); $num = 30 -$num['c']; if($num<=0) continue; $caiji[$ke][$val] = array('minAge'=>$val==1?28:30,'maxAge'=>$val==1?40:42,'num'=>$num); } } mc_set(__class__.'_'.__function__,--$no); unset($collectList,$_MooClass,$dbTablePre); $urls = $this->getListUrl($caiji);unset($caiji); if($urls); foreach($urls as $v){ run(__function__); $url = $this->getPageUrl($v); if(empty($url)) {ll(__function__,$v.'[false],');continue;} $this->webLogin(); foreach($url as $va){ run(__function__); if(dbHas('members_base',array('source'=>trim($va['url']) ) ) ) { ll(__function__,$va['url'].'[has],'); } $data = $this->getData($va['url'],$va['info']); if(empty($data)) {ll(__function__,$va['url'].'[false],');continue;} $uid = $this->formatData($data,'members_search'); ll(__function__,$uid.','); } }unset($urls); } 
	function getPcMembers(){ 
        $caiji['10116000,10116009'] = array(0 => array('minAge'=>19, 'maxAge'=>25, 'num'=>60));
        $urls = $this->getListUrl($caiji);unset($caiji);
        foreach($urls as $v){ 
            run(__function__);
            $url = $this->getPageUrl($v);
            if(empty($url)) {
                ll(__function__,$v.'[false],');continue;
            } 
            $this->webLogin(); 
            foreach($url as $va){ 
                run(__function__); 
                if(dbHas('members_base',array('source'=>trim($va['url']) ) ) ) { 
                    ll(__function__,$va['url'].'[has],'); continue; 
                } $data = $this->getData($va['url'],$va['info']); if(empty($data)) {continue;} $uid = $this->formatData($data,'members_search');unset($data); ll(__function__,$uid.','); if($_SERVER['HTTP_HOST']!='www.07919.com') {pr($uid,1);} } }unset($urls); 
    }

    function getAllZhaMembers(){ global $collectList,$_MooClass,$dbTablePre; while(true){ run(__function__); $i = rand(0,470); $v = $collectList[$i]; if(empty($v)) continue; $v = array_keys($v); $v = explode(',', $v[0]); if ($v[0] == '10106000') continue; if(in_array($v[0], array('10102000','10103000','10104000','10105000'))) { $v[1] = -1; } $where = " WHERE province = {$v[0]} ".($v[1] > 0?" AND city = {$v[1]}":'')." AND gender = 1 AND sendNum >= 0"; $key = "members_collect_".sha1($where); $num = mc_get($key); if(empty($num)){ $num = $_MooClass['MooMySQL']->getOne("SELECT count(*) as c FROM {$dbTablePre}members_collect {$where}"); } if($num['c']>700) { ll(__function__,implode('_', $v).'[full],');continue; }else{ break; } } $caiji[implode(',', $v)] = array(1 => array('minAge'=>19,'maxAge'=>30,'num'=>700)); $urls = $this->getListUrl($caiji);unset($caiji); foreach($urls as $va){ run(__function__); $url = $this->getPageUrl($va); if(empty($url)) {continue;} $this->webLogin(); foreach($url as $val){ run(__function__); if(dbHas('members_collect',array('source'=>trim($val['url'])))) {continue;} $data = $this->getData($val['url'],$val['info']); if(empty($data)) {continue;} $uid = $this->formatData($data,'members_collect');unset($data); if($uid) {$num['c']++;mc_set($key,$num);} if($num['c']>700){ ll(__function__,implode('_', $v).'[full],');break 2; } ll(__function__,$uid.'['.$num['c'].'],'); if($_SERVER['HTTP_HOST']!='www.07919.com') {pr($uid,1);} } }unset($urls); unset($collectList,$_MooClass,$dbTablePre); } 
        function getJiaMembers(){ 
            $caiji[] = array(1=>array('url'=>'http://search.jiayuan.com/v2/search.php?sex=f&key=&stc=1%3A6230%2C2%3A22.35%2C23%3A1&sn=default&sv=1&p=1&f=select','num'=>140)); 
            $urls = $this->getListUrl($caiji,'jia');unset($caiji); 
            foreach ($urls as $k => $v) { 
                run(__function__); 
                $url = $this->getPageUrl($v,'jia'); 
                if(empty($url)) {ll(__function__,$v.'[false],');continue;} 
                $this->webLogin('jia');
                foreach ($url as $va) { 
                    run(__function__); 
                    if(dbHas('members_base',array('source'=>trim($va['url']) ) ) ) { 
                        ll(__function__,$va['url'].'[has],'); continue; 
                    }
                    $data = $this->getData($va['url'],$va['info'],'jia'); 
                    if(empty($data)) {
                        ll(__function__,$va['url'].'[false],');continue;
                    } 
                    $uid = $this->formatData($data,'members_search');unset($data); 
                    ll(__function__,$uid.','); 
                    if($_SERVER['HTTP_HOST']!='www.07919.com') {pr($uid,1);} 
                } 
            } 
        } 
    function getAllJiaMembers(){ 
            global $_MooClass,$dbTablePre,$provinceCityList; 
            $i = 0; $this->webLogin('jia'); $num['c'] = 701; 
            while($num['c'] > 700){ 
                run(__function__); 
                $provinceNo = 0; 
                $cityNo = 0; 
                while(empty($provinceNo)){ 
                    run(__function__); 
                    $p_keys = array_rand($provinceCityList); 
                    $provinceNo = getProviceNo($p_keys); 
                    if(empty($provinceNo)) ll(__function__,$p_keys."[no No]\r\n"); } 
                    if($p_keys == '安徽') continue; 
                    if(in_array($p_keys, array('北京','天津','上海','重庆'))){
                        $search = $p_keys; 
                    }else{ 
                        while(empty($cityNo)){ 
                            run(__function__); 
                            $c_keys = array_rand($provinceCityList[$p_keys]); 
                            $search = $provinceCityList[$p_keys][$c_keys]; 
                            if($search == '淮阴') $search = '淮安'; 
                            $cityNo = getCityNo($search); 
                            if(empty($cityNo)) ll(__function__,$search."[no No]\r\n"); 
                        } 
                    } 
                    $search = strtr($search, array('海东地区'=>'海东','迪庆藏族自治州'=>'迪庆','怒江傈傈族自治州'=>'怒江傈','德宏傣族景颇族自治州'=>'德宏','西双版纳傣族自治州'=>'西双版纳','博尔塔拉蒙古自治州'=>'博尔塔拉','克孜勒苏柯尔克孜自治州'=>'克孜勒苏','湘西土家族苗族自治州'=>'湘西','延边朝鲜族自治州'=>'延边','海北藏族自治州'=>'海北','黄南藏族自治州'=>'黄南','海南藏族自治州'=>'海南')); 
                    $contents = @file_get_contents('http://images1.jyimg.com/w4/global/j/love_location_array.js'); 
                    if(empty($contents)) {ll(__function__,"js_loading[no content]\r\n");continue;} 
                    $pattern = '/\[([0-9]+)\] = '."'".$search."'".'/'; 
                    preg_match($pattern, $contents, $str); 
                    if(!isset($str[1]) || empty($str[1])){ ll(__function__,$search."[no found]\r\n"); continue; } 
                    $where = " WHERE province = {$provinceNo} ".(empty($cityNo)?"":" AND city = {$cityNo}")." AND gender = 1 AND sendNum >= 0"; $key = "members_collect_".sha1($where); $num = mc_get($key); if(empty($num)){ $num = $_MooClass['MooMySQL']->getOne("SELECT COUNT(*) AS c FROM {$dbTablePre}members_collect {$where}"); mc_set($key,$num); } } unset($provinceCityList,$contents); $contents = file_get_contents('http://search.jiayuan.com/v2/search.php?sex=f&key=&stc=2%3A19.30%2C23%3A1%2C1%3A'.$str[1].'&sn=default&sv=1&p=1&f=select'); $contents = json_decode($contents); $max = min($contents->pageTotal,70); $i = 1; while($i <= $max){ run(__function__); $url = 'http://search.jiayuan.com/v2/search.php?sex=f&key=&stc=2%3A19.30%2C23%3A1%2C1%3A'.$str[1].'&sn=default&sv=1&p='.$i.'&f=select'; $url = $this->getPageUrl($url,'jia'); if(empty($url)) {continue;} foreach ($url as $va) { run(__function__); if(dbHas('members_collect',array('source'=>trim($va['url'])))) continue; $data = $this->getData($va['url'],$va['info'],'jia'); if(empty($data)) {continue;} $uid = $this->formatData($data,'members_collect','jia'); if($uid) { $num['c']++;mc_set($key,$num); ll(__function__,$uid.'['.$num['c'].']'.','); if($num['c']>700) {ll(__function__,$data['data']['province'].'_'.$data['data']['city'].'[full],');break 2;} } if($_SERVER['HTTP_HOST']!='www.07919.com') {pr($uid,1);} unset($uid,$where,$data); }unset($url,$page); $i++; } $_MooClass['MooMySQL']->close(); unset($_MooClass,$dbTablePre); } 
    /*临时函数
    批量修正用户图片*/
    function UpMembersData(){
        $t  = $_SERVER['REQUEST_TIME'];
        run(__function__);
        global $_MooClass,$dbTablePre;
        $logs = file_get_contents(dirname(__file__).'/data/'.__function__.'.log');
        preg_match_all('/[0-9]{8}/', $logs, $str);
        if(isset($str[0])) {$uid =  array_pop($str[0]);}
        $where = ' WHERE usertype = 3 AND is_lock = 0 AND sid=665'.(isset($uid) ? ' AND uid < '.$uid : '');
        $num = $_MooClass['MooMySQL']->getOne("SELECT COUNT(uid) AS c FROM {$dbTablePre}members_search {$where}");
        if($num['c']){
            $cl = searchApi('members_man members_women');
            $p = 1;
            while ($p <= ceil($num['c']/100)) {
                run(__function__);
                $ms = $_MooClass['MooMySQL']->getAll("SELECT uid FROM {$dbTablePre}members_search {$where} ORDER BY uid DESC LIMIT ".(($p-1)*100).",100");
                if($ms){
                    foreach ($ms as $k => $v) {
                        run(__function__);
                        if((time() - $t) > 1000) {exit('time over');}
                        $mb = $_MooClass['MooMySQL']->getOne("SELECT uid FROM {$dbTablePre}members_base WHERE uid = {$v['uid']} AND source > '' LIMIT 1");
                        if($mb){
                            if($this->UpMemberData($mb['uid'])){
                                $time = time();
                                $_MooClass['MooMySQL']->query("UPDATE {$dbTablePre}members_search SET is_lock=1,sid=1,updatetime={$time} WHERE uid = {$v['uid']}");
                                $cl->updateAttr(array('is_lock','sid'),array($v['uid']=>array(1,1)));
                                MooFastdbUpdate('members_search','uid',$v['uid'],array('is_lock'=>1,'sid'=>1,'updatetime'=>$time));
                                MooWriteFile(dirname(__file__).'/data/'.__function__.'.log',$v['uid'].'['.(time()-$t).'_'.memory_get_usage().'],','a+');
                            }else{
                                ll(__function__,"\r\n".$v['uid'].'up data false['.(time()-$t).'_'.memory_get_usage().']');
                            }
                        }else{
                            ll(__function__,"\r\n".$v['uid'].'no members as uid:'.$v['uid'].'['.(time()-$t).'_'.memory_get_usage().']');
                        }
                    }
                }else{
                    ll(__function__,"\r\n".'no page members as page:'.$p.'['.(time()-$t).'_'.memory_get_usage().']');
                }
                $p++;
            }
            ll(__function__,"\r\n");
        }else{
            ll(__function__,'no members');
        }
        $_MooClass['MooMySQL']->close();
        unset($_MooClass,$dbTablePre);
    }

    function UpMemberData($uid){
        global $_MooClass,$dbTablePre;
        !isset($_SERVER['REQUEST_TIME']) && $_SERVER['REQUEST_TIME'] = time();
        MooAutoLoad('ImageInfo');
        $im = new ImageInfo();
        $mb = $_MooClass['MooMySQL']->getOne("SELECT source FROM {$dbTablePre}members_base WHERE uid = {$uid} LIMIT 1");
        if($mb){
            $mc = $_MooClass['MooMySQL']->getOne("SELECT web_pic FROM {$dbTablePre}members_collect WHERE source = {$mb['source']} LIMIT 1");
            $status = false;
            if($mc){
                $pic = @my_unserialize($mc['web_pic']);
                if(!array_empty($pic)){
                    $p = $_MooClass['MooMySQL']->getAll("SELECT imgurl FROM {$dbTablePre}pic WHERE uid = {$uid}");
                    foreach ($p as $ke => $va) { 
                        if(is_file($va['imgurl'])) unlink($va['imgurl']);
                    }unset($p);
                    $_MooClass['MooMySQL']->query('DELETE FROM '.$dbTablePre.'pic WHERE uid = '.$uid);
                    $i = 0;
                    foreach ($pic as $ke => $va) {
                         $va['imgurl'] = str_replace('/data/webroot/07919/', '', $va['imgurl']);
                        if(strpos($va['imgurl'], 'default')!==false) {
                            ll(__function__,'uid:'.$uid.' members_collect web_pic:'.$va['imgurl'].' is default');
                            continue;
                        } 
                        if(!exif_imagetype($va['imgurl'])) {
                            ll(__function__,'uid:'.$uid.' members_collect web_pic:'.$va['imgurl'].' not found');
                            continue;
                        }
                        if(isset($va['isimage']) && $va['isimage']==1){
                            $photo = $im->getPhoto($uid,'big');
                            if(!is_dir(dirname($photo))) MooMakeDir(dirname($photo)); 
                            if(@copy($va['imgurl'],$photo)){ 
                                $ic=new ImageCrop($photo,$photo); 
                                $ic->Crop(320,400,1); 
                                $ic->SaveImage(); 
                                $ic->destory();unset($ic,$photo); 
                                $im->createPhoto($v,null,'public/system/images/logo2.png');
                                $status = true;
                            }else{
                                ll(__function__,'uid:'.$uid.' members_collect web_pic:'.$va['imgurl'].' copy false');
                            } continue;
                        }
                        $photo = PIC_PATH.'/'.date("Y",$_SERVER['REQUEST_TIME'])."/".date("m",$_SERVER['REQUEST_TIME'])."/".date("d",$_SERVER['REQUEST_TIME']).'/orgin/';
                        if(!is_dir($photo)) MooMakeDir($photo); 
                        $pathParts = pathinfo($va['imgurl']); 
                        $photosname= $pathParts['basename']; 
                        $date = date('Y/m/d',$_SERVER['REQUEST_TIME']); 
                        $imgurl = $photo.$photosname; 
                        if(!copy($va['imgurl'],$imgurl)) {
                            ll(__function__,'uid:'.$uid.' members_collect web_pic:'.$va['imgurl'].' copy false');
                            continue;
                        }
                        if(exif_imagetype($imgurl)){ 
                            $i++; $pics['uid'] = $uid; $pics['imgurl'] = $imgurl; $pics['pic_date'] = $date; $pics['pic_name'] = $photosname; $pics['syscheck'] = 1; 
                            dbInsert('pic',$pics);
                            $status = true;
                        }else{
                            ll(__function__,'uid:'.$uid.' members_collect web_pic:'.imgurl.' copyed false');
                        }
                        unset($imgurl,$date,$photosname,$pathParts,$photo,$pics);
                    }
                }else{
                    ll(__function__,'uid:'.$uid.' pics not found in members_collect');
                }
                unset($mc,$pic);
            }
            if(!$status){
                if(strpos($mb['source'], 'http://www.jiayuan.com/')!==false){ $arg = 'jia'; } 
                if(strpos($mb['source'], 'http://album.zhenai.com/profile/getmemberdata.jsps?memberid=')!==false){ $arg = 'zha'; }
                if($arg){
                    $this->webLogin($arg); 
                    $data = $this->getPicData($mb['source'],$arg);
                    if($data){
                        if($data['mainimg']){
                            $photo = $im->getPhoto($uid,'big');
                            $photoDir = dirname($photo);
                            if(!is_dir($photoDir)) MooMakeDir($photoDir);
                            $i=0;
                            while(!copy($data['mainimg'], $photo ) ){ if($i>2) break; $i++; }unset($i);
                            if(is_file($photo)){
                                cropImage($photo,$photo);
                                $ic=new ImageCrop($photo,$photo); 
                                $ic->Crop(320,400,1); $ic->SaveImage();
                                $ic->destory();
                                dbUpdate('members_search', array('mainimg'=>$photo,'images_ischeck'=>1), array('uid'=>$uid));
                            }else{
                                ll(__function__,'uid:'.$uid.' mainimg copy false');
                            }
                            unset($photo,$photoDir);
                        }else{
                            ll(__function__,'uid:'.$uid.' getPicData not found mainimg');
                        }
                        $b = 0;
                        if($data['pics']){
                            $p = $_MooClass['MooMySQL']->getAll("SELECT imgurl FROM {$dbTablePre}pic WHERE uid = {$uid}");
                            foreach ($p as $ke => $va) { 
                                if(is_file($va['imgurl'])) unlink($va['imgurl']);
                            }unset($p);
                            $_MooClass['MooMySQL']->query('DELETE FROM '.$dbTablePre.'pic where uid = '.$uid);
                            foreach($data['pics'] as $va){ 
                                $photos = PIC_PATH.'/'.date("Y")."/".date("m")."/".date("d").'/orgin/'; 
                                if(!is_dir($photos)) MooMakeDir($photos); 
                                $pathParts = pathinfo($va); 
                                $photosname= $pathParts['basename']; 
                                $date = date('Y/m/d'); 
                                $imgurl = $photos.$photosname; 
                                $i=0; 
                                while(!copy($va, $imgurl)){ if($i>2) continue 2; $i++; }
                                cropImage($imgurl,$imgurl); 
                                $b++; 
                                $pData['uid'] = $uid; $pData['imgurl'] = $imgurl; $pData['pic_date'] = $date; $pData['pic_name'] = $photosname; $pData['syscheck'] = 1; 
                                dbInsert('pic',$pData);unset($pData,$photos,$pathParts,$photosname,$date,$imgurl,$i); 
                            }
                            dbUpdate('members_search', array('pic_num'=>$b,'regdate'=>$_SERVER['REQUEST_TIME']), array('uid'=>$uid));unset($b);
                            $im->resetPicNew($uid,'public/system/images/logo_original.png'); 
                            $im->createPhoto($uid,null,'public/system/images/logo2.png'); 
                            reset_integrity($uid);
                            $status = true;
                        }else{
                            ll(__function__,'uid:'.$uid.' getPicData not found pics');
                        } 
                        unset($data);
                    }else{
                        ll(__function__,'uid:'.$uid.' getPicData false');
                    }
                }else{
                    ll(__function__,'uid:'.$uid.' source not found');
                }
            }
        }else{
            ll(__function__,'uid:'.$uid.' not found in members_base');
        }
        $_MooClass['MooMySQL']->close();
        unset($im,$_MooClass,$dbTablePre,$mb);
        return $status;
    }
    // 修正用户图片
    function upUserData(){ 
        global $adminid,$_MooClass,$dbTablePre; 
        !isset($_SERVER['REQUEST_TIME']) && $_SERVER['REQUEST_TIME'] = time();
        $users = trim($_GET['users']); 
        if(empty($users)) exit("no user"); 
        $users = explode(',	', $users); 
        MooAutoLoad('ImageInfo'); 
        $im = new ImageInfo(); 
        $msg = '';
        foreach ($users as $k => $v) { 
            $wmb = $_MooClass['MooMySQL']->getOne("select source from {$dbTablePre}members_base where uid = $v"); 
            if(!$wmb) { $msg .= $v.' The user does not exist.';continue; } 
            $mc = $_MooClass['MooMySQL']->getOne("SELECT web_pic FROM {$dbTablePre}members_collect WHERE source = '{$wmb['source']}'"); 
            if($mc){ 
                $pic = my_unserialize($mc['web_pic']); 
                if(!array_empty($pic)){ 
                    $p = $_MooClass['MooMySQL']->getAll("SELECT imgurl FROM {$dbTablePre}pic WHERE uid = {$v}"); 
                    foreach ($p as $ke => $va) { 
                        if(is_file($va['imgurl'])) unlink($va['imgurl']); 
                    }unset($p); 
                    $_MooClass['MooMySQL']->query('DELETE FROM '.$dbTablePre.'pic WHERE uid = '.$v); 
                    $i = 0; 
                    foreach ($pic as $ke => $va) { 
                        if(strpos($va['imgurl'], 'default')!==false) continue; 
                        if(isset($va['isimage']) && $va['isimage']==1){ 
                            $va['imgurl'] = str_replace('/data/webroot/07919/', '', $va['imgurl']); 
                            if(exif_imagetype($va['imgurl'])){ 
                                $photo = $im->getPhoto($v,'big'); 
                                if(!is_dir(dirname($photo))) MooMakeDir(dirname($photo)); 
                                if(copy($va['imgurl'],$photo)){ 
                                    $ic=new ImageCrop($photo,$photo); 
                                    $ic->Crop(320,400,1); 
                                    $ic->SaveImage(); 
                                    $ic->destory();unset($ic,$photo); 
                                    $im->createPhoto($v,null,'public/system/images/logo2.png'); 
                                } 
                            } continue; 
                        } 
                        $photo = PIC_PATH.'/'.date("Y",$_SERVER['REQUEST_TIME'])."/".date("m",$_SERVER['REQUEST_TIME'])."/".date("d",$_SERVER['REQUEST_TIME']).'/orgin/'; 
                        if(!is_dir($photo)) MooMakeDir($photo); 
                        if(!exif_imagetype($va['imgurl'])) continue; 
                        $pathParts = pathinfo($va['imgurl']); 
                        $photosname= $pathParts['basename']; 
                        $date = date('Y/m/d',$_SERVER['REQUEST_TIME']); 
                        $imgurl = $photo.$photosname; 
                        if(!copy($va['imgurl'],$imgurl)) continue; 
                        if(exif_imagetype($imgurl)){ 
                            $i++; $pics['uid'] = $v; $pics['imgurl'] = $imgurl; $pics['pic_date'] = $date; $pics['pic_name'] = $photosname; $pics['syscheck'] = 1; 
                            dbInsert('pic',$pics); 
                        } unset($imgurl,$date,$photosname,$pathParts,$photo,$pics); 
                    } 
                dbUpdate('members_search',array('pic_num'=>$i,'regdate'=>$_SERVER['REQUEST_TIME']),array('uid'=>$v));unset($i); 
                $im->resetPicNew($v,'public/system/images/logo_original.png'); continue; 
            } 
        } 
        if(strpos($wmb['source'], 'http://www.jiayuan.com/')!==false){ $arg = 'jia'; } 
        if(strpos($wmb['source'], 'http://album.zhenai.com/profile/getmemberdata.jsps?memberid=')!==false){ $arg = 'zha'; } 
        if($arg){ 
            $this->webLogin($arg); $data = $this->getPicData($wmb['source'],$arg); 
            if(!$data) {$msg .= $v.' Cannot find data source.';continue;}
            $photo = $im->getPhoto($v,'big'); 
            $photoDir = dirname($photo); 
            if(!is_dir($photoDir)) MooMakeDir($photoDir); 
            $i=0; 
            while(!copy($data['mainimg'], $photo ) ){ if($i>2) break; $i++; }unset($i); 
            if(is_file($photo)){ 
                cropImage($photo,$photo); 
                $ic=new ImageCrop($photo,$photo); 
                $ic->Crop(320,400,1); $ic->SaveImage(); 
                $ic->destory(); 
                dbUpdate('members_search', array('mainimg'=>$photo,'images_ischeck'=>1), array('uid'=>$v)); } 
                $b = 0; 
                if($data['pics']){ 
                    $_MooClass['MooMySQL']->query('DELETE FROM '.$dbTablePre.'pic where uid = '.$v); 
                    foreach($data['pics'] as $va){ 
                        $photos = PIC_PATH.'/'.date("Y")."/".date("m")."/".date("d").'/orgin/'; if(!is_dir($photos)) MooMakeDir($photos); 
                        $pathParts = pathinfo($va); 
                        $photosname= $pathParts['basename']; 
                        $date = date('Y/m/d'); 
                        $imgurl = $photos.$photosname; 
                        $i=0; 
                        while(!copy($va, $imgurl)){ if($i>2) continue 2; $i++; }
                        cropImage($imgurl,$imgurl); 
                        $b++; 
                        $pData['uid'] = $v; $pData['imgurl'] = $imgurl; $pData['pic_date'] = $date; $pData['pic_name'] = $photosname; $pData['syscheck'] = 1; dbInsert('pic',$pData);unset($pData,$photos,$pathParts,$photosname,$date,$imgurl,$i); 
                    } 
                    dbUpdate('members_search', array('pic_num'=>$b,'regdate'=>$_SERVER['REQUEST_TIME']), array('uid'=>$v));unset($b); 
                } unset($data,$photo,$photoDir); 
                $im->resetPicNew($v,'public/system/images/logo_original.png'); 
                $im->createPhoto($v,null,'public/system/images/logo2.png'); 
                reset_integrity($v); 
            }else{ $msg .= $v.' Data cannot be updated.'; } unset($arg,$wmb); } unset($im);
        $_MooClass['MooMySQL']->close(); if($msg) exit($msg);
    } 
    function upMembersCollect(){ run('upMembersCollect'); global $_MooClass,$dbTablePre; $sql ="select count(*) as c from {$dbTablePre}members_collect where source like '%jiayuan%'"; $num = $_MooClass['MooMySQL']->getOne($sql); if($num<=0) exit('no data'); $i=0; $this->webLogin('jia'); while($i<ceil($num['c']/500)){ run('upMembersCollect'); $sql = "SELECT id,web_members_base,web_pic,source FROM {$dbTablePre}members_collect WHERE source like '%jiayuan%' ORDER BY id DESC LIMIT ".($i*$num['c']).",{$num['c']}"; $wmc = $_MooClass['MooMySQL']->getAll($sql); foreach($wmc as $k=>$v){ run('upMembersCollect'); $pic = unserialize($v['web_pic']); foreach ($pic as $ke => $va) { if(isset($va['isimage']) && $va['isimage']==1){ $userPhoto = $va['imgurl']; } } if(empty($userPhoto)) continue; list($width, $height, $type, $attr) = getimagesize($userPhoto); if($width>=320 && $height>=400) continue; $members_base = unserialize($v['web_members_base']); if(isset($members_base['source'])){ $contents = $this->_mc->webVisit(array('url'=>$members_base['source'])); preg_match('/<div class=\"user_pic\"[^>]*>[^<]*<a[^>]*><img src="([^"]+)"[^>]*>/', $contents,$str); if(isset($str[1]) && strpos($str[1], 'xyaqmm_f')===false){ $pathParts = pathinfo($str[1]); $pathParts['filename'] = substr($pathParts['filename'], 0,-1).'o'; $str[1] = $pathParts['dirname'].'/'.$pathParts['filename'].'.'.$pathParts['extension']; if(@exif_imagetype($str[1])){ $mainimg = $str[1]; } if($mainimg){ if(copy($mainimg,$userPhoto)){ cropImage($userPhoto,$userPhoto); } } } } } unset($sql,$pic,$userPhoto,$contents,$members_base,$str,$mainimg,$pathParts,$width, $height, $type, $attr); $i++; } unset($_MooClass,$dbTablePre,$num); } function getNickname($gender=1){ global $_MooClass,$dbTablePre; $sql = "select count(*) as c from {$dbTablePre}members_search where usertype=1 and regdate>1364745600 and nickname!='' and gender={$gender}"; $num = $_MooClass['MooMySQL']->getOne($sql); $data = $_MooClass['MooMySQL']->getAll("select nickname from {$dbTablePre}members_search where usertype=1 and regdate>1364745600 and nickname!='' and gender={$gender} limit ".rand(0,$num['c']-1).",1"); return $data[0]['nickname']; } 
    function getYouYuanMembers(){
        $caiji[] = array('url'=>'http://www.youyuan.com/anhui/mm18-28/advance-0-0-0-0-0/p1/','num'=>'60');
        $urls = $this->getListUrl($caiji,'yy');unset($caiji);
        $this->webLogin('yy');
        foreach ($urls as $k => $v) {
            run(__function__);
            $url = $this->getPageUrl($v,'yy');
            if(empty($url)) {ll(__function__,$v.'[false],');continue;}
            foreach ($url as $ke => $va) {
                run(__function__); 
                if(dbHas('members_base',array('source'=>trim($va) ) ) ) { 
                    ll(__function__,$va.'[has],'); continue; 
                }
                $data = $this->getData($va,array(),'yy'); 
                if(empty($data)) {
                    ll(__function__,$va.'[false],');continue;
                } 
                $uid = $this->formatData($data,'members_search');unset($data); 
                ll(__function__,$uid.','); 
                if($_SERVER['HTTP_HOST']!='www.07919.com') {pr($uid,1);} 
            }unset($url);
        }unset($urls);
    }
    function webLogin($arg='zha'){ 
        switch ($arg) { 
            case 'zha': 
                $loginInfo['url'] = 'http://profile.zhenai.com/login/loginactionindex.jsps'; $loginInfo['peferer'] = 'http://profile.zhenai.com/'; $loginInfo['loginData']['loginInfo'] = '13696545523'; $loginInfo['loginData']['password'] = '120120'; $loginInfo['loginData']['loginmode'] = 2; $loginInfo['loginData']['rememberpassword'] = 1; 
                break; 
            case 'jia': $loginInfo['url'] = 'http://login.jiayuan.com/dologin.php'; $loginInfo['peferer'] = 'http://login.jiayuan.com/'; $loginInfo['loginData']['name'] = '13696545523@qq.com'; $loginInfo['loginData']['password'] = 'nn2006313'; $loginInfo['loginData']['ljg_login'] = 1; $loginInfo['loginData']['channel'] = 0; $loginInfo['loginData']['position'] = 0;  
                break;
            case 'yy':
                $loginInfo['url'] = 'http://www.youyuan.com/user/login.html';
                $loginInfo['peferer'] = 'http://www.youyuan.com/';
                $loginInfo['loginData']['username'] = '13696545523';
                $loginInfo['loginData']['password'] = '123456';
                break;
        } 
        return $this->_mc->webLogin($loginInfo);
    }
    function getListUrl($caiji,$arg='zha'){ 
        if(empty($caiji)) return null; 
        switch ($arg) { 
            case 'zha': 
                foreach($caiji as $k => $v ){ 
                    $a = explode(',', $k); 
                    foreach($v as $ke => $va){ $i=1; 
                        while($i<= ceil($va['num']/10)){ 
                            $url[] = 'http://search.zhenai.com/search/getfastmdata.jsps?condition=2&photo=1&agebegin='.$va['minAge'].'&ageend='.$va['maxAge'].'&workcityprovince='.$a[0].'&workcitycity='.$a[1].'&gender='.$ke.'&currentpage='.$i; $i++; 
                        } 
                    } 
                } 
                break; 
            case 'jia': 
                foreach($caiji as $k =>$v){ 
                    foreach ($v as $ke => $va) { 
                        $contents = $this->_mc->webVisit(array('url'=>$va['url'])); 
                        $i = 0; 
                        while(!$contents){ 
                            if($i>2){ 
                                ll('aataacquisition',$va['url'].'[read false],'); 
                                continue 2; } $contents = $this->_mc->webVisit(array('url'=>$va['url']));$i++; 
                            } 
                            $contents = json_decode($contents); 
                            $num = $contents->pageTotal > $va['num']?$va['num']:$contents->pageTotal; 
                            while($num>0){ 
                                $url[] = preg_replace('/p=\d+/', 'p='.$num, $va['url']); 
                                $num--; 
                            } 
                        } 
                    } 
                    break; 
            case 'yy':
                foreach ($caiji as $key => $value) {
                    while($i < ceil($value['num']/24)){
                        $url[] = preg_replace('/p\d+/', 'p'.++$i, $value['url']);
                    }
                }
                break;
            default: 
                    break; 
            } unset($caiji,$arg,$i,$a); 
            return $url; 
        }

        //falsh 游戏截取
        function getGame(){
            $url = MooGetGPC("url",'string','G');
            if(!$url) exit('the url no found!');
            $contents = $this->_mc->webVisit(array('url'=>$url));
            preg_match('/(https?:\/\/)?[a-zA-z\.\/0-9_-]*\.swf/', $contents, $str);
            if(isset($str[0])){
                exit($str[0]);
            }else{
                exit('not found!');
            }
        }

        function getPageUrl($url,$arg='zha'){ 
            if(empty($url)) return null;
            $contents = $this->_mc->webVisit(array('url'=>$url,'peferer'=>'http://www.youyuan.com'));
            $i = 0; 
            while(empty($contents)){ 
                if($i>2) break; 
                $contents = $this->_mc->webVisit(array('url'=>$url)); 
                $i++; 
            }unset($i); 
            if(empty($contents)) { ll('aataacquisition',$url.'[read false],');return null;} 
            $urls = array(); parse_str($url); 
            switch ($arg) { 
                case 'zha': 
                    preg_match_all('/http:\/\/([a-z\.]+\/)+getmemberdata.jsps\?memberid=[0-9]+/',$contents,$list,PREG_SET_ORDER); 
                    if($list) foreach($list as $v){ 
                        $arr['gender'] = $gender; 
                        $urls[] = array('url'=>$v[0],'info'=>$arr); 
                    }unset($list,$contents); 
                    $urls = super_unique($urls); 
                    break;
                case 'jia': 
                    $contents = json_decode($contents); 
                    foreach ($contents->userInfo as $v) { 
                        $urls[] = array('url'=>'http://www.jiayuan.com/'.$v->realUid,'info'=>$v); 
                    } 
                    break;
                case 'yy':
                    preg_match_all('/<p[^>]+search_user_item">[^<]*<a href="(\/\d+-profile\/)/',$contents,$list,PREG_SET_ORDER);
                    if($list){
                        foreach ($list as $key => $value) {
                            $urls[] = 'http://www.youyuan.com'.$value[1];
                        }
                    }
                    break; 
                default: 
                    break; 
            } 
            return $urls; 
        } 
        function getPicData($url,$arg='zha'){ 
            $contents = $this->_mc->webVisit(array('url'=>$url));
            switch ($arg) { 
                case 'jia': 
                    preg_match('/<div class=\"user_pic\"[^>]*>[^<]*(<span[^>]*>[^<]*<a[^>]*>[^<]*<\/a>[^<]*<\/span>[^<]*)?<a[^>]*><img src="([^"]+)"[^>]*>/', $contents,$str); 
                    if(isset($str[2]) && strpos($str[2], 'xyaqmm_f')===false){ 
                        $pathParts = pathinfo($str[2]); 
                        $pathParts['filename'] = substr($pathParts['filename'], 0,-1).'o'; 
                        $str[2] = $pathParts['dirname'].'/'.$pathParts['filename'].'.'.$pathParts['extension']; 
                        if(@exif_imagetype($str[2])){ $data['mainimg'] = $str[2]; } unset($pathParts); 
                    } 
                    if(empty($data['mainimg'])) return null; 
                    preg_match_all('/<li>[^>]*<div class="img_box">[\s\S]*?<\/li>/', $contents,$str); 
                    $pData = array(); 
                    if(!array_empty($str[0])) 
                        foreach ($str[0] as $k => $v) { 
                            preg_match('/<img.*src=\"([^"]+)\"[^>]*>/', $v, $pics); 
                            if(isset($pics[1])){ 
                                $pathParts = pathinfo($pics[1]); 
                                $pathParts['filename'] = substr($pathParts['filename'], 0,-1).'o'; 
                                $pics[1] = $pathParts['dirname'].'/'.$pathParts['filename'].'.'.$pathParts['extension'];unset($pathParts); 
                                if(@exif_imagetype($pics[1])) $data['pics'][] = $pics[1]; 
                            }unset($pics); 
                        } 
                    break; 
                case 'zha': preg_match('/objDefalutPhoto(.*)/',$contents,$str); if(isset($str[0])) preg_match('/http:\/\/(.*)(jpg|gif|png|jpeg|JPG|GIF|PNG|JPEG)/',$str[0],$str); if($str[0]) $str[0] = str_replace('_3', '_2', $str[0]); if(empty($str[0]) || strpos($str[0], 'default')!==false || !@exif_imagetype($str[0])) return null; $data['mainimg'] = $str[0]; preg_match_all('/<li>\s*<p>\s*<img[^>]+>/',$contents,$str); if(!array_empty($str[0])){ $i=0; foreach($str[0] as $v2){ preg_match('/data-big-img="[^"]+"/',$v2,$str); preg_match('/http:\/\/.+\.(jpg|gif|png|jpeg|JPG|GIF|PNG|JPEG)/',$str[0],$str); if($str[0] && @exif_imagetype($str[0])){ $data['pics'][] = $str[0]; }unset($v2); } } 
                    break; 
            } unset($contents);
        return $data; 
    } 
        function getData($url,$info=array(),$arg='zha'){ 
            if(empty($url)) return null;
            global $corptypeList,$familyList,$fondsportList,$fondactivityList,$fondmusicList,$fondprogramList,$fondfoodList,$fondplaceList,$occupationList,$vehicleList,$religionList,$smokingList,$drinkingList,$wantchildrenList,$childrenList,$genderList,$bodyList,$marriageList,$educationList,$salaryList,$houseList,$animalyearList,$constellationList,$stockList,$provinceCityList,$bloodtypeList; 
            $contents = $this->_mc->webVisit(array('url'=>$url)); 
            $c = 0; 
            while(empty($contents)){ 
                if($c>2) { ll('aataacquisition',$url."[read false]\r\n");break; } 
                $contents = $this->_mc->webVisit(array('url'=>$url,'peferer'=>'http://www.youyuan.com')); $c++; 
            }unset($c); 
            if(empty($contents)) return null;
            $data['nickname2'] = '';
            $data['language'] = '';
            switch ($arg) { 
                case 'zha': 
                    $contents = iconv('GBK', 'utf-8', $contents); 
                    if(empty($info)) {ll('aataacquisition',$url."[no info]\r\n");return null;} 
                    $gender = $info['gender']; 
                    preg_match('/objDefalutPhoto(.*)/',$contents,$str);
                    if(isset($str[0])) preg_match('/http:\/\/photo[0-9]+\.zastatic\.com(.*)(jpg|gif|png|jpeg|JPG|GIF|PNG|JPEG)/',$str[0],$str);
                    if(isset($str[0])) $str[0] = str_replace('_3', '_2', $str[0]); if(empty($str[0]) || !@exif_imagetype($str[0])) {ll('aataacquisition',$url."[no mainimg]\r\n");return null;} $mbData['mainimg'] = $str[0]; preg_match('/职业：<\/strong>\S+/', $contents,$str); $data['occupation'] = getOccupation($str[0],$occupationList); preg_match('/公司：<\/strong>\S+/', $contents,$str); $data['corptype'] =getCorptype($str[0],$corptypeList); preg_match('/是否购车：<\/strong>\S+/', $contents,$str); $data['vehicle'] = getVeicle($str[0],$vehicleList); preg_match('/信仰：<\/strong>\S+/', $contents,$str); $data['religion'] = getReligion($str[0],$religionList); preg_match('/兄弟姐妹：<\/strong>\S+/', $contents,$str); $data['family'] = getFamily($str[0], $familyList); preg_match('/是否吸烟：<\/strong>\S+/', $contents,$str); $data['smoking'] = getSmoking($str[0],$smokingList); preg_match('/是否喝酒：<\/strong>\S+/', $contents,$str); $data['drinking'] = getDrinking($str[0],$drinkingList); preg_match('/是否想要孩子：<\/strong>\S+/', $contents,$str); $data['wantchildren'] = getWantchildren($str[0],$wantchildrenList); preg_match('/<li[^>]+main[^>]+><h1><strong>[^>]+/',$contents,$str); preg_match('/<strong>(.*)<\//',$str[0],$str); $data['nickname'] = $str[1]; if(preg_match('/会员[0-9]+/',$data['nickname'])){ $data['nickname'] = $this->getNickname($gender); } $data['username'] = getUsername(); $data['telphone'] = 0; $data['password'] = md5('qingyuan07919'); $data['truename'] = ''; $data['gender'] = $gender; preg_match('/<strong[^>]+>(\d+)岁/',$contents,$str); if(is_numeric($str[1]) && $str[1]>0){ $data['birthyear'] = date('Y',strtotime('-'.$str[1].'year')); } preg_match('/住在<strong[^>]+>([^<]+)/',$contents,$str); $pc = getProvinceCity($str[1],$provinceCityList); $data['province'] = getProviceNo($pc['provice']); $data['city'] = getCityNo($pc['city']); if(empty($data['province'])) { ll('aataacquisition',$url."[province error]\r\n"); return null; } if(empty($data['city']) && !in_array($data['province'],array('10102000','10103000','10104000','10105000'))) { ll('aataacquisition',$url."[city error]\r\n"); return null; } preg_match('/，<strong[^>]+>(.*)<\/strong>(.*)，住在/',$contents,$str); $data['marriage'] = getMarriage($str[1],$marriageList); preg_match('/，<strong[^>]+>(\S+)<\/strong>，月收入/',$contents,$str); $data['education'] = getEducation($str[1],$educationList); preg_match('/月收入<strong[^>]+>(\S+)元/',$contents,$str); if(isset($str[1])){ $temp = explode('-', $str[1]); $num = $temp[1]>0?((int)$temp[0]+(int)$temp[1])/2:$temp[0]; } $data['salary'] = getSalary($num,$salaryList); preg_match('/元<\/strong>，(\S+)，/',$contents,$str); $data['house'] = getHouse($str[1],$houseList); preg_match('/，<strong[^>]+>(.*)<\/strong>，(.*)，住在/', $contents, $str); if(isset($str[2])) $data['children'] = getChildren($str[2],$childrenList); preg_match('/(\d+)厘米<\/s/', $contents,$str); $data['height'] = is_numeric($str[1])?$str[1]:0; preg_match('/体重：<\/strong><\/dt><dd>(\d+)/', $contents,$str); $data['weight'] = isset($str[1])?(int)$str[1]:0; preg_match('/体型：<\/strong><\/dt><dd>([^<]+)/', $contents, $str); $data['body'] = getBody($str[1],$bodyList); preg_match('/生肖：<\/strong><\/dt><dd>([^<]+)/', $contents, $str); $data['animalyear'] = getAnimailyear($str[1],$animalyearList); $con_key = array_keys($constellationList); preg_match('/星座：<\/strong><\/dt><dd>([^<]+)/', $contents, $str); foreach($constellationList as $key=>$val){ if(strpos($str[1], $key)!==false){ $data['constellation'] = array_search($key, $con_key) + 1; if($data['birthyear']){ $list = explode(',', $val); $mbData['birth'] = rand(strtotime($data['birthyear'].'-'.$list[1]),strtotime($data['birthyear'].'-'.$list[0]));unset($list); } } } preg_match('/血型：<\/strong><\/dt><dd>([^<]+)/', $contents, $str); $data['bloodtype'] = getBloodtype($str[1],$bloodtypeList); preg_match('/籍贯：<\/strong><\/dt><dd[^>]+>([^<]+)/', $contents, $str); $pc = getProvinceCity($str[1],$provinceCityList); $data['hometownprovince'] = getProviceNo($pc['provice']); $data['hometowncity'] = getCityNo($pc['city']); $data['regdate'] = time(); $data['updatetime'] = time(); preg_match('/民族：<\/strong><\/dt><dd>([^<]+)/',$contents,$str); $data['nation'] = getStock($str[1],$stockList); $data['usertype'] = 3; $data['sid'] = 1; if(empty($data['province'])||empty($data['birthyear'])||!is_numeric($data['gender'])) { ll('aataacquisition',$url."[data error]\r\n"); return null; } $ip = getUserIp(); $mbData['source'] = $url; $mbData['regip'] = $ip; $mbData['currentprovince'] = $data['province']; $mbData['currentcity'] = $data['city']; $mbData['friendprovince'] = 'a:3:{i:0;a:1:{i:'.$data['province'].';s:8:"'.$data['city'].'";}i:1;a:1:{i:0;s:1:"0";}i:2;a:1:{i:0;s:1:"0";}}'; preg_match('/喜欢的食物：<\/dt><dd>([^<]+)/', $contents,$str); $mbData['fondfood'] = getFondfood($str[1], $fondfoodList); preg_match('/喜欢的地方：<\/dt><dd>([^<]+)/', $contents,$str); $mbData['fondplace'] = getFondplace($str[1], $fondplaceList); preg_match('/喜欢的活动：<\/dt><dd>([^<]+)/', $contents,$str); $mbData['fondactivity'] = getFondactivity($str[1], $fondactivityList); preg_match('/喜欢的体育运动：<\/dt><dd>([^<]+)/', $contents,$str); $mbData['fondsport'] = getFondsport($str[1], $fondsportList); preg_match('/喜欢的音乐：<\/dt><dd>([^<]+)/', $contents,$str); $mbData['fondmusic'] = getFondmusicList($str[1], $fondmusicList); preg_match('/喜欢的影视节目：<\/dt><dd>([^<]+)/', $contents,$str); $mbData['fondprogram'] = getFondprogram($str[1], $fondprogramList); $mcData['gender'] = $gender==1?0:1; preg_match('/年龄：<\/dt><dd>(\d+)[^\d]+(\d+)/', $contents, $str); $mcData['age1'] = isset($str[1])?(int)$str[1]:0; $mcData['age2'] = isset($str[2])?(int)$str[2]:0; preg_match('/身高：<\/dt><dd>(\d+)[^\d]+(\d+)/', $contents, $str); $mcData['height1'] = isset($str[1])?(int)$str[1]:0; $mcData['height2'] = isset($str[2])?(int)$str[2]:0; preg_match('/体型：<\/dt><dd>([^<]+)/', $contents, $str); $mcData['body'] = getBody($str[1],$bodyList); $mcData['hasphoto']=1; preg_match('/婚姻状况：<\/dt><dd>([^<]+)/',$contents,$str); $mcData['marriage'] = getMarriage($str[1],$marriageList); preg_match('/学历：<\/dt><dd>([^<]+)/',$contents,$str); $mcData['education'] = getEducation($str[1],$educationList); preg_match('/工作地区：<\/dt><dd[^>]+>([^<]+)/',$contents,$str); $pc = getProvinceCity($str[1],$provinceCityList); $mcData['workprovince'] = getProviceNo($pc['provice']); $mcData['workcity'] = getCityNo($pc['city']); preg_match('/是否抽烟：<\/dt><dd>([^<]+)/', $contents,$str); $mcData['smoking'] = getSmoking($str[1],$smokingList); preg_match('/是否喝酒：<\/dt><dd>([^<]+)/', $contents,$str); $mcData['drinking'] = getDrinking($str[1],$drinkingList); preg_match('/是否想要孩子：<\/dt><dd>([^<]+)<\/dd><\/li>[^<]+<\/ul>/', $contents,$str); $mcData['wantchildren'] = getWantchildren($str[1],$wantchildrenList); preg_match('/月收入：<\/dt><dd>(\S+)元/', $contents, $str); if(isset($str[1])){ $temp = explode('-', $str[1]); $num = isset($temp[1])?((int)$temp[0]+(int)$temp[1])/2:$temp[0]; } $mcData['salary'] = getSalary($num,$salaryList); preg_match('/职业：<\/dt><dd>([^<]+)/', $contents,$str); $mcData['occupation'] = getOccupation($str[0],$occupationList); preg_match('/<li class="folded">([\s\S]*?)<\/li>/',$contents,$str); $miData['introduce'] = isset($str[1])?checkStr(strip_tags($str[1])) :''; $miData['introduce_pass'] = empty($miData['introduce'])?0:1; $miData['introduce_check'] = 1; $maData['real_lastvisit'] = time(); $maData['finally_ip'] = $ip; $mlData['lastip'] = $ip; $mlData['lastvisit'] = time(); $mlData['last_login_time'] = time(); $mlData['login_meb'] = 1; preg_match_all('/<li>\s*<p>\s*<img[^>]+>/',$contents,$str); if(!array_empty($str[0])){ $i=0; foreach($str[0] as $v2){ preg_match('/data-big-img="[^"]+"/',$v2,$str); preg_match('/http:\/\/.+\.(jpg|gif|png|jpeg|JPG|GIF|PNG|JPEG)/',$str[0],$str); if($str[0] && @exif_imagetype($str[0])){ $pData[] = $str[0]; }unset($v2); } } $cData['email'] = 'yes'; if(rand(0,9)>=5){ $cData['identity_check'] = 3; } $cData['telphone'] = '12345678900'; 
                        break; 
                case 'jia': 
                    $mbData['source'] = $url; 
                    preg_match_all('/<h2><a href="#detail"\s\w+[^<]+<\/a>([^<]+)<\/h2>/', $contents, $str); 
                    if(isset($str[1][0])){ $strs = explode('，', $str[1][0]); $str = $strs[3];unset($strs); } 
                    if(!empty($str)){ 
                        $pc = getProvinceCity($str,$provinceCityList); $data['province'] = getProviceNo($pc['provice']); $data['city'] = getCityNo($pc['city']); 
                    } 
                    if(empty($data['province'])) { ll('aataacquisition',$url."[province error]\r\n"); return null; } 
                    if(empty($data['city']) && !in_array($data['province'],array('10102000','10103000','10104000','10105000'))) { ll('aataacquisition',$url."[city error]\r\n"); return null; } 
                    $data['gender'] = getGender($info->sex, $genderList); preg_match('/<b>职业：<\/b>[^<]+/', $contents,$str); $data['occupation'] = getOccupation($str[0], $occupationList); preg_match('/<span>公司类型：<\/span>[^<]+/', $contents,$str); $data['corptype'] = getCorptype($str[0], $corptypeList); preg_match('/<b>购车：<\/b>[^<]+/', $contents,$str); $data['vehicle'] = getVeicle($str[0], $vehicleList); preg_match('/<span>宗教信仰：<\/span>[^<]+/', $contents,$str); $data['religion'] = getReligion($str[0], $religionList); preg_match('/<span>家中排行：<\/span>[^<]+/', $contents,$str); $data['family'] = getFamily($str[0], $familyList); preg_match('/<span>是否吸烟：<\/span>[^<]+/', $contents,$str); $data['smoking'] = getSmoking($str[0], $smokingList); preg_match('/<span>是否饮酒：<\/span>[^<]+/', $contents,$str); $data['smoking'] = getDrinking($str[0], $drinkingList); preg_match('/<span>愿意要孩子：<\/span>[^<]+/', $contents,$str); $data['wantchildren'] = getWantchildren($str[0], $wantchildrenList); if(checkNickName($info->nickname)){ $data['nickname'] = $info->nickname; }else{ $data['nickname'] = $this->getNickname($data['gender']); } $data['username'] = getUsername('jia'); $data['telphone'] = 0; $data['password'] = md5('qingyuan07919'); $data['birthyear'] = 2013-$info->age-1; preg_match('/<b>婚姻：<\/b>[^<]+/', $contents,$str); if(strpos($str[0], ',')!==false){ $list = explode(',', $str[0]); $marriage = $list[0]; $children = $list[1]; }else{ $marriage = $str[0]; $children = null; } $data['marriage'] = getMarriage($marriage,$marriageList); $data['children'] = getChildren($children, $childrenList);unset($list,$marriage,$children); $data['education'] = getEducation($info->education,$educationList); preg_match('/<b>月薪：<\/b>[^<]+/', $contents,$str); if(strpos($str[0], '～')!==false){ $list = explode('～', $str[0]); preg_match('/[0-9]+/', $list[0],$begin); preg_match('/[0-9]+/', $list[1],$end); $num = ((int)$begin[0]+(int)$end[0])/2; }else{ preg_match('/[0-9]+/', $str[0],$begin); $num = $begin[0]; } $data['salary'] = getSalary($num, $salaryList);unset($num,$begin,$end,$list); preg_match('/<b>住房：<\/b>[^<]+/', $contents,$str); $data['house'] = getHouse($str[0], $houseList); $data['height'] = $info->height; preg_match('/<span>体　　重：<\/span>[^<]+/', $contents,$str); preg_match('/[0-9]+/', $str[0], $str); if(isset($str[0])) $data['weight'] = $str[0]; preg_match('/<span>体　　型：<\/span>[^<]+/', $contents,$str); $data['body'] = getBody($str[0], $bodyList); preg_match('/<span>生　　肖：<\/span>[^<]+/', $contents,$str); $data['animalyear'] = getAnimailyear($str[0], $animalyearList); $con_key = array_keys($constellationList); foreach($constellationList as $key=>$val){ if(strpos($contents, $key)!==false){ $data['constellation'] = array_search($key, $con_key) + 1; if($data['birthyear']){ $list = explode(',', $val); $mbData['birth'] = rand(strtotime($data['birthyear'].'-'.$list[1]),strtotime($data['birthyear'].'-'.$list[0]));unset($list); } } } preg_match('/<span>籍　　贯：<\/span>[^<]+/', $contents,$str); $pc = getProvinceCity($str[0],$provinceCityList); $data['hometownprovince'] = getProviceNo($pc['provice']); $data['hometowncity'] = getCityNo($pc['city']);unset($pc); $data['regdate'] = time(); $data['updatetime'] = time(); $data['usertype'] = 3; $data['sid'] = 1; $ip = getUserIp(); preg_match('/<b>民族：<\/b>[^<]+/', $contents,$str); $data['nation'] = getStock($str[0],$stockList); preg_match('/<span>喜欢美食：<\/span>[^<]+/', $contents,$str); $mbData['fondfood'] = getFondfood($str[0], $fondfoodList); preg_match('/<span>喜欢旅游：<\/span>[^<]+/', $contents,$str); $mbData['fondplace'] = getFondplace($str[0], $fondplaceList); preg_match('/<span>业余爱好：<\/span>[^<]+/', $contents,$str); $mbData['fondactivity'] = getFondactivity($str[0], $fondactivityList); preg_match('/<span>喜欢运动：<\/span>[^<]+/', $contents,$str); $mbData['fondsport'] = getFondsport($str[0], $fondsportList); preg_match('/<span>喜爱的影片：<\/span>[^<]+/', $contents,$str); $mbData['fondprogram'] = getFondprogram($str[0], $fondprogramList); $mbData['callno'] = 0; $mbData['regip'] = $ip; 
                    preg_match('/<div class=\"user_pic\"[^>]*>[^<]*(<span[^>]*>[^<]*<a[^>]*>[^<]*<\/a>[^<]*<\/span>[^<]*)?<a[^>]*><img src="([^"]+)"[^>]*>/', $contents,$str); 
                    if(isset($str[2]) && strpos($str[2], 'xyaqmm_f')===false){ $pathParts = pathinfo($str[2]); $pathParts['filename'] = substr($pathParts['filename'], 0,-1).'o'; $str[2] = $pathParts['dirname'].'/'.$pathParts['filename'].'.'.$pathParts['extension']; 

                    if(@exif_imagetype($str[2])){ $mbData['mainimg'] = $str[2]; } unset($pathParts); } 
                    if(empty($mbData['mainimg'])){ ll('aataacquisition',$url."[no mainimg]\r\n"); return null; } 
                    $mbData['currentprovince'] = $data['province']; $mbData['currentcity'] = $data['city']; $fp = 'a:3:{i:0;a:1:{i:'.$data['province'].';s:8:"'.$data['city'].'";}i:1;a:1:{i:0;s:1:"0";}i:2;a:1:{i:0;s:1:"0";}}'; $mbData['friendprovince'] = $fp;unset($fp); $mcData['gender'] = $data['gender']==1?0:1; preg_match('/[0-9]+-[0-9]+岁/', $info->matchCondition,$str); if($str[0]) preg_match_all('/[0-9]+/', $str[0], $str); if(!array_empty($str)){ $mcData['age1'] = $str[0][0]; $mcData['age2'] = $str[0][1]; } preg_match('/[0-9]+-[0-9]+cm/', $info->matchCondition,$str); if($str[0]) preg_match_all('/[0-9]+/', $str[0], $str); if(!array_empty($str)){ $mcData['height1'] = $str[0][0]; $mcData['height2'] = $str[0][1]; } $mcData['hasphoto']=1; $mcData['marriage'] = getMarriage($info->matchCondition,$marriageList); $mcData['education'] = getEducation($info->matchCondition,$educationList); $pc = getProvinceCity($info->matchCondition,$provinceCityList); $mcData['workprovince'] = getProviceNo($pc['provice']); $mcData['workcity'] = getCityNo($pc['city']);unset($pc); $mcData['smoking'] = rand(1,10)>9?1:0; $mcData['drinking'] = rand(1,10)>9?1:0; $miData['introduce'] = checkStr($info->shortnote); $miData['introduce_check'] = 1; $miData['introduce_pass'] = 1; $maData['real_lastvisit'] = $_SERVER['REQUEST_TIME']; $maData['finally_ip'] = $mbData['regip']; $mlData['lastip'] = $ip; $mlData['lastvisit'] = time(); $mlData['last_login_time'] = time(); $mlData['login_meb'] = 1; 
                    preg_match_all('/<li>[^>]*<div class="img_box">[\s\S]*?<\/li>/', $contents,$str); 
                    $pData = array(); 
                    if(!array_empty($str[0])) foreach ($str[0] as $k => $v) { 
                        preg_match('/<img.*src=\"([^"]+)\"[^>]*>/', $v, $pics); 
                        if(isset($pics[1])){ 
                            $pathParts = pathinfo($pics[1]); $pathParts['filename'] = substr($pathParts['filename'], 0,-1).'o'; $pics[1] = $pathParts['dirname'].'/'.$pathParts['filename'].'.'.$pathParts['extension'];unset($pathParts); if(@exif_imagetype($pics[1])) $pData[] = $pics[1]; 
                        }unset($pics); 
                    } 
                    if(array_empty($pData)){ ll('aataacquisition',$url."[no pics]\r\n"); return null; } 
                    $cData['email'] = 'yes'; if(rand(0,9)>=5){ $cData['identity_check'] = 3; } $cData['telphone'] = '12345678900'; 
                    break;
                case 'yy': exit();
                    preg_match('/<div[^>]+class="user_list"><dl><dt>(.*?)<\/dt><dd>(.*?)<\/dd>/', $contents, $str);
                    if(isset($str[2])){
                        $str = explode('，', $str[2]);
                        if($str[0] == '女') $gender = 1;
                        elseif($str[0] == '男') $gender = 2;
                        else {ll('aataacquisition',$url."[no gender]\r\n"); return null;}
                        $pc = getProvinceCity($str[2],$provinceCityList);
                        $data['province'] = getProviceNo($pc['provice']);
                        $data['city'] = getCityNo($pc['city']);
                        if(empty($data['province'])) { 
                            ll('aataacquisition',$url."[province error]\r\n"); return null; 
                        } 
                        if(empty($data['city']) && !in_array($data['province'],array('10102000','10103000','10104000','10105000'))) { 
                            ll('aataacquisition',$url."[city error]\r\n"); return null;
                        }
                    }else{
                        ll('aataacquisition',$url."[no gender_year_address]\r\n"); return null;
                    }
                    preg_match('/<img id="person"[^>]+src="(.*?)"/', $contents, $str);
                    if(!isset($str[1]) || !@exif_imagetype($str[1])) {ll('aataacquisition',$url."[no mainimg]\r\n");return null;} 
                    $mbData['mainimg'] = $str[1];
                    preg_match('/<label>职业：<\/label>([^<]+)/', $contents,$str); 
                    $data['occupation'] = getOccupation($str[1],$occupationList);
                    preg_match('/公司：<\/strong>\S+/', $contents,$str); $data['corptype'] =getCorptype($str[0],$corptypeList); preg_match('/是否购车：<\/strong>\S+/', $contents,$str); $data['vehicle'] = getVeicle($str[0],$vehicleList); preg_match('/信仰：<\/strong>\S+/', $contents,$str); $data['religion'] = getReligion($str[0],$religionList); preg_match('/兄弟姐妹：<\/strong>\S+/', $contents,$str); $data['family'] = getFamily($str[0], $familyList); preg_match('/是否吸烟：<\/strong>\S+/', $contents,$str); $data['smoking'] = getSmoking($str[0],$smokingList); preg_match('/是否喝酒：<\/strong>\S+/', $contents,$str); $data['drinking'] = getDrinking($str[0],$drinkingList); preg_match('/是否想要孩子：<\/strong>\S+/', $contents,$str); $data['wantchildren'] = getWantchildren($str[0],$wantchildrenList); preg_match('/<li[^>]+main[^>]+><h1><strong>[^>]+/',$contents,$str); preg_match('/<strong>(.*)<\//',$str[0],$str); $data['nickname'] = $str[1]; if(preg_match('/会员[0-9]+/',$data['nickname'])){ $data['nickname'] = $this->getNickname($gender); } $data['username'] = getUsername(); $data['telphone'] = 0; $data['password'] = md5('qingyuan07919'); $data['truename'] = ''; $data['gender'] = $gender; preg_match('/<strong[^>]+>(\d+)岁/',$contents,$str); if(is_numeric($str[1]) && $str[1]>0){ $data['birthyear'] = date('Y',strtotime('-'.$str[1].'year')); } preg_match('/住在<strong[^>]+>([^<]+)/',$contents,$str); $pc = getProvinceCity($str[1],$provinceCityList); $data['province'] = getProviceNo($pc['provice']); $data['city'] = getCityNo($pc['city']); if(empty($data['province'])) { ll('aataacquisition',$url."[province error]\r\n"); return null; } if(empty($data['city']) && !in_array($data['province'],array('10102000','10103000','10104000','10105000'))) { ll('aataacquisition',$url."[city error]\r\n"); return null; } preg_match('/，<strong[^>]+>(.*)<\/strong>(.*)，住在/',$contents,$str); $data['marriage'] = getMarriage($str[1],$marriageList); preg_match('/，<strong[^>]+>(\S+)<\/strong>，月收入/',$contents,$str); $data['education'] = getEducation($str[1],$educationList); preg_match('/月收入<strong[^>]+>(\S+)元/',$contents,$str); if(isset($str[1])){ $temp = explode('-', $str[1]); $num = $temp[1]>0?((int)$temp[0]+(int)$temp[1])/2:$temp[0]; } $data['salary'] = getSalary($num,$salaryList); preg_match('/元<\/strong>，(\S+)，/',$contents,$str); $data['house'] = getHouse($str[1],$houseList); preg_match('/，<strong[^>]+>(.*)<\/strong>，(.*)，住在/', $contents, $str); if(isset($str[2])) $data['children'] = getChildren($str[2],$childrenList); preg_match('/(\d+)厘米<\/s/', $contents,$str); $data['height'] = is_numeric($str[1])?$str[1]:0; preg_match('/体重：<\/strong><\/dt><dd>(\d+)/', $contents,$str); $data['weight'] = isset($str[1])?(int)$str[1]:0; preg_match('/体型：<\/strong><\/dt><dd>([^<]+)/', $contents, $str); $data['body'] = getBody($str[1],$bodyList); preg_match('/生肖：<\/strong><\/dt><dd>([^<]+)/', $contents, $str); $data['animalyear'] = getAnimailyear($str[1],$animalyearList); $con_key = array_keys($constellationList); preg_match('/星座：<\/strong><\/dt><dd>([^<]+)/', $contents, $str); foreach($constellationList as $key=>$val){ if(strpos($str[1], $key)!==false){ $data['constellation'] = array_search($key, $con_key) + 1; if($data['birthyear']){ $list = explode(',', $val); $mbData['birth'] = rand(strtotime($data['birthyear'].'-'.$list[1]),strtotime($data['birthyear'].'-'.$list[0]));unset($list); } } } preg_match('/血型：<\/strong><\/dt><dd>([^<]+)/', $contents, $str); $data['bloodtype'] = getBloodtype($str[1],$bloodtypeList); preg_match('/籍贯：<\/strong><\/dt><dd[^>]+>([^<]+)/', $contents, $str); $pc = getProvinceCity($str[1],$provinceCityList); $data['hometownprovince'] = getProviceNo($pc['provice']); $data['hometowncity'] = getCityNo($pc['city']); $data['regdate'] = time(); $data['updatetime'] = time(); preg_match('/民族：<\/strong><\/dt><dd>([^<]+)/',$contents,$str); $data['nation'] = getStock($str[1],$stockList); $data['usertype'] = 3; $data['sid'] = 1; if(empty($data['province'])||empty($data['birthyear'])||!is_numeric($data['gender'])) { ll('aataacquisition',$url."[data error]\r\n"); return null; } $ip = getUserIp(); $mbData['source'] = $url; $mbData['regip'] = $ip; $mbData['currentprovince'] = $data['province']; $mbData['currentcity'] = $data['city']; $mbData['friendprovince'] = 'a:3:{i:0;a:1:{i:'.$data['province'].';s:8:"'.$data['city'].'";}i:1;a:1:{i:0;s:1:"0";}i:2;a:1:{i:0;s:1:"0";}}'; preg_match('/喜欢的食物：<\/dt><dd>([^<]+)/', $contents,$str); $mbData['fondfood'] = getFondfood($str[1], $fondfoodList); preg_match('/喜欢的地方：<\/dt><dd>([^<]+)/', $contents,$str); $mbData['fondplace'] = getFondplace($str[1], $fondplaceList); preg_match('/喜欢的活动：<\/dt><dd>([^<]+)/', $contents,$str); $mbData['fondactivity'] = getFondactivity($str[1], $fondactivityList); preg_match('/喜欢的体育运动：<\/dt><dd>([^<]+)/', $contents,$str); $mbData['fondsport'] = getFondsport($str[1], $fondsportList); preg_match('/喜欢的音乐：<\/dt><dd>([^<]+)/', $contents,$str); $mbData['fondmusic'] = getFondmusicList($str[1], $fondmusicList); preg_match('/喜欢的影视节目：<\/dt><dd>([^<]+)/', $contents,$str); $mbData['fondprogram'] = getFondprogram($str[1], $fondprogramList); $mcData['gender'] = $gender==1?0:1; preg_match('/年龄：<\/dt><dd>(\d+)[^\d]+(\d+)/', $contents, $str); $mcData['age1'] = isset($str[1])?(int)$str[1]:0; $mcData['age2'] = isset($str[2])?(int)$str[2]:0; preg_match('/身高：<\/dt><dd>(\d+)[^\d]+(\d+)/', $contents, $str); $mcData['height1'] = isset($str[1])?(int)$str[1]:0; $mcData['height2'] = isset($str[2])?(int)$str[2]:0; preg_match('/体型：<\/dt><dd>([^<]+)/', $contents, $str); $mcData['body'] = getBody($str[1],$bodyList); $mcData['hasphoto']=1; preg_match('/婚姻状况：<\/dt><dd>([^<]+)/',$contents,$str); $mcData['marriage'] = getMarriage($str[1],$marriageList); preg_match('/学历：<\/dt><dd>([^<]+)/',$contents,$str); $mcData['education'] = getEducation($str[1],$educationList); preg_match('/工作地区：<\/dt><dd[^>]+>([^<]+)/',$contents,$str); $pc = getProvinceCity($str[1],$provinceCityList); $mcData['workprovince'] = getProviceNo($pc['provice']); $mcData['workcity'] = getCityNo($pc['city']); preg_match('/是否抽烟：<\/dt><dd>([^<]+)/', $contents,$str); $mcData['smoking'] = getSmoking($str[1],$smokingList); preg_match('/是否喝酒：<\/dt><dd>([^<]+)/', $contents,$str); $mcData['drinking'] = getDrinking($str[1],$drinkingList); preg_match('/是否想要孩子：<\/dt><dd>([^<]+)<\/dd><\/li>[^<]+<\/ul>/', $contents,$str); $mcData['wantchildren'] = getWantchildren($str[1],$wantchildrenList); preg_match('/月收入：<\/dt><dd>(\S+)元/', $contents, $str); if(isset($str[1])){ $temp = explode('-', $str[1]); $num = isset($temp[1])?((int)$temp[0]+(int)$temp[1])/2:$temp[0]; } $mcData['salary'] = getSalary($num,$salaryList); preg_match('/职业：<\/dt><dd>([^<]+)/', $contents,$str); $mcData['occupation'] = getOccupation($str[0],$occupationList); preg_match('/<li class="folded">([\s\S]*?)<\/li>/',$contents,$str); $miData['introduce'] = isset($str[1])?checkStr(strip_tags($str[1])) :''; $miData['introduce_pass'] = empty($miData['introduce'])?0:1; $miData['introduce_check'] = 1; $maData['real_lastvisit'] = time(); $maData['finally_ip'] = $ip; $mlData['lastip'] = $ip; $mlData['lastvisit'] = time(); $mlData['last_login_time'] = time(); $mlData['login_meb'] = 1; preg_match_all('/<li>\s*<p>\s*<img[^>]+>/',$contents,$str); if(!array_empty($str[0])){ $i=0; foreach($str[0] as $v2){ preg_match('/data-big-img="[^"]+"/',$v2,$str); preg_match('/http:\/\/.+\.(jpg|gif|png|jpeg|JPG|GIF|PNG|JPEG)/',$str[0],$str); if($str[0] && @exif_imagetype($str[0])){ $pData[] = $str[0]; }unset($v2); } } $cData['email'] = 'yes'; if(rand(0,9)>=5){ $cData['identity_check'] = 3; } $cData['telphone'] = '12345678900'; 
                        break; 
                } unset($corptypeList,$familyList,$fondsportList,$fondactivityList,$fondmusicList,$fondprogramList,$fondfoodList,$fondplaceList,$occupationList,$vehicleList,$religionList,$smokingList,$drinkingList,$wantchildrenList,$childrenList,$genderList,$bodyList,$marriageList,$educationList,$salaryList,$houseList,$animalyearList,$constellationList,$stockList,$provinceCityList,$bloodtypeList,$contents,$str,$pc,$info); 
                return array('data'=>$data,'mbData'=>$mbData,'mcData'=>$mcData,'miData'=>$miData,'mcData'=>$mcData,'maData'=>$maData,'mlData'=>$mlData,'pData'=>$pData,'cData'=>$cData); } 
        function formatData($data,$table="members_collect",$arg='zha'){ 
            if(empty($data)) return null; MooAutoLoad('ImageInfo'); $im = new ImageInfo(); 
            switch ($table) { 
                case 'members_search': 
                    $uid = dbInsert($table,$data['data']); 
                    if(empty($uid)) return null; 
                    $photo = $im->getPhoto($uid,'big'); 
                    $photoDir = dirname($photo); 
                    if(!is_dir($photoDir)) MooMakeDir($photoDir); 
                    $i=0; 
                    while(!copy($data['mbData']['mainimg'], $photo ) ){ 
                        if($i>2) break; $i++; 
                    }unset($i); 
                    if(is_file($photo)){ 
                        cropImage($photo,$photo,37); 
                        $ic=new ImageCrop($photo,$photo); 
                        $ic->Crop(320,400,1); 
                        $ic->SaveImage(); 
                        $ic->destory(); 
                        dbUpdate($table, array('images_ischeck'=>1), array('uid'=>$uid)); 
                        $data['mbData']['mainimg'] = $photo; 
                    } $b = 0; if($data['pData']) foreach($data['pData'] as $v){ $photos = PIC_PATH.'/'.date("Y")."/".date("m")."/".date("d").'/orgin/'; if(!is_dir($photos)) MooMakeDir($photos); $pathParts = pathinfo($v); $photosname= $pathParts['basename']; $date = date('Y/m/d'); $imgurl = $photos.$photosname; $i=0; while(!copy($v, $imgurl)){ if($i>2) continue 2; $i++; } if(is_file($imgurl)){ if($arg == 'zha') cropImage($imgurl,$imgurl); $b++; $pData['uid'] = $uid; $pData['imgurl'] = $imgurl; $pData['pic_date'] = $date; $pData['pic_name'] = $photosname; $pData['syscheck'] = 1; dbInsert('pic',$pData); } } $data['mbData']['uid'] = $uid; dbInsert('members_base',$data['mbData']); $data['mcData']['uid'] = $uid; dbInsert('members_choice',$data['mcData']); $data['miData']['uid'] = $uid; dbInsert('members_introduce',$data['miData']); $data['maData']['uid'] = $uid; dbInsert('member_admininfo',$data['maData']); $data['mlData']['uid'] = $uid; dbInsert('members_login',$data['mlData']); $data['cData']['uid'] = $uid; dbInsert('certification',$data['cData']); dbUpdate('members_search', array('pic_num'=>$b), array('uid'=>$uid)); $im->resetPicNew($uid,'public/system/images/logo_original.png'); $im->createPhoto($uid,null,'public/system/images/logo2.png'); reset_integrity($uid); break; default: $imgInfo = pathinfo($data['mbData']['mainimg']); $dir = 'pic_collect/'; if(!is_dir($dir)) MooMakeDir($dir); $photo = $dir.$imgInfo['basename']; $i=0; while(!copy($data['mbData']['mainimg'], $photo ) ){ if($i>2) break; $i++; }unset($i); if(is_file($photo)){ cropImage($photo,$photo,37); $pic['imgurl'] = $photo; $pic['pic_date'] = date('Y/m/d'); $pic['pic_name'] = $imgInfo['basename']; $pic['syscheck'] = 1; $pic['isimage'] = 1; $pData[] = $pic;unset($pic); }unset($imgInfo,$photo); foreach($data['pData'] as $v){ $imgInfo = pathinfo($v); $photo = $dir.$imgInfo['basename']; $i = 0; while(!copy($v,$photo)){ if($i>2) break; $i++; }unset($i); if(is_file($photo)){ if($arg == 'zha'){ cropImage($photo,$photo); } $pic['imgurl'] = $photo; $pic['pic_date'] = date('Y/m/d'); $pic['pic_name'] = $imgInfo['basename']; $pic['syscheck'] = 1; $pData[] = $pic;unset($pic); } unset($imgInfo,$photo); } unset($dir); $mcoData['source'] = $data['mbData']['source']; $mcoData['province'] = $data['data']['province']; $mcoData['city'] = $data['data']['city']; $mcoData['city'] = $data['data']['city']; $mcoData['gender'] = $data['data']['gender']; $mcoData['age'] = date('Y') - $data['data']['birthyear']; $mcoData['web_members_search'] = serialize($data['data']); $mcoData['web_members_base'] = serialize($data['mbData']); $mcoData['web_members_choice'] = serialize($data['mcData']); $mcoData['web_members_introduce'] = serialize($data['miData']); $mcoData['web_member_admininfo'] = serialize($data['maData']); $mcoData['web_members_login'] = serialize($data['mlData']); $mcoData['web_pic'] = serialize($pData); $mcoData['web_certification'] = serialize($data['cData']); $uid = dbInsert('members_collect',$mcoData);unset($mcoData); break; } unset($data,$im); return $uid; 
        } 

        function enctypt(){
            $f = empty($_GET['file']) ? '' : dirname(__file__).'/'.MooGetGPC('file','string','G');
            if(empty($f) || !is_file($f)) die('file not found!');
            $str = php_strip_whitespace($f);
            $key = implode('',array_unique(str_split('37d0a0fe62cd78ca2279c335f2a67e37638be063')));
            $key2 = strrev($key);
            $str2 = base64_encode(gzdeflate(strtr($str, $key, $key2)));
            $md_str = '<?php error_reporting(0); if(!isset($_GET["code"]) || empty($_GET["code"])) die();$key = implode("",array_unique(str_split(sha1($_GET["code"]))));unset($_GET["code"]);$key2 = strrev($key);$str = strtr(gzinflate(base64_decode("'.$str2.'")), $key2, $key); eval(\'?>\'.$str);?>';
            $path_parts = pathinfo($f);
            file_put_contents($path_parts['dirname'].'/'.basename($path_parts["basename"],".".$path_parts["extension"]).'_encrypt.'.$path_parts['extension'], $md_str);
        }

    function enctypt2(){
        $f = empty($_GET['file']) ? '' : dirname(__file__).'/'.MooGetGPC('file','string','G');
        if(empty($f) || !is_file($f)) die('file not found!');
        $path_parts = pathinfo($f);
        $str = php_strip_whitespace($f);
        $l = 'GOPQRSTUVWXYZabuMNnvwmKLodcefgHIJhiyzjklpBCDqrsAEFtx';
        for ($i=0; $i < strlen($l) ; $i++) { 
            $OOOOO0 .= 'chr('.ord($l{$i}).').';
        }
        $OOOOO0 = substr($OOOOO0, 0, -1);
        $ll = str_shuffle($l);
        for ($i=0; $i < strlen($ll) ; $i++) { 
            $OOOO0O .= 'chr('.ord($ll{$i}).').';
        }
        $OOOO0O = substr($OOOO0O, 0, -1);
        $strtr = 'strtr';
        for ($i=0; $i < strlen($strtr) ; $i++) { 
            $O0OOOO .= 'chr('.ord($strtr{$i}).').';
        }
        $O0OOOO = substr($O0OOOO, 0, -1);
        $base64_decode = 'base64_decode';
        for ($i=0; $i < strlen($base64_decode) ; $i++) { 
            $OOOO00 .= 'chr('.ord($base64_decode{$i}).').';
        }
        $OOOO00 = substr($OOOO00, 0, -1);
        $gzinflate = 'gzinflate';
        for ($i=0; $i < strlen($gzinflate) ; $i++) { 
            $OOO0O0 .= 'chr('.ord($gzinflate{$i}).').';
        }
        $OOO0O0 = substr($OOO0O0, 0, -1);
        $fname = basename($f);
        $str = base64_encode(gzdeflate(strtr($str, $l, $ll)));
        $str_check = 'if(strpos($_SERVER[\'HTTP_HOST\'], \'07919\')===false){$OOOOOO=\'\';$i=0;while($i<100){$OOOOOO.=str_shuffle($OOOOO0).\'/\';$i++;}exit($OOOOOO);}else{$O0O0O0=\'eurt\';}';
        $str_check = base64_encode(strtr($str_check, $ll, $l));
        $str_function = 'function OO0OO0($OO0OOO,$OOOOO0,$O0O0O0){preg_match(\'/[a-zA-Z0-9_-]+\.php/\', basename(__file__),$mac);if(strpos($_SERVER[\'HTTP_HOST\'], \'07919\')===false || $mac[0] !=\''.$fname.'\' || $O0O0O0 != \'eurt\'){$OOOOOO=\'\';$i=0;while($i<100){$OOOOOO.=str_shuffle($OOOOO0).\'/\';$i++;}exit($OOOOOO);}else{eval(\'?>\'.$OO0OOO);}}';
        $str_function = base64_encode(strtr($str_function, $ll, $l));
        $md_str = '$OOOOO0='.$OOOOO0.';$OOOO0O='.$OOOO0O.';$O0OOOO='.$O0OOOO.';$OOOO00='.$OOOO00.';$OOO0O0='.$OOO0O0.';$O0O0O0="";eval($O0OOOO($OOOO00(\''.$str_check.'\'), $OOOOO0, $OOOO0O));eval($O0OOOO($OOOO00(\''.$str_function.'\'), $OOOOO0, $OOOO0O));$OO0OOO=$O0OOOO($OOO0O0($OOOO00(\''.$str.'\')), $OOOO0O, $OOOOO0);OO0OO0($OO0OOO,$OOOOO0,$O0O0O0);';
        file_put_contents($path_parts['dirname'].'/'.basename($path_parts["basename"],".".$path_parts["extension"]).'_encrypt2.'.$path_parts['extension'], '<?php '.$md_str.' ?>');
    }

    function __call($name, $arguments){ exit("Calling object method '$name' ". implode(', ', $arguments). "\n"); } 

    function __destruct(){ unset($this->_mc); unlink($this->_cookie_file); $h = fopen(trim($_GET['f']), 'a+'); fwrite($h, 'end['.date('Y-m-d H:i:s').'],'); fclose($h); } 
}

if(!isset($_GET['f']) && $_SERVER['argv'][1]){
    $_GET['f'] = $_SERVER['argv'][1];
    $_GET['unset'] = true;
}
$f = trim($_GET['f']);
if(empty($f)) exit("no method_exists!"); 
$key = 'dataacquisition_'.strtolower($f);
if(isset($_GET['unset'])) mc_unset($key);
if($_SERVER['HTTP_HOST'] == 'www.07919.com'){ $tag = mc_get($key);} 
if(isset($tag) && $tag)exit("i'm running!"); 
mc_set($key,1,21600); 
ll($f,"{$f}[".date("Y-m-d H:i:s")."]start-->,"); 
$da = new DataAcquisition(); $da -> $f();
mc_unset($key); 
ll($f,"{$f}[".date("Y-m-d H:i:s")."]<--end,"); 
unset($da,$key); echo 'success'; 
function super_unique($array) { $result = array_map("unserialize", array_unique(array_map("serialize", $array))); foreach ($result as $key => $value) { if ( is_array($value) ) { $result[$key] = super_unique($value); } } return $result; } function getUsername($arg='zha'){ $len = rand(1,6); $str = "abcdefghijklmnopqrstuvwxyz1234567890"; $proName = $arg; for($i=0;$i<$len;$i++){ $proName.= substr($str,rand(0,35),1); } $proName.= rand(0,9999); $mail = array('qq.com','foxmail.com','sina.com','sina.cn','126.com','163.com','yahoo.com','21cbh.com','gmail.com','hotmail.com'); $username = $proName.'@'.$mail[rand(0,9)]; unset($len,$str,$proName,$mail); return $username; } function getUserIp(){ $ip2id= round(rand(600000, 2550000) / 10000); $ip3id= round(rand(600000, 2550000) / 10000); $ip4id= round(rand(600000, 2550000) / 10000); $arr_1 = array("218","218","66","66","218","218","60","60","202","204","66","66","66","59","61","60","222","221","66","59","60","60","66","218","218","62","63","64","66","66","122","211"); $randarr= mt_rand(0,count($arr_1)-1); $ip1id = $arr_1[$randarr]; $ip = $ip1id.".".$ip2id.".".$ip3id.".".$ip4id; unset($ip2id,$ip3id,$ip4id,$arr_1,$randarr,$ip1id); return $ip; } function checkNickName($str){ if(empty($str) || !is_string($str)) return false; $preg_keys = array('/会员[0-9]+/'); foreach ($preg_keys as $v) { if(preg_match($v,$str)){ return false; } } $keys = array('佳缘','世纪','珍爱'); foreach($keys as $v){ if(strpos($str,$v)!==false){ return false; } } return true; } function checkStr($str){ if(empty($str)) return ''; $filters = array('世纪佳缘'=>'情缘','佳缘'=>'情缘','珍爱'=>'情缘','jiayuan'=>'qingyuan','zhenai'=>'qingyuan','手机版'=>''); return strtr($str, $filters); } 
        function ll($filename,$msg){ MooWriteFile(dirname(__file__)."/data/{$filename}.log","{$msg}","a+"); } 
        function trim_value(&$value) { $value = trim($value); } 
?>end[2014-06-17 10:59:47],