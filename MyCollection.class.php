<?php
class MyCollection {
	
	public $cookie_file;
	public $userAgent;
	public $proxy;
	public $httpHearder;

	/**
	* $info['userAgent'] = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11"
	* $info['httpHearder'] = array('X-FORWARDED-FOR:211.71.95.158', 'CLIENT-IP:211.71.95.158')
	* $info['proxy'] = "http://110.173.0.18:80"
	*/
	public function MyCollection($cookie_file=null, $proxy = null, $httpHearder = null, $userAgent="Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11"){
		if(is_file($cookie_file)){
			$this->cookie_file = $cookie_file;
		}
		$this->userAgent = $userAgent;
		$this->proxy = $proxy;
		$this->userAgent = $userAgent;
		$this->httpHearder = $httpHearder;
	}
	/**
	* web login
	* $info['url']	
	* $info['loginData']
	* $info['peferer'] = "http://profile.zhenai.com/login/login.jsp"
	*/
	function webLogin($info){
		if(!is_array($info)) return false;
		if(empty($info['url'])) return false;
		$ch= curl_init();
		curl_setopt($ch, CURLOPT_URL, $info['url']);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		if(empty($info['loginData'])) return false;
		curl_setopt($ch, CURLOPT_POSTFIELDS, $info['loginData']);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		if($this->userAgent)
		curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
		if($this->httpHearder)
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->httpHearder);
		if($this->proxy)
		curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
		if($info['peferer'])
		curl_setopt ($ch, CURLOPT_REFERER, $info['peferer']);
		if($this->cookie_file)
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
		$contents = curl_exec($ch);
		if(curl_errno($ch)) return false;
		curl_close($ch);
		return $contents;
		unset($info);
	}

	/**
	* web visit
	*/
	function webVisit($info){
		$ch = curl_init($info['url']);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if($this->httpHearder)
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->httpHearder);
		if($this->userAgent)
		curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
		if($this->proxy)
		curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
		if(isset($info['peferer']))
		curl_setopt ($ch, CURLOPT_REFERER, $info['peferer']);
		if($this->cookie_file)
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
		$contents = curl_exec($ch);
		if(curl_errno($ch)) return false;
		curl_close($ch);
		return $contents;
		unset($info);
	}
}
?>