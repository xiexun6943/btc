<?php
/* 交易中心 */
namespace Mobile\Controller;

class DrawController extends MobileController
{
    
	protected function _initialize()
	{
		parent::_initialize();	$allow_action=array("draw","kefu");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error(L("非法操作"));
		}
		
	}
	
	//成交详情
	public function billinfo($id = null){
	    $uid = userid();
	    if($uid <= 0){
	        $this->redirect('Login/index');
	    }
	    $info = M("bborder")->where(array('id'=>$id))->find();
	    $this->assign('info',$info);
	    $this->display();
	}
	


	//红包页面首页
	public function draw(){
		$uid = userid();
        $lang=cookie("language");
        $this->assign('lang',$lang);

//		$this->assign('uid',$uid);
//		$clist = M("config")->where(array('id'=>1))->field("websildea_y,websildea_z,websildea_r,websildeb_y,websildeb_z,websildeb_r,websildec_y,websildec_z,websildec_r,kefu")->find();
//
//		//查询最上级是否代理
//		$user = M("user")->where(['id'=>$uid])->find();
//		if(M("user")->where(['id'=>$user['invit_3']])->getField('is_agent')){
//		    $clist['kefu'] = M("user")->where(['id'=>$user['invit_3']])->getField('kefu');
//		}else if(M("user")->where(['id'=>$user['invit_2']])->getField('is_agent')){
//		    $clist['kefu'] = M("user")->where(['id'=>$user['invit_2']])->getField('kefu');
//		}else if(M("user")->where(['id'=>$user['invit_1']])->getField('is_agent')){
//		    $clist['kefu'] = M("user")->where(['id'=>$user['invit_1']])->getField('kefu');
//		}
//        $lang=cookie("language");
//		switch ($lang){
//            case $lang=="zh-cn":
//                $clist['websildea']=$clist['websildea_z'];
//                $clist['websildeb']=$clist['websildeb_z'];
//                $clist['websildec']=$clist['websildec_z'];
//                break;
//            case $lang=="ja-jp":
//                $clist['websildea']=$clist['websildea_r'];
//                $clist['websildeb']=$clist['websildeb_r'];
//                $clist['websildec']=$clist['websildec_r'];
//                break;
//            default:
//                $clist['websildea']=$clist['websildea_y'];
//                $clist['websildeb']=$clist['websildeb_y'];
//                $clist['websildec']=$clist['websildec_y'];
//                break;
//
//        }
//
//		$this->assign('clist',$clist);
//
//        $websildec = $clist['websildec'];
//        $this->assign('websildec',$websildec);
//
//        $nlist = M("content")->where(array('status'=>1))->order("id desc")->field("title,id")->select();
//        $this->assign('nlist',$nlist);
//
//        if($uid > 0){
//            $sum = M("notice")->where(array('uid'=>$uid,'status'=>1))->count();
//        }else{
//            $sum = 0;
//        }
//        $this->assign('sum',$sum);
//
//        $list = M("ctmarket")->where(array('status'=>1))->field("coinname,id,logo,sort")->select();
//        $list_res=array_column($list,null,'sort') ;
//        ksort($list_res);
//        $this->assign("market",$list_res);
//        $info = M("content")->where(array('status'=>1))->order("id desc")->find();
//	    $this->assign('info',$info);
//
//	    $this->assign('select','index');
		$this->display();
	}



	
	
	


}
?>