<?php
set_time_limit(0);

$ip = '127.0.0.1';
$port = '10000';
$sock =  socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if( $sock < 0){
	exit('socket_create() 失败的原因是:'.socket_strerror($sock));
}
$ret = socket_bind($sock, $ip, $port);
if($ret < 0) {
	exit('socket_bind() 失败的原因是:'.socket_strerror($ret));
}
$ret = socket_listen($sock, 4);
if($ret < 0) {
	exit('socket_listen() 失败的原因是:'.socket_strerror($ret));
}
$count = 0;
do {
	if(($msgsock = socket_accept($sock))<0 ){
		exit("socket_accept() failed: reason:".socket_strerror($msgsock));break;
	}else{
		$msg = "测试成功！\n";
		socket_write($msgsock, $msg, strlen($msg));
		echo "测试成功了啊\n";
		$buf = socket_read($msgsock,8192);
		$talkback = "收到的信息:$buf\n";
		echo $talkback;
		if(++$count >= 5){
			break;
		};
	}
	socket_close($msgsock);
}while (true);
socket_close($sock);
?>