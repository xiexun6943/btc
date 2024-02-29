<?php
namespace Mobile\Controller;

use http\Exception;

class UserController extends MobileController
{
    protected function _initialize()
    {
        parent::_initialize();
        $allow_action=array("index","getmoneyinfo","getallzhehe","login","qianbao","qianbaoadd","qianbao_coin_list","upqianbao",
            "delqianbao","log","czcoin","getsymbol","czpage","recharge_img","paycoin","czlist","czinfo","txcoin",
            "getcoinnum","txpage","txaddlist","plusaddress","upplusaddress","getaddress","tbhandle","txlist","txinfo",
            "coininfo","getnewprice","deladdress","addresslist","getmoneyusdt","getmoneybtc","getmoneyeth","getmoneyeos",
            "getmoneydoge","getmoneybch","getmoneyltc","getmoneytrx","getmoneyxrp","getmoneyiotx","getmoneyfil","getmoneyshib",
            "getmoneyflow","getmoneyjst","getmoneyitc","getmoneyht","getmoneyogo","getmoneyusdz","getmoneyatm","getmoneiota",
            "getmoneyttc","authrz","upauthrz","online","uptxt","getlineinfo",'appeal','recharge','payBank','withdraw','withdrawDo','getTxConfig');
        if(!in_array(ACTION_NAME,$allow_action)){
            $this->error("非法操作");
        }
    }

    //在线客服
    public function online(){

        exit;
        $uid = userid();
        if($uid <= 0){
            $this->redirect('Login/index');
        }
        $this->display();
    }
    //获取聊天记录
    public function getlineinfo(){
        $uid = userid();
        $list = M("online")->where(array('uid'=>$uid))->order('id asc')->field("type,content,addtime")->limit(30)->select();
        foreach($list as $k=>$v){
            $list[$k]['time'] = date("H:i",strtotime($v['addtime']));
        }
        $this->ajaxReturn(['code'=>1,'data'=>$list]);exit();

    }

    //提交聊天内容
    public function uptxt($txt = null){

        if (checkstr($txt) ) {
            $this->ajaxReturn(['code'=>0,'info'=>L('您输入的信息有误')]);
        }
        $uid = userid();
        $uinfo = M("user")->where(array('id'=>$uid))->field("id,username")->find();
        if($uid <= 0){
            $this->ajaxReturn(['code'=>0,'info'=>L('请先登陆')]);
        }

        if($txt == ''){
            $this->ajaxReturn(['code'=>0,'info'=>L('请输入信息内容')]);
        }

        $data['uid'] = $uid;
        $data['username'] = $uinfo['username'];
        $data['type'] = 2;
        $data['content'] = $txt;
        $data['addtime'] = date("Y-m-d H:i:s",time());
        $result = M("online")->add($data);
        if($result){
            $this->ajaxReturn(['code'=>1]);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>L('信息发送失败')]);
        }
    }

    //实名认证页面
    public function authrz(){
        $uid = userid();
        if($uid <= 0){
            $this->redirect('Login/index');
        }

        $userinfo = M("user")->where(array('id'=>$uid))->find();
//	    dump($userinfo);
        $this->assign("info",$userinfo);
        $this->display();
    }


    //上诉页面
    public function appeal(){

        $this->data = M('Config')->where(array('id' => 1))->find();
        $this->display();
    }


    //实名认证处理
    public function upauthrz($phone,$cardzm,$cardfm){
        if (checkstr($phone) || checkstr($cardzm) || checkstr($cardfm)) {
            $this->ajaxReturn(['code'=>0,'info'=>L('您输入的信息有误')]);
        }
        $uid = userid();

        $userinfo = M("user")->where(array('id'=>$uid))->find();
        if($uid <= 0){
            $this->ajaxReturn(['code'=>0,'info'=>L('请先登陆')]);
        }
        if (!$userinfo['phone']) {
            $uinfo = M("user")->where(array('phone'=>$phone))->find();
            if(!empty($uinfo)){
                $this->ajaxReturn(['code'=>0,'info'=>L('手机号已绑定')]);
            }
            $data['phone'] = $phone;
        }


        if($cardzm == ""){
            $this->ajaxReturn(['code'=>0,'info'=>L('请上传上传身份证正面')]);
        }
        if($cardfm == ""){
            $this->ajaxReturn(['code'=>0,'info'=>L('请上传上传身份证背面')]);
        }


        $data['cardzm'] = $cardzm;
        $data['cardfm'] = $cardfm;
        $data['rzstatus'] = 1;
        $data['rztime'] = time();

        $re = M("user")->where(array('id'=>$uid))->save($data);
        if($re){
            $notice['uid'] = $uid;
            $notice['account'] = $userinfo['username'];
            $notice['title'] = L('认证资料提交成功');
            $notice['content'] = L('实名资料提成功，耐心等待管理员审核');
            $notice['addtime'] = date("Y-m-d H:i:s",time());
            $notice['status'] = 1;
            M("notice")->add($notice);
            $this->ajaxReturn(['code'=>1,'info'=>L('认证资料提交成功')]);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>L('认证资料提交失败')]);
        }

    }

    //获取折合资产
    public function getallzhehe(){

        $uid = userid();
        $minfo = M("user_coin")->where(array('userid'=>$uid))->find();
        $usdt = $minfo['usdt'] + $minfo['usdtd'];

        $allzhehe = $usdt + session('flowzh')+ session('htzh') + session('jstzh') + session('filzh') + session('iotazh') + session('ltczh') + session('bchzh') + session('dogezh') + session('eoszh') + session('ethzh') + session('btczh');
        $this->ajaxReturn(['code'=>1,'allzhehe'=>$allzhehe]);
    }


    //获取单个币种资产(ttc)
//    public function getmoneyttc(){
//        $uid = userid();
//        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();
//
//        $ttcusdt = 0.01; //需要获取实时的行情
//
//        $re['num'] = $wallinfo['ttc'];
//        $re['numd'] = $wallinfo['ttcd'];
//        $re['zhe'] = $wallinfo['ttc'] * 0.01 + $wallinfo['ttcd'] * 0.01;
//        $re['code'] = 1;
//
//        session("ttczh",$re['zhe']);
//        $this->ajaxReturn($re);
//    }

    //获取单个币种资产(atm)
/*    public function getmoneyatm(){
        $uid = userid();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        $atmusdt = 0.01; //需要获取实时的行情

        $re['num'] = $wallinfo['atm'];
        $re['numd'] = $wallinfo['atmd'];
        $re['zhe'] = $wallinfo['atm'] * 0.01 + $wallinfo['atmd'] * 0.01;
        $re['code'] = 1;

        session("atmzh",$re['zhe']);
        $this->ajaxReturn($re);
    }*/

    //获取单个币种资产(usdz)
//    public function getmoneyusdz(){
//        $uid = userid();
//        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();
//        $where['name'] = "usdz_usdt";
//        $marketinfo = M("market")->where($where)->field("new_price")->find();
//        $usdzusdt = $marketinfo['new_price'];
//
//        $re['num'] = $wallinfo['usdz'];
//        $re['numd'] = $wallinfo['usdzd'];
//        $re['zhe'] = $wallinfo['usdz'] * $usdzusdt + $wallinfo['usdzd'] * $usdzusdt;
//        $re['code'] = 1;
//
//        session("usdzzh",$re['zhe']);
//        $this->ajaxReturn($re);
//    }



    //获取单个币种资产(ogo)
  /*  public function getmoneyogo(){
        $uid = userid();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        $coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=ogousdt";
        $result = $this->get_maket_api($coinapi);
        $price_arr  = $result['data'][0];
        $usdt_price = $price_arr['close'];

        $re['num'] = $wallinfo['ogo'];
        $re['numd'] = $wallinfo['ogod'];
        $re['zhe'] = $wallinfo['ogo'] * $usdt_price + $wallinfo['ogod'] * $usdt_price;
        $re['code'] = 1;

        session("ogozh",$re['zhe']);
        $this->ajaxReturn($re);
    }*/

    //获取单个币种资产(ht)
    public function getmoneyht(){
        $uid = userid();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        /*$coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=htusdt";
        $result = $this->get_maket_api($coinapi);
        $price_arr  = $result['data'][0];
        $usdt_price = $price_arr['close'];*/

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'HT/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

        $re['num'] = $wallinfo['ht'];
        $re['numd'] = $wallinfo['htd'];
        $re['zhe'] = round(($wallinfo['ht']  + $wallinfo['htd']) * $usdt_price,4);
        $re['code'] = 1;

        session("htzh",$re['zhe']);
        $this->ajaxReturn($re);
    }

    //获取单个币种资产(itc)
/*    public function getmoneyitc(){
        $uid = userid();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        $coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=itcusdt";
        $result = $this->get_maket_api($coinapi);
        $price_arr  = $result['data'][0];
        $usdt_price = $price_arr['close'];

        $re['num'] = $wallinfo['itc'];
        $re['numd'] = $wallinfo['itcd'];
        $re['zhe'] = $wallinfo['itc'] * $usdt_price + $wallinfo['itcd'] * $usdt_price;
        $re['code'] = 1;

        session("itczh",$re['zhe']);
        $this->ajaxReturn($re);
    }*/

    //获取单个币种资产(jst)
    public function getmoneyjst(){
        $uid = userid();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        /*$coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=jstusdt";
        $result = $this->get_maket_api($coinapi);
        $price_arr  = $result['data'][0];
        $usdt_price = $price_arr['close'];*/

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'JST/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

        $re['num'] = $wallinfo['jst'];
        $re['numd'] = $wallinfo['jstd'];
        $re['zhe'] = round(($wallinfo['jst']  + $wallinfo['jstd']) * $usdt_price,4);
        $re['code'] = 1;

        session("jstzh",$re['zhe']);
        $this->ajaxReturn($re);
    }

    //获取单个币种资产(flow)
    public function getmoneyflow(){
        $uid = userid();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        /*$coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=flowusdt";
        $result = $this->get_maket_api($coinapi);
        $price_arr  = $result['data'][0];
        $usdt_price = $price_arr['close'];*/

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'FLOW/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

        $re['num'] = $wallinfo['flow'];
        $re['numd'] = $wallinfo['flowd'];
        $re['zhe'] = $wallinfo['flow'] * $usdt_price + $wallinfo['flowd'] * $usdt_price;
        $re['code'] = 1;

        session("flowzh",$re['zhe']);
        $this->ajaxReturn($re);
    }

    //获取单个币种资产(shib)
   /* public function getmoneyshib(){
        $uid = userid();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        $coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=shibusdt";
        $result = $this->get_maket_api($coinapi);
        $price_arr  = $result['data'][0];
        $usdt_price = $price_arr['close'];

        $re['num'] = $wallinfo['shib'];
        $re['numd'] = $wallinfo['shibd'];
        $re['zhe'] = $wallinfo['shib'] * $usdt_price + $wallinfo['shibd'] * $usdt_price;
        $re['code'] = 1;

        session("shibzh",$re['zhe']);
        $this->ajaxReturn($re);
    }*/

    //获取单个币种资产(fil)
    public function getmoneyfil(){
        $uid = userid();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

       /* $coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=filusdt";
        $result = $this->get_maket_api($coinapi);
        $price_arr  = $result['data'][0];
        $usdt_price = $price_arr['close'];*/
        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'FIL/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

        $re['num'] = $wallinfo['fil'];
        $re['numd'] = $wallinfo['fild'];
        $re['zhe'] = round(($wallinfo['fil']+ $wallinfo['fild']) * $usdt_price,4);
        $re['code'] = 1;

        session("filzh",$re['zhe']);
        $this->ajaxReturn($re);
    }

    //获取单个币种资产(iotx)
   /* public function getmoneyiotx(){
        $uid = userid();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        $coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=iotxusdt";
        $result = $this->get_maket_api($coinapi);
        $price_arr  = $result['data'][0];
        $usdt_price = $price_arr['close'];

        $re['num'] = $wallinfo['iotx'];
        $re['numd'] = $wallinfo['iotxd'];
        $re['zhe'] = $wallinfo['iotx'] * $usdt_price + $wallinfo['iotxd'] * $usdt_price;
        $re['code'] = 1;

        session("iotxzh",$re['zhe']);
        $this->ajaxReturn($re);
    }*/

    //获取单个币种资产(xrp)
  /*  public function getmoneyxrp(){
        $uid = userid();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        $coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=xrpusdt";
        $result = $this->get_maket_api($coinapi);
        $price_arr  = $result['data'][0];
        $usdt_price = $price_arr['close'];

        $re['num'] = $wallinfo['xrp'];
        $re['numd'] = $wallinfo['xrpd'];
        $re['zhe'] = $wallinfo['xrp'] * $usdt_price + $wallinfo['xrpd'] * $usdt_price;
        $re['code'] = 1;

        session("xrpzh",$re['zhe']);
        $this->ajaxReturn($re);
    }*/

    //获取单个币种资产(trx)
 /*   public function getmoneytrx(){
        $uid = userid();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        $coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=trxusdt";
        $result = $this->get_maket_api($coinapi);
        $price_arr  = $result['data'][0];
        $usdt_price = $price_arr['close'];

        $re['num'] = $wallinfo['trx'];
        $re['numd'] = $wallinfo['trxd'];
        $re['zhe'] = $wallinfo['trx'] * $usdt_price + $wallinfo['trxd'] * $usdt_price;
        $re['code'] = 1;

        session("trxzh",$re['zhe']);
        $this->ajaxReturn($re);
    }*/




    //获取单个币种资产(iota)
    public function getmoneiota(){
        $uid = userid();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        /* $coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=ltcusdt";
         $result = $this->get_maket_api($coinapi);
         $price_arr  = $result['data'][0];
         $usdt_price = $price_arr['close'];*/

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'IOTA/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close
        $re['num'] = $wallinfo['iota'];
        $re['numd'] = $wallinfo['iotad'];
        $re['zhe'] =round(( $wallinfo['iota']  + $wallinfo['iotad'] )* $usdt_price,4);
        $re['code'] = 1;

        session("iotazh",$re['zhe']);
        $this->ajaxReturn($re);
    }

    //获取单个币种资产(ltc)
    public function getmoneyltc(){
        $uid = userid();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        /* $coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=ltcusdt";
         $result = $this->get_maket_api($coinapi);
         $price_arr  = $result['data'][0];
         $usdt_price = $price_arr['close'];*/

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'LTC/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close
        $re['num'] = $wallinfo['ltc'];
        $re['numd'] = $wallinfo['ltcd'];
        $re['zhe'] =  round(( $wallinfo['ltc']  + $wallinfo['ltcd'] )* $usdt_price,4);
        $re['code'] = 1;

        session("ltczh",$re['zhe']);
        $this->ajaxReturn($re);
    }

    //获取单个币种资产(bch)
    public function getmoneybch(){
        $uid = userid();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        /*$coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=bchusdt";
        $result = $this->get_maket_api($coinapi);
        $price_arr  = $result['data'][0];
        $usdt_price = $price_arr['close'];*/

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'BCH/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

        $re['num'] = $wallinfo['bch'];
        $re['numd'] = $wallinfo['bchd'];
        $re['zhe'] = round(($wallinfo['bch']  + $wallinfo['bchd']) * $usdt_price,4);
        $re['code'] = 1;

        session("bchzh",$re['zhe']);
        $this->ajaxReturn($re);
    }

    //获取单个币种资产(doge)
    public function getmoneydoge(){
        $uid = userid();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        /*$coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=dogeusdt";
        $result = $this->get_maket_api($coinapi);
        $price_arr  = $result['data'][0];
        $usdt_price = $price_arr['close'];*/

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'DOGE/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close
        $re['num'] = $wallinfo['doge'];
        $re['numd'] = $wallinfo['doged'];
        $re['zhe'] = round(($wallinfo['doge']  + $wallinfo['doged']) * $usdt_price,4);
        $re['code'] = 1;

        session("dogezh",$re['zhe']);
        $this->ajaxReturn($re);
    }

    //获取单个币种资产(eos)
    public function getmoneyeos(){
        $uid = userid();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        /*$coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=eosusdt";
        $result = $this->get_maket_api($coinapi);
        $price_arr  = $result['data'][0];
        $usdt_price = $price_arr['close'];*/
        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'EOS/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close
        $re['num'] = $wallinfo['eos'];
        $re['numd'] = $wallinfo['eosd'];
        $re['zhe'] = round(($wallinfo['eos']  + $wallinfo['eosd'] )* $usdt_price,4);
        $re['code'] = 1;

        session("eoszh",$re['zhe']);
        $this->ajaxReturn($re);
    }

    //获取单个币种资产(eth)
    public function getmoneyeth(){
        $uid = userid();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        /*$coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=ethusdt";
        $result = $this->get_maket_api($coinapi);
        $price_arr  = $result['data'][0];
        $usdt_price = $price_arr['close'];*/

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'ETH/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close
        $re['num'] = $wallinfo['eth'];
        $re['numd'] = $wallinfo['ethd'];
        $re['zhe'] = round(($wallinfo['eth']  + $wallinfo['ethd'])* $usdt_price,4) ;
        $re['code'] = 1;

        session("ethzh",$re['zhe']);
        $this->ajaxReturn($re);
    }

    //获取单个币种资产(btc)
    public function getmoneybtc(){

        $uid = userid();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();
        /*$coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=btcusdt";
        $result = $this->get_maket_api($coinapi);
        $price_arr  = $result['data'][0];
        $usdt_price = $price_arr['close'];*/
        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'BTC/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close
        $re['num'] = $wallinfo['btc'];
        $re['numd'] = $wallinfo['btcd'];
        $re['zhe'] = round(($wallinfo['btc']   + $wallinfo['btcd'] )* $usdt_price,4);
        $re['code'] = 1;

        session("btczh",$re['zhe']);
        $this->ajaxReturn($re);
    }


    //获取单个币种资产(usdt)
    public function getmoneyusdt(){
        $uid = userid();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        $re['num'] = $wallinfo['usdt'];
        $re['numd'] = $wallinfo['usdtd'];
        $re['zhe'] = round($wallinfo['usdt'] + $wallinfo['usdtd'],4);
        $re['code'] = 1;
        session("usdtzh",$re['zhe']);
        $this->ajaxReturn($re);
    }




    //获取资产列表
    public function getmoneyinfo(){
        $uid = userid();
        $coinlist = M("coin")->where(array('status'=>1))->field("name,id")->select();
        $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();
        $allzhehe = 0;
        if(!empty($coinlist)){
            foreach($coinlist as $k=>$v){
                $coinname = strtoupper($v['name']);
                $symbol = $v['name']."usdt";
                $coinnames = $v['name'];
                $coinnamed = $v['name']."d";
                //获取币的行情
                //获取币的余额
                if($coinname != "USDT"){
                    $coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
                    $result = $this->get_maket_api($coinapi);
                    $price_arr  = $result['data'][0];
                    $usdt_price = $price_arr['close'];
                    $re[$k]['cid'] = $v['id'];
                    $re[$k]['cname'] = $coinname;
                    $re[$k]['num'] = $wallinfo[$coinnames];
                    $re[$k]['numd'] = $wallinfo[$coinnamed];
                    $re[$k]['zhe'] = $wallinfo[$coinnamed] * $usdt_price + $wallinfo[$coinnames] * $usdt_price;

                }else{
                    $re[$k]['cid'] = $v['id'];
                    $re[$k]['cname'] = $coinname;
                    $re[$k]['num'] = $wallinfo['usdt'];
                    $re[$k]['numd'] = $wallinfo['usdtd'];
                    $re[$k]['zhe'] = $wallinfo['usdt'] + $wallinfo['usdtd'];
                }

                $allzhehe += $re[$k]['zhe'];
            }
            session("allzhehe",$allzhehe);
            $this->ajaxReturn(['code'=>1,'data'=>$re]);
        }
    }



    //获取单币种单价
    public function getnewprice(){

        $coinname = trim(I('post.coinname'));
        if($coinname == "USDZ"){
            $symbol = "usdz_usdt";
            $mlist = M("market")->where(array('name'=>$symbol))->field("new_price,min_price,max_price,faxingjia,volume")->find();
            $open = $mlist['min_price'];//开盘价
            $close = $mlist['new_price'] + $num;//现价
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            if($change >= 0){
                $changestr = "<span  class='fzmmm green'>+". $change ."%</span>";
            }else{
                $changestr = "<span  class='fzmmm red'>". $change ."%</span>";
            }

            if($close >= $open){
                $close = "<span  class='fzmmm green'>".$close."</span>";
            }else{
                $close = "<span  class='fzmmm red'>".$close."</span>";
            }

        }else{
            /*$lowcoin = strtolower($coinname);
            $symbol = $lowcoin.'usdt';
            $coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
            $result = $this->get_maket_api($coinapi);
            $price_arr = $result['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价*/

            $symbol=strtoupper($coinname.'/'.'usdt');
            $result=M('currency')->where(['name'=>$symbol])->find();
            $price_arr = json_decode($result['data'],true);
            $close = $price_arr[0]['close'];//现价 close
            $open = $price_arr[0]['open'];//现价 close

            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            if($change >= 0){
                $changestr = "<span  class='fzmmm green'>+". $change ."%</span>";
            }else{
                $changestr = "<span  class='fzmmm red'>". $change ."%</span>";
            }
            if($close >= $open){
                $close = "<span  class='fzmmm green'>".$close."</span>";
            }else{
                $close = "<span  class='fzmmm red'>".$close."</span>";
            }
        }

        $data['code'] = 1;
        $data['newprice'] = $close;
        $data['changestr'] = $changestr;
        $this->ajaxReturn($data);
    }





    //币种详情
    public function coininfo(){
        $lowcoin = trim(I('get.cid'));
        $coinname = strtoupper($lowcoin);
        $this->assign('coinname',$coinname);
        $uid = userid();
        $minfo = M("user_coin")->where(array('userid'=>$uid))->find();

        if($lowcoin == 'usdt'){
            $data['kymoney'] = $minfo['usdt'];
            $data['djmoney'] = $minfo['usdtd'];
            $data['zhmoney'] = $minfo['usdt'] + $minfo['usdtd'];
        }elseif($lowcoin == 'usdz'){
            $mlist = M("market")->where(array('name'=>"usdz_usdt"))->field("new_price")->find();
            $data['kymoney'] = $minfo['usdz'];
            $data['djmoney'] = $minfo['usdzd'];
            $data['zhmoney'] = ($minfo['usdzd'] + $minfo['usdz']) * $mlist['new_price'];
        }else{
//            $symbol = $lowcoin.'usdt';
//            $coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
//            $result = $this->get_maket_api($coinapi);
//            $price_arr  = $result['data'][0];
//            $usdt_price = $price_arr['close'];
            $symbol=strtoupper($lowcoin.'/'.'usdt');
            $result=M('currency')->where(['name'=>$symbol])->find();
            $price_arr = json_decode($result['data'],true);
            $usdt_price = $price_arr[0]['close'];//现价 close
            $data['kymoney'] = $minfo[$lowcoin];
            $data['djmoney'] = $minfo[$lowcoin.'d'];
            $data['zhmoney'] = $minfo[$lowcoin] * $usdt_price + $minfo[$lowcoin.'d'] * $usdt_price;
        }
        $this->assign('data',$data);
        $this->assign('lowcoin',$lowcoin);
        $uid = userid();

        $billist = M("bill")->where(array('uid'=>$uid,'coinname'=>$lowcoin))->order("id desc")->select();
        foreach ($billist as $k=> $vo){
         
            if(in_array($vo['type'],[1,2]) && $vo['status'] ==1 ){
                $tx_status=gettype_info($vo['type']).'中';
                $billist[$k]['tx_status']=L($tx_status);
            }else{
                $billist[$k]['tx_status']=L('已完成');
            }
            
          
        }
        $this->assign('billlist',$billist);

        $this->assign('oid',$lowcoin);
        $this->display();
    }

    //提币详情
    public function txinfo(){
        $id = trim(I('get.oid'));
        $info = M("myzc")->where(array('id'=>$id))->find();
        $n=substr($info['address'],0,'5');
        $m=substr($info['address'],-4);
        $info['address']=$n.'************'.$m;
        $this->assign('info',$info);
        $this->display();
    }

    //提币列表
    public function txlist(){
        $uid = userid();
        $txlist = M("myzc")->where(array('userid'=>$uid))->order('id desc')->select();
        $this->assign("txlist",$txlist);
        $this->display();
    }

    //提币处理
    public function tbhandle(){
        if($_POST){
            $uid = userid();
            $uinfo = M("user")->where(array('id'=>$uid))->field("id,rzstatus,username,paypassword,txstate ,bill,st_bill")->find();
            if(empty($uinfo)){
                $this->ajaxReturn(['code'=>0,'info'=>L('请先登陆')]);
            }
            // dump($uinfo);exit;
            if($uinfo['txstate'] == 2){
                $this->ajaxReturn(['code'=>0,'info'=>L('禁止提币,请联系客服')]);
            }

            if(empty($uinfo['paypassword'])){
                $this->ajaxReturn(['code'=>0,'info'=>L('请先设置提现密码')]);
            }
            $paypassword = trim(I('post.paypwd'));
            if($paypassword == '' || $paypassword == null){
                $this->ajaxReturn(['code'=>0,'info'=>L('请输入提现密码')]);
            }

            if(md5($paypassword) != $uinfo['paypassword']){
                $this->ajaxReturn(['code'=>0,'info'=>L('提现密码错误')]);
            }


//             if($uinfo['rzstatus'] != 2){
//                $this->ajaxReturn(['code'=>0,'info'=>L('请先完成实名认证')]);
//            }

            $id = trim(I('post.id'));
            if($id <= 0){
                $this->ajaxReturn(['code'=>0,'info'=>L('参数错误')]);
            }
            $address = trim(I('post.address'));
            if($address == '' || $address == null){
                $this->ajaxReturn(['code'=>0,'info'=>L('请输入提币地址')]);
            }
            $num = trim(I('post.num'));
            if($num <= 0){
                $this->ajaxReturn(['code'=>0,'info'=>L('请输入正确的额度')]);
            }
            $cinfo = M("coin")->where(array('id'=>$id))->find();
            if(empty($cinfo)){
                $this->ajaxReturn(['code'=>0,'info'=>L('参数错误')]);
            }
            if($num < $cinfo['txminnum']){
                $this->ajaxReturn(['code'=>0,'info'=>L('不能低于最小提币值')]);
            }
            if($num > $cinfo['txmaxnum']){
                $this->ajaxReturn(['code'=>0,'info'=>L('不能高于最大提币值')]);
            }
            if($uinfo['bill'] < $uinfo['st_bill']){
                $this->ajaxReturn(['code'=>0,'info'=>L('交易码量未达到，暂时无法提现')]);
            }



            $coinname = $cinfo['name'];
            $minfo = M("user_coin")->where(array('userid'=>$uid))->find();

            $sxftype = $cinfo['sxftype'];
            if($sxftype == 1){
                $sxf = $num * $cinfo['txsxf'] / 100;
            }elseif($sxftype == 2){
                $sxf = $cinfo['txsxf_n'];
            }

            if($sxf <= 0 || $sxf == ''){
                $sxf = 0;
            }

            $tnum = $num - $sxf;

            if($minfo[$coinname] < $num){
                $this->ajaxReturn(['code'=>0,'info'=>L('账户余额不足')]);
            }


            $dec_re = M("user_coin")->where(array('userid'=>$uid))->setDec($coinname,$num);
            $inc_re = M("user_coin")->where(array('userid'=>$uid))->setInc($coinname.'d',$num);


            $data['userid'] = $uid;
            $data['username'] = $uinfo['username'];
            $data['coinname'] = $cinfo['name'];
            $data['num'] = $num;
            $data['fee'] = $sxf;
            $data['mum'] = $tnum;
            $data['address'] = $address;
            $data['sort'] = 1;
            $data['addtime'] = date("Y-m-d H:i:s",time());
            $data['endtime'] = '';
            $data['status'] = 1;
            $result = M("myzc")->add($data);

            //操作日志
            $bill['uid'] = $uid;
            $bill['username'] = $uinfo['username'];
            $bill['num'] = $num;
            $bill['coinname'] = $cinfo['name'];
            $bill['afternum'] = $minfo[$coinname] - $num;
            $bill['type'] = 2;
            $bill['status'] = 1;
            $bill['addtime'] = date("Y-m-d H:i:s",time());
            $bill['st'] = 2;
            $bill['remark'] = "提币申请";

            $billre = M("bill")->add($bill);

            if($result && $inc_re&& $dec_re && $billre){
                $this->ajaxReturn(['code'=>1,'info'=>L('提交成功')]);
            }else{
                $this->ajaxReturn(['code'=>0,'info'=>L('提交失败')]);
            }
        }else{

        }
    }
    //银行卡提币处理
    public function withdrawDo(){
        if($_POST){
            $uid = userid();
            $uinfo = M("user")->where(array('id'=>$uid))->field("id,rzstatus,username,paypassword,txstate ,bill,st_bill")->find();
            if(empty($uinfo)){
                $this->ajaxReturn(['code'=>0,'info'=>L('请先登陆')]);
            }
            if($uinfo['txstate'] == 2){
                $this->ajaxReturn(['code'=>0,'info'=>L('禁止提币,请联系客服')]);
            }

            if(empty($uinfo['paypassword'])){
                $this->ajaxReturn(['code'=>0,'info'=>L('请先设置提现密码')]);
            }


//            if($uinfo['rzstatus'] != 2){
//                $this->ajaxReturn(['code'=>0,'info'=>L('请先完成实名认证')]);
//            }

            $id = trim(I('post.id'));
            $bankName = trim(I('post.bank_name'));
            if($id <= 0){
                $this->ajaxReturn(['code'=>0,'info'=>L('参数错误')]);
            }
            

            $address = trim(I('post.address'));
            if($address == '' || $address == null){
                $this->ajaxReturn(['code'=>0,'info'=>L('请输入银行卡号')]);
            }
            $withdrawalName = trim(I('post.user_name'));
            if($withdrawalName == '' || $withdrawalName == null){
                $this->ajaxReturn(['code'=>0,'info'=>L('请输入收款人姓名')]);
            }
            $paypassword = trim(I('post.paypwd'));
            if($paypassword == '' || $paypassword == null){
                $this->ajaxReturn(['code'=>0,'info'=>L('请输入提现密码')]);
            }

            if(md5($paypassword) != $uinfo['paypassword']){
                $this->ajaxReturn(['code'=>0,'info'=>L('提现密码错误')]);
            }
            $num = trim(I('post.num'));
            if($num <= 0){
                $this->ajaxReturn(['code'=>0,'info'=>L('请输入正确的额度')]);
            }
            $cinfo = M("coin")->where(array('id'=>$id))->find();
            if(empty($cinfo)){
                $this->ajaxReturn(['code'=>0,'info'=>L('参数错误')]);
            }
            if($num < $cinfo['txminnum']){
                $this->ajaxReturn(['code'=>0,'info'=>L('不能低于最小提币值')]);
            }
            if($num > $cinfo['txmaxnum']){
                $this->ajaxReturn(['code'=>0,'info'=>L('不能高于最大提币值')]);
            }

            if($uinfo['bill'] < $uinfo['st_bill']){
                $this->ajaxReturn(['code'=>0,'info'=>L('交易码量未达到，暂时无法提现')]);
            }


            $coinname = $cinfo['name'];
            $minfo = M("user_coin")->where(array('userid'=>$uid))->find();
            $config = M("config")->field('gu_hl,ru_hl,ug_hl,ur_hl')->find();
            $hl=$config['ug_hl'];

            $sxftype = $cinfo['sxftype'];
            if($sxftype == 1){ // 比例 手续费
                $sxf = $num * $cinfo['txsxf'] / 100;
            }elseif($sxftype == 2){ // 数量手续费
                $sxf = $cinfo['txsxf_n'];
            }

            if($sxf <= 0 || $sxf == ''){
                $sxf = 0;
            }


            $fee=round($sxf/$hl,3);
            $unum=round($num/$hl,3); // 提币数量
            $tnum =$unum-$fee;// 真实到账数量

            if($minfo['usdt'] < $unum){ // 只能在ustd中扣

                $this->ajaxReturn(['code'=>0,'info'=>L('账户余额不足')]);
            }
        
            // 用户 usdt 扣款
            $dec_re = M("user_coin")->where(array('userid'=>$uid))->setDec('usdt',$unum);
            // 用户 usdtd 冻结添加
            $inc_re = M("user_coin")->where(array('userid'=>$uid))->setInc('usdtd',$unum);

            $data['userid'] = $uid;
            $data['username'] = $uinfo['username'];
            $data['coinname'] = $cinfo['name'];
            $data['num'] = $unum;
            $data['fee'] = $fee;
            $data['mum'] = $tnum;
            $data['address'] = $address;
            $data['sort'] = 1;
            $data['bank_name'] = $bankName;
            $data['withdrawal_name'] = $withdrawalName;
            $data['addtime'] = date("Y-m-d H:i:s",time());
            $data['endtime'] = '';
            $data['status'] = 1;
            $data['remark'] = $num;
            $result = M("myzc")->add($data);

            //操作日志
            $bill['uid'] = $uid;
            $bill['username'] = $uinfo['username'];
            $bill['num'] = $unum;
            $bill['coinname'] = $cinfo['name'];
            $bill['afternum'] = $minfo['usdt'] - $unum;
            $bill['type'] = 2;
            $bill['status'] = 1;
            $bill['addtime'] = date("Y-m-d H:i:s",time());
            $bill['st'] = 2;
            $bill['remark'] = "提币申请";

            $billre = M("bill")->add($bill);

            if($result && $inc_re && $dec_re && $billre){
                $this->ajaxReturn(['code'=>1,'info'=>L('提交成功')]);
            }else{
                $this->ajaxReturn(['code'=>0,'info'=>L('提交失败')]);
            }
        }
    }

    //判断是否绑定提币地址
    public function getaddress(){
        if($_POST){
            $id = trim(I('post.id'));
            $czline = trim(I('post.czline'));
            $coininfo = M("coin")->where(array('id'=>$id))->field("id,name")->find();
//	        dump($czline);exit();
            if(empty($coininfo)){
                $this->ajaxReturn(['code'=>0]);
            }
            $coinname = $coininfo['name'];
            $uid = userid();
            $adrinfo = M("user_qianbao")->where(array('uid'=>$uid,'name'=>$coinname,'czline'=>$czline))->order('id desc')->limit(1)->find();

            if(!empty($adrinfo)){
                $this->ajaxReturn(['code'=>1]);
            }else{
                $this->ajaxReturn(['code'=>0]);
            }
        }else{
            $this->ajaxReturn(['code'=>0]);
        }
    }

    //添加地址处理
    public function upplusaddress(){
        if($_POST){
            $uid = userid();
            if($uid <= 0){
                $this->ajaxReturn(['code'=>0,'msg'=> L('请先登陆')]);
            }
            $uinfo = M("user")->where(array('id'=>$uid))->field("id,username")->find();
            $address = trim(I('post.address'));
            $remark = trim(I('post.remark'));
            $oid = trim(I('post.oid'));
            $czline = trim(I('post.czline'));
            if($oid <= 0){
                $this->ajaxReturn(['code'=>0,'msg'=> L('缺少重要参数')]);
            }

            if($address == ''){
                $this->ajaxReturn(['code'=>0,'msg'=> L('请输入提币地址')]);
            }
            if($remark == ''){
                $this->ajaxReturn(['code'=>0,'msg'=> L('请输入备注')]);
            }

            $cinfo = M("coin")->where(array('id'=>$oid))->find();

            $data['uid'] = $uid;
            $data['username'] = $uinfo['username'];
            $data['name'] = $cinfo['name'];
            $data['remark'] = $remark;
            $data['czline'] = $czline;
            $data['addr'] = $address;
            $data['sort'] = 1;
            $data['addtime'] = date('Y-m-d H:i:s');
            $data['status'] = 1;

            $result = M('user_qianbao')->add($data);
            if($result){
                $this->ajaxReturn(['code'=>1,'msg'=> L('添加成功')]);
            }else{
                $this->ajaxReturn(['code'=>0,'msg'=> L('添加失败')]);
            }

        }else{

        }
    }


    //添加地址
    public function plusaddress(){
        $id = trim(I('get.id'));

        $cinfo = M("coin")->where(array('id'=>$id))->find();
        $this->assign('info',$cinfo);

        $this->display();
    }

    //添加的提币的地址
    public function txaddlist(){
        $id = trim(I('get.id'));
        $cinfo = M("coin")->where(array('id'=>$id))->find();
        $coinname = $cinfo['name'];
        $uid = userid();
        $qblist = M("user_qianbao")->where(array('uid'=>$uid,'name'=>$coinname))->order("id desc")->select();
//        echo M("user_qianbao")->getLastSql();
//        dump($qblist);exit();
        $this->assign('qblist',$qblist);
        $this->assign('id',$id);
        $this->assign('coinname',$coinname);
        $this->display();
    }
    //提币页面
    public function txpage(){
        $id = trim(I('get.id'));
        if($id <= 0){
            $this->redirect('User/txcoin');
        }
        $info = M("coin")->where(array('id'=>$id))->find();
        $this->assign('info',$info);

        $coinname = $info['name'];
        $uid = userid();
        $minfo = M("user_coin")->where(array('userid'=>$uid))->find();
        $money = $minfo[$coinname];
        $minfo[$coinname]?$money = $minfo[$coinname]:$money = "0.00";
        $this->assign('money',$money);

        $adrinfo = M("user_qianbao")->where(array('uid'=>$uid,'name'=>$coinname,'czline'=>$info['czline']))->order('id desc')->limit(1)->find();

        if(!empty($adrinfo)){
            $this->assign('adrinfo',$adrinfo);
        }

        $this->display();
    }
    //銀行卡提币页面
    public function withdraw(){
        $id = trim(I('get.id'));
        if($id <= 0){
            $this->redirect('User/txcoin');
        }
        $info = M("coin")->where(array('id'=>$id))->find();
        $this->assign('info',$info);

        $coinname = $info['name'];
        $uid = userid();
        $minfo = M("user_coin")->where(array('userid'=>$uid))->find();
        $config = M("config")->field('ug_hl,ur_hl')->find();

        if ($info['name'] == 'aud') {
            $money = $minfo['usdt'] * $config['ug_hl'];
        }
        // if ($info['name'] == 'jpy') {
        //     $money = $minfo['usdt'] * $config['ur_hl'];
        // }

        $this->assign('money',intval($money));

        $adrinfo = M("user_qianbao")->where(array('uid'=>$uid,'name'=>$coinname,'czline'=>$info['czline']))->order('id desc')->limit(1)->find();

        if(!empty($adrinfo)){
            $this->assign('adrinfo',$adrinfo);
        }

        $this->display();
    }

    //提现币列表
    public function txcoin(){
        $uid = userid();
        if($uid <= 0){
            $this->redirect('Login/index');
        }
        $clist = M("coin")->where(array('txstatus'=>1,'type'=>1))->field("id,name,title")->order('sort asc')->select();
        $bklist = M("coin")->where(array('txstatus'=>1,'type'=>4))->field("id,name,title")->order('sort asc')->select();
        $minfo = M("user_coin")->where(array('userid'=>$uid))->find();
        $config = M("config")->field('ug_hl,ur_hl')->find();

        if(!empty($clist)){
            foreach($clist as $k=>$v){
                $coinname = $v['name'];
                $minfo[$coinname]?$coin_num = $minfo[$coinname]:$coin_num="0.00";
                $data[$k]['cname'] = strtoupper($coinname);
                $data[$k]['title'] = $v['title'];
                $data[$k]['id'] = $v['id'];
                $data[$k]['cnum'] = $coin_num;
            }

        }

        if(!empty($bklist)){
            foreach($bklist as $k=>$v){
                $coinname = $v['name'];
                if ($v['name'] == 'aud') {
                    $minfo['usdt']*$config['ug_hl']?$coin_num=  intval($minfo['usdt']*$config['ug_hl']):$coin_num="0";
                }
                // if ($v['name'] == 'jpy') {
                //     $minfo['usdt']*$config['ug_hl']?$coin_num=intval($minfo['usdt']*$config['ur_hl']):$coin_num="0";
                // }
                $bddata[$k]['cname'] = strtoupper($coinname);
                $bddata[$k]['title'] = $v['title'];
                $bddata[$k]['id'] = $v['id'];
                $bddata[$k]['cnum'] = $coin_num;
            }

        }

        $this->assign("list",$data);
        $this->assign("bklist",$bddata);
        $this->display();
    }

    //提币地址管理
    public function addresslist(){
        $uid = userid();
        if($uid <= 0){
            $this->redirect('Index/index');
        }
        $adrlist = M("user_qianbao")->where(array('uid'=>$uid))->select();
        $this->assign("adrlist",$adrlist);

        $this->display();
    }

    //删除提币地址
    public function deladdress($aid){
        $uid = userid();
        if($uid <= 0){
            $this->redirect('Index/index');
        }
        $ainfo = M("user_qianbao")->where(array('id'=>$aid))->find();
        if(empty($ainfo)){
            $this->ajaxReturn(['code'=>0,'info'=> L('提币地址不存在')]);
        }
        $delre = M("user_qianbao")->where(array('id'=>$aid))->delete();

        if($delre){
            $this->ajaxReturn(['code'=>1,'info'=> L('删除成功')]);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=> L('删除失败')]);
        }
        $this->display();
    }

    //充值列表
    public function czlist(){
        $uid = userid();
        $mlist = M("recharge")->where(array('uid'=>$uid))->order("id desc")->select();
        $data=[];
        if ($mlist && !empty($mlist)) {
            foreach ($mlist as$k=> $vo) {
                $data[$k]=$vo;
                $data[$k]['sxf']=round($vo['num']-$vo['real_num'],2);
            }

        }
        $this->assign('mlist',$data);
        $this->display();
    }

    public function czinfo(){
        $id = trim(I('get.oid'));
        $uid = userid();
        $info = M("recharge")->where(array('uid'=>$uid,'id'=>$id))->order("id desc")->find();
        if(empty($info)){
            $this->redirect('User/czlist');
        }
        $this->assign("info",$info);
        $this->display();
    }

    //上传转账号凭证
    public function paycoin(){
        if($_POST){
            $uid = userid();
            $uinfo = M("user")->where(array('id'=>$uid))->field("id,username")->find();
            if(empty($uinfo)){
                $this->ajaxReturn(['code'=>0,'info'=> L('请先登陆')]);
            }
            $cid = trim(I('post.cid'));
            $zznum = trim(I('post.zznum'));
            $payimg = trim(I('post.payimg'));
            $coinname = trim(I('post.coinname'));
            if($zznum <= 0){
                $this->ajaxReturn(['code'=>0,'info'=> L('请输入正确充值数量')]);
            }

            if($payimg == ""){
                $this->ajaxReturn(['code'=>0,'info'=> L('请上传转账凭证')]);
            }

            if($coinname == ""){
                $this->ajaxReturn(['code'=>0,'info'=> L('缺少重要参数')]);
            }

            if($cid == ""){
                $this->ajaxReturn(['code'=>0,'info'=> L('缺少重要参数')]);
            }


            $cinfo = M("coin")->where(array('id'=>$cid))->find();

            //$bi=M('coin')->where(array('name'=>$coinname))->find();
            if($zznum < $cinfo['czminnum']){
                $this->ajaxReturn(['code'=>0,'info'=> L('低于最低额度')]);
            }
            $data['uid'] = $uid;
            $data['username'] = $uinfo['username'];
            $data['coin'] = strtoupper($coinname);
            $data['num'] = $zznum;
            $data['addtime'] = date("Y-m-d H:i:s",time());
            $data['updatetime'] = null;
            $data['status'] = 1;
            $data['payimg'] = $payimg;
            $data['msg'] = '无';
            $result = M("recharge")->add($data);

            if($result){
                $this->ajaxReturn(['code'=>1,'info'=> L('凭证提交成功')]);
            }else{
                $this->ajaxReturn(['code'=>0,'info'=> L('凭证提交失败')]);
            }

        }else{
            $this->ajaxReturn(['code'=>0,'info'=> L('参数错误')]);
        }
    }

    /* 币种列表页 */
    public function qianbao_coin_list(){

        $this->display();
    }


    public function qianbaoadd(){

        $this->display();
    }

    //我的资产中心
    public function index(){
        $uid = userid();
        if($uid <= 0){
            $this->redirect('Login/index');
        }
//        $clist = M("coin")->where(array('status'=>1))->order('id asc')->field("id,name")->select();
//        $data=array_column($clist,null,'name');
        $data=[ // 手动配置添加 的
            'usdt'=>['name'=>'usdt'],
            'btc'=>['name'=>'btc'],
            'eth'=>['name'=>'eth'],
            'eos'=>['name'=>'eos'],
            'doge'=>['name'=>'doge'],
            'bch'=>['name'=>'bch'],
            'ltc'=>['name'=>'ltc'],
            'iota'=>['name'=>'iota'],
            'fil'=>['name'=>'fil'],
            'flow'=>['name'=>'flow'],
            'jst'=>['name'=>'jst'],
            'ht'=>['name'=>'ht']
        ];

        $this->assign("list",$data);
        $this->assign("select",'user');
        $this->display();
    }

    public function getsymbol(){
        $symbol = trim(I('post.symbol'));

        $symbol = strtolower($symbol);
        if ($symbol) {
            $map['name'] = array('like',"%$symbol%");
            $info = M("coin")->where($map)->field("name,title,id")->find();
            $uname = strtoupper($info['name']);
            if ($info) {
                $info['uname'] = $uname;
                $this->ajaxReturn(['code'=>1,'info'=>$info]);
            }
            $this->ajaxReturn(['code'=>0,'info'=>[]]);
        }else{
            $list = M("coin")
                ->where(array('status'=>1,'type'=>['in',[1,4]]))
                ->order('sort asc')
                ->field("name,title,id,czstatus")
                ->select();
            $this->ajaxReturn(['code'=>1,'info'=>$list]);
        }

    }

    //获取单个提币币种列表
    public function getcoinnum(){
        $symbol = trim(I('post.symbol'));
        if ($symbol) {

            $symbol = strtolower($symbol);
            $map['name'] = array('like',"%$symbol%");
            $info = M("coin")->where($map)->field("name,title,id")->find();
            $coinname = $info['name'];
            $uid = userid();
            $minfo = M("user_coin")->where(array('userid'=>$uid))->find();
            $cnum="0.00";
            if(!empty($minfo)){
                $minfo[$coinname]?$cnum = $minfo[$coinname]:$cnum="0.00";
            }
            if ($info) {
                $data['cname'] = strtoupper($info['name']);
                $data['title'] = $info['title'];
                $data['cnum'] = $cnum;
                $data['id'] = $info['id'];
                $this->ajaxReturn(['code'=>1,'info'=>$data]);
            }
            $this->ajaxReturn(['code'=>0,'info'=>[]]);
        }else {
            $uid = userid();
            $clist = M("coin")->where(array('txstatus'=>1,'type'=>1))->field("id,name,title")->order('sort asc')->select();
            $bklist = M("coin")->where(array('txstatus'=>1,'type'=>4))->field("id,name,title")->order('sort asc')->select();
            $minfo = M("user_coin")->where(array('userid'=>$uid))->find();
            $config = M("config")->field('ug_hl,ur_hl')->find();

            if(!empty($clist)){
                foreach($clist as $k=>$v){
                    $coinname = $v['name'];
                    $minfo[$coinname]?$coin_num = $minfo[$coinname]:$coin_num="0.00";
                    $data[$k]['cname'] = strtoupper($coinname);
                    $data[$k]['title'] = $v['title'];
                    $data[$k]['id'] = $v['id'];
                    $data[$k]['cnum'] = $coin_num;
                }

            }

            if(!empty($bklist)){
                foreach($bklist as $k=>$v){
                    $coinname = $v['name'];
                    if ($v['name'] == 'hkd') {
                        $minfo['usdt']*$config['ug_hl']?$coin_num=  round($minfo['usdt']*$config['ug_hl'],2):$coin_num="0.00";
                    }
                    if ($v['name'] == 'jpy') {
                        $minfo['usdt']*$config['ug_hl']?$coin_num=round($minfo['usdt']*$config['ur_hl'],0):$coin_num="0.00";
                    }
                    $bddata[$k]['cname'] = strtoupper($coinname);
                    $bddata[$k]['title'] = $v['title'];
                    $bddata[$k]['id'] = $v['id'];
                    $bddata[$k]['cnum'] = $coin_num;
                }

            }
            $list=array_merge($data,$bddata);
            $this->ajaxReturn(['code'=>1,'info'=>$list]);
        }


    }
    //币种充值页面
    public function recharge()
    {
        $id = trim(I('get.id'));
        $uid = userid();
        if($id <= 0){
            $this->redirect('User/czcoin');
        }
        $info = M("coin")->where(array('id'=>$id))->find();
        if($info['czstatus'] != 1){
            $this->redirect('User/czcoin');
        }

        //查询最上级是否代理
        $user = M("user")->where(['id'=>$uid])->find();

        $this->assign('info',$info);

        $address = $info['czaddress'];


        $this->assign("address",$address);


        $this->display();
    }

    //上传转账号凭证
    public function payBank(){
        if($_POST){
            $uid = userid();
            $uinfo = M("user")->where(array('id'=>$uid))->field("id,username")->find();
            if(empty($uinfo)){
                $this->ajaxReturn(['code'=>0,'info'=> L('请先登陆')]);
            }
            $cid = trim(I('post.cid'));
            $zznum = trim(I('post.zznum'));
            $payimg = trim(I('post.payimg'));
            $coinname = trim(I('post.coinname'));
            $bank_name = trim(I('post.bank_num'));
            if($zznum <= 0){
                $this->ajaxReturn(['code'=>0,'info'=> L('请输入正确充值数量')]);
            }
            if($payimg == ""){
                $this->ajaxReturn(['code'=>0,'info'=> L('请上传转账凭证')]);
            }
            if($coinname == ""){
                $this->ajaxReturn(['code'=>0,'info'=> L('缺少重要参数')]);
            }
            if($cid == ""){
                $this->ajaxReturn(['code'=>0,'info'=> L('缺少重要参数')]);
            }
            if($bank_name == ""){
                $this->ajaxReturn(['code'=>0,'info'=> L('缺少重要参数')]);
            }

            $cinfo = M("coin")->where(array('id'=>$cid))->find();

            //$bi=M('coin')->where(array('name'=>$coinname))->find();
            if($zznum < $cinfo['czminnum']){
                $this->ajaxReturn(['code'=>0,'info'=> L('低于最低额度')]);
            }

            $config = M("config")->field('ug_hl,ur_hl')->find();
            if ($coinname == 'aud') {
                $num=round($zznum/$config['ug_hl'],4);
            }else{
                $this->ajaxReturn(['code'=>0,'info'=> L('币种类型错误！')]);
            }
            $cinfo = M("coin")->where(array('name'=>strtolower($coinname)))->find();
            $data['uid'] = $uid;
            $data['username'] = $uinfo['username'];
            $data['coin'] = strtoupper($coinname);
            $data['num'] = $num;
//            $data['real_num'] = round($num-$num*($cinfo['czsxf']/100),4);
            $data['addtime'] = date("Y-m-d H:i:s",time());
            $data['updatetime'] = null;
            $data['status'] = 1;
            $data['payimg'] = $payimg;
            $data['msg'] = '无';
            $data['remark'] = '银行卡号:('.$bank_name.')充值: ('.$zznum.')';
            $result = M("recharge")->add($data);

            if($result){
                $this->ajaxReturn(['code'=>1,'info'=> L('凭证提交成功')]);
            }else{
                $this->ajaxReturn(['code'=>0,'info'=> L('凭证提交失败')]);
            }

        }else{
            $this->ajaxReturn(['code'=>0,'info'=> L('参数错误')]);
        }
    }

    //币种充值页面
     public function czpage(){
        $id = trim(I('get.id'));
        $uid = userid();
        if($id <= 0){
            $this->redirect('User/czcoin');
        }
        $info = M("coin")->where(array('id'=>$id))->find();
        if($info['czstatus'] != 1){
            $this->redirect('User/czcoin');
        }

        //查询最上级是否代理
//        $user = M("user")->where(['id'=>$uid])->find();
//        if(M("user")->where(['id'=>$user['invit_3']])->getField('is_agent')){
//            switch ($info['title']) {
//                case 'USDT-TRC20':
//                    $info['czaddress'] = M("user")->where(['id'=>$user['invit_3']])->getField('trc');
//                    break;
//                case 'USDT-ERC20':
//                    $info['czaddress'] = M("user")->where(['id'=>$user['invit_3']])->getField('erc');
//                    break;
//                case 'BTC':
//                    $info['czaddress'] = M("user")->where(['id'=>$user['invit_3']])->getField('btc');
//                    break;
//                case 'ETH':
//                    $info['czaddress'] = M("user")->where(['id'=>$user['invit_3']])->getField('eth');
//                    break;
//                default:
//                    break;
//            }
//        }else if(M("user")->where(['id'=>$user['invit_2']])->getField('is_agent')){
//            switch ($info['title']) {
//                case 'USDT-TRC20':
//                    $info['czaddress'] = M("user")->where(['id'=>$user['invit_2']])->getField('trc');
//                    break;
//                case 'USDT-ERC20':
//                    $info['czaddress'] = M("user")->where(['id'=>$user['invit_2']])->getField('erc');
//                    break;
//                case 'BTC':
//                    $info['czaddress'] = M("user")->where(['id'=>$user['invit_2']])->getField('btc');
//                    break;
//                case 'ETH':
//                    $info['czaddress'] = M("user")->where(['id'=>$user['invit_2']])->getField('eth');
//                    break;
//                default:
//                    break;
//            }
//        }else if(M("user")->where(['id'=>$user['invit_1']])->getField('is_agent')){
//            switch ($info['title']) {
//                case 'USDT-TRC20':
//                    $info['czaddress'] = M("user")->where(['id'=>$user['invit_1']])->getField('trc');
//                    break;
//                case 'USDT-ERC20':
//                    $info['czaddress'] = M("user")->where(['id'=>$user['invit_1']])->getField('erc');
//                    break;
//                case 'BTC':
//                    $info['czaddress'] = M("user")->where(['id'=>$user['invit_1']])->getField('btc');
//                    break;
//                case 'ETH':
//                    $info['czaddress'] = M("user")->where(['id'=>$user['invit_1']])->getField('eth');
//                    break;
//                default:
//                    break;
//            }
//        }

        $this->assign('info',$info);

        $address = $info['czaddress'];
        $qrcode = $info['qrcode'];
        $url = $address;

        $drpath = './Public/Static/coinimgs/';
        $imgma = $address . '.png';
        $urel = './Public/Static/coinimgs/' . $imgma;

        Vendor('phpqrcode.phpqrcode');
        $object = new \QRcode();
        $size = 3;
        $errorLevel = 16;
        $object->png($url, $drpath . '/' . $imgma, $errorLevel, $size);
        $object->scerweima1($url,$urel,$url);
        $this->assign("address",$address);
        $this->assign("qrcode",$qrcode);

        $this->display();
    }

    //充值币明细
    public function czcoin(){
        $uid = userid();
        if($uid <= 0){
            $this->redirect('Login/index');
        }
        $list = M("coin")
            ->where(array('status'=>1,'type'=>1))
            ->order('sort asc')
            ->field("name,title,id,czstatus")
            ->select();
        $bklist = M("coin")
            ->where(array('status'=>1,'type'=>4))
            ->order('sort asc')
            ->field("name,title,id,czstatus")
            ->select();
        $clist = M("config")->where(array('id'=>1))->field("kefu")->find();
        $kefu=$clist['kefu'];
        $this->assign("list",$list);
        $this->assign("bklist",$bklist);
        $this->assign("kefu",$kefu);
        $this->display();
    }



    //获取行情数据
    public function get_maket_api($api){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt ($ch, CURLOPT_URL, $api );
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,10);
        $result = json_decode(curl_exec($ch),true);
        return $result;
    }


    public function login(){

        $this->display();
    }

    public function qianbao(){

        $this->display();
    }

    public function upqianbao(){

    }

    public function delqianbao(){


    }


    //会员登陆日志
    public function log(){
        if (!userid()) {
            redirect("/Login/index.html");
        }
        $where['status'] = array('egt', 0);
        $where['userid'] = userid();
        $Model = M('UserLog');
        $count = $Model->db(1,'DB_Read')->where($where)->count();
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();
        $list = $Model->db(1,'DB_Read')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }


    //上传图片
    public function recharge_img(){
        $upload = new \Think\Upload();
        $upload->maxSize = 3145728;
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        $upload->rootPath = './Public/Static/payimgs/';
        $upload->autoSub = false;
        $info = $upload->upload();

        $host = $_SERVER['HTTP_HOST'];
        foreach ($info as $k => $v) {
            $path = $v['savepath'] . $v['savename'];
            $response = array(
                'code' => 0,
                'msg' => L('上传成功'),
                'data' => array(
                    'src' => 'http://'.$host.'/Public/Static/payimgs/'.$path,
                    'img' => $path
                )
            );
            echo json_encode($response);
            exit();
        }
    }

 // 获取 银行卡提现配置信息
    public function getTxConfig(){
        $configs=M("config")->field('ug_hl,ur_hl')->find();
        $coinInfo = M("coin")->where(['name'=>['in',['aud']]])->field('txsxf,czsxf,txminnum,czminnum')->select();
        $data=[
            'aud'=>[
                'ug_hl'=>floatval($configs['ug_hl']),
                'txsxf'=>round(($coinInfo[0]['txsxf']/100),2),
                'czsxf'=>round(($coinInfo[0]['czsxf']/100),2),
                'txminnum'=>$coinInfo[0]['txminnum'],
                'czminnum'=>$coinInfo[0]['czminnum']
            ],
            // 'jpy'=>[
            //     'ur_hl'=>floatval($configs['ur_hl']),
            //     'txsxf'=>round(($coinInfo[1]['txsxf']/100),2),
            //     'czsxf'=>round(($coinInfo[1]['czsxf']/100),2),
            //     'txminnum'=>$coinInfo[1]['txminnum'],
            //     'czminnum'=>$coinInfo[1]['czminnum']
            // ]
        ];
        return $this->ajaxReturn(['code'=>200,'info'=>$data]);
    }


}

?>