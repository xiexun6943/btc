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
	public function index(){
		$field=trim(I('get.field'));
		$search=trim(I('get.search'));

		if($field != ''  && $search != '' ){
			$where[$field] = $search;
		}else{
			$where=[];
		}

		$count = M('user')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('user')->field("id,username,phone")->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
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




	//抽奖参数设置
	public function setting(){
		if($_POST){
			$setting_arr = $_POST['setting'];
//			$setting_arr['lang']['name']['4']='韩文';
//			$setting_arr['lang']['type']['4']='kr';
			$data=$setting_arr;
			unset($data['lang']);
			$setting_data['draw_control'] = urlencode(serialize($setting_arr['draw_control']));
			$lang = array();
			foreach ($setting_arr['lang'] as $keyL => $valL) {
				foreach ($valL as $keL => $vaL) {
					if(!empty($vaL)){
						$lang[$keL][$keyL] = $vaL;
					}

				}
			}

			$setting_data['lang'] = urlencode(serialize($lang));
			unset($setting_arr['draw_control']);
			unset($setting_arr['lang']);

			$setting_data['draw'] = urlencode(serialize($setting_arr));
			foreach($setting_data as $k => $v) {
				M("settings") ->where(array('name' => $k ))->save(array('data' => $this->safe_replace(trim($v)))); //更新数据
			}

			$inserts=[
				'type'=>2,// 红包抽奖设置
				'data'=>json_encode($data),
				'created_at'=>date('Y-m-d H:i:s',time())
			];
			M('operate_log')->add($inserts);
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

    
    /**
     * 安全过滤函数
     *
     * @param  $string
     * @return string
 */
    public function safe_replace($string) {
    	$string = str_replace('%20', '', $string);
    	$string = str_replace('%27', '', $string);
    	$string = str_replace('%2527', '', $string);
    	$string = str_replace('"', '&quot;', $string);
    	// $string = str_replace("'", "", $string);
    	$string = str_replace('<', '&lt;', $string);
    	$string = str_replace('>', '&gt;', $string);
    	return $string;
    	// return new_htmlspecialchars($string);
    }




}

?>
