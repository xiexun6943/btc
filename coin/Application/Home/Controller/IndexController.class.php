<?php
namespace Home\Controller;

class IndexController extends HomeController
{
	protected function _initialize()
	{
		parent::_initialize();
		$allow_action = array("index","gglist","gginfo","coins",'aboutUs','service');
		if (!in_array(ACTION_NAME,$allow_action)) {
			$this->error("非法操作！");
		}
	}

    //网站首页面
	public function index(){
        $language=$_GET[C('VAR_LANGUAGE')];// 获取语言检测
        $list = M("ctmarket")->where(array('status'=>1))->field("coinname,id,logo")->select();
        $this->assign("market",$list);
        $content = M('content')->where(['status' => 1])->order('id desc')->select();
        $title_arr = array_column($content, 'title');
        $title_string  = json_encode($title_arr);
        $clist = M("config")->where(array('id'=>1))->field("websildea_y,websildea_z,websildea_r,websildea_f,websildeb_y,websildeb_z,websildeb_r,websildeb_f,websildec_y,websildec_z,websildec_r,websildec_f,kefu,websilded_y,websilded_f,websilded_z,websilded_r,websildea_kr,websildeb_kr,websildec_kr,websilded_kr")->find();

        $lang=I('get.Lang');
        switch ($lang){
            case $lang=="zh-cn":
                $clist['websildea']=$clist['websildea_z'];
                $clist['websildeb']=$clist['websildeb_z'];
                $clist['websildec']=$clist['websildec_z'];
                $clist['websilded']=$clist['websilded_z'];
                break;
            case $lang=="ja-jp":
                $clist['websildea']=$clist['websildea_r'];
                $clist['websildeb']=$clist['websildeb_r'];
                $clist['websildec']=$clist['websildec_r'];
                $clist['websilded']=$clist['websilded_r'];
                break;
            case $lang=="zh-tw":
                $clist['websildea']=$clist['websildea_f'];
                $clist['websildeb']=$clist['websildeb_f'];
                $clist['websildec']=$clist['websildec_f'];
                $clist['websilded']=$clist['websilded_f'];
                break;
            case $lang=="ko-kr":
                $clist['websildea']=$clist['websildea_kr'];
                $clist['websildeb']=$clist['websildeb_kr'];
                $clist['websildec']=$clist['websildec_kr'];
                $clist['websilded']=$clist['websilded_kr'];
                break;
            default:
                $clist['websildea']=$clist['websildea_y'];
                $clist['websildeb']=$clist['websildeb_y'];
                $clist['websildec']=$clist['websildec_y'];
                $clist['websilded']=$clist['websilded_y'];
                break;

        }

        $this->assign("clist",$clist);
        $this->assign("notice",$content);
        $this->assign("language",$language);
		$this->display();
	}
    
    //公告中心
    public function gglist(){
        
        $list = M("content")->where(array('status'=>1))->select();

        $this->assign("list",$list);
        $this->display();
    }
    //公告详情
    public function gginfo($id = null){
        
        if (checkstr($id)) {
			$this->error(L('您输入的信息有误'));
		}
        
        $info = M("content")->where(array('id'=>$id))->find();
        if(empty($info)){
            redirect('/Index/gglist.html');
        }
        $this->assign("info",$info);
        $this->display();
    }
    
    //关于我们
	public function aboutUs()
	{
	    $config = M("config")->where(array('id'=>1))->field('weblogo')->find();
        $logo = $config['weblogo'] ? '/Upload/public/'.$config['weblogo'] : '/Public/Static/Icoinfont/icon/logo.png';
        $this->assign('logo',$logo);
        $this->display();
	}
    
    //服务
	public function service()
	{
	    $config = M("config")->where(array('id'=>1))->field('weblogo')->find();
        $logo = $config['weblogo'] ? '/Upload/public/'.$config['weblogo'] : '/Public/Static/Icoinfont/icon/logo.png';
        $this->assign('logo',$logo);
        $this->display();
	}
    
    //红包
	public function coins()
	{
	    $uid = userid();
	   // if($uid >= 1){
    //         redirect('/Index/getIcon.html');
	   // }
		$this->display();
	}

}
?>