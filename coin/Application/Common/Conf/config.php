<?php
require dirname(__FILE__).'/secure.php';
return array(
	'DB_TYPE'              => DB_TYPE,
	'DB_HOST'              => DB_HOST,
	'DB_NAME'              => DB_NAME,
	'DB_USER'              => DB_USER,
	'DB_PWD'               => DB_PWD,
	'DB_PORT'              => DB_PORT,
	'DB_PREFIX'            => 'tw_',
	'ACTION_SUFFIX'        => '',
	'MULTI_MODULE'         => true,
	'MODULE_DENY_LIST'     => array('Common', 'Runtime'),
	'MODULE_ALLOW_LIST'    => array('Home', 'Admin', 'Mobile', 'Support','Agent','Contrab','Api'),
	'DEFAULT_MODULE'       => WHERECOME,
	'URL_CASE_INSENSITIVE' => false,
	'URL_MODEL'            => 2,
	'URL_HTML_SUFFIX'      => '',
	'LANG_SWITCH_ON'       => true, //开启多语言支持开关
	
	'LANG_AUTO_DETECT'     => true, // 自动侦测语言
	'DEFAULT_LANG'         => 'zh-tw', // 默认语言
	'LANG_LIST'     	   => 'zh-cn,zh-tw,en-us,fr-fr,de-de,it-it,ja-jp,ko-kr,tr-tr',
	'VAR_LANGUAGE'         => 'Lang', //默认语言切换变量
    'NATION'     =>array('zh_CN'=>'中国','en_US'=>'美国',),

	'TMPL_ACTION_ERROR' => './Public/error.html', //默认错误跳转对应的模板文件优选源码库  www.yxymk.net
	'TMPL_ACTION_SUCCESS' => './Public/success.html', //默认成功跳转对应的模板文件
    'redis_expire'        =>    88200, // redis过期时间
    'COOKIE_EXPIRE'       =>    86400,// cookie 过期时间
	);
?>