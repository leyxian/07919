<?php
     //加载
	error_reporting(E_ALL);
	set_time_limit(0);
	 require 'config.php';
     require 'framwork/MooPHP.php';

	 $raw_post_data =  isset($GLOBALS['HTTP_RAW_POST_DATA'])?$GLOBALS['HTTP_RAW_POST_DATA']:'';
	 if(empty($raw_post_data))  $raw_post_data = file_get_contents("php://input");  
	 //echo $raw_post_data;
	 //$memcachhost="127.0.0.1";
	 //$memcacheport="11211";
	 //$memcached = new Memcache;
	 //$memcached->connect($memcachehost, $memcacheport);
	 
	 /* $key=uniqid('telpay');
	 $memcached->set($key,$raw_post_data,1800);
	 
	 $result=$memcached->get($key);
	 
	 if (empty($result)) $result=$raw_post_data; */
	 $result=$raw_post_data;
	 
	 
	 
	 $transcode=getXmlValueByTag($result,'transcode');
	 $pan=getXmlValueByTag($result,'pan');
	 $amount=getXmlValueByTag($result,'amount');
	 $transdate=getXmlValueByTag($result,'transdate');
	 $odtransdate=getXmlValueByTag($result,'odtransdate');
	 $sys_trace=getXmlValueByTag($result,'sys_trace');
	 $localtime=getXmlValueByTag($result,'localtime');
	 $localdate=getXmlValueByTag($result,'localdate');
	 $panexpr=getXmlValueByTag($result,'panexpr');
	 $settledate=getXmlValueByTag($result,'settledate');
	 $mhttype=getXmlValueByTag($result,'mhttype');
	 $servicecode=getXmlValueByTag($result,'servicecode');
	 $Recvorgcode=getXmlValueByTag($result,'Recvorgcode');
	 $Sendorgcode=getXmlValueByTag($result,'Sendorgcode');
	 $Srcinstno=getXmlValueByTag($result,'Srcinstno');
	 $destinstno=getXmlValueByTag($result,'destinstno');
	 $referenceno=getXmlValueByTag($result,'referenceno');
	 $rejcode=getXmlValueByTag($result,'rejcode');
	 $terminal=getXmlValueByTag($result,'terminal');
	 $mcht_no=getXmlValueByTag($result,'mcht_no');
	 $mhtaddr=getXmlValueByTag($result,'mhtaddr');
	 $mchttranstype=getXmlValueByTag($result,'mchttranstype');
	 $tel_no=getXmlValueByTag($result,'tel_no');
	 $channelid=getXmlValueByTag($result,'channelid');
	 $channeladdr=getXmlValueByTag($result,'channeladdr');
	 $recorded=getXmlValueByTag($result,'recorded');
	 $moneycode=getXmlValueByTag($result,'moneycode');
	 $persionid=getXmlValueByTag($result,'persionid');
	 $revflag=getXmlValueByTag($result,'revflag');
	 $mac=getXmlValueByTag($result,'mac');
	 
	 //发送字符串 商户销帐/入账 请求 从交易字段 一直 到 交易标志 字段结束 ,组构成类似以下的发送字段：
	 //100362258802969880180000000000300805183112201202091124160000081831120805000001240000009999112799991111999911279999111600000002679700050000309610150390005B0B2BBD5BAC5B0D920000013592693823309610150390005B0B2BBD5BAC5B0D9201831156016101001980120296320
	 
	  $sendStr=$transcode.$pan.$amount.$transdate.$odtransdate.$sys_trace.$localtime.$localdate.$panexpr.$settledate.$mhttype.$servicecode.$Recvorgcode.$Sendorgcode.$Srcinstno.$destinstno.$referenceno.$rejcode.$terminal.$mcht_no.$mhtaddr.$mchttranstype.$tel_no.$channelid.$channeladdr.$recorded.$moneycode.$persionid.$revflag;
	 
	 //require_once 'socket_send.php';
	
	// set some variables
	$host = "123.138.28.20";
	$port = 13000;

	// don't timeout!
   

	// create socket
	$commonProtocol = getprotobyname("tcp");
	$socket = socket_create(AF_INET, SOCK_STREAM, $commonProtocol) or die("Could not create socket/n");
	$connection=socket_connect($socket,$host,$port);
	if (!$connection)
	{  
	   //$errorcode = socket_strerror();
	   //error_log("time:".date('Y-m-d H:i:s').",telphone：".$tel_no.",tracenum:".$transcode."，pan:".$pan ."，error:".$errorcode ,3 ,'/var/www/7651.com/error/telPay.log');
	   exit( "Can't Connect to Asterisk Manager Port .");
	}
	else
	{
	    // echo "Connect to Asterisk Manager Successfully.";
	}
    
	$workKey="0123456789ABCDEF";	//工作密钥
	// $sendStr="100362258802969880180000000000300805183112201202091124160000081831120805000001240000009999112799991111999911279999111600000002679700050000309610150390005B0B2BBD5BAC5B0D920000013592693823309610150390005B0B2BBD5BAC5B0D9201831156016101001980120296320";
	$logintext="001|".$workKey."|".$sendStr;
	$Socket_logintext=substr("0000".strlen($logintext),-4).$logintext;
	//$Socket_logintext="0541001|0123456789ABCDEF|";
	socket_send($socket,$Socket_logintext,strlen($Socket_logintext),0x4);
	//socket_write($socket,$Socket_logintext,strlen($Socket_logintext));

	 /* while ($buffer=socket_read($socket,24,PHP_NORMAL_READ))
	 {

		echo $buffer;
	  
	} */

	// echo "Reading response:\n\n";
	
	$buffer = '';//This is my buffer.
	if (false !== ($bytes = socket_recv($socket, $buffer, 2048, MSG_WAITALL))) {
		// echo "Read $buffer\n$bytes bytes from socket_recv(). Closing socket...";
		//buffer 返回类似值 0020001|04B955A6E284A187 
	} else {
		echo "socket_recv() failed; reason: " . socket_strerror(socket_last_error($socket)) . "\n";
	}

	 
	socket_close($socket);

	 
	 $mac2=array();
	 $arr=explode("|",$buffer);
	 if(count($arr)>=2){
	    $mac2=$arr[1];
	 } 
	 
	
	 //把xml内容通过socket发送到服务端，服务器会返回mac，用来和原<mac>域做比对,然后你要把xml在通过http返回到服务商，返回的这个mac也可以通过服务端进行计算 

	 if(!empty($mac) && $mac==$mac2){ 
	     $xml="<?xml version=\"1.0\" encoding=\"UTF-8\"?><package><head><transcode>";
		 $xml.=$transcode."</transcode></head><body><request><pan>";
		 $xml.=$pan."</pan><amount>";
		 $xml.=$amount."</amount><transdate>";
		 $xml.=$transdate."</transdate><odtransdate>";
		 $xml.=$odtransdate."</odtransdate><sys_trace>";
		 $xml.=$sys_trace."</sys_trace><localtime>";
		 $xml.=$localtime."</localtime><localdate>";
		 $xml.=$localdate."</localdate><panexpr>";
		 $xml.=$panexpr."</panexpr><settledate>";
		 $xml.=$settledate."</settledate><mhttype>";
		 $xml.=$mhttype."</mhttype><servicecode>";
		 $xml.=$servicecode."</servicecode><Recvorgcode>";
		 $xml.=$Recvorgcode."</Recvorgcode><Sendorgcode>";
		 $xml.=$Sendorgcode."</Sendorgcode><Srcinstno>";
		 $xml.=$Srcinstno."</Srcinstno><destinstno>";
		 $xml.=$destinstno."</destinstno><referenceno>";
		 $xml.=$referenceno."</referenceno><terminal>";  //<rejcode>";$xml.=$rejcode."</rejcode>
		 $xml.=$terminal."</terminal><mcht_no>";
		 $xml.=$mcht_no."</mcht_no><mhtaddr>";
		 $xml.=$mhtaddr."</mhtaddr><mchttranstype>";
		 $xml.=$mchttranstype."</mchttranstype><tel_no>";
		 $xml.=$tel_no."</tel_no><channelid>";
		 $xml.=$channelid."</channelid><channeladdr>";
		 $xml.=$channeladdr."</channeladdr><recorded>";
		 $xml.=$recorded."</recorded><moneycode>";
		 $xml.=$moneycode."</moneycode><persionid>";
		 $xml.=$persionid."</persionid><revflag>";
		 $xml.=$revflag."</revflag><mac>";
		 $xml.=$mac."</mac></request><response><rejcode>000</rejcode></response></body></package>";
	     echo $xml;
	 	 
		 $apply_time=strtotime($odtransdate);
		 
		 $result=$resutl_other=array();
		 $sql="select id from web_payment_new where sysTraceNum={$sys_trace} and apply_time={$apply_time} and Pan={$pan}";
		 $result=$_MooClass['MooMySQL']->getOne($sql,true);
		 if(!empty($result['id'])){
		    $sql="update web_payment_new set check_sid=21,status=1 where sysTraceNum={$sys_trace} and apply_time={$apply_time} and Pan={$pan}";
		    $_MooClass['MooMySQL']->query($sql);
		 }
		 
		 //补款预付
		 $sql="select id from web_payment_other where sysTraceNum={$sys_trace} and apply_time={$apply_time} and Pan={$pan}";
		 $result_other=$_MooClass['MooMySQL']->getOne($sql,true);
		 if(!empty($result_other['id'])){
		    $sql="update web_payment_other set status=1,pay_time={$apply_time} where sysTraceNum={$sys_trace} and apply_time={$apply_time} and Pan={$pan}";
		    $_MooClass['MooMySQL']->query($sql);
		 }
		 

	  }  
	  
	  
	  
	  /**
	   *  function :   xml文档解析函数
	   *  argument1:   $inXmlset  xml文档
	   *  $argument2 :  $needle  xml文档节点
	   * 
	   *  $return   :返回xml节点值
	  */
	  
	  function getXmlValueByTag($inXmlset,$needle){
	    $tagValue='';
        $resource    =    xml_parser_create();//Create an XML parser
        xml_parse_into_struct($resource, $inXmlset, $outArray);// Parse XML data into an array structure
        xml_parser_free($resource);//Free an XML parser
       
        for($i=0;$i<count($outArray);$i++){
            if($outArray[$i]['tag']==strtoupper($needle)){
                $tagValue    =    $outArray[$i]['value'];
            }
        }
        return $tagValue;
     } 
?>