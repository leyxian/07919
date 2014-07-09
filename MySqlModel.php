<?php
require './framwork/MooPHP.php';
class MySqlModel {
	
	//private $_table;

	function __construct(){
		//$this->$_table
	}

	static public function select($table,$field,$data){
		global $_MooClass,$dbTablePre;
		$data = self::checkDate($table,$data);
		pr($data);
	}

	//检查数据,剔除不合法数据
	static public function checkDate($table,$data){
		global $_MooClass,$dbTablePre;
		$result = $_MooClass['MooMySQL']->query("SHOW FIELDS FROM {$table}");
		while($row = mysql_fetch_array($result)){
			$i = 0;
			$info[$row['Field']] = mysql_field_type($result, $i);
			$i++;
		}
		foreach ($data as $k => $v) {
			if(!in_array($k, array_keys($info)))
				unset($data[$k]);
			else{
				switch ($info[$k]) {
					case 'int':
						$data[$k] = filter_int($v);
						break;
					case 'real':
						$data[$k] = filter_float($v);
						break;
					default:
						$data[$k] = filter_str($v);
						break;
				}
			}
		}
		return $data;
	}
}
function filter_str($str) {
    return filter_var($str,FILTER_SANITIZE_STRING,FILTER_FLAG_NO_ENCODE_QUOTES);
}
function filter_int($str) {
    return filter_var($str,FILTER_VALIDATE_INT);
}
function filter_email($str) {
    return filter_var($str,FILTER_VALIDATE_EMAIL);
}
function filter_url($str) {
    return filter_var($str,FILTER_VALIDATE_URL);
}
function filter_float($str) {
    return filter_var($str,FILTER_VALIDATE_FLOAT);
}

$data['uid'] = 1;
$data['username'] = 'ah.liulei@foxmail.com';
MySqlModel::Select('web_members_search','*',$data);
?>