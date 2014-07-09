<?php
require "./framwork/MooPHP.php";
MooPlugins('ipdata');


//note 获得上个星期时间
$getWeekDay=date("w");
$weektime = mktime(0,0,0,date("m"),date("d")-$getWeekDay+1-7,date("Y"));

//note 查询出本星期的注册ip
$sql = "SELECT uid,regip,regdate  FROM {$dbTablePre}members WHERE regdate >= '{$weektime}'";
$list = $_MooClass['MooMySQL']->getAll($sql);

foreach($list as $k => $v){
	
		$ipname = convertIp($v['regip']);
		if(preg_match('/(厦门)/',$ipname)){
			
			$xiamen[] = $v['uid'];
			$xiamen_list[uid][] = $v['uid'];		
			$xiamen_list[regip][] = $v['regip'];
			$xiamen_list[time][] = date("Y-m-d",$v['regdate']);
		}	
		
		$ipname = convertIp($v['regip']);
		if(preg_match('/(深圳)/',$ipname)){
			$szhen[] = $v['uid'];
			$szhen_list[uid][] = $v['uid'];		
			$szhen_list[regip][] = $v['regip'];  
			$szhen_list[time][] =  date("Y-m-d",$v['regdate']);
		}	
	
}
//not 去除重复ip后的值
$xiamen_regip_re = array_count_values($xiamen_list[regip]);
echo "<br>";
echo "厦门重复次数";
print_r($xiamen_regip_re);
echo "<br>";
 
$szhen_regip_re = array_count_values($szhen_list[regip]);
echo "<br>";
echo "深圳重复次数";
print_r($szhen_regip_re);
echo "<br>";

echo "从";
echo date("Y-m-d",mktime(0,0,0,date("m"),date("d")-$getWeekDay+1-7,date("Y")));
echo "到现在本星期内<br>";
$total = count($szhen) + count($xiamen);

echo "属于厦门或者深圳的UID个数：".$total;
echo "<br>";
echo "<br>";
echo "厦门的UID个数:".count($xiamen)."<br>";
echo "厦门uid:";
echo "<br>";
print_r($xiamen);
echo "<br><br>";
print_r($xiamen_list);
echo "<br><br>";
echo "深圳的UID个数:".count($szhen);
echo "<br>";
echo "深圳uid:";
echo "<br>";
print_r($szhen);
echo "<br><br>";
print_r($szhen_list);

?>