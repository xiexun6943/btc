<?php
namespace Admin\Controller;

class DrawController extends AdminController
{

	protected function _initialize(){
		parent::_initialize();
		$allow_action=array("index","draw","setting");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error("页面不存在！");
		}
	}
	//系统设置首页
	public function index($uid=null,$name=null){
		if($uid != ''){
			$where['uid'] = $uid;
		}

		if($name != ''){
			$where['username'] = $name;
		}

		$count = M('draw')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('user')->field("id,username,phone")->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		echo M()->getLastSql();
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



	public function search()
	{
		$page = isset($_GET['page']) && intval($_GET['page']) ? intval($_GET['page']) : 1;
		$where = " 1 ";
		if (is_array($_GET['search'])) extract($_GET['search']);
		$search_uid = trim($uid);
		$search_start_time = $start_time;
		$search_end_time = $end_time;
		if ($search_uid) {
			if (is_numeric($search_uid)) {
				$where .= " and uid = {$search_uid} ";
			} else {
				$where .= " and name LIKE '%{$search_uid}%' ";
			}
		}

		if ($search_start_time) {
			$search_start_time = date('Y-m-d 00:00:00', strtotime($search_start_time));
			$where .= " and create_time >= '{$search_start_time}' ";
		}

		if ($search_end_time) {
			$search_end_time = date('Y-m-d 23:59:59', strtotime($search_end_time));
			$where .= " and create_time <= '{$search_end_time}' ";
		}

		$list = $this->db->listinfo($where, 'uid DESC', $page, 15, 1, 10, 1, '', '', [], 'uid,name,sum(amount) as amount,count(id) as draw_ed_num', 'uid',1);
		$pages = $this->db->pages;
		$list = $this->listData($list);
		base:: load_sys_class('format', '', 0);
		base:: load_sys_class('form');
		include $this->admin_tpl('draw_list');
	}




	//秒合约参数设置
	public function setting(){
		if($_POST){
			$setting_arr = $_POST['setting'];

			$draw = array();
			foreach ($setting_arr['draw'] as $keyD => $valD) {
				foreach ($valD as $keD => $vaD) {
					if(!empty($vaD)){
						$draw[$keD][$keyD] = trim($vaD);
					}
				}
			}
			$setting_arr['draw'] = urlencode(serialize($draw));

			$drawControl = array();
			foreach ($setting_arr['draw_control'] as $keyC => $valC) {
				if(!empty($valC)){
					$drawControl[$keyC] = trim($valC);
				}else{
					$drawControl[$keyC] = '';
				}
			}
			$setting_arr['draw_control'] = urlencode(serialize($drawControl));

			$lang = array();
			foreach ($setting_arr['lang'] as $keyL => $valL) {
				foreach ($valL as $keL => $vaL) {
					if(!empty($vaL)){
						$lang[$keL][$keyL] = $vaL;
					}

				}
			}
			$setting_arr['lang'] = urlencode((serialize($lang)));
			foreach($setting_arr as $k => $v) {
				$setting[$k] = safe_replace(trim($v));
				M("hysetting") ->where(array('name' => $k ))->save(array('data' => safe_replace(trim($v)))); //更新数据
			}
			$this->success("操作成功!",U('Draw/setting'));
		}else{
			$drawArr = [];
			$draw = M('settings')->where("name in('draw','lang','draw_control')")->select();
			foreach ($draw as $k => $v) {
				$drawArr[$v['name']] = $v['data'];
			}
			$draw = unserialize(urldecode($drawArr['draw']));
			$drawControl = unserialize(urldecode($drawArr['draw_control']));
			$lang = unserialize(urldecode($drawArr['lang']));
			$this->assign("draw",$draw);
			$this->assign("drawControl",$drawControl);
			$this->assign("lang",$lang);
			$this->display();
		}

	}






}

?>
