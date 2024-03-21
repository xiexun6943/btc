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
        if (!cookie('agent_id')) {
			$this->redirect('Agent/Login/index');
		}
		$uid = cookie('agent_id');
		$this->assign('uid', $uid);
		$field=I('get.field');
		$search=I('get.search');
		if ($field && $search) {
//            $map_3 = "$field like '%{$search}%' and (invit_1 = $uid or invit_2 = $uid or invit_3 = $uid)";
            $map_3 = "$field like '%{$search}%' and path like '%,{$uid}%'";
		}else{
//		    $map_3 = "invit_1 = $uid or invit_2 = $uid or invit_3 = $uid";
		    $map_3 = "path like '%,{$uid}%'";
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
	    $uid = cookie('agent_id');
	    $agent_type=cookie('agent_type');
		$this->assign('agent_type',$agent_type);
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
				if ($agent_type == 1) {
					if($_POST['up_uid'] > 0 && $_POST['up_uid'] != ''){
						$inv_user = M('User')->where(array('id' => $_POST['up_uid']))->field("id,username,invit_1,invit_2,path")->find();
						if(empty($inv_user)){
							$this->error("推荐人不存在");exit();
						}
						$_POST['invit_1'] = $inv_user['id'];
						$_POST['invit_2'] = $inv_user['invit_1'];
						$_POST['invit_3'] = $inv_user['invit_2'];
						$_POST['path'] = $inv_user['path'].','.$inv_user['id'];
					}else{
						$_POST['invit_1'] = 0;
						$_POST['invit_2'] = 0;
						$_POST['invit_3'] = 0;
						$_POST['path']  = '';
						unset($_POST['up_uid']);
					}
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
		if (!cookie('agent_id')) {
			$this->redirect('Agent/Login/index');
		}

		$uid = cookie('agent_id');

		$field=I('get.field');
		$search=I('get.search');
		$where = array();
		if ($field && $search) {
            $map_3 = "$field like '%{$search}%' and path like '%,{$uid}%'";
		}else{
		    $map_3 = "path like '%,{$uid}%'";
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
		if (!cookie('agent_id')) {
			$this->redirect('Agent/Login/index');
		}

		$uid = cookie('agent_id');

		$field=I('get.field');
		$search=I('get.search');
		$where = array();
		if ($field && $search) {
			$map_3 = "$field like '%{$search}%' and path like '%,{$uid}%'";
		}else{
			$map_3 = "path like '%,{$uid}%'";
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
	   if (!cookie('agent_id')) {
			$this->redirect('Agent/Login/index');
		}
		
		$uid = cookie('agent_id');
		$agent_type = cookie('agent_type');

		 $field=I('get.field');
		 $search=I('get.search');
		 $where = array();
		 if ($field && $search) {
			 $map_3 = "$field like '%{$search}%' and path like '%,{$uid}%'";
		 }else{
			 $map_3 = "path like '%,{$uid}%'";
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
			$usdt_all_recharge=0;
			$btc_all_recharge=0;
			$eth_all_recharge=0;
			foreach ($list as $k => $v) {
				$list[$k]['phone'] =$userInfo[$v['uid']] ;
				if (in_array($v['coin'],['USDT','HKD','JPY']) && $v['status'] == 2) {
					$usdt_all_recharge+=$v['num'];
				}
				if ($v['coin']=='BTC' && $v['status'] == 2) {
					$btc_all_recharge+=$v['num'];
				}
				if ($v['coin']=='ETH' && $v['status'] == 2) {
					$eth_all_recharge+=$v['num'];
				}
			}
		    $this->assign('list', $list);
		    $this->assign('page', $show);
		}
		 $this->assign('usdt_all_recharge', $usdt_all_recharge);
		 $this->assign('btc_all_recharge', $btc_all_recharge);
		 $this->assign('eth_all_recharge', $eth_all_recharge);
		 $this->assign('agent_type', $agent_type);
	     $this->display();
	 }
	 
	 
	 	/**
	 * 代理中心提币列表
	 */ 
	 public function withdraw($name=null){
		 if (!cookie('agent_id')) {
			 $this->redirect('Agent/Login/index');
		 }
		 $uid = cookie('agent_id');
		 $field=I('get.field');
		 $search=I('get.search');
		 $where = array();
		 if ($field && $search) {
			 $map_3 = "$field like '%{$search}%' and path like '%,{$uid}%'";
		 }else{
			 $map_3 = "path like '%,{$uid}%'";
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
			 $usdt_all_withdraw=0;
			 $btc_all_withdraw=0;
			 $eth_all_withdraw=0;
			 foreach ($list as $k => $v) {
				 $list[$k]['phone'] =$userInfo[$v['userid']] ;
				 if (in_array($v['coinname'],['usdt','hkd','jpy']) && $v['status'] == 2) {
					 $usdt_all_withdraw+=$v['num'];
				 }
				 if ($v['coinname']=='btc' && $v['status'] == 2) {
					 $btc_all_withdraw+=$v['num'];
				 }
				 if ($v['coinname']=='eth' && $v['status'] == 2) {
					 $eth_all_withdraw+=$v['num'];
				 }
			 }
			 $this->assign('list', $list);
			 $this->assign('page', $show);
		 }
		 $this->assign('usdt_all_withdraw', $usdt_all_withdraw);
		 $this->assign('btc_all_withdraw', $btc_all_withdraw);
		 $this->assign('eth_all_withdraw', $eth_all_withdraw);
		 $this->display();
	 }
	 
	 
	 /**
	  * 用户财产
	  */
	  public function property(){
		if (!cookie('agent_id')) {
		  $this->redirect('Agent/Login/index');
		}
		$uid = cookie('agent_id');
		$field=I('get.field');
		$search=I('get.search');

		  if ($field && $search) {
			  $map_3 = "$field like '%{$search}%' and path like '%,{$uid}%'";
		  }else{
			  $map_3 = "path like '%,{$uid}%'";
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




	// 团队统计列表
	public function count()
	{
		$agent_type= cookie('agent_type');
		if (!cookie('agent_id') || $agent_type <=0 ) {
			$this->redirect('Agent/Login/index');
		}

		$uid = cookie('agent_id');


		$status=I('get.status')?:3;
		$is_get=I('get.is_get');
		$field=I('get.field');
		$search=I('get.search');
		$start_time=I('get.start_time');
		$end_time=I('get.end_time');
		$xiaji=I('get.xiaji');
		$agent_id=I('get.agent_id');
		$this->assign('field', $field);
		$this->assign('agent_id', $agent_id);
		$start_time?$start_time=$start_time:$start_time=date('Y-m-d 00:00:00');
		$end_time?$end_time=$end_time:$end_time=date('Y-m-d 23:59:59');
		$this->assign('start_time', $start_time);
		$this->assign('end_time', $end_time);

		$all_btc_recharge=0;
		$all_eth_recharge=0;
		$all_usdt_recharge=0;

		$all_btc_withdraw=0;
		$all_eth_withdraw=0;
		$all_usdt_withdraw=0;


		if ($is_get == 1) {  // 筛选 查询
			$this->assign('field', $field);
			$this->assign('search', $search);
			$this->assign('xiaji', $xiaji);
			$where = array();
			switch ($status) {
				case 1: //一级代理
					$where['is_agent']=1;
					$where['path']='';
					$where['invit_1']= 0;
					$where['invit_2']= 0;
					$where['invit_3']= 0;
					break;
				case 2: //普通用户
					$where['is_agent']=0;break;
				case 3: // 全部用户
					$where=[]; break;
				case 4: // 普通代理
					$where['is_agent']=1;
					$where['invit_1'] =[['gt',0]];
				case 5: // 二级代理
					$where['is_agent']=1;
					$where['invit_1'] =[['gt',0]];
					$where['invit_2'] =[['eq',0]];
					$where['invit_3'] =[['eq',0]];
					break;
				case 6: // 三级代理
					$where['is_agent']=1;
					$where['invit_1'] =[['gt',0]];
					$where['invit_2'] =[['gt',0]];
					$where['invit_3'] =[['eq',0]];
					break;
			}
			if ($agent_id) { // 有 上级id
				if ($field && $search) {
					$where[$field] = $search;
				}
				$where['type']=0;
				$where['invit_1']=$agent_id;
			}else{
				if ($field && $search) {
					$where[$field] = $search;
					$where['path'] = ['like',"%,{$uid}%"];
				}else{
					$where['path'] = ['like',"%,{$uid}%"];
				}
				$where['type']=0;

			}
			$count = M('User')->where($where)->count();
			$Page = new \Think\Page($count, 15);
			$show = $Page->show();
			$list = M('User')->field("id,username,phone,money,invit_1,invit_2,invit_3,path,addtime,is_agent,type")
				->where($where)
				->order('id asc')
				->limit($Page->firstRow . ',' . $Page->listRows)
				->select();
			$this->assign('status', $status);
			$list=$this->_searchList($list,$start_time,$end_time); // 筛选列表数据

		}else{   // 列表默认展示
			if ($xiaji) { // 有上级uid
				$where['invit_1']=$xiaji;
			}else{
				$where=[];
				$where['invit_1']=$uid;
			}
			$where['type']=0; // 0 为正式用户
			$count = M('User')->where($where)->count();
			$Page = new \Think\Page($count, 15);
			$show = $Page->show();
			$list = M('User')->field("id,username,phone,money,invit_1,invit_2,invit_3,path,addtime,is_agent,type")
				->where($where)
				->order('id asc')
				->limit($Page->firstRow . ',' . $Page->listRows)
				->select();
			$this->assign('status', '');
			$list=$this->_moRenList($list,$start_time,$end_time); // 列表数据

		}
		// 页面 总统计
		foreach ($list as $v) {
			$all_btc_recharge+=$v['btc_recharge'];
			$all_eth_recharge+=$v['eth_recharge'];
			$all_usdt_recharge+=$v['usdt_recharge'];

			$all_btc_withdraw+=$v['btc_withdraw'];
			$all_eth_withdraw+=$v['eth_withdraw'];
			$all_usdt_withdraw+=$v['usdt_withdraw'];
		}
		$all_btc_yingkui= $all_btc_recharge - $all_btc_withdraw;
		$all_eth_yingkui= $all_eth_recharge - $all_eth_withdraw;
		$all_usdt_yingkui= $all_usdt_recharge - $all_usdt_withdraw;

		$this->assign('all_btc_recharge', $all_btc_recharge);
		$this->assign('all_eth_recharge', $all_eth_recharge);
		$this->assign('all_usdt_recharge', $all_usdt_recharge);

		$this->assign('all_btc_withdraw', $all_btc_withdraw);
		$this->assign('all_eth_withdraw', $all_eth_withdraw);
		$this->assign('all_usdt_withdraw', $all_usdt_withdraw);

		$this->assign('all_btc_yingkui', $all_btc_yingkui);
		$this->assign('all_eth_yingkui', $all_eth_yingkui);
		$this->assign('all_usdt_yingkui', $all_usdt_yingkui);
		$this->assign('status', $status);
		$this->assign('page', $show);
		$this->assign('list', $list);
		$this->display();
	}


	// 根据用户uid 集获取所有下级用户的
	private function _getAllUIds($all_id)
	{
		$user_ids=[];
		if (!empty($all_id)) {
			foreach ($all_id as $v) {
				$ids=$this->_getAllId($v);
				$user_ids= array_merge ($user_ids,$ids) ;
			}
		}
		return $user_ids;
	}

	/**
	 * 根据 uid 集合 统计 总充值 提现 盈亏统计
	 * @param $all_zs_ids
	 * @param $start_time
	 * @param $end_time
	 * @return array
	 */
	private function _allTotal($all_zs_ids,$start_time,$end_time){
		// 总充值
		$allBtcRecharge = M('recharge')->field("sum(num) as total")->where(array('uid' => ['in',$all_zs_ids],'type'=>['in',[1,2]],'status'=>2,'updatetime'=>[['EGT',$start_time],['ELT',$end_time]],'coin'=>'BTC'))->select();
		$allEthRecharge = M('recharge')->field("sum(num) as total")->where(array('uid' => ['in',$all_zs_ids],'type'=>['in',[1,2]],'status'=>2,'updatetime'=>[['EGT',$start_time],['ELT',$end_time]],'coin'=>'ETH'))->select();
//                echo M()->getLastSql();exit();
		$allUsdtRecharge = M('recharge')->field("sum(num) as total")->where(array('uid' => ['in',$all_zs_ids],'type'=>['in',[1,2]],'status'=>2,'updatetime'=>[['EGT',$start_time],['ELT',$end_time]],'coin'=>['in',['HKD','USDT','JPY']]))->select();

		$allBtcRecharge[0]['total'] ? $all_btc_recharge= round($allBtcRecharge[0]['total'],3):$all_btc_recharge=0 ;
		$allEthRecharge[0]['total'] ? $all_eth_recharge= round($allEthRecharge[0]['total'],3):$allEthRecharge=0 ;
		$allUsdtRecharge[0]['total'] ? $all_usdt_recharge= round($allUsdtRecharge[0]['total'],3):$allUsdtRecharge=0 ;

		//提现
		$allBtcWithdraw = M('myzc')->field("sum(num) as total")->where(array('userid' =>  ['in',$all_zs_ids],'status'=>2,'endtime'=>[['EGT',$start_time],['ELT',$end_time]],'coinname'=>'btc'))->select();
		$allEthWithdraw = M('myzc')->field("sum(num) as total")->where(array('userid' =>  ['in',$all_zs_ids],'status'=>2,'endtime'=>[['EGT',$start_time],['ELT',$end_time]],'coinname'=>'eth'))->select();
		$allUsdtWithdraw = M('myzc')->field("sum(num) as total")->where(array('userid' =>  ['in',$all_zs_ids],'status'=>2,'endtime'=>[['EGT',$start_time],['ELT',$end_time]],'coinname'=>['in',['hkd','usdt','jpy']]))->select();

		$allBtcWithdraw[0]['total']?$all_btc_withdraw= round($allBtcWithdraw[0]['total'],3):$all_btc_withdraw=0 ;
		$allEthWithdraw[0]['total']?$all_eth_withdraw= round($allEthWithdraw[0]['total'],3):$all_eth_withdraw=0 ;
		$allUsdtWithdraw[0]['total']?$all_usdt_withdraw= round($allUsdtWithdraw[0]['total'],3):$all_usdt_withdraw=0 ;

		$all_btc_yingkui= $all_btc_recharge - $all_btc_withdraw;
		$all_eth_yingkui= $all_eth_recharge - $all_eth_withdraw;
		$all_usdt_yingkui= $all_usdt_recharge - $all_usdt_withdraw;

		$all_total=[
			"all_btc_recharge"=>$all_btc_recharge,
			"all_eth_recharge"=>$all_eth_recharge,
			"all_usdt_recharge"=>$all_usdt_recharge,
			"all_btc_withdraw"=>$all_btc_withdraw,
			"all_eth_withdraw"=>$all_eth_withdraw,
			"all_usdt_withdraw"=>$all_usdt_withdraw,
			"all_btc_yingkui"=>$all_btc_yingkui,
			"all_eth_yingkui"=>$all_eth_yingkui,
			"all_usdt_yingkui"=>$all_usdt_yingkui,
		];
		return $all_total;
	}

	// 默认统计列表数据
	private function _moRenList($list,$start_time,$end_time){

		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$ids=$this->_getAllId($v['id']); // 该id下面所有用户id
				empty($ids)?$list[$k]['down']=0:$list[$k]['down']=1;
				$ids[]=$v['id'];

				$list[$k]['invit_1'] = M('User')->field('username,id,phone')->where(array('id' => $v['invit_1']))->find();
				$list[$k]['coin'] = M('User_coin')->field('userid,usdt,usdtd,btc,btcd,eth,ethd')->where(array('userid' => $v['id']))->find();
				$list[$k]['coin_usdt']=$list[$k]['coin']['usdt']+$list[$k]['coin']['usdtd'];
				$list[$k]['coin_btc']=$list[$k]['coin']['btc']+$list[$k]['coin']['btcd'];
				$list[$k]['coin_eth']=$list[$k]['coin']['eth']+$list[$k]['coin']['ethd'];

				//充值
				if (!empty($ids)) {
					$btc_recharge = M('recharge')->field("sum(num) as total")->where(array('uid' => ['in',$ids],'type'=>['in',[1,2]],'status'=>2,'updatetime'=>[['EGT',$start_time],['ELT',$end_time]],'coin'=>'BTC'))->select();
					$eth_recharge = M('recharge')->field("sum(num) as total")->where(array('uid' => ['in',$ids],'type'=>['in',[1,2]],'status'=>2,'updatetime'=>[['EGT',$start_time],['ELT',$end_time]],'coin'=>'ETH'))->select();
					$usdt_recharge = M('recharge')->field("sum(num) as total")->where(array('uid' => ['in',$ids],'type'=>['in',[1,2]],'status'=>2,'updatetime'=>[['EGT',$start_time],['ELT',$end_time]],'coin'=>['in',['HKD','USDT','JPY']]))->select();
					$btc_recharge[0]['total']?$list[$k]['btc_recharge']= round($btc_recharge[0]['total'],3):$list[$k]['btc_recharge']='0.00' ;
					$eth_recharge[0]['total']?$list[$k]['eth_recharge']= round($eth_recharge[0]['total'],3):$list[$k]['eth_recharge']='0.00' ;
					$usdt_recharge[0]['total']?$list[$k]['usdt_recharge']= round($usdt_recharge[0]['total'],3):$list[$k]['usdt_recharge']='0.00' ;
					//提现
					$btc_withdraw = M('myzc')->field("sum(num) as total")->where(array('userid' =>  ['in',$ids],'status'=>2,'endtime'=>[['EGT',$start_time],['ELT',$end_time]],'coinname'=>'btc'))->select();
					$eth_withdraw = M('myzc')->field("sum(num) as total")->where(array('userid' =>  ['in',$ids],'status'=>2,'endtime'=>[['EGT',$start_time],['ELT',$end_time]],'coinname'=>'eth'))->select();
					$usdt_withdraw = M('myzc')->field("sum(num) as total")->where(array('userid' =>  ['in',$ids],'status'=>2,'endtime'=>[['EGT',$start_time],['ELT',$end_time]],'coinname'=>['in',['hkd','usdt','jpy']]))->select();
					$btc_withdraw[0]['total']?$list[$k]['btc_withdraw']= round($btc_withdraw[0]['total'],3):$list[$k]['btc_withdraw']='0.00' ;
					$eth_withdraw[0]['total']?$list[$k]['eth_withdraw']= round($eth_withdraw[0]['total'],3):$list[$k]['eth_withdraw']='0.00' ;
					$usdt_withdraw[0]['total']?$list[$k]['usdt_withdraw']= round($usdt_withdraw[0]['total'],3):$list[$k]['usdt_withdraw']='0.00' ;

					//盈亏
					$list[$k]['btc_yingkui']=round($list[$k]['btc_recharge']-$list[$k]['btc_withdraw'],3);
					$list[$k]['eth_yingkui']=round($list[$k]['eth_recharge']-$list[$k]['eth_withdraw'],3);
					$list[$k]['usdt_yingkui']=round($list[$k]['usdt_recharge']-$list[$k]['usdt_withdraw'],3);
				}else{
					$list[$k]['btc_recharge']='0.00';
					$list[$k]['eth_recharge']='0.00';
					$list[$k]['usdt_recharge']='0.00';

					$list[$k]['btc_withdraw']='0.00';
					$list[$k]['eth_withdraw']='0.00';
					$list[$k]['usdt_withdraw']='0.00';

					$list[$k]['btc_yingkui']='0.00';
					$list[$k]['eth_yingkui']='0.00';
					$list[$k]['usdt_yingkui']='0.00';
				}
				$user_login_state=M('user_log')->where(array('userid'=>$v['id'],'type' => 'login'))->order('id desc')->find();
				$list[$k]['state']=$user_login_state['state'];

				// 用户分类
				if ($v['invit_1'] == 0 && $v['invit_2'] == 0 && $v['invit_3'] == 0 && $v['is_agent'] == 1) {
					$list[$k]['user_type']=1;// 一级代理
				} elseif ($v['invit_1'] > 0 && $v['invit_2'] == 0 && $v['invit_3'] == 0 && $v['is_agent'] == 1) {
					$list[$k]['user_type']=2;// 二级代理
				} elseif ($v['invit_1'] > 0 && $v['invit_2'] > 0 && $v['invit_3'] == 0 && $v['is_agent'] == 1) {
					$list[$k]['user_type']=3;// 三级代理
				}else{
					$list[$k]['user_type']=4;// 普通用户
				}

			}
		}
		return $list;
	}

	// 筛选列表数据
	private function _searchList($list,$start_time,$end_time){
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$ids=$this->_getAllId($v['id']); // 该id下面所有用户id
				empty($ids)?$list[$k]['down']=0:$list[$k]['down']=1;
				$ids[]=$v['id'];
				$list[$k]['invit_1'] = M('User')->field('username,id,phone')->where(array('id' => $v['invit_1']))->find();
				$list[$k]['coin'] = M('User_coin')->field('userid,usdt,usdtd,btc,btcd,eth,ethd')->where(array('userid' => $v['id']))->find();
				$list[$k]['coin_usdt']=$list[$k]['coin']['usdt']+$list[$k]['coin']['usdtd'];
				$list[$k]['coin_btc']=$list[$k]['coin']['btc']+$list[$k]['coin']['btcd'];
				$list[$k]['coin_eth']=$list[$k]['coin']['eth']+$list[$k]['coin']['ethd'];

				//充值
				if (!empty($ids)) {
					$btc_recharge = M('recharge')->field("sum(num) as total")->where(array('uid' => ['in',$ids],'type'=>['in',[1,2]],'status'=>2,'updatetime'=>[['EGT',$start_time],['ELT',$end_time]],'coin'=>'BTC'))->select();
					$eth_recharge = M('recharge')->field("sum(num) as total")->where(array('uid' => ['in',$ids],'type'=>['in',[1,2]],'status'=>2,'updatetime'=>[['EGT',$start_time],['ELT',$end_time]],'coin'=>'ETH'))->select();
					$usdt_recharge = M('recharge')->field("sum(num) as total")->where(array('uid' => ['in',$ids],'type'=>['in',[1,2]],'status'=>2,'updatetime'=>[['EGT',$start_time],['ELT',$end_time]],'coin'=>['in',['HKD','USDT','JPY']]))->select();
					$btc_recharge[0]['total']?$list[$k]['btc_recharge']= round($btc_recharge[0]['total'],3):$list[$k]['btc_recharge']='0.00' ;
					$eth_recharge[0]['total']?$list[$k]['eth_recharge']= round($eth_recharge[0]['total'],3):$list[$k]['eth_recharge']='0.00' ;
					$usdt_recharge[0]['total']?$list[$k]['usdt_recharge']= round($usdt_recharge[0]['total'],3):$list[$k]['usdt_recharge']='0.00' ;

					//提现
					$btc_withdraw = M('myzc')->field("sum(num) as total")->where(array('userid' =>  ['in',$ids],'status'=>2,'endtime'=>[['EGT',$start_time],['ELT',$end_time]],'coinname'=>'btc'))->select();
					$eth_withdraw = M('myzc')->field("sum(num) as total")->where(array('userid' =>  ['in',$ids],'status'=>2,'endtime'=>[['EGT',$start_time],['ELT',$end_time]],'coinname'=>'eth'))->select();
					$usdt_withdraw = M('myzc')->field("sum(num) as total")->where(array('userid' =>  ['in',$ids],'status'=>2,'endtime'=>[['EGT',$start_time],['ELT',$end_time]],'coinname'=>['in',['hkd','usdt','jpy']]))->select();
					$btc_withdraw[0]['total']?$list[$k]['btc_withdraw']= round($btc_withdraw[0]['total'],3):$list[$k]['btc_withdraw']='0.00' ;
					$eth_withdraw[0]['total']?$list[$k]['eth_withdraw']= round($eth_withdraw[0]['total'],3):$list[$k]['eth_withdraw']='0.00' ;
					$usdt_withdraw[0]['total']?$list[$k]['usdt_withdraw']= round($usdt_withdraw[0]['total'],3):$list[$k]['usdt_withdraw']='0.00' ;

					//盈亏
					$list[$k]['btc_yingkui']=round($list[$k]['btc_recharge']-$list[$k]['btc_withdraw'],3);
					$list[$k]['eth_yingkui']=round($list[$k]['eth_recharge']-$list[$k]['eth_withdraw'],3);
					$list[$k]['usdt_yingkui']=round($list[$k]['usdt_recharge']-$list[$k]['usdt_withdraw'],3);
				}else{
					$list[$k]['btc_recharge']='0.00';
					$list[$k]['eth_recharge']='0.00';
					$list[$k]['usdt_recharge']='0.00';

					$list[$k]['btc_withdraw']='0.00';
					$list[$k]['eth_withdraw']='0.00';
					$list[$k]['usdt_withdraw']='0.00';

					$list[$k]['btc_yingkui']='0.00';
					$list[$k]['eth_yingkui']='0.00';
					$list[$k]['usdt_yingkui']='0.00';
				}

				$user_login_state=M('user_log')->where(array('userid'=>$v['id'],'type' => 'login'))->order('id desc')->find();
				$list[$k]['state']=$user_login_state['state'];
				//  用户分类
				if ($v['invit_1'] == 0 && $v['invit_2'] == 0 && $v['invit_3'] == 0 && $v['is_agent'] == 1) {
					$list[$k]['user_type']=1;// 一级代理
				} elseif ($v['invit_1'] > 0 && $v['invit_2'] == 0 && $v['invit_3'] == 0 && $v['is_agent'] == 1) {
					$list[$k]['user_type']=2;// 二级代理
				} elseif ($v['invit_1'] > 0 && $v['invit_2'] > 0 && $v['invit_3'] == 0 && $v['is_agent'] == 1) {
					$list[$k]['user_type']=3;// 三级代理
				}else{
					$list[$k]['user_type']=4;// 普通用户
				}
			}
		}

		return  $list;
	}


	/**
	 * 获取所有下级id
	 * @param $id
	 * @return array
	 */
	private  function _getAllId($id){
		$son= M('User')->field('id')->where("path like '%,{$id}%' and type =0")->select();
		if ($son && !empty($son)) {
			return array_unique(array_column($son,'id'));
		} else {
			return [];
		}

	}

	/**
	 * 遍历方式
	 * 获取所有下级id
	 * @param $id
	 * @param array $sons
	 * @return array|mixed
	 */
	private function getAllId($id, &$sons = [])
	{
		$son= M('User')->field('id')->where(['invit_1'=>$id,'type'=>0])->select();
		if ($son && !empty($son)) {
			foreach ($son as $v) {
				$sons[] = $v['id'];
				self::_getAllId($v['id'], $sons);
			}
		} else {
			return $sons;
		}
		return $sons;
	}

	/**
	 * 根据条件获取用户uid
	 * @param $where
	 * @return array
	 */
	private function _getAllZSUserId($where)
	{
		$ids= M('User')->field('id')->where($where)->select();
		if (empty($ids)) {
			return [];
		}
		return array_column($ids,'id');
	}








}

?>
