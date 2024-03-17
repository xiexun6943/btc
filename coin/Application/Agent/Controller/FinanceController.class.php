<?php
namespace Agent\Controller;

class FinanceController extends AgentController
{
	//驳回充币
    public function rejectzr($id = null){
        if($id <= 0){
            $this->error("缺少重要参数");exit();
        }
        $info = M("recharge")->where(array('id'=>$id))->find();
        if(empty($info)){
            $this->error("充币订单不存在");exit();
        }
        if($info['status'] != 1){
            $this->error("此订单已处理");exit();
        }
        //修改订单状态
        $save['updatetime'] = date("Y-m-d H:i:s",time());
        $save['status'] = 3;
        $upre = M("recharge")->where(array('id'=>$id))->save($save);
        if($upre){

            $data['uid'] = $info['uid'];
            $data['account'] = $info['username'];
            $data['title'] = '充币审核';
            $data['content'] = '您的充币记录被系统驳回，请联系客服';
            $data['addtime'] = date("Y-m-d H:i:s",time());
            $data['status'] = 1;
            M("notice")->add($data);

            $this->success("充值驳回成功");
        }else{
            $this->error("驳回失败");
        }
    }
	
	//确认充币
    public function adoptzr($id = null){
        if($id <= 0){
            $this->error("缺少重要参数");exit();
        }
        $info = M("recharge")->where(array('id'=>$id))->find();
        if(empty($info)){
            $this->error("充币订单不存在");exit();
        }
        if($info['status'] != 1){
            $this->error("此订单已处理");exit();
        }
        $uid = $info['uid'];
        $num = $info['num'];
        if (in_array(strtolower(trim($info['coin'])),['jpy','hkd'])) {
            $coinname='usdt';
        }else{
            $coinname = strtolower(trim($info['coin']));
        }

        $cinfo = M("coin")->where(array('name'=>strtolower(trim($info['coin']))))->find();
        $real_num=round($num-$num*($cinfo['czsxf']/100),3);
        $minfo = M("user_coin")->where(array('userid'=>$uid))->find();
        //修改订单状态
        $save['updatetime'] = date("Y-m-d H:i:s",time());
        $save['status'] = 2;
        $save['real_num'] = $real_num;
        M()->startTrans();

        $upre = M("recharge")->where(array('id'=>$id))->save($save);
        if($minfo){
            //增加会员资产
            $incre = M("user_coin")->where(array('userid'=>$uid))->setInc($coinname,$real_num);
        }else{
            $coinData=[
                'userid'=>$uid,
                $coinname=>$real_num
            ];
            $incre = M("user_coin")->add($coinData);
        }

        //增加充值日志
        $data['uid'] = $info['uid'];
        $data['username'] = $info['username'];
        $data['num'] = $real_num;
        $data['coinname'] = $coinname;
        $data['afternum'] = $minfo[$coinname] + $real_num;
        $data['type'] = 17;
        $data['addtime'] = date("Y-m-d H:i:s",time());
        $data['st'] = 1;
        $data['remark'] = '充币到账';

        $addre = M("bill")->add($data);
        if($upre && $incre && $addre){
            M()->commit();
            $notice['uid'] = $info['uid'];
            $notice['account'] = $info['username'];
            $notice['title'] = '充币审核';
            $notice['content'] = '您的充值金额已到账，请注意查收';
            $notice['addtime'] = date("Y-m-d H:i:s",time());
            $notice['status'] = 1;
            M("notice")->add($notice);
            $this->success(L('处理成功'));
        }else{
            M()->commit();
            $this->error(L('处理失败'));
        }
    }
	
	
	    //删除充币记录
    public function del($id = null){
        $info = M("recharge")->where(array('id'=>$id))->find();
	    if(empty($info)){
	        $this->error(L("充币订单不存在"));exit();
	    }
	    $result = M("recharge")->where(array('id'=>$id))->delete();
	    if($result){
	         $this->success(L('删除成功'),U('Finance/myzr'));
	    }else{
	        $this->error(L("删除失败"));exit();
	    }
    }
    
    
    //删除提币记录
    public function delT($id = null){
        $info = M("myzc")->where(array('id'=>$id))->find();
	    if(empty($info)){
	        $this->error(L("提币订单不存在"));exit();
	    }
	    $result = M("myzc")->where(array('id'=>$id))->delete();
	    if($result){
	         $this->success(L('删除成功'),U('Finance/myzc'));
	    }else{
	        $this->error(L("删除失败"));exit();
	    }
    }


    //驳回提币记录
    public function reject($id = null){
        if($id <= 0){
            $this->error("缺少重要参数");exit();
        }
        $info = M("myzc")->where(array('id'=>$id))->find();
        if(empty($info)){
            $this->error("提币订单不存在");exit();
        }
        if($info['status'] != 1){
            $this->error("此订单已处理");exit();
        }

        $uid = $info['userid'];
        $num = $info['num'];

        if (in_array($info['coinname'],['jpy','hkd'])) {
            $coinname = 'usdt';
        }else{
            $coinname = strtolower(trim($info['coinname']));
        }

        //修改记录状态
        $save['endtime'] = date("Y-m-d H:i:s",time());
        $save['status'] = 3;
        $upre = M("myzc")->where(array('id'=>$id))->save($save);
        //把提币的数量返回给账号户，并写入日志
        $minfo = M("user_coin")->where(array('userid'=>$uid))->find();

        if (in_array($info['coinname'],['hkd','jpy'])) {
            $payname='usdt';
        }else{
            $payname=$info['coinname'];
        }
        $paynamed=$payname.'d';
        // 用户 usdtd  冻结添加
        $dec_re = M("user_coin")->where(array('userid'=>$info['userid']))->setDec($paynamed,$num);
        // 用户 usdt 添加
        $inc_re = M("user_coin")->where(array('userid'=>$info['userid']))->setInc($payname,$num);

        $bill['uid'] = $uid;
        $bill['username'] = $info['username'];
        $bill['num'] =$num;
        $bill['coinname'] = $info['coinname'];
        $bill['afternum'] = $minfo[$coinname] + $num;
        $bill['type'] = 16;
        $bill['addtime'] = date("Y-m-d H:i:s",time());
        $bill['st'] = 1;
        $bill['remark'] = L('提币驳回，退回资金');;
        $billre = M("bill")->add($bill);
        if($upre && $inc_re && $dec_re && $billre){

            $notice['uid'] = $uid;
            $notice['account'] = $info['username'];
            $notice['title'] = '提币审核';
            $notice['content'] = '您的提币申请被驳回，请联系管理员';
            $notice['addtime'] = date("Y-m-d H:i:s",time());
            $notice['status'] = 1;
            M("notice")->add($notice);

            $this->success("操作成功");exit();
        }else{
            $this->error("操作失败");exit();
        }

    }

    //通过提币处理
    public function adopttb($id = null){
        if($id <= 0){
            $this->error("缺少重要参数");exit();
        }
        $info = M("myzc")->where(array('id'=>$id))->find();
        if(empty($info)){
            $this->error("提币订单不存在");exit();
        }
        if($info['status'] != 1){
            $this->error("此订单已处理");exit();
        }
        $save['endtime'] = date("Y-m-d H:i:s",time());
        $save['status'] = 2;
        $result = M("myzc")->where(array('id'=>$id))->save($save);
        // 提现成功将 冻结提醒币扣掉
        if (in_array($info['coinname'],['hkd','jpy'])) {
            $payname='usdt';
        }else{
            $payname=$info['coinname'];
        }
        $paynamed=$payname.'d';
        $dec_re = M("user_coin")->where(array('userid'=>$info['userid']))->setDec($paynamed,$info['num']);
        if($result &&  $dec_re){

            $notice['uid'] = $info['userid'];
            $notice['account'] = $info['username'];
            $notice['title'] = '提币审核';
            $notice['content'] = '您的提币申请已通过，请及时查询';
            $notice['addtime'] = date("Y-m-d H:i:s",time());
            $notice['status'] = 1;
            M("notice")->add($notice);

            $this->success('处理成功！',U('Finance/myzc'));
        }else{
            $this->error("处理失败");exit();
        }
    }
    //账务明细
	public function index($name=null){
		if($name != ''){
		    $where['username'] = $name;
		}
		$count = M('bill')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('bill')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	

	//充币列表
	public function myzr($name=null){
	    if($name != ''){
		    $where['username'] = $name;
		}
		$count = M('recharge')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('recharge')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);

		$this->display();
	}
	
	//提币列表
	public function myzc($name=null){
	    if($name != ''){
		    $where['username'] = $name;
		}
		$count = M('myzc')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('myzc')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		
		$this->display();
	}


}
?>