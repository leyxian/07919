<?php
$url='http://m.weather.com.cn/data/101180101.html';
$xinxiang='http://m.weather.com.cn/data/101180301.html';
$w=curl($url);
$weather=json_decode($w);
$weatherinfo=object_to_array($weather);
$info=$weatherinfo['weatherinfo'];
$str=$info['date_y'].','.$info['week'].'。'.$info['city'].'今天气温：'.$info['temp1'].'，天气：'.$info['weather1'].'有'.$info['wind1'].',风力：'.$info['fx1'].',穿衣建议：'.$info['index_d'].$info['index48_d'];

$feixin='http://wjima.a173.cnaaa4.com/feixin/str.php';
$postArray['userName']='13696545523';//飞信账号
$postArray['password']='nn2006313';//飞信密码
$postArray['content']=$str;//发送内容
$postArray['other']='13696545523';//接收方手机号，无此参数，默认给自己发送
 
$send=curl($feixin,$postArray);
 
print_r($send);die();
 
function curl($url, $postFields = null){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FAILONERROR, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	//https 请求
	if(strlen($url) > 5 && strtolower(substr($url,0,5)) == "https" ) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	}
 
	if (is_array($postFields) && 0 < count($postFields)){
		$postBodyString = "";
		$postMultipart = false;
		foreach ($postFields as $k => $v){
			if("@" != substr($v, 0, 1))//判断是不是文件上传
			{
				$postBodyString .= "$k=" . urlencode($v) . "&"; 
			}
			else//文件上传用multipart/form-data，否则用www-form-urlencoded
			{
				$postMultipart = true;
			}
		}
		unset($k, $v);
		curl_setopt($ch, CURLOPT_POST, true);
		if ($postMultipart)
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
		}
		else
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));
		}
	}
	$reponse = curl_exec($ch);
 
	if (curl_errno($ch)){
		throw new Exception(curl_error($ch),0);
	}
	else{
		$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if (200 !== $httpStatusCode){
		throw new Exception($reponse,$httpStatusCode);
		}
	}
	curl_close($ch);
	return $reponse;
}
function object_to_array($obj){
	$_arr = is_object($obj) ? get_object_vars($obj) : $obj;
	foreach ($_arr as $key => $val)	{
		$val = (is_array($val) || is_object($val)) ? object_to_array($val) : $val;
		$arr[$key] = $val;
	}
	return $arr;
}
?>