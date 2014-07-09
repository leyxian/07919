<?php
/*
	More & Original PHP Framwork
	Copyright (c) 2007 - 2008 IsMole Inc.

	$Id: MooConfig.php 311 2008-06-05 02:05:40Z kimi $
*/

/**
* 定义MooPHP框架的一些配置参数，而这些参数本来是应该在在MooPHP/MooConfig.php文件中配置的。
* 由于Examles目录下的所有演示文件是使用的同一个MooPHP框架核心，所以需要每个演示实例独立配置参数。
*/
date_default_timezone_set('PRC');
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
//note 开启Block缓存功能
//define('MOOPHP_ALLOW_BLOCK', TRUE);
//note 开启系统缓存功能
//define('MOOPHP_ALLOW_CACHE', TRUE);
//note 系统是否使用MYSQL数据库，当其值为TRUE时，将根据MYSQL的相关配置参数自动初始化MySQL链接
//define('MOOPHP_ALLOW_MYSQL', TRUE);

//note 定义Cache、Block、Templates等缓存数据的目录
define('MOOPHP_DATA_DIR','data');
//note 定义默认模板
define('MOOPHP_TEMPLATE_SKIN', 'default');
//note 定义Templates模板路径以及相对URL路径
define('MOOPHP_TEMPLATE_DIR', 'module/'.$name.'/templates');
define('MOOPHP_TEMPLATE_URL', 'module/'.$name.'/templates/'.MOOPHP_TEMPLATE_SKIN);
//note 定义admin路径
//define('MOOPHP_ADMIN_DIR', 'admin');


//note 定义加密所需Key，请修改此处为唯一
define('MOOPHP_AUTHKEY', '0051master');
//note 定义COOKIE加密的密钥
define('HLW_KEY', 'e4r9O87dQfZdNdK3t5D4u9a038Edp8F6w684P8m9OcY5fdg801v76biehcr758Ja');
//note 定义订单号前缀
define('ORDER_PRE_ID','100');
//note 定义情缘网址
define('MOOPHP_HOST','www.07919.com');

// 发送邮件地址列表
$g_mail_list = array( "robot@7651.com", "robot4@7651.com", "robot5@7651.com", "robot6@7651.com",
					"robot7@7651.com", "robot8@7651.com", "robot9@7651.com" ,"robot10@7651.com" ,"robot11@7651.com","robot12@7651.com",
					"robot13@7651.com", "robot14@7651.com", "robot15@7651.com", "robot16@7651.com", "robot17@7651.com", "robot18@7651.com",
					"robot19@7651.com", "robot20@7651.com", "robot210@7651.com", "robot22@7651.com", "robot23@7651.com", "robot24@7651.com",
					"robot25@7651.com", "robot26@7651.com", "robot27@7651.com", "robot28@7651.com", "robot29@7651.com", "robot30@7651.com",
					"robot31@7651.com", "robot32@7651.com", "robot33@7651.com", "robot34@7651.com", "robot35@7651.com", "robot36@7651.com",
					"robot37@7651.com", "robot38@7651.com", "robot39@7651.com", "robot40@7651.com"
);

