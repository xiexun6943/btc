<?php
/* 红包抽奖 */
namespace Home\Controller;

class DrawController extends HomeController
{
    
	protected function _initialize()
	{
		parent::_initialize();	$allow_action=array("draw","drawList","goDraw");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error(L("非法操作"));
		}
		
	}

    public function drawList()
    {
        header('Content-Type: application/json; charset=utf-8');
        $uid = userid();
        if($uid <= 0){ //检查登录
            return $this->ajaxReturn(['code'=>0,'msg'=>L('用户失效,请登录！')]) ;
        }
        $return = [
            'total_draw' => 0,
            'today_draw' => 0,
            'today_draw_num' => 0,
            'draw_desc' => 0,
            'draw_list' => 0,
            'draw_list_other' => 0,
        ];

        $todays = date('Y-m-d 00:00:00');
        $todaye = date('Y-m-d 23:59:59');
        $totalDraw= M("draw")->field('sum(amount) as amount')->where(array('uid'=>$uid))->find();
        if ($totalDraw) {
            $return['total_draw'] = $totalDraw['amount'] ?: 0;
        }
        $todayDraw= M("draw")->field('sum(amount) as amount')->where("create_time >= '{$todays}' AND create_time <= '{$todaye}' AND uid = {$uid}")->find();
        if ($todayDraw) {
            $return['today_draw'] = $todayDraw['amount'] ?: 0;
        }

        $todayST = strtotime($todays);
        $todayET = strtotime($todaye);
        $todayRecharge= M("recharge")->field('sum(num) as amount')->where("type in (1,2) and addtime between '{$todayST}' and '{$todayET}' AND uid = {$uid}")->find();

        $todayRechargeArr = false;
        if ($todayRecharge) {
            $todayRechargeArr = true;
        }

        $setting = M("settings")->where(['name'=>'draw'])->find();
        $drawSetArr = [];
        $amount = [];

        if ($setting) {
            $drawSetArr = array_column($setting, null, 'amount');
            $amount = array_column($setting, 'amount');
            rsort($amount);
        }

        $totalRecharge= M("recharge")->field('sum(num) as  amount')->where("type in (1,2) and addtime >= '{$todayST}' and addtime <= '{$todayET}' and uid = {$uid}")->find();

        if ($totalRecharge && !empty($totalRecharge['money']) && $amount) {
            $totalDrawNum = 0;
            foreach ($amount as $v) {
                if ($v <= $totalRecharge['money']  && $drawSetArr[$v]['status'] == 1 && $todayRechargeArr) {
                    // if ($v <= $totalRecharge['money']  && $drawSetArr[$v]['status'] == 1) {
                    $totalDrawNum = $drawSetArr[$v]['num'];
                    break;
                }
            }
            $drawEdNum= M("draw")->field('count(id) as draw_ed_num')->where("uid = {$uid} and create_time between '{$todays}' and '{$todaye}'")->find();
            $drawEdNum = $drawEdNum['draw_ed_num'] ? $drawEdNum['draw_ed_num'] : 0;
            $return['today_draw_num'] = ($totalDrawNum - $drawEdNum) > 0 ? $totalDrawNum - $drawEdNum : 0;
        }
        $setting = get_settings('lang');
        $langArr = [];
        if ($setting) {
            $langArr = array_column($setting, null, 'type');
        }

        $lang=cookie("language");
        $lang='zh-cn';
        switch ($lang) {
            case 'zh-cn':
                $langArr['s'] ? $return['draw_desc'] = $langArr['s']: [];
                break;
            case 'en-us':
                $langArr['e'] ? $return['draw_desc'] = $langArr['e']: [];
                break;
            case 'ja-jp':
                $langArr['j'] ? $return['draw_desc'] = $langArr['j']: [];
                break;
            case 'zh-tw':
                $langArr['t'] ? $return['draw_desc'] = $langArr['t']: [];
                break;
            case 'tr-tr':
                $langArr['v'] ? $return['draw_desc'] = $langArr['v']: [];
                break;
            default:
                $return['draw_desc'] = [];
                break;
        }
        $return['draw_list']= M("draw")->field('id,uid,name,sum(amount) as amount')->where("create_time >= '{$todays}' AND create_time <= '{$todaye}'")->select();
        foreach ($return['draw_list'] as &$v){
            $v['name'] = mb_substr($v['name'], 0, 1).'****'.mb_substr($v['name'],3);
        }
        $return['draw_list_other'] = M("draw")->order("amount desc")->limit(20)->select();
        foreach ($return['draw_list_other'] as &$ov){
            $ov['name'] = '*****'.substr($ov['name'],3);
        }
        $msg['code'] = 1;
        $msg['msg'] = L('成功!');
        $msg['data'] = $return;
        return $this->ajaxReturn($msg);

    }
    // 抽奖
    public function goDraw()
    {
        header('Content-Type: application/json; charset=utf-8');
        $uid = userid();
        if($uid <= 0){ //检查登录
            return $this->ajaxReturn(['code'=>0,'msg'=>L('用户失效,请登录！')]) ;
        }

        $users=M('user')->field('id,username,phone')->where(['id'=>$uid])->find();
        $todays = date('Y-m-d 00:00:00');
        $todaye = date('Y-m-d 23:59:59');
        $totalDrawNum = 0;

        $setting = get_settings('draw');
        $drawSetArr = [];
        $amount = [];
        if ($setting) {
            $drawSetArr = array_column($setting, null, 'amount');
            $amount = array_column($setting, 'amount');
            rsort($amount);
        }

        $todayST = strtotime($todays);
        $todayET = strtotime($todaye);
        $todayRecharge = M('recharge')->where("type in (1,2) and addtime between '{$todayST}' and '{$todayET}' AND uid = {$uid}")->find();
        $todayRechargeArr = false;
        if ($todayRecharge) {
            $todayRechargeArr = true;
        }

        $currDrawSet=[];
        $drawTodayEdData = M('draw')->where("create_time >= '{$todays}' AND create_time <= '{$todaye}' AND uid = {$uid} and is_control = 1")->order('id DESC')->find();
        $drawTodayEdDataEC = M('draw')->where("create_time = '{$todays}' AND uid = {$uid} and is_control = 1")->order('id DESC')->find();
        $drawEdData = M('draw')->where("create_time >= '{$todays}' AND create_time <= '{$todaye}' AND uid = {$uid}")->select() ?: [];
        $drawEdDataED = M('draw')->where("create_time = '{$todays}' AND uid = {$uid}")->select() ?: [];
        $drawEdNum= count($drawEdData);
        $todayDrawEdData = M('draw')->where("create_time >= '{$todays}' AND create_time <= '{$todaye}' AND uid = {$uid} and is_control = 1")->find() ?: [];
        $totalRecharge = M('recharge')->field('sum(num) as money')->where("type in (1,2) and addtime >= '{$todayST}' and addtime <= '{$todayET}' and uid = {$uid}")->find();

        if ($totalRecharge && !empty($totalRecharge['money']) && $amount) {
            foreach ($amount as $k => $v) {
                if ($v <= $totalRecharge['money']  && $drawSetArr[$v]['status'] == 1 && $todayRechargeArr) {
                    // if ($v <= $totalRecharge['money']  && $drawSetArr[$v]['status'] == 1) {
                    $currDrawSet = $drawSetArr[$v];
                    $totalDrawNum = $drawSetArr[$v]['num'];
                    break;
                }
            }
        }

        if ($totalDrawNum < ($drawEdNum + 1)){
            $msg['data'] = [];
            $msg['err'] = 'y';
            $msg['msg'] = L('您目前没有抽奖机会');
            $this->ajaxReturn($msg);
            exit;
        }
        if (empty($currDrawSet)){
            $msg['data'] = [];
            $msg['err'] = 'y';
            $msg['msg'] = L('数据错误');
            $this->ajaxReturn($msg);
            exit;
        }
        $drawControl = $this->drawControl($uid, $drawEdNum, $currDrawSet);

        $drawAmount = $drawControl['draw_amount'] ?: 0;
        $isControl = $drawControl['is_control'] ?: 0;
        $users['username']? $acount=$users['username']:$acount=$users['email'];
        M('draw')->add(array(
            'uid' => $uid,
            'name' => $acount,
            'amount' => $drawAmount,
            'is_control' => $isControl,
            'create_time' => date('Y-m-d H:i:s')));
        M('user')->where(['uid' => $uid])->setInc('money',$drawAmount);
        M('bill')->add(array(
            'uid' => $uid,
            'money' => $drawAmount,
            'countmoney' => $users['money'] + $drawAmount,
            'type' => 20,
            'addtime' => time(),
            'comment' => '中奖'
        ));
        $msg['data'] = $drawAmount;
        $msg['err'] = 'n';
        $msg['msg'] = L('成功!');
        $this->ajaxReturn($msg);
        exit;
    }


	
	
	


}
?>