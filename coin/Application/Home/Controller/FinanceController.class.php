<?php
namespace Home\Controller;

class FinanceController extends HomeController
{
	protected function _initialize()
	{
		parent::_initialize();
		$allow_action=array("index","getnewprice","getmoneyusdt","getmoneybtc","getmoneyeth","getmoneyeos","getmoneydoge","getmoneybch",
            "getmoneyltc","getmoneytrx","getmoneyxrp","getmoneyiotx","getmoneyfil","getmoneyshib","getmoneyflow","getmoneyjst",
            "getmoneyitc","getmoneyht","getmoneyogo","getmoneyusdz","getmoneyatm","getmoneyttc","getallzhehe","czpage","paycoin",
            "txpage","tbhandle","czlist","txlist",'withdraw','recharge','payBank','getBank','getTxConfig','withdrawDo',"getmoneiota");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error(L("非法操作"));
		}
	}
	
	//提币列表
	public function txlist(){
	    $uid = userid();
	    $txlist = M("myzc")->where(array('userid'=>$uid))->order('id desc')->limit(15)->select();
	    $data=[];
        if ($txlist) {
            foreach ($txlist as$k=> $v){
                $n=substr($v['address'],0,'5');
                $m=substr($v['address'],-4);
                $address=$n.'************'.$m;
                $data[$k]['address']=$address;
                $data[$k]['coinname']=$v['coinname'];;
                $data[$k]['num']=$v['num'];
                $data[$k]['mum']=$v['mum'];
                $data[$k]['fee']=$v['fee'];;
                $data[$k]['addtime']=$v['addtime'];
                if ($v['status'] == 1) {
                    $data[$k]['status']="确认中";
                }elseif($v['status'] == 2){
                    $data[$k]['status']="完成";
                }else{
                    $data[$k]['status']="原路返回";
                }
            }

        }

	    $this->assign("txlist",$data);
	    $this->display();
	}
	
	//充币列表
	public function czlist(){
	   $uid = userid();
	   $mlist = M("recharge")->where(array('uid'=>$uid))->order("id desc")->limit(15)->select();
	   $this->assign('mlist',$mlist);
	   $this->display(); 
	}
	
	//提币页面
	public function txpage(){
	   $id = trim(I('get.id'));
//	   if($id <= 0){
//	       $this->redirect('Finance/index');
//	   }
	   $info = M("coin")->where(array('name'=>$id))->find();
	   $this->assign('info',$info);
	   
	   $coinname = $info['name'];
	   $uid = userid();
	   $minfo = M("user_coin")->where(array('userid'=>$uid))->find();
	   $money = $minfo[$coinname];
	   $this->assign('money',$money);
	   
	   $adrinfo = M("user_qianbao")->where(array('userid'=>$uid,'name'=>$coinname))->order('id desc')->limit(1)->find();
	   if(!empty($adrinfo)){
	       $this->assign('adrinfo',$adrinfo);
	   }
	   
	   $this->display();
	}
	
    //提币处理
	public function tbhandle(){
	    if($_POST){
	        $uid = userid();
	        $uinfo = M("user")->where(array('id'=>$uid))->field("id,rzstatus,username,paypassword ,bill,st_bill")->find();
	        if(empty($uinfo)){
	            $this->ajaxReturn(['code'=>0,'info'=>L('请先登陆')]);
	        }
	        
//	        if($uinfo['rzstatus'] != 2){
//		        $this->ajaxReturn(['code'=>0,'info'=>L('请先完成实名认证')]);
//		    }
            $paypassword = trim(I('post.paypwd'));
            if($paypassword == '' || $paypassword == null){
                $this->ajaxReturn(['code'=>0,'info'=>L('请输入提现密码')]);
            }

            if(md5($paypassword) != $uinfo['paypassword']){
                $this->ajaxReturn(['code'=>0,'info'=>L('提现密码错误')]);
            }
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
                $this->ajaxReturn(['code'=>0,'info'=>L('您的流水不够，暂时无法提现')]);
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
	        $bill['addtime'] = date("Y-m-d H:i:s",time());
	        $bill['st'] = 2;
	        $bill['remark'] = "提币申请";
	        
	        $billre = M("bill")->add($bill);

	        if($result && $dec_re && $billre){
	            $this->ajaxReturn(['code'=>1,'info'=>L('提交成功')]);
	        }else{
	            $this->ajaxReturn(['code'=>0,'info'=>L('提交失败')]);
	        }
	    }else{
	        
	    }
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

	        if($zznum < $cinfo['czminnum']){
	            $this->ajaxReturn(['code'=>0,'info'=> L('低于最低额度')]);
	        }
	        
	        $data['uid'] = $uid;
	        $data['username'] = $uinfo['username'];
	        $data['coin'] = strtoupper($coinname);
            $data['num'] = $zznum;
//            $data['real_num'] = round($zznum-$zznum*($cinfo['czsxf']/100),4);
	        $data['addtime'] = date("Y-m-d H:i:s",time());
	        $data['updatetime'] = '';
	        $data['status'] = 1;
	        $data['payimg'] = $payimg;
	        $data['msg'] = '';
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
    //上传转账号凭证 银行卡提单
    public function payBank(){
        if($_POST){
            $uid = userid();
            $uinfo = M("user")->where(array('id'=>$uid))->field("id,username")->find();
            if(empty($uinfo)){
                $this->ajaxReturn(['code'=>0,'info'=> L('请先登陆')]);
            }
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

            if($bank_name == ""){
                $this->ajaxReturn(['code'=>0,'info'=> L('缺少重要参数')]);
            }

            $cinfo = M("coin")->where(array('name'=>strtolower($coinname)))->find();
            if (!$cinfo || empty( $cinfo)) {
                $this->ajaxReturn(['code'=>0,'info'=> L('支付配置错误')]);
            }
            if($zznum < $cinfo['czminnum']){
                $this->ajaxReturn(['code'=>0,'info'=> L('低于最低额度')]);
            }
            $config = M("config")->field('gu_hl,ru_hl')->find();
            if ($coinname == 'HKD') {
                $num=round($zznum*$config['gu_hl'],4);
            }elseif($coinname == 'JPY'){
                $num=round($zznum*$config['ru_hl'],4);
            }else{
                $this->ajaxReturn(['code'=>0,'info'=> L('币种类型错误！')]);
            }

            $data['uid'] = $uid;
            $data['username'] = $uinfo['username'];
            $data['coin'] = strtoupper($coinname);
            $data['num'] = $num;
//            $data['real_num'] = round($num-$num*($cinfo['czsxf']/100),4);
            $data['addtime'] = date("Y-m-d H:i:s",time());
            $data['updatetime'] = '';
            $data['status'] = 1;
            $data['payimg'] = $payimg;
            $data['msg'] = '';
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
    //银行卡提现页面
    public function withdraw(){
        $id = trim(I('get.id'));

        $info = M("coin")->where(array('name'=>$id))->find();
        $this->assign('info',$info);

        $uid = userid();
        $minfo = M("user_coin")->where(array('userid'=>$uid))->find();
        $money = $minfo['usdt'];
        $this->assign('money',$money);
        $this->display();
    }
    // 银行卡提款
    public function withdrawDo(){
        if($_POST){
            $uid = userid();
            $uinfo = M("user")->where(array('id'=>$uid))->field("id,rzstatus,username,paypassword,bill,st_bill")->find();
            if(empty($uinfo)){
                $this->ajaxReturn(['code'=>0,'info'=>L('请先登陆')]);
            }
//            if($uinfo['rzstatus'] != 2){
//                $this->ajaxReturn(['code'=>0,'info'=>L('请先完成实名认证')]);
//            }
            $coinname = trim(I('post.coinname'));

            $address = trim(I('post.address'));
            $bankName = trim(I('post.bank_name'));
            $paypassword = trim(I('post.paypwd'));
            $withdrawalName = trim(I('post.user_name'));

            if($paypassword == '' || $paypassword == null){
                $this->ajaxReturn(['code'=>0,'info'=>L('请输入提现密码')]);
            }

             if($withdrawalName == '' || $withdrawalName == null){
                $this->ajaxReturn(['code'=>0,'info'=>L('请输入提现人名')]);
            }
            if(md5($paypassword) != $uinfo['paypassword']){
                $this->ajaxReturn(['code'=>0,'info'=>L('提现密码错误')]);
            }

            if($address == '' || $address == null){
                $this->ajaxReturn(['code'=>0,'info'=>L('请输入提币地址')]);
            }
            $num = trim(I('post.num'));
            if($num <= 0){
                $this->ajaxReturn(['code'=>0,'info'=>L('请输入正确的额度')]);
            }
            $cinfo = M("coin")->where(array('name'=>$coinname))->find();
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
                $this->ajaxReturn(['code'=>0,'info'=>L('您的流水不够，暂时无法提现')]);
            }

            $minfo = M("user_coin")->where(array('userid'=>$uid))->find();

            $config = M("config")->field('gu_hl,ru_hl')->find();
            $coinname =='hkd'?$hl=$config['gu_hl']:$hl=$config['ru_hl'];
            $sxftype = $cinfo['sxftype'];
            if($sxftype == 1){
                $sxf = $num * ($cinfo['txsxf'] / 100);
            }elseif($sxftype == 2){
                $sxf = $cinfo['txsxf_n'];
            }

            if($sxf <= 0 || $sxf == ''){
                $sxf = 0;
            }

            $tnum = round(($num - $sxf)*$hl,4);
            $unum=round($num*$hl,2);
            if($minfo['usdt'] < $unum){ // 只能在ustd中扣
                $this->ajaxReturn(['code'=>0,'info'=>L('账户余额不足')]);
            }
            $dec_re = M("user_coin")->where(array('userid'=>$uid))->setDec('usdt',$unum);

            $data['userid'] = $uid;
            $data['username'] = $uinfo['username'];
            $data['coinname'] = $cinfo['name'];
            $data['num'] = $unum;
            $data['fee'] = round($sxf*$hl,2);
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
            $bill['addtime'] = date("Y-m-d H:i:s",time());
            $bill['st'] = 2;
            $bill['remark'] = "提币申请";

            $billre = M("bill")->add($bill);

            if($result && $dec_re && $billre){
                $this->ajaxReturn(['code'=>1,'info'=>L('提交成功')]);
            }else{
                $this->ajaxReturn(['code'=>0,'info'=>L('提交失败')]);
            }
        }
    }

    // 银行卡充值页面
    public function recharge($name = ''){
        // 获取钱币
        $info = M("coin")->where(array('name'=>$name))->find();
        $this->assign('info',$info);
        $this->display();
    }

    // 银行卡获取
    public function getBank(){
	    $name=I('coinname');
        $info = M("coin")->where(array('name'=>$name))->find();
        if ($info) {
            $data=[
                'code'=>200,
                'bank'=>$info['czaddress'],
                'czminnum'=>$info['czminnum'],
                'msg'=>'获取成功'
            ];
        }else{
            $data=[
                'code'=>202,
                'bank'=>'',
                'czminnum'=>'',
                'msg'=>'亲联系后台配置银行卡配置'
            ];
        }
        return $this->ajaxReturn($data) ;
    }

    // 获取 银行卡提现配置信息
    public function getTxConfig(){
        $configs=M("config")->field('ug_hl,ur_hl')->find();
        $coinInfo = M("coin")->where(['name'=>['in',['hkd','jpy']]])->field('txsxf,czsxf,txminnum,czminnum')->select();
        $data=[
            'hkd'=>[
                'ug_hl'=>floatval($configs['ug_hl']),
                'txsxf'=>round(($coinInfo[0]['txsxf']/100),2),
                'czsxf'=>round(($coinInfo[0]['czsxf']/100),2),
                'txminnum'=>$coinInfo[0]['txminnum'],
                'czminnum'=>$coinInfo[0]['czminnum']
            ],
            'jpy'=>[
                'ur_hl'=>floatval($configs['ur_hl']),
                'txsxf'=>round(($coinInfo[1]['txsxf']/100),2),
                'czsxf'=>round(($coinInfo[1]['czsxf']/100),2),
                'txminnum'=>$coinInfo[1]['txminnum'],
                'czminnum'=>$coinInfo[1]['czminnum']
            ]
        ];
        return $this->ajaxReturn(['code'=>200,'info'=>$data]);
    }

	//充值页面
	public function czpage($id = null){

	    if(checkstr($id)){
	        $this->redirect('Finance/index');
	    }
//	    if($id <= 0){
//	        $this->redirect('Finance/index');
//	    }
	    $info = M("coin")->where(array('name'=>$id))->find();
//	    if($info['czstatus'] != 1){
//	        $this->redirect('Finance/index');
//	    }
	    $this->assign('info',$info);
	    $address = $info['czaddress'];
	    $qrcode=$info['qrcode'];
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
    
    //钱包总览
    public function index(){
        if (!userid()) {
			$this->redirect('/Login/index');
		}
//		$clist = M("coin")->where(array('status'=>1))->order('id asc')->field("id,name,title")->select();
        $clist=[ // 手动配置添加 的
            'usdt'=>['name'=>'usdt'],
            'btc'=>['name'=>'btc'],
            'eth'=>['name'=>'eth'],
            'eos'=>['name'=>'eos'],
            'doge'=>['name'=>'doge'],
            'bch'=>['name'=>'bch'],
            'ltc'=>['name'=>'ltc'],
            'iota'=>['name'=>'iota'],
            'flow'=>['name'=>'flow'],
            'fil'=>['name'=>'fil'],
            'jst'=>['name'=>'jst'],
            'ht'=>['name'=>'ht']
        ];
//        dump($clist);exit();
	    $this->assign("list",$clist);
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
    
    //获取折合资产
	public function getallzhehe(){

	    $uid = userid();
	    $minfo = M("user_coin")->where(array('userid'=>$uid))->find();
	    $usdt = $minfo['usdt'] + $minfo['usdtd'];

        $allzhehe = $usdt + session('flowzh')+ session('htzh') + session('jstzh') + session('filzh') + session('iotazh') + session('ltczh') + session('bchzh') + session('dogezh') + session('eoszh') + session('ethzh') + session('btczh');
	    $this->ajaxReturn(['code'=>1,'allzhehe'=>$allzhehe]);
	}
	

	
	//获取单个币种资产(usdz)
	public function getmoneyusdz(){
	    $uid = userid();
	    $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();
        $where['name'] = "usdz_usdt";
        $marketinfo = M("market")->where($where)->field("new_price")->find();
        $usdzusdt = $marketinfo['new_price'];
        
	    $re['num'] = $wallinfo['usdz'];
	    $re['numd'] = $wallinfo['usdzd'];
	    $zhehe = $wallinfo['usdz'] * $usdzusdt + $wallinfo['usdzd'] * $usdzusdt;
	    if($zhehe <= 0){
	        $zhehe = "0.0000";
	    }
	    $re['zhe'] = $zhehe;
	    $re['code'] = 1;
	    
	    session("usdzzh",$re['zhe']);
	    $this->ajaxReturn($re);
	}
	
	

	
	//获取单个币种资产(ogo)
	/*public function getmoneyogo(){
	    $uid = userid();
	    $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'BCH/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

	    $re['num'] = $wallinfo['ogo'];
	    $re['numd'] = $wallinfo['ogod'];
	    $zhehe = $wallinfo['ogo'] * $usdt_price + $wallinfo['ogod'] * $usdt_price;
	    if($zhehe <= 0){
	        $zhehe = "0.0000";
	    }
	    $re['zhe'] = $zhehe;
	    $re['code'] = 1;

	    session("ogozh",$re['zhe']);
	    $this->ajaxReturn($re);
	}*/
	
	//获取单个币种资产(ht)
	public function getmoneyht(){
	    $uid = userid();
	    $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'HT/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

	    $re['num'] = $wallinfo['ht'];
	    $re['numd'] = $wallinfo['htd'];
	    $zhehe = round(($wallinfo['ht']  + $wallinfo['htd']) * $usdt_price,4);
	    if($zhehe <= 0){
	        $zhehe = "0.0000";
	    }
	    $re['zhe'] = $zhehe;
	    $re['code'] = 1;
	    
	    session("htzh",$re['zhe']);
	    $this->ajaxReturn($re);
	}
	
	//获取单个币种资产(itc)
	public function getmoneyitc(){
	    $uid = userid();
	    $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'ITC/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

	    $re['num'] = $wallinfo['itc'];
	    $re['numd'] = $wallinfo['itcd'];
	    $zhehe = round(($wallinfo['itc']  + $wallinfo['itcd']) * $usdt_price,4);
	    if($zhehe <= 0){
	        $zhehe = "0.0000";
	    }
	    $re['zhe'] = $zhehe;
	    $re['code'] = 1;
	    
	    session("itczh",$re['zhe']);
	    $this->ajaxReturn($re);
	}
	
	//获取单个币种资产(jst)
	public function getmoneyjst(){
	    $uid = userid();
	    $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'JST/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

	    $re['num'] = $wallinfo['jst'];
	    $re['numd'] = $wallinfo['jstd'];
	    $zhehe = round(($wallinfo['jst']  + $wallinfo['jstd']) * $usdt_price,4);
	    if($zhehe <= 0){
	        $zhehe = "0.0000";
	    }
	    $re['zhe'] = $zhehe;
	    $re['code'] = 1;
	    
	    session("jstzh",$re['zhe']);
	    $this->ajaxReturn($re);
	}
	
	//获取单个币种资产(flow)
	public function getmoneyflow(){
	    $uid = userid();
	    $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'FLOW/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

	    $re['num'] = $wallinfo['flow'];
	    $re['numd'] = $wallinfo['flowd'];
	    $zhehe = round(($wallinfo['flow'] + $wallinfo['flowd']) * $usdt_price,4);
	    if($zhehe <= 0){
	        $zhehe = "0.0000";
	    }
	    $re['zhe'] = $zhehe;
	    $re['code'] = 1;
	    
	    session("flowzh",$re['zhe']);
	    $this->ajaxReturn($re);
	}
	
	//获取单个币种资产(shib)
/*	public function getmoneyshib(){
	    $uid = userid();
	    $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'BCH/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

	    $re['num'] = $wallinfo['shib'];
	    $re['numd'] = $wallinfo['shibd'];
	    $zhehe = $wallinfo['shib'] * $usdt_price + $wallinfo['shibd'] * $usdt_price;
	    if($zhehe <= 0){
	        $zhehe = "0.0000";
	    }
	    $re['zhe'] = $zhehe;
	    $re['code'] = 1;
	    
	    session("shibzh",$re['zhe']);
	    $this->ajaxReturn($re);
	}*/
	
	//获取单个币种资产(fil)
	public function getmoneyfil(){
	    $uid = userid();
	    $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'FIL/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

	    $re['num'] = $wallinfo['fil'];
	    $re['numd'] = $wallinfo['fild'];
	    $zhehe = round(($wallinfo['fil']+ $wallinfo['fild']) * $usdt_price,4);
	    if($zhehe <= 0){
	        $zhehe = "0.0000";
	    }
	    $re['zhe'] = $zhehe;
	    $re['code'] = 1;
	    
	    session("filzh",$re['zhe']);
	    $this->ajaxReturn($re);
	}
	
	//获取单个币种资产(iota)
	public function getmoneiota(){
	    $uid = userid();
	    $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'IOTA/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close
//        var_dump($usdt_price);exit();
	    $re['num'] = $wallinfo['iota'];
	    $re['numd'] = $wallinfo['iotad'];
	    $zhehe = $wallinfo['iota'] * $usdt_price + $wallinfo['iotad'] * $usdt_price;
	    if($zhehe <= 0){
	        $zhehe = "0.0000";
	    }
	    $re['zhe'] = $zhehe;
	    $re['code'] = 1;
	    
	    session("iotazh",$re['zhe']);
	    $this->ajaxReturn($re);
	}
	
	//获取单个币种资产(xrp)
/*	public function getmoneyxrp(){
	    $uid = userid();
	    $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'BCH/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

	    $re['num'] = $wallinfo['xrp'];
	    $re['numd'] = $wallinfo['xrpd'];
	    $zhehe = $wallinfo['xrp'] * $usdt_price + $wallinfo['xrpd'] * $usdt_price;
	    if($zhehe <= 0){
	        $zhehe = "0.0000";
	    }
	    $re['zhe'] = $zhehe;
	    $re['code'] = 1;
	    
	    session("xrpzh",$re['zhe']);
	    $this->ajaxReturn($re);
	}*/
	
	//获取单个币种资产(trx)
/*	public function getmoneytrx(){
	    $uid = userid();
	    $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'BCH/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

	    $re['num'] = $wallinfo['trx'];
	    $re['numd'] = $wallinfo['trxd'];
	    $zhehe = $wallinfo['trx'] * $usdt_price + $wallinfo['trxd'] * $usdt_price;
	    if($zhehe <= 0){
	        $zhehe = "0.0000";
	    }
	    $re['zhe'] = $zhehe;
	    $re['code'] = 1;
	    
	    session("trxzh",$re['zhe']);
	    $this->ajaxReturn($re);
	}*/
	
	
	//获取单个币种资产(ltc)
	public function getmoneyltc(){
	    $uid = userid();
	    $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'LTC/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

	    $re['num'] = $wallinfo['ltc'];
	    $re['numd'] = $wallinfo['ltcd'];
	    $zhehe = round(($wallinfo['ltc']  + $wallinfo['ltcd']) * $usdt_price,4);
	    if($zhehe <= 0){
	        $zhehe = "0.0000";
	    }
	    $re['zhe'] = $zhehe;
	    $re['code'] = 1;
	    
	    session("ltczh",$re['zhe']);
	    $this->ajaxReturn($re);
	}
	
	//获取单个币种资产(bch)
	public function getmoneybch(){
	    $uid = userid();
	    $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'BCH/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

	    $re['num'] = $wallinfo['bch'];
	    $re['numd'] = $wallinfo['bchd'];
	    $zhehe = round(($wallinfo['bch'] + $wallinfo['bchd']) * $usdt_price,4);
	    if($zhehe <= 0){
	        $zhehe = "0.0000";
	    }
	    $re['zhe'] = $zhehe;
	    $re['code'] = 1;
	    
	    session("bchzh",$re['zhe']);
	    $this->ajaxReturn($re);
	}
	
	//获取单个币种资产(doge)
	public function getmoneydoge(){
	    $uid = userid();
	    $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'DOGE/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

	    $re['num'] = $wallinfo['doge'];
	    $re['numd'] = $wallinfo['doged'];
	    $zhehe = round(($wallinfo['doge']  + $wallinfo['doged']) * $usdt_price,4);
	    if($zhehe <= 0){
	        $zhehe = "0.0000";
	    }
	    $re['zhe'] = $zhehe;
	    $re['code'] = 1;
	    
	    session("dogezh",$re['zhe']);
	    $this->ajaxReturn($re);
	}
	
	//获取单个币种资产(eos)
	public function getmoneyeos(){
	    $uid = userid();
	    $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'EOS/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

	    $re['num'] = $wallinfo['eos'];
	    $re['numd'] = $wallinfo['eosd'];
	    $zhehe = round(($wallinfo['eos'] + $wallinfo['eosd']) * $usdt_price,4);
	    if($zhehe <= 0){
	        $zhehe = "0.0000";
	    }
	    $re['zhe'] = $zhehe;
	    $re['code'] = 1;
	    
	    session("eoszh",$re['zhe']);
	    $this->ajaxReturn($re);
	}
	
	//获取单个币种资产(eth)
	public function getmoneyeth(){
	    $uid = userid();
	    $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();

        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'ETH/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

	    $re['num'] = $wallinfo['eth'];
	    $re['numd'] = $wallinfo['ethd'];
	    $zhehe = round(($wallinfo['eth']  + $wallinfo['ethd']) * $usdt_price,4);
	    if($zhehe <= 0){
	        $zhehe = "0.0000";
	    }
	    $re['zhe'] = $zhehe;
	    $re['code'] = 1;
	    
	    session("ethzh",$re['zhe']);
	    $this->ajaxReturn($re);
	}
	
	//获取单个币种资产(btc)
	public function getmoneybtc(){
	    $uid = userid();
	    $wallinfo = M("user_coin")->where(array('userid'=>$uid))->find();
	    
//	    $coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=btcusdt";
//	    $result = $this->get_maket_api($coinapi);
//	    $price_arr  = $result['data'][0];
//	    $usdt_price = $price_arr['close'];
        // 使用数据库 采集的数据
        $result=M('currency')->where(['name'=>'BTC/USDT'])->find();
        $price_arr = json_decode($result['data'],true);
        $usdt_price = $price_arr[0]['close'];//现价 close

	    $re['num'] = $wallinfo['btc'];
	    $re['numd'] = $wallinfo['btcd'];
	    $zhehe = round(($wallinfo['btc'] + $wallinfo['btcd']) * $usdt_price,4);
	    if($zhehe <= 0){
	        $zhehe = "0.0000";
	    }
	    $re['zhe'] = $zhehe;
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
	    $zhehe = $wallinfo['usdt'] + $wallinfo['usdtd'];
	    if($zhehe <= 0){
	        $zhehe = "0.0000";
	    }
	    $re['zhe'] = $zhehe;
	    $re['code'] = 1;
	    session("usdtzh",$re['zhe']);
	    $this->ajaxReturn($re);
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
	        $lowcoin = strtolower($coinname);
	        $symbol = $lowcoin.'usdt';
	        $coinapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
	        $result = $this->get_maket_api($coinapi);
	        
	        $price_arr = $result['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
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

}
?>