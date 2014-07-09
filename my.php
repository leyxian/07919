<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
require 'config.php';
require 'framwork/MooPHP.php';

$time =  rand(strtotime("1988-03-21"),strtotime("1988-04-20"));
pr(date('Y-m-d',$time));

$sec = ceil(microtime(true)-strtotime('1988-11-05 13:00:00'));
pr('<html><head><head><body><input name="sec" value="'.$sec.'"><script type="text/javascript"> function dosec(){console.log(document.getElementsByName("sec")[0].value);var sec = document.getElementsByName("sec")[0].value;sec=parseInt(sec)+1;document.getElementsByName("sec")[0].value=sec;window.setTimeout(dosec,1000);}dosec();</script></body></html>');
$res = system('systeminfo');
var_dump($res);
echo preg_match('/主机名:/',$res);
// $paths = "D:/fox/";
// $paths2 = "D:/fox2/";
// $d = dir($paths);
// $i = 1;
// while (false !== ($entry = $d->read())) {
	// if(!is_file($paths.$entry)) continue;	
	// $extend = pathinfo($entry);
	// $extend = strtolower($extend['extension']);
	// $newName = $i.'.'.$extend;
	// echo $i;
	// echo "&nbsp;&nbsp;";
	// echo $paths.$entry;
	// echo "&nbsp;&nbsp;";
	// echo $paths2.$newName;
	// echo '<br/>';
	// if(file_exists($paths.$entry))
		// rename($paths.$entry, $paths2.$newName);
	// unset($newName);$i++;
// }
// $d->close(); 
?>