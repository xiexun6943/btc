<?php

namespace Api\Controller;

use Think\Controller;


class GdanController extends Controller
{
    protected $member;
    public function _initialize()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");

        $allow_controller = array("Index", "Ajax", "Api", "Orepool", "Finance", "Login", "Queue", "Trade", "User", "Chart", "Issue", "Ajaxtrade", "Contract", "Gdan", "Logout","record");
        if (!in_array(CONTROLLER_NAME, $allow_controller)) {
            $result = [
                'code' => 405,
                'msg' => 'Unauthorized'
            ];
            $this->ajaxReturn($result);
            exit();
        }

    }

    //token生成
    protected function CreateToken($userId)
    {
        $time = time();
        $end_time = time() + 86400;
        $info = $userId . '.' . $time . '.' . $end_time;// 设置token过期为1天
        $signature = hash_hmac('md5', $info, SIGNATURE);
        return $token = $info . '.' . $signature;
    }


    //验证token
    protected function checkToken($token)
    {
        if (!isset($token) || empty($token)) {
            $data = [
                'code' => 400,
                'message' => L('非法操作')
            ];
            return $data;
        }
        $redis=$this->getRedis();
        $explode = explode('.', $token);
        if ($token != $redis->hGet('user:token',$explode[0])) {
            $data = [
                'code' => 400,
                'message' => L('Token不合法')
            ];
            return $data;
        }
        if (!empty($explode[0]) && !empty($explode[1]) && !empty($explode[2]) && !empty($explode[3])) {
            $info = $explode[0] . '.' . $explode[1] . '.' . $explode[2];
            $true_signature = hash_hmac('md5', $info, SIGNATURE);
            if (time() > $explode[2]) {
                $data = [
                    'code' => 401,
                    'message' => L('Token已过期，请重新登录')
                ];
                return $data;
            }
            if ($true_signature == $explode[3]) {
                $data = [
                    'code' => 200,
                    'message' => L('Token合法')
                ];
                $id=$explode[0];
                $userArr= M('user')->where(['id'=>$id])->find();
                $this->member = $userArr;
            } else {
                $data = [
                    'code' => 400,
                    'message' => L('Token不合法')
                ];
            }
            return $data;
        } else {
            $data = [
                'code' => 400,
                'message' =>L( 'Token不合法')
            ];
        }
        return $data;
    }
    /**
     * 登录
     */
    public function Login()
    {
        if (!IS_POST) {
            $result = ApiResponseController::fail([
                'code' => 405,
                'msg' => L('方法不允许')
            ]);
            $this->ajaxReturn($result);
            exit;
        }
        $param = I('post.');
        $username = trim($param['username']);
        $password = trim($param['password']);
        if (!$username | !$password) {
            $result = ApiResponseController::fail([
                'code' => 400,
                'msg' => 'Invalid Params',
                'data' => [
                    "sign" => false,
                    "message" => L('登录信息不完整')
                ]
            ]);
            $this->ajaxReturn($result);
            exit;
        }

        //检测用户名是否存在
        $is_user_exist = M('user')->where(['username' => $username])->find();
        if (!$is_user_exist) {
            $return = [];
            $return['sign'] = false;
            $return['message'] = L('用户不存在');
            $result = ApiResponseController::fail([
                'code' => 400,
                'msg' => 'Login Error',
                'data' => $return
            ]);
            $this->ajaxReturn($result);
            exit;
        }
        $userinfo = M('user')->where(['username' => $username, 'password' => md5($password)])->find();
        if (!$userinfo || empty($userinfo)) {
            $return = [];
            $return['sign'] = false;
            $return['message'] = L('用户名或密码错误');
            $result = ApiResponseController::fail([
                'code' => 400,
                'msg' => 'Login Error',
                'data' => $return
            ]);
            $this->ajaxReturn($result);
            exit;
        }

        if ($userinfo['status'] == 2) { //检测是否封号
            $return = [];
            $return['sign'] = false;
            $return['message'] = L('账户不允许登入');
            $result = ApiResponseController::fail([
                'code' => 400,
                'msg' => 'Login Error',
                'data' => $return
            ]);
            $this->ajaxReturn($result);
        }


        $data = [];
        $data['username'] = $username;
        $data['password'] = $password;
        $data['loginip'] = trim($param['ip']) ? trim($param['ip']) : get_client_ip();
        if (!$data['loginip'] || !preg_match("/\d+\.\d+\.\d+\.\d+/", $data['loginip'])) {
            $return = [];
            $return['sign'] = false;
            $return['message'] = L('缺少登陆IP');
            $result = ApiResponseController::fail([
                'code' => 400,
                'msg' => 'Invalid Params',
                'data' => $return
            ]);
            $this->ajaxReturn($result);
        }
        $userData = [
            'lgtime' => date('Y-m-d H:i:s', time()),
            'loginaddr' => $data['loginip'],
            'logins' => $userinfo['logins'] + 1
        ];
        $userLogData = [
            'userid' => $userinfo['id'],
            'type' => 'Sign in',
            'remark' => 'Mailbox login',
            'addip' => $data['loginip'],
            'addr' => get_city_ip(),
            'addtime' => time(),
            'status' => 1,
        ];

        $user_result = M('user')->where(['id' => $userinfo['id']])->save($userData);
        if ($user_result) { // 用户表修改成功 在写入日志表
            $log_result = M('User_log')->add($userLogData);
        }

        if ($user_result && $log_result) {
            $return['token'] = $this->CreateToken($userinfo['id']);
            //保存登陆信息
            $redis=$this->getRedis();
            $redis->hSet('user:token',$userinfo['id'],$return['token']);
            $return['sign'] = true;

            $return['message'] = L('登陆成功');
            $result = ApiResponseController::fail([
                'code' => 200,
                'msg' => L('登陆成功'),
                'data' => $return
            ]);

            $this->ajaxReturn($result);
            exit;
        } else {
            $return['sign'] = false;
            $return['message'] = L('登陆失败');
            $result = ApiResponseController::fail([
                'code' => 500,
                'msg' => L('登陆失败'),
                'data' => $return
            ]);
            $this->ajaxReturn($result);
        }
        exit;
    }

    public function Logout($token)
    {
        $this->verifyToken($token);
        $explode = explode('.', $token);
        $redis=$this->getRedis();
        if ($redis->hExists('user:token',$explode[0])  ) {
            $redis->hDel('user:token',$explode[0]);
        }
        if ($redis->hExists('user:token',$explode[0]) == 0) {
            $return['sign'] = true;
            $return['message'] = L('退出成功');
            $result = ApiResponseController::fail([
                'code' => 200,
                'msg' => L('退出成功'),
                'data' => $return
            ]);
        } else {
            $return['sign'] = false;
            $return['message'] = L('退出失败');
            $result = ApiResponseController::fail([
                'code' => 500,
                'msg' => L('退出失败'),
                'data' => $return
            ]);
        }

        $this->ajaxReturn($result);
        exit;
    }

    // 检查token 输出
    public function verifyToken($token){
        $result=$this->checkToken($token);
        if($result['code'] != 200){
            $data=[
                'code'=>$result['code'],
                'message'=>$result['message']
            ];
            return $data;
        }
        return  [];
    }

    /**
     * 跟单开关
     */
    public function gdanSwitch()
    {
        $token=trim($_POST['token']);
        $state=trim($_POST['state']); // 开关状态
        $tokenInfo =$this->verifyToken($token); // 不为空继续执行
        if (!empty($tokenInfo)) {
            $this->ajaxReturn($tokenInfo);
        }
        $userinfo= explode('.',$token);
        $id=$userinfo[0];

        $userArr= M('user')->where(['id'=>$id])->find();
        if ($userArr['gd_state'] ==$state ) {
            $data=[
                'code'=>500,
                'message'=>L('操作失败'),
            ];
            $this->ajaxReturn($data);
        }

        if ($userArr['status'] == 2 ) { // 用户冻结
            $data=[
                'code'=>500,
                'message'=>L('用户冻结'),
            ];
            $this->ajaxReturn($data);
        }


        // 检查后台是否开启跟单功能
        $gdConfig=$this->getGdConfig();
        if (!$gdConfig || empty($gdConfig) || $gdConfig['state'] == 0) {
            $data=[
                'code'=>500,
                'message'=>L('平台未开启跟单功能,请联系平台客服!'),
            ];
            $this->ajaxReturn($data);
        }

        //  检查用户的usdt  是否达到开启标准
        if ($state == 1) {
            if (intval($this->getUserUsdt($id)) < intval($gdConfig['allow_usdt']) ) {
                $data=[
                    'code'=>500,
                    'message'=>L('用户余额不足,不能开启跟单功能!'),
                ];
                $this->ajaxReturn($data);
            }
        }
   
        if (M('user')->where(['id'=>$id])->save(['gd_state'=>$state])) {
            if ($state == 1) { // 开启写入跟单用户表
                M('gd_member')->add(['uid'=>$userArr['id'],'username'=>$userArr['username'],'addtime'=>time()]);
            }
            if ($state == 0) { // 开启写入跟单用户表
                M('gd_member')->where(['uid'=>$userArr['id']])->delete();
            }

            // 写入 跟单日志表
            $gd_log = [
                'uid' => $userArr['id'],
                'username' =>$userArr['username'] ,
                'before_state' =>$userArr['gd_state'] ,
                'after_state' => $state,
                'oddtime' => time(),
                'remark'=>'更新挂机状态成功'
            ];
            M('gd_open_log')->add($gd_log);
            $data=[
                'code'=>200,
                'message'=>L('操作成功'),
            ];
        }else{
            $data=[
                'code'=>500,
                'message'=>L('操作失败'),
            ];
        }
        $this->ajaxReturn($data);
    }

    // 设置语言
    public function setLang()
    {
        $lang=trim($_GET['Lang']);
        $data=[
            'code'=>200,
            'message'=>L('操作成功'),
        ];
        $this->ajaxReturn($data);
    }

    //获取用户可用usdt
    protected function getUserUsdt($uid){
        $user_coin= M('user_coin')->where(['userid'=>$uid])->field('userid,usdt')->find();
        $userCoin=$user_coin['usdt']?$user_coin['usdt']:'0.00000000'; //usdt
        return $userCoin;
    }
    /**
     * 获取指定账号的余额接口
     */
    public function getUsdt()
    {
        if (!IS_POST) {
            $result = ApiResponseController::fail([
                'code' => 405,
                'msg' => 'Method Not Allowed'
            ]);
            $this->ajaxReturn($result);
            exit;
        }

        $param = I('post.');
        $tokenInfo =$this->verifyToken($param['token']); // 不为空继续执行
        if (!empty($tokenInfo)) {
            $this->ajaxReturn($tokenInfo);
        }

        $user_coin= M('user_coin')->where(['userid'=>$this->member['id']])->field('userid,usdt')->find();
        $userCoin=$user_coin['usdt']?$user_coin['usdt']:'0.00000000'; //usdt

        $data=[
            'code' => 200,
            'msg' => L('获取数据成功'),
            'data'=>[
                'usdt'=>$userCoin
            ]
        ];
            $this->ajaxReturn($data);
            exit;
    }

    /**
     * 用戶信息
     */
    public function getUserInfo()
    {
        if (!IS_POST) {
            $result = ApiResponseController::fail([
                'code' => ResponseCodeController::METHOD_NOT_ALLOWED,
                'msg' => 'Method Not Allowed'
            ]);
            $this->ajaxReturn($result);
            exit;
        }
        $param = I('post.');
        $tokenInfo =$this->verifyToken($param['token']); // 不为空继续执行
        if (!empty($tokenInfo)) {
            $this->ajaxReturn($tokenInfo);
        }
        $gdUser = M('gd_member')->where(['uid'=>$this->member['id']])->find();
        $is_open=0;
        if ($gdUser && !empty($gdUser)) {
            $is_open=1;
        }
        $data=[
            'code' => 200,
            'msg' => L('获取数据成功'),
            'data'=>[
                'id'=>(int)$this->member['id'],
                'username'=>$this->member['username'],
                'gd_state'=>(int)$this->member['gd_state'], // 跟单状态 0 关闭 1 开启
                'status'=>(int)$this->member['status'],// 状态 1 正常 2 禁用
                'is_open'=>$is_open
            ]
        ];
        $this->ajaxReturn($data);
        exit;

    }

    /**
     *  跟单记录 (2个月记录)
     */
    public function record(){
        if (!IS_POST) {
            $result = ApiResponseController::fail([
                'code' => 405,
                'msg' => 'Method Not Allowed'
            ]);
            $this->ajaxReturn($result);
            exit;
        }
        $param = I('post.');
        $page=$param['page'];
        $page_size=$param['page_size'];
        $tokenInfo =$this->verifyToken($param['token']); // 不为空继续执行
        if (!empty($tokenInfo)) {
            $this->ajaxReturn($tokenInfo);
        }
        $where=[
            'uid'=>$this->member['id'],
            'is_gd'=>2 // 2是跟单
        ];
        $count = M('hyorder')->where($where)->count();
        $total_page= ceil($count / $page_size);
        $offset=($page-1)*$page_size;
        $list = M('hyorder')->where($where)->order('id desc')->limit($offset,$page_size)->select();
        if (empty($list)) {

        }
        $data=[
            'page'=>intval($page),
            'page_num'=>intval($page_size),
            'total'=>intval($count),
            'total_page'=>$total_page,
            'data'=>$list,
        ];
        $this->ajaxReturn($data);
        exit;
    }

    /**
     * 获取 跟单 数据库中的 配置
     * @return array|mixed|string
     */
    protected function getGdConfig(){
        $result=M('gendan')->find();
        if ($result && !empty($result)) {
            return $result;
        }
        return [];
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

    /**
     * @return array|false|mixed|string
     */
    protected function getConfig(){
        // 获取 redis 缓存中
        $redis=$this-> getRedis();
        if ($redis->hLen('gdan_config') == 5) {
            $configs = $redis->hGetAll('gdan_config');
        }else{
            $configs = M("gendan")->find();

            $hyconfig=M("hysetting")->find();
            $hy_time=explode(',',$hyconfig['hy_time']);
            $hy_ykbl=explode(',',$hyconfig['hy_ykbl']);
            $hysetting=array_combine($hy_time,$hy_ykbl);

            $redis->hMSet('gdan_config',$configs);
            $redis->hMSet('hy_config',$hysetting);
        }
        // 检查后台配置是否开启跟单功能
        if (empty($configs  || !$configs  )) {
            echo "后台未配置跟单数据!". "\r\n";; exit();
        }
        if ($configs['state'] == 0) {
            echo "平台未开启跟单功能,请联系平台客服!!". "\r\n";; exit();
        }
        return  $configs;
    }

    /**
     * 今日　收益　
     */
    public function todayIncome(){
        if (!IS_POST) {
            $result = ApiResponseController::fail([
                'code' => ResponseCodeController::METHOD_NOT_ALLOWED,
                'msg' => 'Method Not Allowed'
            ]);
            $this->ajaxReturn($result);
            exit;
        }
        $param = I('post.');
        $tokenInfo =$this->verifyToken($param['token']); // 不为空继续执行
        if (!empty($tokenInfo)) {
            $this->ajaxReturn($tokenInfo);
        }
        $start_time=strtotime("today");
        $end_time=strtotime("today")+86400;
        $uid=$this->member['id'];
        $total_coin= M('hyorder')
            ->where("uid = {$uid} and status = 2 and intselltime >= {$start_time} and intselltime < $end_time")
            ->field('uid ,username ,status, is_win, intselltime ,ploss ')
            ->select();
        $total=0;
        if (!empty($total_coin)) {
            foreach ($total_coin as $v) {
                if ($v['is_win'] == 1) {
                    $total+=$v['ploss'];
                }
                if ($v['is_win'] == 2) {
                    $total-=$v['ploss'];
                }
            }
        }
        $data=[
            'code' => 200,
            'msg' => L('获取数据成功'),
            'data'=>[
                'id'=>(int)$this->member['id'],
                'username'=>$this->member['username'],
                'ploss'=>$total,
            ]
        ];
        $this->ajaxReturn($data);
    }

    /**
     * 符合挂机条件的用户id 集合
     * @return array
     */
    private  function getGdMember($configs){
        $redis=$this-> getRedis();
        //  获取开启跟单的用户
        $all_users= M('gd_member')->field('uid')->select();
         $redis->del('dg_user');
        if ($all_users && !empty($all_users) ) {
           
            foreach ($all_users as $v) {

                $userCoin=M('user_coin')->field('usdt')->where(['userid'=>$v['uid']])->find();
                if ($userCoin) { // 否则删除 挂机表中数据 和 缓存集合中用户uid

                    if ($userCoin['usdt'] >$configs['allow_usdt'] ) { // 用户usdt 打印触发线 这写入挂机id名中
                        $redis->sAdd('dg_user',$v['uid']);
                    }else{
                        M('gd_member')->where(['uid'=>$v['uid']])->delete();
                        
                        // 写入 跟单日志表
                        $gd_log = [
                            'uid' => $v['uid'],
                            'username' =>$v['username'] ,
                            'before_state' =>1,
                            'after_state' => 0,
                            'oddtime' => time(),
                            'remark'=>'余额不足,弹出挂机状态'
                        ];
                        M('gd_open_log')->add($gd_log);
                    }
                }else{
                    M('gd_member')->where(['uid'=>$v['uid']])->delete();
                }

            }
        }
        return $redis->sMembers('dg_user');
    }

    //跟单 订单创建 方法
    public function gdanRun(){
        $redis=$this->getRedis();
        $hy_config=$redis->hGetAll('hy_config');
        // 检查后台是否开启跟单功能
       $configs= $this->getConfig();
        //  获取开启跟单的用户id集合
      
       $ids= $this->getGdMember($configs);
        // 合约订单生成
        if ($ids && !empty($ids)) {
            foreach ($ids as $v) {
                if ($redis->hGet('user',$v)) {
                    $username=$redis->hGet('user',$v);
                }else{
                    $user=M('user')->where(['id'=>$v])->find();
                    $redis->hSet('user',$v,$user['username']);
                    $username=$user['username'];
                }

                $coins=M('user_coin')->where(['userid'=>$v])->find();
                $coinInfo=M('currency')->where(['title'=>$configs['coin_name']])->find();
                $price_arr = json_decode($coinInfo['data'],true);
                $open = $price_arr[0]['open'];//开盘价
                $close = $price_arr[0]['close'];//现价
                $time=time();
                $hyData=[
                    'uid'=>$v,
                    'username'=>$username,
                    'num'=>$coins['usdt'],//投资金额
                    'hybl'=>$hy_config[$configs['trade_time']],//盈亏比厉
                    'hyzd'=>$configs['hyzd'],// 合约涨跌  1 涨 2跌
                    'coinname'=>$configs['coin_name'],// 交易对
                    'status'=>1,// 状态  1 待结算
                    'buytime'=>date('Y-m-d H:i:s',$time),// 购买时间
                    'buyprice'=>$close,// 建仓价格
                    'time'=>$configs['trade_time'],//  结算秒
                    'tznum'=>'0', // 通知
                    'intselltime'=>$time+$configs['trade_time'], // 通知
                    'selltime'=>date('Y-m-d H:i:s',$time+$configs['trade_time']), // 通知
                    'is_gd'=>2, // 是否跟单 2 是  1 不是
                ];
                M('hyorder')->add($hyData);
      
                echo "Uid为：".$v.'跟单合约创建成功!'. "\r\n";;
            }

        }else{
            echo "没有用户在跟单!". "\r\n";
        }
    }

    // 结算脚本
    protected function settlement(){

    }



}
?>