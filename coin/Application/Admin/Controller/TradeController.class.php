<?php
namespace Admin\Controller;

class TradeController extends AdminController
{

	protected function _initialize(){
		parent::_initialize();
		$allow_action=array("index","sethy","hylog","market","marketEdit","marketStatus","tradeclear","orderinfo","orderinfo_ty","setwinloss_ty","setwinloss","bbsetting","bbxjlist","bbsjlist","gethyorder","settzstatus","tyorder","setgd","gdopenlog","gdopenuser");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error("页面不存在！");
		}
	}

	//设置已通知
	public function settzstatus(){
		$where['status'] = 1;
		$where['tznum'] = 0;
		$list = M("hyorder")->where($where)->field('id')->select();
		if(!empty($list)){
			foreach($list as $key=>$vo){
				$id = $vo['id'];
				M("hyorder")->where(array('id'=>$id))->save(array('tznum'=>1));
			}
			$this->ajaxReturn(['code'=>1]);
		}

	}

	/**
	 *  提示信息 轮询
	 */
	public function gethyorder(){
		$where['status'] = 1;
		$where['tznum'] = 0;
		$count = M("hyorder")->where($where)->count();
		if($count > 0){
			$this->ajaxReturn(['code'=>1]);
		}
	}

	//币币交易市价交易记录
	public function bbsjlist(){
		$field=I('get.field');
		$search=I('get.search');
		$where = array();
		if ($field && $search) {
			$where['uid'] = M('User')->where(array($field => $search))->getField('id');
		}

		if(I('get.type') > 0){
			$hyzd = trim(I('get.type'));
			$where['type'] = $hyzd;
		}

		if(I('get.status') > 0){
			$status = trim(I('get.status'));
			$where['status'] = $status;
		}

		if(I('get.username') > 0){
			$username = trim(I('get.username'));
			$where['account'] = $username;
		}


		$where['ordertype'] = 2;
		$count = M('bborder')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('bborder')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $v) {
			$userInfo=M('User')->Field('username,phone,is_agent')->where(array('id' => $v['uid']))->find();
			if ($userInfo) {
				$list[$k]['username'] = $userInfo['username'];
				$list[$k]['phone'] = $userInfo['phone'];
				$list[$k]['is_agent'] = $userInfo['is_agent'];
			}

		}
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	//币币交易限价委托记录
	public function bbxjlist(){
		$field=I('get.field');
		$search=I('get.search');
		$where = array();
		if ($field && $search) {
			$where['uid'] = M('User')->where(array($field => $search))->getField('id');
		}

		if(I('get.type') > 0){
			$hyzd = trim(I('get.type'));
			$where['type'] = $hyzd;
		}

		if(I('get.status') > 0){
			$status = trim(I('get.status'));
			$where['status'] = $status;
		}

		$where['ordertype'] = 1;
		$count = M('bborder')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('bborder')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $v) {
			$userInfo=M('User')->Field('username,phone,is_agent')->where(array('id' => $v['uid']))->find();
			if ($userInfo) {
				$list[$k]['username'] = $userInfo['username'];
				$list[$k]['phone'] = $userInfo['phone'];
				$list[$k]['is_agent'] = $userInfo['is_agent'];
			}
		}
		$this->assign('list', $list);
		$this->assign('page', $show);

		$this->display();
	}


	//币币交易参数设置
	public function bbsetting(){
		if($_POST){
			$id = trim($_POST['bbid']);
			if($id <= 0){
				$result = M("bbsetting")->add($_POST);
			}else{
				unset($_POST['bbid']);
				$result = M("bbsetting")->where(array('id'=>$id))->save($_POST);
			}
			if($result){

				$this->success("操作成功!",U('Trade/bbsetting'));
			}else{
				$this->error("操作失败!",U('Trade/bbsetting'));
			}

		}else{

			$info = M("bbsetting")->where(array('id'=>1))->find();
			$this->assign("info",$info);
			$this->display();
		}

	}

	//单控盈亏
	public function setwinloss(){
		if($_POST){
			$id = trim(I('post.id'));
			$kongyk = trim(I('post.kongyk'));
			$info = M("hyorder")->where(array('id'=>$id))->find();
			if(empty($info)){
				$this->ajaxReturn(['code'=>0,'info'=>"参少重要参数"]);
			}

			$result = M("hyorder")->where(array('id'=>$id))->save(array('kongyk'=>$kongyk));
			if($result){
				$this->ajaxReturn(['code'=>1,'info'=>"操作成功"]);
			}else{
				$this->ajaxReturn(['code'=>0,'info'=>"操作失败"]);
			}
		}else{
			$this->ajaxReturn(['code'=>0,'info'=>"网络错误"]);
		}
	}

	//单控盈亏
	public function setwinloss_ty(){
		if($_POST){
			$id = trim(I('post.id'));
			$kongyk = trim(I('post.kongyk'));
			$info = M("tyhyorder")->where(array('id'=>$id))->find();
			if(empty($info)){
				$this->ajaxReturn(['code'=>0,'info'=>"参少重要参数"]);
			}

			$result = M("tyhyorder")->where(array('id'=>$id))->save(array('kongyk'=>$kongyk));
			if($result){
				$this->ajaxReturn(['code'=>1,'info'=>"操作成功"]);
			}else{
				$this->ajaxReturn(['code'=>0,'info'=>"操作失败"]);
			}
		}else{
			$this->ajaxReturn(['code'=>0,'info'=>"网络错误"]);
		}
	}

	//合约订单详情
	public function orderinfo(){
		$id = trim(I('get.id'));
		$info = M("hyorder")->where(array("id"=>$id))->find();
		$this->assign('info',$info);
		$this->display();
	}
	//合约订单详情
	public function orderinfo_ty(){
		$id = trim(I('get.id'));
		$info = M("tyhyorder")->where(array("id"=>$id))->find();
		$this->assign('info',$info);
		$this->display();
	}

	//秒合约参数设置
	public function sethy(){
		if($_POST){
			$id = trim($_POST['hy_id']);
			if($id <= 0){
				$result = M("hysetting")->add($_POST);
			}else{
				unset($_POST['hy_id']);
				$result = M("hysetting")->where(array('id'=>$id))->save($_POST);
			}
			if($result){

				$this->success("操作成功!",U('Trade/sethy'));
			}else{
				$this->error("操作失败!",U('Trade/sethy'));
			}

		}else{
			$info = M("hysetting")->where(array('id'=>1))->find();
			$this->assign("info",$info);
			$this->display();
		}

	}
	protected function getRedis()
	{
		$redis = new \Redis();
		$host = REDIS_HOST;
		$port = REDIS_PORT;
		$password= REDIS_PWD;
		$redis->connect($host ,$port, 30);
		$redis->auth($password);
		return $redis;
	}

	//跟单参数设置
	public function setgd(){
		if($_POST){
			$id = trim($_POST['id']);
			if($id <= 0){
				$result = M("gendan")->add($_POST);
			}else{
				unset($_POST['id']);
				$result = M("gendan")->where(array('id'=>$id))->save($_POST);
			}
			if($result){
				// 写入redis 缓存中
				$gdanInfo = M("gendan")->where(array('id'=>$id))->find();
				$redis=$this-> getRedis();
				$redis->hMSet('gdan_config',$gdanInfo);
//				$redis->del('gdan_config');
				$this->success("操作成功!",U('Trade/setgd'));
			}else{
				$this->error("操作失败!",U('Trade/setgd'));
			}

		}else{
			$gendan_arr=M('gendan')->find();
			$info = M("hysetting")->where(array('id'=>1))->find();
			$trade_time=explode(',',$info['hy_time']);
			$coin_name=['BTC/USDT','ETH/USDT','EOS/USDT','DOGE/USDT','BCH/USDT','LTC/USDT','IOTA/USDT','FIL/USDT','FLOW/USDT','JST/USDT','HT/USDT'];
			$this->assign("coin",$coin_name);
			$this->assign("info",$trade_time);
			$this->assign("data",$gendan_arr);
			$this->display();
		}

	}

	//体验订单记灵
	public function tyorder(){

		$where = array();
		if(I('get.username') != '' || I('get.username') != null){
			$username = trim(I('get.username'));
			$where['username'] = $username;
		}

		if(I('get.hyzd') > 0){
			$hyzd = trim(I('get.hyzd'));
			$where['hyzd'] = $hyzd;
		}
		$count = M('tyhyorder')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('tyhyorder')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);

		$this->display();
	}

	//合约购买记录（未平仓的）
	public function index(){

		$field=I('get.field');
		$search=I('get.search');
		$where = array();
		if ($field && $search) {
			$where['uid'] = M('User')->where(array($field => $search))->getField('id');
		}

		if(I('get.hyzd') > 0){
			$hyzd = trim(I('get.hyzd'));
			$where['hyzd'] = $hyzd;
		}
		if(I('get.is_gd') > 0){
			$is_gd= trim(I('get.is_gd'));
			$where['is_gd'] = $is_gd;
		}
		$where['status'] = 1;

		$count = M('hyorder')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('hyorder')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $v) {
			$userInfo=M('User')->Field('phone,is_agent')->where(array('id' => $v['uid']))->find();
			if ($userInfo) {
				$list[$k]['phone'] = $userInfo['phone'];
				$list[$k]['is_agent'] = $userInfo['is_agent'];
			}
		}
		$this->assign('list', $list);
		$this->assign('page', $show);

		$this->display();
	}


	//合约交易平仓记录
	public function hylog($invit=null,$username=null){

		$field=I('get.field');
		$search=I('get.search');
		$where = array();
		if ($field && $search) {
			$where['uid'] = M('User')->where(array($field => $search))->getField('id');
		}

		if(I('get.hyzd') > 0){
			$hyzd = trim(I('get.hyzd'));
			$where['hyzd'] = $hyzd;
		}
		if(I('get.is_gd') > 0){
			$is_gd= trim(I('get.is_gd'));
			$where['is_gd'] = $is_gd;
		}
		$where['status'] = 2;

		$count = M('hyorder')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('hyorder')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $v) {
			$userInfo=M('User')->Field('phone,is_agent')->where(array('id' => $v['uid']))->find();
			if ($userInfo) {
				$list[$k]['phone'] = $userInfo['phone'];
				$list[$k]['is_agent'] = $userInfo['is_agent'];
			}
		}
		$this->assign('list', $list);
		$this->assign('page', $show);

		$this->display();
	}


	//跟单开关记录
	public function gdopenlog($username=null){

		$where=[];
		if($username != ''){
			$where['username'] = trim($username);
		}


		$count = M('gd_open_log')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('gd_open_log')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	//开启跟单用户
	public function gdopenuser($username=null){

		if($username != ''){
			$where['username'] = trim($username);
		}
		$count = M('gd_member')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('gd_member')->where($where)->order('addtime desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	///机器人刷单币种列表
	public function market($field = NULL, $name = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}

		$count = M('Market')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Market')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	//编辑刷单
	public function marketEdit($id = NULL)
	{
		$getCoreConfig = getCoreConfig();
		if(!$getCoreConfig){
			$this->error('核心配置有误');
		}
		if (empty($_POST)) {
			if (empty($id)) {
				$this->data = array();
			}
			else {
				$this->data = M('Market')->where(array('id' => $id))->find();
			}
			$time_arr = array('00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23');
			$time_minute = array('00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49','50','51','52','53','54','55','56','57','58','59');
			$this->assign('time_arr', $time_arr);
			$this->assign('time_minute', $time_minute);
			$this->assign('getCoreConfig',$getCoreConfig['indexcat']);
			$this->display();
		}
		else {
			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}

			$round = array(0, 1, 2, 3, 4, 5, 6);

			if (!in_array($_POST['round'], $round)) {
				$this->error('小数位数格式错误！');
			}

			if ($_POST['id']) {
				$rs = M('Market')->save($_POST);
			}
			else {
				$_POST['name'] = $_POST['sellname'] . '_' . $_POST['buyname'];
				unset($_POST['buyname']);
				unset($_POST['sellname']);

				if (M('Market')->where(array('name' => $_POST['name']))->find()) {
					$this->error('市场存在！');
				}

				$rs = M('Market')->add($_POST);
			}

			if ($rs) {
				$this->success('操作成功！');
			}
			else {
				$this->error('操作失败！');
			}
		}
	}

	public function marketStatus($id = NULL, $type = NULL, $mobile = 'Market')
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		if (empty($id)) {
			$this->error('参数错误！');
		}

		if (empty($type)) {
			$this->error('参数错误1！');
		}

		if (strpos(',', $id)) {
			$id = implode(',', $id);
		}

		$where['id'] = array('in', $id);

		switch (strtolower($type)) {
			case 'forbid':
				$data = array('status' => 0);
				break;

			case 'resume':
				$data = array('status' => 1);
				break;

			case 'repeal':
				$data = array('status' => 2, 'endtime' => time());
				break;

			case 'delete':
				$data = array('status' => -1);
				break;

			case 'del':
				if (M($mobile)->where($where)->delete()) {
					$this->success('操作成功！');
				}
				else {
					$this->error('操作失败！');
				}

				break;

			default:
				$this->error('操作失败！');
		}

		if (M($mobile)->where($where)->save($data)) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function tradeclear($type=NULL,$id=NULL)
	{
		if(!$id){
			$this->error('请选择交易市场!');
		}
		if(!$type){
			$this->error('请选择清理类型!');
		}
		$market= M('Market')->where(array('id' => $id))->find();
		if($type==1){
			$allclear=M('Trade')->where(array('market'=>$market['name'],'userid'=>0))->delete();
		}
		if($type==2){
			if(!$market['sdhigh'] or !$market['sdlow']){
				$this->error('该市场未设置刷单最高价或最低价,无法部分清理');
			}
			$map['market']=$market['name'];
			$map['userid']=0;
			$map['price']=array('notbetween',array($market['sdhigh'],$market['sdlow']));
			$allclear=M('Trade')->where($map)->delete();
		}
		if($allclear){
			$this->success('清理成功,一共'.$allclear.'条刷单记录');
		}else{
			$this->error('清理失败!');
		}
	}


}

?>
