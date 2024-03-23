<?php

namespace Home\Controller;

class LoginController extends HomeController
{
	protected function _initialize()
	{
		parent::_initialize();	$allow_action=array("index","register","upregister","loginsubmit","loginout","findpwd","findpaypwd","webreg",
        "loption","setlang","lhelp","sendcode","findsendcode","resetpwd","sendSmsCode",'smsregister');
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error(L("非法操作"));
		}
	}



	//未登陆状态的选项页面
	public function loption(){


		$this->display();
	}

	//未登陆状态的设置语言
	public function setlang(){

		$this->display();
	}

	//帮助中心
	public function lhelp(){
		$uid = userid();
		$this->assign('uid',$uid);
		$this->display();
	}


	// 用户协议
	public function webreg()
	{
		$this->display();
	}

	public function index()
	{
	    $uid = userid();
	    if($uid >= 1){
	       $this->redirect("Index/index");
	    }
		$this->display();
	}

	//注册页面
	public function register(){

		$qrcode = I("get.qr");
		if($qrcode != ''){
			$this->assign('qrcode',$qrcode);
		}

		$this->display();
	}


    //提交重置密码
    public function resetpwd($email=null,$ecode=null,$lpwd=null){
//         if (checkstr($email) || checkstr($lpwd) || checkstr($ecode)) {
// 			$this->ajaxReturn(['code'=>0,'info'=>L('您输入的信息有误')]);
// 		}
		if($email == ''){
		    $this->ajaxReturn(['code'=>0,'info'=>L('请输入邮箱')]);
		}
		if($ecode == ''){
		    $this->ajaxReturn(['code'=>0,'info'=>L('请输入邮箱验证码')]);
		}
		if($lpwd == ''){
		    $this->ajaxReturn(['code'=>0,'info'=>L('请输入密码')]);
		}
		$findcode = session("findcode");
		if($findcode != $ecode){
		    $this->ajaxReturn(['code'=>0,'info'=>L('邮箱验证码错误')]);
		}

		$uinfo = M("user")->where(array('username'=>$email))->field("id,username")->find();
		if(empty($uinfo)){
		    $this->ajaxReturn(['code'=>0,'info'=>L('邮箱未注册')]);
		}

		$password = md5($lpwd);
		$result = M("user")->where(array('username'=>$email))->save(array('password'=>$password));
		if($result){
		    $data['uid'] = $uinfo['id'];
		    $data['account'] = $uinfo['username'];
		    $data['title'] = '重置密码';
		    $data['content'] = '登陆密码重置成功';
		    $data['addtime'] = date("Y-m-d H:i:s",time());
		    $data['status'] = 1;
		    M("notice")->add($data);
		    $this->ajaxReturn(['code'=>1,'info'=>L('登陆密码重置成功')]);
		}else{
		    $this->ajaxReturn(['code'=>0,'info'=>L('密码重置失败')]);
		}

    }

	// 登录提交处理
	public function loginsubmit(){
        $pwd = I("post.pwd");
        $vcode = I("post.vcode");
        $type = I("post.type");
// 		if (!check_verify(strtoupper($vcode),'.web')) {
// 			$this->ajaxReturn(['code'=>0,'info'=>L('图形验证码错误!')]);
// 		}
        if ($type == 1) { // type 1、邮箱 ，2、手机号码
            $email = I("post.email");
            $user = M('User')->where(array('username' => $email))->find();
            $remark="邮箱登录";
        }else{
            $phone = I("post.phone");
            $user = M('User')->where(array('phone' => $phone))->find();
            $remark="手机登录";
        }

		if(empty($user)){
			$this->ajaxReturn(['code'=>0,'info'=> L('用户不存在')]);
		}

		if (md5($pwd) != $user['password']){
			$this->ajaxReturn(['code'=>0,'info'=> L('登录密码错误')]);
		}

		if (isset($user['status']) && $user['status'] != 1) {
			$this->ajaxReturn(['code'=>0,'info'=> L('你的账号已冻结请联系管理员')]);
		}
		//增加登陆次数
		$incre = M("user")->where(array('id' => $user['id']))->setInc('logins', 1);

		//新增登陆记录
		$data['userid'] = $user['id'];
		$data['type'] = '登录';
		$data['remark'] = $remark;
		$data['addtime'] = time();
		$data['addip'] = get_client_ip();
		$data['addr'] = get_city_ip();
		$data['status'] = 1;
		$dre = M("user_log")->add($data);

		if($incre && $dre){
		    $lgdata['lgtime'] = date("Y-m-d",time());
		    $lgdata['loginip'] = get_client_ip();
		    $lgdata['loginaddr'] = get_city_ip();
		    $lgdata['logintime'] = date("Y-m-d H:i:s",time());
	    	M("user")->where(array('id' => $user['id']))->save($lgdata);
		    session('userId', $user['id']);
			session('userName', $user['username']);
			$this->ajaxReturn(['code'=>1,'info'=>L('登录成功')]);
		}else{
		    $this->ajaxReturn(['code'=>0,'info'=>L('登录失败')]);
		}
	}


	//注册处理程序
	public function upregister($email,$ecode,$lpwd,$invit){
		if($_POST){
// 			if(checkstr($email) || checkstr($ecode) || checkstr($lpwd) || checkstr($invit)){
// 				$this->ajaxReturn(['code'=>0,'info'=>L('您输入的信息有误')]);
// 			}
			$checkus = M('User')->where(array('username' => $email))->find();
			if(!empty($checkus)){
				$this->ajaxReturn(['code'=>0,'info'=>L('用户名已存在')]);
			}

			$secode = session('regcode');
			if($secode != $ecode){
				$this->ajaxReturn(['code'=>0,'info'=>L('邮箱验证码错误')]);
			}

			if($lpwd == ''){
				$this->ajaxReturn(['code'=>0,'info'=>L('请输入密码')]);
			}

			if($invit == ''){
			    $this->ajaxReturn(['code'=>0,'info'=>L('请输入邀请码')]);
			}
            $config = M("config")->where(array('id'=>1))->field("tymoney")->find();
			if($invit != 0 || $invit != ''){
				$inv_user = M('User')->where(array('invit' => $invit))->field("id,username,invit_1,invit_2,path")->find();
				if(empty($inv_user)){
					$this->ajaxReturn(['code'=>0,'info'=>L('推荐人不存在')]);
				}
				$invit_1 = $inv_user['id'];
				$invit_2 = $inv_user['invit_1'];
				$invit_3 = $inv_user['invit_2'];
				$path = $inv_user['path'].','.$inv_user['id'];
			}else{
				$invit_1 = 0;
				$invit_2 = 0;
				$invit_3 = 0;
				$path = '';
			}

			for (; true; ) {
				$myinvit = tradenoa();
				if (!M('User')->where(array('invit' => $myinvit))->find()) {
					break;
				}
			}

			$mo = M();
			$mo->execute('set autocommit=0');
			$mo->execute('lock tables tw_user write , tw_user_coin write ');
			$rs = array();
			$rs[] = $mo->table('tw_user')->add(
				array(
				'username' => $email,
				'password' => md5($lpwd),
				'money' => $config['tymoney'],
				'invit' => $myinvit,
				'invit_1' => $invit_1,
				'invit_2' => $invit_2,
				'invit_3' => $invit_3,
				'path'=>$path,
				'addip' => get_client_ip(),
				'addr' => get_city_ip(),
				'addtime' => time(),
				'status' => 1,
				'txstate' => 1,
				));

			$user_coin = array('userid' => $rs[0]);
			// 创建用户数字资产档案
			$rs[] = $mo->table('tw_user_coin')->add($user_coin);
			if (check_arr($rs)) {
				$mo->execute('commit');
				$mo->execute('unlock tables');
				session('regcode', null); //初始化动态验证码
				$user = $mo->table('tw_user')->where(array('id'=>$rs[0]))->find();
				$this->ajaxReturn(['code'=>1,'info'=>L('注册成功')]);
			} else {
		    	$mo->execute('rollback');
		    	$this->ajaxReturn(['code'=>0,'info'=>L('注册失败')]);
			}

		}else{
			$this->ajaxReturn(['code'=>0,'info'=>L('网络错误')]);
		}
	}

    public function findsendcode($email,$vcode){
//         if(checkstr($email) || checkstr($vcode)) {
// 			$this->ajaxReturn(['code'=>0,'info'=>L('您输入的信息有误')]);
// 		}
		$email =  I('post.email');
		$vcode =  I('post.vcode');

		if (!check_verify(strtoupper($vcode),'1')) {
			$this->ajaxReturn(['code'=>0,'info'=>L('图形验证码错误')]);
		}
		if($email == ''){
			$this->ajaxReturn(['code'=>0,'info'=>L('请输入邮箱')]);
		}
		$uinfo = M("user")->where(array('username'=>$email))->find();
		if(empty($uinfo)){
		    $this->ajaxReturn(['code'=>0,'info'=>L('邮箱未注册')]);
		}
		$code = rand(10000,99999);
		$result = $this->emailsend($code,$email);

		if($result){

			session('findcode',$code);

			$this->ajaxReturn(['code'=>1,'info'=>L('验证码发送成功')]);
		}else{
			$this->ajaxReturn(['code'=>0,'info'=>L('验证码发送失败')]);
		}
    }

	public function sendcode($email){
		if($_POST){
// 			if(checkstr($email) || checkstr($vcode)) {
// 				$this->ajaxReturn(['code'=>0,'info'=>L('您输入的信息有误')]);
// 			}
			$email =  I('post.email');
// 			$vcode =  I('post.vcode');

// 			if (!check_verify(strtoupper($vcode),'1')) {
// 				$this->ajaxReturn(['code'=>0,'info'=>L('图形验证码错误')]);
// 			}

			if($email == ''){
				$this->ajaxReturn(['code'=>0,'info'=>L('请输入邮箱')]);
			}

			$uinfo = M("user")->where(array('username'=>$email))->find();
			if(!empty($uinfo)){
				$this->ajaxReturn(['code'=>0,'info'=>L('账号已存在')]);
			}
			$code = rand(10000,99999);
			$result = $this->emailsend($code,$email);

			if($result){

				session('regcode',$code);

				$this->ajaxReturn(['code'=>1,'info'=>L('验证码发送成功')]);
			}else{
				$this->ajaxReturn(['code'=>0,'info'=>L('验证码发送失败')]);
			}

		}else{
			$this->ajaxReturn(['code'=>0,'info'=>L('网络错误')]);
		}

	}


	//邮件发送验证码
	public function emailsend($desc_content, $toemail){

//	    $config = $clist = M("config")->where(array('id'=>1))->field("smsemail,emailcode,smstemple")->find();
	    $smsemail = "pnscx.s@gmail.com";
//	    $emailcode = trim($config['emailcode']);
//	    $smstemple = trim($config['smstemple']);
		Vendor('PHPMailer.src.PHPMailer');
		Vendor('PHPMailer.src.SMTP');
		$mail = new \PHPMailer();
		$mail->SMTPDebug = 0;
		$mail->isSMTP();
		//$mail->CharSet = "utf8";
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;
		$mail->Username = $smsemail; //@qq.com此账号仅供测试使用
		$mail->Password = "kooifzmmwyehgqmh";
		$mail->SMTPSecure = "ssl";
		$mail->Port = 465;
		$mail->CharSet = 'UTF-8';
		$mail->setFrom($smsemail,"Verification Code");
		$mail->addAddress($toemail,'');
		$mail->addReplyTo($smsemail,"Reply");
		$mail->Subject = L('Verification Code');
        $mail->Body = '[BingX] '.L("验证码5分钟内有效,").L("您的验证码是:").$desc_content;;
		if(!$mail->send()){
			return 0;
		}else{
			return 1;
		}

	}



	public function loginout()
	{
		session(null);
		redirect('/');
	}

	// 找回密码页面
	public function findpwd(){

		$this->display();
	}

	// 找回交易密码
	public function findpaypwd(){
		$this->display();
	}
    //手机注册注册处理程序
    public function smsregister(){
        if($_POST){
            $phone=I('post.phone');
            $sms_code=I('post.sms_code');
            $pwd=I('post.pwd');
            $invit=I('post.invitation_code');
            $area_code=I('post.area_code');
            $checkus = M('User')->where(array('phone' => $phone))->find();
            if(!empty($checkus)){
                $this->ajaxReturn(['code'=>0,'info'=>L('手机号已存在')]);
            }

            $redis=$this->_Redis();
            $secode=$redis->hGet('sms_reg_code',$area_code.$phone);
            if($secode != $sms_code){
                $this->ajaxReturn(['code'=>0,'info'=>L('手机验证码错误')]);
            }

            if($pwd == ''){
                $this->ajaxReturn(['code'=>0,'info'=>L('请输入密码')]);
            }

            if($invit == ''){
                $this->ajaxReturn(['code'=>0,'info'=>L('请输入邀请码')]);
            }
            $config = M("config")->where(array('id'=>1))->field("tymoney")->find();
            if($invit != 0 || $invit != ''){
                $inv_user = M('User')->where(array('invit' => $invit))->field("id,username,invit_1,invit_2,path")->find();
                if(empty($inv_user)){
                    $this->ajaxReturn(['code'=>0,'info'=>L('推荐人不存在')]);
                }
                $invit_1 = $inv_user['id'];
                $invit_2 = $inv_user['invit_1'];
                $invit_3 = $inv_user['invit_2'];
                $path = $inv_user['path'].','.$inv_user['id'];
            }else{
                $invit_1 = 0;
                $invit_2 = 0;
                $invit_3 = 0;
                $path = '';
            }

            for (; true; ) {
                $myinvit = tradenoa();
                if (!M('User')->where(array('invit' => $myinvit))->find()) {
                    break;
                }
            }

            $mo = M();
            $mo->execute('set autocommit=0');
            $mo->execute('lock tables tw_user write , tw_user_coin write ');
            $rs = array();
            $rs[] = $mo->table('tw_user')->add(
                array(
                    'phone' => $phone,
                    'password' => md5($pwd),
                    'money' => $config['tymoney'],
                    'invit' => $myinvit,
                    'invit_1' => $invit_1,
                    'invit_2' => $invit_2,
                    'invit_3' => $invit_3,
                    'path'=>$path,
                    'addip' => get_client_ip(),
                    'addr' => get_city_ip(),
                    'addtime' => time(),
                    'status' => 1,
                    'txstate' => 1,
                ));

            $user_coin = array('userid' => $rs[0]);
            // 创建用户数字资产档案
            $rs[] = $mo->table('tw_user_coin')->add($user_coin);
            if (check_arr($rs)) {
                $mo->execute('commit');
                $mo->execute('unlock tables');
                $redis->hSet('sms_reg_code',$phone,''); //初始化动态验证码
                $user = $mo->table('tw_user')->where(array('id'=>$rs[0]))->find();
                $this->ajaxReturn(['code'=>1,'info'=>L('注册成功')]);
            } else {
                $mo->execute('rollback');
                $this->ajaxReturn(['code'=>0,'info'=>L('注册失败')]);
            }

        }else{
            $this->ajaxReturn(['code'=>0,'info'=>L('网络错误')]);
        }
    }
    //短信发送验证码
    public function sendSmsCode(){
        if($_POST){
            $phone =  I('post.phone');
            $area_code =  I('post.area_code');
            if($phone == ''){
                $this->ajaxReturn(['code'=>0,'info'=>L('请输入手机号码')]);
            }

            $uinfo = M("user")->where(array('phone'=>$phone))->find();
            if(!empty($uinfo)){
                $this->ajaxReturn(['code'=>0,'info'=>L('该手机号码已经注册过')]);
            }
            $code = rand(10000,99999);
            $desc_content='[BingX] '.L("验证码5分钟内有效,").L("您的验证码是:").$code;
            $phone=$area_code.$phone;

            $result = $this->smsSend($desc_content,$phone);
            $redis=$this->_Redis();

            if($result){
                $redis->hSet('sms_reg_code',$phone,$code);

                $this->ajaxReturn(['code'=>1,'info'=>L('验证码发送成功')]);
            }else{
                $this->ajaxReturn(['code'=>0,'info'=>L('验证码发送失败')]);
            }

        }else{
            $this->ajaxReturn(['code'=>0,'info'=>L('网络错误')]);
        }

    }

    //短信发送验证码
    public function smsSend($desc_content, $toPhone){
        $url = "https://api.liasmart.com/api/SendSMS";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = <<<DATA
{
 "api_id":"API119226292925",
 "api_password":"P7DRQt1tPX",
 "sms_type": "T",
 "encoding": "U",
 "sender_id":"LIASMT",
 "phonenumber":"$toPhone",
 "textmessage":"$desc_content"
}
DATA;

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);
        $result=json_decode($resp,true);
        if($result && $result['status'] == 'S' ){
            return 1;
        }else{
            return 0;
        }

    }
    /**
     * 连接redis
     * @return \Redis
     */
    private function _Redis()
    {
        $redis = new \Redis();
        $host = REDIS_HOST;
        $port = REDIS_PORT;
        $password= REDIS_PWD;
        $redis->connect($host ,$port, 30);
        $redis->auth($password);
        return $redis;
    }
}
?>
