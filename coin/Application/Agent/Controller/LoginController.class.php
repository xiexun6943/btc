<?php
namespace Agent\Controller;

class LoginController extends \Think\Controller
{

	public function index($username = NULL, $password = NULL, $verify = NULL, $urlkey = NULL)
	{

		if (IS_POST) {

			$uinfo = M('User')->where(array('username' => $username))->find();

			if ($uinfo['password'] != md5($password)) {
				$this->error(L('用户名或密码错误'));
			} else {
			    
			    if($uinfo['is_agent'] != 1){
			        $this->error(L('该账号不是代理'));exit();
			    }
				cookie('agent_id', $uinfo['id']);
                if ($uinfo['invit_1'] == 0 && $uinfo['invit_2'] == 0 && $uinfo['invit_3'] == 0 && $uinfo['is_agent'] == 1) {
                    $agent_type=1;// 一级代理
                } elseif ($uinfo['invit_1'] > 0 && $uinfo['invit_2'] == 0 && $uinfo['invit_3'] == 0 && $uinfo['is_agent'] == 1) {
                    $agent_type=2;// 二级代理
                } elseif ($uinfo['invit_1'] > 0 && $uinfo['invit_2'] > 0 && $uinfo['invit_3'] == 0 && $uinfo['is_agent'] == 1) {
                    $agent_type=3;// 三级代理
                }else{
                    $agent_type=4;// 普通用户
                }
                cookie('agent_type', $agent_type);
				$this->success(L('登陆成功'), U('Agent/Index/index'));
			}
		} else {
			if(cookie('agent_id')) {
				$this->redirect('Index/index');
			}
			$this->display();
		}
	}

	public function loginout()
	{
        cookie('agent_id',null);
		$this->redirect('Agent/Login/index');
	}

}
?>