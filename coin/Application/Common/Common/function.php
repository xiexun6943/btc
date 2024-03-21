<?php

if (!function_exists('array_column')) {
	function array_column(array $input, $columnKey, $indexKey = NULL)
	{
		$result = array();

		if (NULL === $indexKey) {
			if (NULL === $columnKey) {
				$result = array_values($input);
			} else {
				foreach ($input as $row) {
					$result[] = $row[$columnKey];
				}
			}
		} else if (NULL === $columnKey) {
			foreach ($input as $row) {
				$result[$row[$indexKey]] = $row;
			}
		} else {
			foreach ($input as $row) {
				$result[$row[$indexKey]] = $row[$columnKey];
			}
		}

		return $result;
	}
}


//获取文件最后修改时间
function stamp($file){
   $version = filemtime('.'.$file);
   return $file."?v=".$version;
}

//获取财务类型
function gettype_info($type=null){
    switch ($type)
    {
        case 1:
            return "充币";
            break;
        case 2:
            return "提币";
            break;
        case 3:
            return "购买合约";
            break;
        case 4:
            return "出售合约";
            break;
        case 5:
            return "购买矿机";
            break;
        case 6:
            return "购机奖励";
            break;
        case 7:
            return "矿机收益冻结";
            break;
        case 8:
            return "释放冻结收益";
            break;
        case 9:
            return "币币交易USDT";
            break;
        case 10:
            return "币币交易币种";
            break;
        case 11:
            return "认购扣除";
            break;
        case 12:
            return "认购增加";
            break;
        case 13:
            return "一代认购奖励";
            break;
        case 14:
            return "二代认购奖励";
            break;
        case 15:
            return "三代认购奖励";
            break;
        case 16:
            return "认购币解冻";
            break;
        case 17:
            return "充币";
            break;
        case 18:
            return "释放冻结币";
            break;
        case 20:
            return "中奖";
            break;
        case 21:
            return "彩金";
            break;
        default:
            return "未定义";
        
    }
    
}

function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
{
	$tree = array();
	if (is_array($list)) {
		$refer = array();
		foreach ($list as $key => $data) {
			$refer[$data[$pk]] = &$list[$key];
		}
		foreach ($list as $key => $data) {
			$parentId = $data[$pid];
			if ($root == $parentId) {
				$tree[] = &$list[$key];
			} else if (isset($refer[$parentId])) {
				$parent = &$refer[$parentId];
				$parent[$child][] = &$list[$key];
			}
		}
	}
	return $tree;
}

function check_arr($rs)
{
	foreach ($rs as $v) {
		if (!$v) {
			return false;
		}
	}

	return true;
}
function getCoreConfig()
{
	$file_path = DATABASE_PATH . '/core.json';

	if (file_exists($file_path)) {
		$CoreConfig = preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($file_path));
		$CoreConfig = json_decode($CoreConfig, true);

		return $CoreConfig;
	} else {
		return false;
	}
}

function addtime($time = NULL, $type = NULL)
{
	if (empty($time)) {
		return '---';
	}

	if (($time < 2545545) && (1893430861 < $time)) {
		return '---';
	}

	if (empty($type)) {
		$type = 'Y-m-d H:i:s';
	}

	return date($type, $time);
}

function clear_html($str)
{
	$str = preg_replace("/<style .*?<\/style>/is", "", $str);
	$str = preg_replace("/<script .*?<\/script>/is", "", $str);
	$str = preg_replace("/<br \s*\/?\/>/i", "\n", $str);
	$str = preg_replace("/<\/?p>/i", "\n\n", $str);
	$str = preg_replace("/<\/?td>/i", "\n", $str);
	$str = preg_replace("/<\/?div>/i", "\n", $str);
	$str = preg_replace("/<\/?blockquote>/i", "\n", $str);
	$str = preg_replace("/<\/?li>/i", "\n", $str);
	$str = preg_replace("/\&nbsp\;/i", " ", $str);
	$str = preg_replace("/\&nbsp/i", " ", $str);
	$str = preg_replace("/\&amp\;/i", "&", $str);
	$str = preg_replace("/\&amp/i", "&", $str);
	$str = preg_replace("/\&lt\;/i", "<", $str);
	$str = preg_replace("/\&lt/i", "<", $str);
	$str = preg_replace("/\&ldquo\;/i", '"', $str);
	$str = preg_replace("/\&ldquo/i", '"', $str);
	$str = preg_replace("/\&lsquo\;/i", "'", $str);
	$str = preg_replace("/\&lsquo/i", "'", $str);
	$str = preg_replace("/\&rsquo\;/i", "'", $str);
	$str = preg_replace("/\&rsquo/i", "'", $str);
	$str = preg_replace("/\&gt\;/i", ">", $str);
	$str = preg_replace("/\&gt/i", ">", $str);
	$str = preg_replace("/\&rdquo\;/i", '"', $str);
	$str = preg_replace("/\&rdquo/i", '"', $str);
	$str = strip_tags($str);
	$str = html_entity_decode($str, ENT_QUOTES);
	$str = preg_replace("/\&\#.*?\;/i", "", $str);
	return $str;
}

function getUrl($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 3);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, '');
	$data = curl_exec($ch);
	return $data;
}

 function getRedis()
{
    $redis = new \Redis();
    $host = REDIS_HOST;
    $port = REDIS_PORT;
    $password= REDIS_PWD;
    $redis->connect($host ,$port, 30);
    $redis->auth($password);
    return $redis;
}
// 获取登录用户信息
function userid($username = NULL, $type = 'username')
{
	if ($username && $type) {
		$userid = (APP_DEBUG ? NULL : S('userid' . $username . $type));

		if (!$userid) {
			$userid = M('User')->where(array($type => $username))->getField('id');
			S('userid' . $username . $type, $userid);
		}
	} else {
	    // 老的
        //$userid = session('userId');
        $redis=getRedis();
        $uid=cookie('web.uid');
        $userInfo=$redis->hGetAll($uid.'user_token');
        if ($userInfo) {
            $userid = $userInfo['uid'];
        }
	}
	return $userid ? $userid : NULL;
}



function username($id = NULL, $type = 'id')
{
	if ($id && $type) {
		$username = (APP_DEBUG ? NULL : S('username' . $id . $type));

		if (!$username) {
			$username = M('User')->where(array($type => $id))->getField('username');
			S('username' . $id . $type, $username);
		}
	} else {
		$username = session('userName');
	}

	return $username ? $username : NULL;
}


function op_t($text, $addslanshes = false)
{
	$text = nl2br($text);
	$text = real_strip_tags($text);

	if ($addslanshes) {
		$text = addslashes($text);
	}

	$text = trim($text);
	return $text;
}

function text($text, $addslanshes = false)
{
	return op_t($text, $addslanshes);
}

function html($text)
{
	return op_h($text);
}

function op_h($text, $type = 'html')
{
	$text_tags = '';
	$link_tags = '<a>';
	$image_tags = '<img>';
	$font_tags = '<i><b><u><s><em><strong><font><big><small><sup><sub><bdo><h1><h2><h3><h4><h5><h6>';
	$base_tags = $font_tags . '<p><br><hr><a><img><map><area><pre><code><q><blockquote><acronym><cite><ins><del><center><strike>';
	$form_tags = $base_tags . '<form><input><textarea><button><select><optgroup><option><label><fieldset><legend>';
	$html_tags = $base_tags . '<ul><ol><li><dl><dd><dt><table><caption><td><th><tr><thead><tbody><tfoot><col><colgroup><div><span><object><embed><param>';
	$all_tags = $form_tags . $html_tags . '<!DOCTYPE><meta><html><head><title><body><base><basefont><script><noscript><applet><object><param><style><frame><frameset><noframes><iframe>';
	$text = real_strip_tags($text, $$type . '_tags');

	if ($type != 'all') {
		while (preg_match('/(<[^><]+)(ondblclick|onclick|onload|onerror|unload|onmouseover|onmouseup|onmouseout|onmousedown|onkeydown|onkeypress|onkeyup|onblur|onchange|onfocus|action|background[^-]|codebase|dynsrc|lowsrc)([^><]*)/i', $text, $mat)) {
			$text = str_ireplace($mat[0], $mat[1] . $mat[3], $text);
		}

		while (preg_match('/(<[^><]+)(window\\.|javascript:|js:|about:|file:|document\\.|vbs:|cookie)([^><]*)/i', $text, $mat)) {
			$text = str_ireplace($mat[0], $mat[1] . $mat[3], $text);
		}
	}

	return $text;
}

function real_strip_tags($str, $allowable_tags = '')
{
	return strip_tags($str, $allowable_tags);
}

function clean_cache($dirname = './Runtime/')
{
	$dirs = array($dirname);
	foreach ($dirs as $value) {
		rmdirr($value);
	}

	@(mkdir($dirname, 511, true));
}


function debug($value, $type = 'DEBUG', $verbose = false, $encoding = 'UTF-8')
{
	if (M_DEBUG || MSCODE) {
		if (!IS_CLI) {
			Common\Ext\FirePHP::getInstance(true)->log($value, $type);
		}
	}
}



function check_verify($code, $id = "")
{
	$verify = new \Think\Verify();
	return  $verify->check($code, $id);
}

function get_city_ip($ip = NULL)
{
	if (empty($ip)) {
		$ip = get_client_ip();
	}

	$Ip = new Org\Net\IpLocation();
	$area = $Ip->getlocation($ip);
	$str = $area['country'] . $area['area'];
	$str = mb_convert_encoding($str, 'UTF-8', 'GBK');

	if (($ip == '127.0.0.1') || ($str == false) || ($str == 'IANA保留地址用于本地回送')) {
		$str = '未分配或者内网IP';
	}

	return $str;
}

function send_post($url, $post_data)
{
	$postdata = http_build_query($post_data);
	$options = array(
		'http' => array('method' => 'POST', 'header' => 'Content-type:application/x-www-form-urlencoded', 'content' => $postdata, 'timeout' => 15 * 60)
	);
	$context = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	return $result;
}

function request_by_curl($remote_server, $post_string)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $remote_server);
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'mypost=' . $post_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'qianyunlai.com\'s CURL Example beta');
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

function tradeno()
{
	return substr(str_shuffle('ABCDEFGHIJKLMNPQRSTUVWXYZ'), 0, 2) . substr(str_shuffle(str_repeat('123456789', 4)), 0, 9);
}

function tradenoa()
{
	return substr(str_shuffle('ABCDEFGHIJKLMNPQRSTUVWXYZ'), 0, 9);
}



function ismobile()
{
	if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
		return true;
	}

	if (isset($_SERVER['HTTP_CLIENT']) && ('PhoneClient' == $_SERVER['HTTP_CLIENT'])) {
		return true;
	}

	if (isset($_SERVER['HTTP_VIA'])) {
		return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
	}

	if (isset($_SERVER['HTTP_USER_AGENT'])) {
		$clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile');

		if (preg_match('/(' . implode('|', $clientkeywords) . ')/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
			return true;
		}
	}

	if (isset($_SERVER['HTTP_ACCEPT'])) {
		if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && ((strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false) || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
			return true;
		}
	}

	return false;
}



function check($data, $rule = NULL, $ext = NULL)
{
	$data = trim(str_replace(PHP_EOL, '', $data));

	if (empty($data)) {
		return false;
	}

	$validate['require'] = '/.+/';
	$validate['url'] = '/^http(s?):\\/\\/(?:[A-za-z0-9-]+\\.)+[A-za-z]{2,4}(?:[\\/\\?#][\\/=\\?%\\-&~`@[\\]\':+!\\.#\\w]*)?$/';
	$validate['currency'] = '/^\\d+(\\.\\d+)?$/';
	$validate['number'] = '/^\\d+$/';
	$validate['zip'] = '/^\\d{6}$/';
	$validate['cny'] = '/^(([1-9]{1}\\d*)|([0]{1}))(\\.(\\d){1,2})?$/';
	$validate['integer'] = '/^[\\+]?\\d+$/';
	$validate['double'] = '/^[\\+]?\\d+(\\.\\d+)?$/';
	$validate['english'] = '/^[A-Za-z]+$/';
	$validate['idcard'] = '/^([0-9]{15}|[0-9]{17}[0-9a-zA-Z])$/';
	$validate['truename'] = '/^[\\x{4e00}-\\x{9fa5}]{2,4}$/u';
	$validate['username'] = '/^[a-zA-Z]{1}[0-9a-zA-Z_]{5,15}$/';
	$validate['email'] = '/^\\w+([-+.]\\w+)*@\\w+([-.]\\w+)*\\.\\w+([-.]\\w+)*$/';
	$validate['mobile'] = '/^(((1[0-9][0-9]{1})|159|153)+\\d{8})$/';
	$validate['password'] = '/^[a-zA-Z0-9_\\@\\#\\$\\%\\^\\&\\*\\(\\)\\!\\,\\.\\?\\-\\+\\|\\=]{6,16}$/';
	// $validate['password'] = '/^[a-zA-Z0-9]{6,16}$/';
	$validate['xnb'] = '/^[a-zA-Z]$/';

	if (isset($validate[strtolower($rule)])) {
		$rule = $validate[strtolower($rule)];
		return preg_match($rule, $data);
	}

	$Ap = '\\x{4e00}-\\x{9fff}' . '0-9a-zA-Z\\@\\#\\$\\%\\^\\&\\*\\(\\)\\!\\,\\.\\?\\-\\+\\|\\=';
	$Cp = '\\x{4e00}-\\x{9fff}';
	$Dp = '0-9';
	$Wp = 'a-zA-Z';
	$Np = 'a-z';
	$Tp = '@#$%^&*()-+=';
	$_p = '_';
	$pattern = '/^[';
	$OArr = str_split(strtolower($rule));
	in_array('a', $OArr) && ($pattern .= $Ap);
	in_array('c', $OArr) && ($pattern .= $Cp);
	in_array('d', $OArr) && ($pattern .= $Dp);
	in_array('w', $OArr) && ($pattern .= $Wp);
	in_array('n', $OArr) && ($pattern .= $Np);
	in_array('t', $OArr) && ($pattern .= $Tp);
	in_array('_', $OArr) && ($pattern .= $_p);
	isset($ext) && ($pattern .= $ext);
	$pattern .= ']+$/u';
	return preg_match($pattern, $data);
}



function url($link = '', $param = '', $default = '')
{
	return $default ? $default : U($link, $param);
}



function checkstr($strsql)
{
	//检测字符串是否有注入风险
    $strsql = str_replace("'","",$strsql);
	$strsql = trim($strsql);	$check=preg_match('/select|SELECT|or|OR|and|AND|char|CHAR|create|CREATR|drop|DROP|database|DATABASE|table|TABLE|insert|INSERT|script|SCRIPT|function|FUNCTION|update|UPDATE|delete|DELETE|exec|EXEC|system|SYSTEM|passthru|PASSTHRU|shell_exec|SHELL_EXEC|<|\`|\%|\"|\'|\/\*|\*|\.\.\/|\.\/|union|UNION|into|INTO|load_file|LOAD_FILE|outfile|OUTFILE/i',$strsql);

	if($check)
	{
		return 1;
	}

}

function  creat_sharecode($len = 13) {
    $chars='ABDEFGHJKLMNPQRSTVWXYabdefghijkmnpqrstvwxy23456789';
    mt_srand((double)microtime() * 1000000 * getmypid());

    $result = '';
    while(strlen($result) < $len)
        $result .= substr($chars, (mt_rand() % strlen($chars)), 1);

    return $result;
}


function  randStr($len = 6) {
    $chars='ABDEFGHJKLMNPQRSTVWXYabdefghijkmnpqrstvwxy23456789';
    mt_srand((double)microtime() * 1000000 * getmypid());

    $result = '';
    while(strlen($result) < $len)
        $result .= substr($chars, (mt_rand() % strlen($chars)), 1);

    return $result;
}



//创建TOKEN
function creatToken() {
    $code = chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE));
    session('__token__', authcode($code));
}
/* 加密TOKEN */
function authcode($str) {
    $key = "ANDIAMON";
    $str = substr(md5($str), 8, 10);
    return md5($key . $str);
}
//判断TOKEN
function checkToken($token) {
    if ($token == session('__token__')) {
        //session('__token__', NULL);
        return true;
    } else {
		return false;
    }
}


if (!function_exists('get_config')){
    function get_config($key = '') {
        if (!session('sys_config')) {
            $config = M('Config')->where(['id' => 1])->find();
            session('sys_config', $config);
        }
        $config = session('sys_config');
        if ($key === '') {
            return $config;
        }
        return isset($config[$key]) ? $config[$key] : '';
    }
}

function returnMsg($code){
    $response=['code'=>404,'msg'=>L('接口异常')];
    switch ($code) {
        case 10001:
            $response= ['code'=>10001,'msg'=>L('参数无效')];break;
        case 10002:
            $response= ['code'=>10001,'msg'=>L('参数为空')];break;
        case 10003:
            $response= ['code'=>10001,'msg'=>L('参数缺失')];break;
        case 20001:
            $response=  ['code'=>20001,'msg'=>L('用户失效,请登录！')];break;
        case 20002:
            $response= ['code'=>20002,'msg'=>L('账号密码错误')];break;
        case 20003:
            $response= ['code'=>20002,'msg'=>L('账号被禁用！')];break;
        case 20004:
            $response= ['code'=>20004,'msg'=>L('用户不存在!')];break;
    }
    return  $response;
}
// 获取系统设置信息
 function get_settings($filed = '')
{
    if ($filed) {
        $settingdata = M("settings")->where(['name'=>$filed])->find();
        if ($filed == 'draw' || $filed == 'lang') {
            return unserialize(urldecode($settingdata['data']));
        }
        return $settingdata['data'];
    } else {
        $settingdata = M("settings")->select();
        foreach ($settingdata as $k => $v) {
            $settingarr[$v['name']] = $v['data'];
        }
        return $settingarr;
    }
}

// 获取config 表中配置
function qrcodeImg($filed)
{
    $qrcodeImg = M("config")->getField($filed);
    return $qrcodeImg;
}


?>