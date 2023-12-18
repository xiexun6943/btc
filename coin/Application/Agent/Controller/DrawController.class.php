<?php
namespace Agent\Controller;

class DrawController extends AgentController
{
	protected function _initialize()
	{
		parent::_initialize();
	}

	//系统设置首页
	public function index(){
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
		$count = M('user')->where($map_3)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('user')->field("id,username,phone")->where($map_3)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$data=[];
		if ($list) {
			foreach ($list as$k=> $v) {
				$data[$k]['uid']=$v['id'];
				$data[$k]['username']=$v['username'];
				$data[$k]['phone']=$v['phone'];
				$todays=$this->getTodayDraw($v['id']);
				$data[$k]['today_draw']=$todays['amount'];
				$data[$k]['total_draw']=$this->getUserDraw($v['id']);
				$data[$k]['total_recharge']=$this->getUserRecharge($v['id']);
				$data[$k]['is_draw']=$todays['num'];
				$data[$k]['draw_total']=$this->getTotalDraw($v['id']);
			}
		}
		$this->assign('list', $data);
		$this->assign('page', $show);
		$this->display();

	}
	//当前总计有多少抽奖次数
	public function getTotalDraw($uid)
	{
		$todayS = date('Y-m-d 00:00:00');
		$todayE = date('Y-m-d 23:59:59');
		$recharge = M('recharge')->field('sum(num) as amount')->where("uid ={$uid} and updatetime between '{$todayS}' and '{$todayE}' and status =2 ")->find();
		$draw_num=0;
		if ($recharge) {
			$setting = get_settings('draw');
			krsort($setting);

			foreach ($setting as $item) {
				if ($item['amount']<$recharge['amount']) {
					$draw_num=$item['num'];break;
				}
			}
		}
		return $draw_num;
	}
	// 当天抽奖红包
	public function getTodayDraw($uid){
		$todayS = date('Y-m-d 00:00:00');
		$todayE = date('Y-m-d 23:59:59');
		$todayDraw = M('draw')->where("uid ={$uid} and create_time between '{$todayS}' and '{$todayE}'")->select();
		$data=['num'=>0,'amount'=>'0.00'];
		if ($todayDraw) {
			foreach ($todayDraw as $v) {
				$data['amount']+=$v['amount'];
			}
			$data['num']=count($todayDraw);
		}
		return $data;
	}
	// 个人所有抽奖总计
	public function getUserDraw($uid){
		$userAmount = M('draw')->field('sum(amount) as amount')->where(['uid'=>$uid])->find();
		if ($userAmount && $userAmount['amount']) {
			return $userAmount['amount'];
		}
		return '0.00';
	}
	// 个人所有充值
	public function getUserRecharge($uid){
		$recharge = M('recharge')->field('sum(num) as amount')->where(['uid'=>$uid,'status'=>2])->find();
		if ($recharge && $recharge['amount']) {
			return $recharge['amount'];
		}
		return '0.0000';
	}






}

?>
