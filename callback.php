<?php
require './framwork/MooPHP.php';
require_once("./connect/API/qqConnectAPI.php");
$qc = new QC();
$qc->qq_callback();
$openId = $qc->get_openid();
if(empty($openId)){
	MooMessage("登录错误！",'/index.php','01');exit();
}
MooSetCookie('qq_open_id',$openId,86400);
$mo = MooFastdbGet('members_other','qq_open_id',$openId);

$MooUid = 0;	//用户信息
MooUserInfo();
if($MooUid){
	if($mo && $mo['qq_open_id']){
		MooMessage("您当前是登录状态！",'/index.php','03');exit();
	}else{
		$_MooClass['MooMySQL']->query("UPDATE {$dbTablePre}members_other SET qq_open_id='{$openId}' WHERE uid={$MooUid}");
		if(!$_MooClass['MooMySQL']->affectedRows()){
			$_MooClass['MooMySQL']->query("INSERT INTO {$dbTablePre}members_other (uid,qq_open_id) VALUES ({$MooUid},'{$openId}')");
		}
		MooMessage("恭喜您,您的帐号已与QQ绑定,以后可使用QQ登录情缘网！",'/index.php','03');exit();
	}
}else{
	if($mo && $mo['qq_open_id']){
		$user = MooFastdbGet('members_search','uid',$mo['uid']);
		if($user){
			if($user['is_lock']!=1){
				MooMessage("很抱歉您的用户名已经被锁定！<br>请联系情缘网客服：<b>400-800-7529</b>","{$returnurl}",'01',6);
			}else{
				MooSetCookie('auth',MooAuthCode("{$user['uid']}\t{$user['password']}",'ENCODE'),86400*7);
				$_MooClass['MooMySQL']->query("update {$dbTablePre}members_login set last_login_time = '{$_SERVER['REQUEST_TIME']}',login_meb = login_meb+1,lastvisit='{$_SERVER['REQUEST_TIME']}' where uid = '{$user['uid']}'");//更新最后登录时间
				MooSetCookie('last_login_time', $time,86400);	//会员最后登录时间
				$_MooClass['MooMySQL']->query("INSERT INTO `{$dbTablePre}admin_remark` SET sid='".getSid($user['uid'])."',title='会员上线',content='ID:{$user['uid']}会员刚刚通过pc机上线[QQ登录],请联系',awoketime='".($_SERVER['REQUEST_TIME']+3600)."',dateline='{$_SERVER['REQUEST_TIME']}'");	//note 客服提醒
				MooSetCookie('username',$user['username'],3600);	//note 记住用户名
				MooPlugins('ipdata');
				$online_ip = GetIP();
				if($online_ip != $user['lastip']){
					$user_address = convertIp($online_ip);
				    include("./module/crontab/crontab_config.php");
				    foreach($provice_list as $key=>$provice_arr){
				        if(strstr($user_address,$provice_arr)!==false){
				            $province=$key;
				            break;
				        }
				    }
				    if(empty($province)){
				        $province=$current_user['province'];
				    }
				    //得到市对应的城市代号
				    foreach($city_list as $city_key => $city_val){
				    	if(strstr($user_address,$city_val)!==false){
				        	$city = $city_key;
				        	break;
				    	}
				    }
				}
				MooSetCookie('province', $user['province'],86400);
				MooSetCookie('city', $user['city'],86400);
				$lastactive = time();
				$uid = $user['uid'];
				//note 更新用户的最近登录ip和最近登录时间
				$updatesqlarr = array(
					'lastip' => $online_ip,
				);
				$wheresqlarr = array(
					'uid' => $uid
				);
				updatetable("members_login",$updatesqlarr,$wheresqlarr);
				if(MOOPHP_ALLOW_FASTDB){
					$val = array();
		            $val['lastip'] = $online_ip;
		            //$val['client']=0;
		            $val['last_login_time']= $time;
		            $val['lastvisit']=$time;
		            //$val['isOnline']=1;
					MooFastdbUpdate('members_login','uid',$uid, $val);//!!
				}
				$sql_ip = "SELECT last_ip,finally_ip FROM {$GLOBALS['dbTablePre']}member_admininfo WHERE uid='{$uid}'";
				$member_admin_info = $GLOBALS['_MooClass']['MooMySQL']->getOne($sql_ip);
				if(!empty($member_admin_info)){
					$sql_ip = "UPDATE {$GLOBALS['dbTablePre']}member_admininfo SET last_ip='{$member_admin_info['finally_ip']}',finally_ip='{$online_ip}',real_lastvisit='{$lastactive}' WHERE uid='{$uid}'";
				}else{
					$sql_ip = "INSERT INTO {$GLOBALS['dbTablePre']}member_admininfo SET finally_ip='{$online_ip}',uid='{$uid}',real_lastvisit='{$lastactive}'";
				}
				$GLOBALS['_MooClass']['MooMySQL']->query($sql_ip);
				MooMessage("登录成功！",'/index.php','03');exit();
				exit();
			}
		}else{
			MooMessage("帐号不存在","index.php?n=login",'01');
		}
	}else{
		MooMessage('已有帐号?&nbsp;<a href="/index.php?n=login">点此进行绑定</a>&nbsp;还没有帐号？&nbsp;<a href="/index.php?n=register">点此进行注册</a>',null,null,0);exit();
	}
}
?>
