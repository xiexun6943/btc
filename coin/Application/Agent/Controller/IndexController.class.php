<?php
namespace Agent\Controller;

class IndexController extends AgentController
{
	protected function _initialize()
	{
		parent::_initialize();
	}


	//代理中心会员管理
	public function index($name=NULL){
        if (!session('agent_id')) {
			$this->redirect('Agent/Login/index');
		}
		$uid = session('agent_id');
		$field=I('get.field');
		$search=I('get.search');
		if ($field && $search) {
            $map_3 = "$field like '%{$search}%' and (invit_1 = $uid or invit_2 = $uid or invit_3 = $uid)";
		}else{
		    $map_3 = "invit_1 = $uid or invit_2 = $uid or invit_3 = $uid";
		}

		$count = M('User')->where($map_3)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('User')->where($map_3)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $v) {
			$list[$k]['invit_1'] = M('User')->where(array('id' => $v['invit_1']))->getField('username');
			$list[$k]['invit_2'] = M('User')->where(array('id' => $v['invit_2']))->getField('username');
			$list[$k]['invit_3'] = M('User')->where(array('id' => $v['invit_3']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
	    $this->display();
	}
	
	
	
	
	
	//编辑或新增会员
	public function edit($id = NULL)
	{
	    $uid = session('agent_id');
		if (empty($_POST)) {
			if (empty($id)) {
				$this->data = '';
			} else {
				$this->data = M('User')->where(array('id' => trim($id)))->find();
			}
			$this->display();
		} else {
		    //新增会员
		    if($id <= 0 || $id == null){
		        $username = trim($_POST['username']);
		        if($username == ''){
		            $this->error(L("请输入会员账号"));exit();
		        }else{
		            $add['username'] = $username;
		        }
		        
		        if($_POST['password'] == ""){
		            $this->error(L("请输入登陆密码"));exit();
		        }else{
		            $add['password'] = md5($_POST['password']);
		        }
		        if($_POST['paypassword'] != ""){
		            $add['paypassword'] = md5($_POST['paypassword']);
		        }
		        
	        	$add['invit_1'] = $uid;
		    	$add['invit_2'] = 0;
		    	$add['invit_3'] = 0;
		    	$add['path'] = ','.$uid;
			    
		        $add['addtime'] = time();
		        $add['addip'] = get_client_ip();
		        $add['addr'] = get_city_ip();
                $add['invit']  = tradenoa();
                $add['status']  =1;
                $add['txstate']  = 1;

                $re = M("user")->add($add);
		        if($re){
		            M('user_coin')->add(array('userid' => $re));
		             $this->success(L("新增成功"));exit();
		        }else{
		            $this->error(L("新增失败"));exit();
		        }
		    //编辑会员   
		    }else{
		   
		       if ($_POST['password']) {
				    $_POST['password'] = md5($_POST['password']);
			    } else {
			    	unset($_POST['password']);
			    }
			    if ($_POST['paypassword']) {
			    	$_POST['paypassword'] = md5($_POST['paypassword']);
			    } else {
			    	unset($_POST['paypassword']);
			    }
		        
		        $result = M("user")->where(array('id'=>$id))->save($_POST);
		        if($result){
		            $this->success(L("编辑成功"));exit();
		        }else{
		            $this->error(L("编辑失败"));exit();
		        }
		    }

		}
	}
	
    //实名认证页面
	public function authrz($id){
	    
	    $klist = M("kuangji")->where(array('rtype'=>2))->field("id,title")->select();
	    $this->assign("klist",$klist);
	    $info = M("user")->where(array('id'=>$id))->field("id,username,phone,cardzm,cardfm,rztime")->find();
	    $this->assign('info',$info);
	    $this->display();
	}
	
	//实名认证处理
	public function upanthrz(){
	    $rzstatus = $_POST['rzstatus'];
	    $uid = $_POST['uid'];
	    if($uid <= 0 || $uid == ''){
	        $this->error(L("参数得要参数"));
	    }
	    if($rzstatus== 2){//表示认证成功
	    
	        $result = M("user")->where(array('id'=>$uid))->save(array('rzstatus'=>2,'rzuptime'=>time()));
	        if($result){
	           // $kjid = $_POST['kjid'];
	        
	           // $minfo = M("kuangji")->where(array('id'=>$kjid))->find();
	        
	           // //建仓矿机订单数据
	           // $odate['kid'] = $minfo['id'];
	           // $odate['type'] = 1;
	           // $odate['sharebl'] = '';
	           // $odate['uid'] = $uid;
	           // $odate['username'] = $_POST['username'];
	           // $odate['kjtitle'] = $minfo['title'];
	           // $odate['imgs'] = $minfo['imgs'];
	           // $odate['status'] = 1;
	           // $odate['cycle'] = $minfo['cycle'];
	           // $odate['synum'] = $minfo['cycle'];
	           // $odate['outtype'] = $minfo['outtype'];
	           // $odate['outcoin'] = $minfo['outcoin'];
	           // if($minfo['outtype'] == 1){//按产值收益
	           //    $odate['outnum'] = '';
	           //    $odate['outusdt'] = $minfo['dayoutnum'];
	           // }elseif($minfo['outtype'] == 2){//按币量收益
	           //    $odate['outnum'] = $minfo['dayoutnum']; 
	           //    $odate['outusdt'] = '';
	           // }
	           // $odate['djout'] = $minfo['djout'];
	           // if($minfo['djout'] == 2){
	           //    $odate['djnum'] = $minfo['djday']; 
	           // }else{
	           //    $odate['djnum'] = $minfo['djday']; 
	           // }
	           // $odate['addtime'] = date("Y-m-d H:i:s",time());
	           // $odate['endtime'] = date("Y-m-d H:i:s",(time() + 86400 * $minfo['cycle']));
	           // $odate['intaddtime'] = time();
	           // $odate['intendtime'] = time() + 86400 * $minfo['cycle'];
	            
            //     $adre = M("kjorder")->add($odate);
	             
	            $notice['uid'] = $uid;
		        $notice['account'] = $_POST['username'];
		        $notice['title'] = L('Certification audit successful');
		        $notice['content'] = L('Your certification application has been reviewed successfully');
		        $notice['addtime'] = date("Y-m-d H:i:s",time());
		        $notice['status'] = 1;
		        M("notice")->add($notice);
		    
	            $this->success(L("认证成功"));
	        }else{
	            $this->error(L("操作失败"));
	        }

	    }elseif($rzstatus == 3){//表示驳回认证
            $result = M("user")->where(array('id'=>$uid))->save(array('rzstatus'=>3,'rzuptime'=>time()));
            if($result){
                $notice['uid'] = $uid;
		        $notice['account'] = $_POST['username'];
		        $notice['title'] = L('Certification rejected');
		        $notice['content'] = L('Your certification application was rejected by the administrator, please contact the administrator');
		        $notice['addtime'] = date("Y-m-d H:i:s",time());
		        $notice['status'] = 1;
		        M("notice")->add($notice);
                $this->success(L("操作成功"));
                
            }else{
                $this->error(L("操作失败"));
            }
	    }

	}	
	
	//代理中心建仓订单
	public function jclist(){
		if (!session('agent_id')) {
			$this->redirect('Agent/Login/index');
		}

		$uid = session('agent_id');

		$field=I('get.field');
		$search=I('get.search');
		$where = array();
		if ($field && $search) {
			$map_3 = "$field like '%{$search}%' and (invit_1 = $uid or invit_2 = $uid or invit_3 = $uid)";

		}else{
			$map_3 = "invit_1 = $uid or invit_2 = $uid or invit_3 = $uid";
		}

		$ulist = M('User')->field('id,username,phone')->where($map_3)->select();
		if(!empty($ulist)){
			$userInfo=array_column($ulist,'phone','id');
			$ulist=array_column($ulist,'id');
			$where['uid'] = ['in',$ulist];
			$where['status'] = 1;

			$count = M('hyorder')->where($where)->count();
			$Page = new \Think\Page($count, 15);
			$show = $Page->show();
			$list = M('hyorder')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows    )->select();
			foreach ($list as $k => $v) {
				$list[$k]['phone'] =$userInfo[$v['uid']] ;
			}
			$this->assign('list', $list);
			$this->assign('page', $show);
		}
		$this->display();
	}
	
	
	//单控盈亏
	public function setwinloss(){
	    if($_POST){
	        $id = trim(I('post.id'));
	        $kongyk = trim(I('post.kongyk'));
	        $info = M("hyorder")->where(array('id'=>$id))->find();
	        if(empty($info)){
	            $this->ajaxReturn(['code'=>0,'info'=>L("参少重要参数")]);
	        }
	        
	        $result = M("hyorder")->where(array('id'=>$id))->save(array('kongyk'=>$kongyk));
	        if($result){
	            $this->ajaxReturn(['code'=>1,'info'=>L("操作成功")]); 
	        }else{
	            $this->ajaxReturn(['code'=>0,'info'=>L("操作失败")]);
	        }
	    }else{
	        $this->ajaxReturn(['code'=>0,'info'=>L("网络错误")]);
	    }
	}
	
	
	//代理中心平仓订单
	public function pclist(){
		if (!session('agent_id')) {
			$this->redirect('Agent/Login/index');
		}

		$uid = session('agent_id');

		$field=I('get.field');
		$search=I('get.search');
		$where = array();
		if ($field && $search) {
			$map_3 = "$field like '%{$search}%' and (invit_1 = $uid or invit_2 = $uid or invit_3 = $uid)";

		}else{
			$map_3 = "invit_1 = $uid or invit_2 = $uid or invit_3 = $uid";
		}

		$ulist = M('User')->field('id,username,phone')->where($map_3)->select();
		if(!empty($ulist)){
			$userInfo=array_column($ulist,'phone','id');
			$ulist=array_column($ulist,'id');
			$where['uid'] = ['in',$ulist];
			$where['status'] = ['in',[2,3]];

			$count = M('hyorder')->where($where)->count();
			$Page = new \Think\Page($count, 15);
			$show = $Page->show();
			$list = M('hyorder')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows    )->select();
			foreach ($list as $k => $v) {
				$list[$k]['phone'] =$userInfo[$v['uid']] ;
			}
			$this->assign('list', $list);
			$this->assign('page', $show);
		}
		$this->display();
		
	}
	
	
	
	
	/**
	 * 代理中心充币列表
	 */ 
	 public function recharge(){
	   if (!session('agent_id')) {
			$this->redirect('Agent/Login/index');
		}
		
		$uid = session('agent_id');

		 $field=I('get.field');
		 $search=I('get.search');
		 $where = array();
		 if ($field && $search) {
			 $map_3 = "$field like '%{$search}%' and (invit_1 = $uid or invit_2 = $uid or invit_3 = $uid)";

		 }else{
			 $map_3 = "invit_1 = $uid or invit_2 = $uid or invit_3 = $uid";
		 }

		$ulist = M('User')->field('id,username,phone')->where($map_3)->select();
		if(!empty($ulist)){
			$userInfo=array_column($ulist,'phone','id');
			$ulist=array_column($ulist,'id');
		    $where['uid'] = ['in',$ulist];
	    	$count = M('recharge')->where($where)->count();
		    $Page = new \Think\Page($count, 15);
	    	$show = $Page->show();
		    $list = M('recharge')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows    )->select();
			foreach ($list as $k => $v) {
				$list[$k]['phone'] =$userInfo[$v['uid']] ;
			}
		    $this->assign('list', $list);
		    $this->assign('page', $show);
		}
	     $this->display();
	 }
	 
	 
	 	/**
	 * 代理中心提币列表
	 */ 
	 public function withdraw($name=null){
		 if (!session('agent_id')) {
			 $this->redirect('Agent/Login/index');
		 }
		 $uid = session('agent_id');
		 $field=I('get.field');
		 $search=I('get.search');
		 $where = array();
		 if ($field && $search) {
			 $map_3 = "$field like '%{$search}%' and (invit_1 = $uid or invit_2 = $uid or invit_3 = $uid)";
		 }else{
			 $map_3 = "invit_1 = $uid or invit_2 = $uid or invit_3 = $uid";
		 }

		 $ulist = M('User')->field('id,username,phone')->where($map_3)->select();

		 if(!empty($ulist)){
			 $userInfo=array_column($ulist,'phone','id');
			 $ulist=array_column($ulist,'id');
			 $where['userid'] = ['in',$ulist];
			 $count = M('myzc')->where($where)->count();
			 $Page = new \Think\Page($count, 15);
			 $show = $Page->show();
			 $list = M('myzc')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows    )->select();
			 foreach ($list as $k => $v) {
				 $list[$k]['phone'] =$userInfo[$v['userid']] ;
			 }
			 $this->assign('list', $list);
			 $this->assign('page', $show);
		 }
		 $this->display();
	 }
	 
	 
	 /**
	  * 用户财产
	  */
	  public function property(){
		if (!session('agent_id')) {
		  $this->redirect('Agent/Login/index');
		}
		$uid = session('agent_id');
		$field=I('get.field');
		$search=I('get.search');

		if ($field && $search) {
		  $map_3 = "$field like '%{$search}%' and (invit_1 = $uid or invit_2 = $uid or invit_3 = $uid)";
		}else{
		  $map_3 = "invit_1 = $uid or invit_2 = $uid or invit_3 = $uid";
		}
      	$ulist = M('User')->where($map_3)->order('id desc')->getField('id',true);
      	if(!empty($ulist)){
      	    $where['userid'] = ['in',$ulist];
		    $count = M('UserCoin')->where($where)->count();
	    	$Page = new \Think\Page($count, 15);
		    $show = $Page->show();
	    	$list = M('UserCoin')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		    foreach ($list as $k => $v) {
				$userinfo=M('User')->where(array('id' => $v['userid']))->field('username,phone')->find();
				if ($userinfo) {
					$list[$k]['username'] =$userinfo['username'];
					$list[$k]['phone'] =$userinfo['phone'];
				}
	    	}
		    $this->assign('list', $list);
		    $this->assign('page', $show);

		    $coinlist = M("coin")->where("type = 1")->order("id desc")->field("name,title")->select();
            $this->assign("coinlist",$coinlist);

      	}
	      $this->display();
	  }
}

?>
