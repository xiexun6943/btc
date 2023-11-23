<?php
namespace Admin\Controller;

class DrawController extends AdminController
{

	protected function _initialize(){
		parent::_initialize();
		$allow_action=array("index","draw","hylog","market","marketEdit","marketStatus","tradeclear");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error("页面不存在！");
		}
	}



	//红包抽奖记录
	public function draw(){
		$this->display();
	}

	private $db, $db2, $db3, $db4;

	public function __construct()
	{
		parent:: __construct();
		$this->db = base:: load_model('draw_model');
		$this->db2 = base:: load_model('pay_model');
		$this->db3 = base:: load_model('user_model');
		$this->db4 = base:: load_model('settings_model');
	}

	public function init()
	{
		$page = isset($_GET['page']) && intval($_GET['page']) ? intval($_GET['page']) : 1;
		$list = $this->db->listinfo('', 'uid DESC', $page, 15, 1, 10, 1, '', '', [], 'uid,name,sum(amount) as amount', 'uid',1);
		$pages = $this->db->pages;
		$list = $this->listData($list);
		base:: load_sys_class('format', '', 0);
		base:: load_sys_class('form');
		include $this->admin_tpl('draw_list');
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

	public function listData($list)
	{
		$todayS = date('Y-m-d 00:00:00');
		$todayE = date('Y-m-d 23:59:59');
		$todayDraw = $this->db->select("create_time between '{$todayS}' and '{$todayE}'"
			, 'uid,sum(amount) as amount,count(id) as draw_ed_num', '', '', 'uid');
		$todayDrawArr = [];
		$todayDrawNumArr = [];
		if ($todayDraw) {
			$todayDrawArr = array_column($todayDraw, 'amount', 'uid');
			$todayDrawNumArr = array_column($todayDraw, 'draw_ed_num', 'uid');
		}
		$todayST = strtotime($todayS);
		$todayET = strtotime($todayE);
		$totalRecharge = $this->db2->select("state in (1,2) and addtime between '{$todayST}' and '{$todayET}'", 'uid,sum(money) as money', '', '', 'uid');
		$totalRechargeArr = [];
		if ($totalRecharge) {
			$totalRechargeArr = array_column($totalRecharge, 'money', 'uid');
		}
		$todayST = strtotime($todayS);
		$todayET = strtotime($todayE);
		$todayRecharge = $this->db2->select("state in (1,2) and addtime between '{$todayST}' and '{$todayET}'", 'uid,sum(money) as money', '', '', 'uid');
		$todayRechargeArr = [];
		if ($todayRecharge) {
			$todayRechargeArr = array_column($todayRecharge, 'money', 'uid');
		}
		$user = $this->db3->select('', '*');
		$userArr = [];
		if ($user) {
			$userArr = array_column($user, null, 'uid');
		}
		$setting = $this->get_settings('draw');
		$drawSetArr = [];
		$amount = [];
		if ($setting) {
			$drawSetArr = array_column($setting, null, 'amount');
			$amount = array_column($setting, 'amount');
			rsort($amount);
		}
		foreach ($list as $k => $v) {
			$list[$k]['today_amount'] = $todayDrawArr[$v['uid']] ?? 0;
			$list[$k]['draw_ed_num'] = $todayDrawNumArr[$v['uid']] ?? 0;
			$list[$k]['total_recharge'] = $totalRechargeArr[$v['uid']] ?? 0;
			$list[$k]['agent_uid'] = '';
			$list[$k]['agent_name'] = '';
			$list[$k]['draw_num'] = 0;
			if (isset($userArr[$v['uid']])) {
				$tmpUser = $userArr[$v['uid']];
				isset($tmpUser['agent']) && !empty($tmpUser['agent']) && $list[$k]['agent_uid'] = $tmpUser['agent'];
				$list[$k]['agent_uid'] && $list[$k]['agent_name'] = $userArr[$tmpUser['agent']]['username'];
				if ($amount) {
					foreach ($amount as $av) {
						// if ($av <= $list[$k]['total_recharge'] && $drawSetArr[$av]['status'] == 1 && isset($todayRechargeArr[$v['uid']])) {
						if ($av <= $list[$k]['total_recharge'] && $drawSetArr[$av]['status'] == 1) {
							$list[$k]['draw_num'] = $drawSetArr[$av]['num'];
							break;
						}
					}
				}
			}
		}
		return $list;
	}

	// 获取系统设置信息
	public function get_settings($filed = '')
	{
		$setdb = base:: load_model('settings_model');
		if ($filed) {
			$settingdata = $setdb->get_one(array('name' => $filed));
			if ($filed == 'draw') {
				return unserialize(urldecode($settingdata['data']));
			}
			return $settingdata['data'];
		} else {
			$settingdata = $setdb->select();
			foreach ($settingdata as $k => $v) {
				$settingarr[$v['name']] = $v['data'];
			}
			return $settingarr;
		}
	}

	public function get()
	{
		$drawArr = [];
		$draw = $this->db4->select("name in('draw','draw_control','lang')");
		foreach ($draw as $k => $v) {
			$drawArr[$v['name']] = $v['data'];
		}
		$draw = $drawArr['draw'];
		$drawControl = $drawArr['draw_control'];
		$lang = $drawArr['lang'];
		include $this->admin_tpl('draw_set');
	}

	public function set()
	{
		if (isset($_POST['dosubmit'])) {
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
				$this -> db4 -> update(array('data' => safe_replace(trim($v))),array('name' => $k )); //更新数据
			}
			showmessage(_lang('更新成功！'), HTTP_REFERER);
		}
	}






}

?>
