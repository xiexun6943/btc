<?php
namespace Admin\Controller;

class IssueController extends AdminController
{
	
	//认购项目记录
	public function index(){
		$count = M('issue')->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('issue')->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	

	public function issueimage()
	{
		$upload = new \Think\Upload();
		$upload->maxSize = 3145728;
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
		$upload->rootPath = './Upload/public/';
		$upload->autoSub = false;
		
		$info = $upload->upload();
		foreach ($info as $k => $v) {
			$path = $v['savepath'] . $v['savename'];
			echo $path;
			exit();
		}
	}
    
    //新增或编辑认购项目
	public function edit($id = null){
        /*$clist = M("coin")->where("type = 3 or type = 2")->order("id desc")->field("name,title")->select();
        $this->assign("clist",$clist);*/

        /*$buyCoins = M("ctmarket")->where("status = 1 or state = 2")->order("sort")->field("coinname,title")->select();
        $this->assign("buy_coins",$buyCoins);*/
        $paylist=[
            ['name'=>'usdt'],
            ['name'=>'eth'],
            ['name'=>'btc']
        ];
        $this->assign("paylist",$paylist);

        //$alllist = M("coin")->order("id desc")->field("name,title")->select();
        //$this->assign("alllist",$alllist);
        if($id > 0){
            $data = M("issue")->where(array('id'=>$id))->find();
            $this->assign('data',$data);
        }

		$this->display();
	}

    //处理新增或编辑认购项目
	public function save(){

        $tian = $_POST['tian'];
        $_POST['addtime'] = date("Y-m-d H:i:s",time());
        $_POST['finishtime'] = date("Y-m-d H:i:s",(strtotime($_POST['starttime']) + 86400 * $tian));
        
        
		if ($_POST['id']) {
			$rs = M('Issue')->save($_POST);
		} else {
			$rs = M('Issue')->add($_POST);
		}
//        dump($_POST);exit();
		if ($rs) {
			$this->success('操作成功！');
		} else {
			$this->error('操作失败！');
		}
	}

    /**
     * 删除 认购项目
     * @param null $type
     * @param null $id
     */
    public function billdel($type=null,$id=null){
        if(empty($id)){
            $this->error("缺少重要参数");exit();
        }
        $where['id'] = array('in', $id);
        $re = M('issue')->where($where)->delete();
        if($re){
            $this->success("删除成功");exit();
        }else{
            $this->error("删除失败");exit();
        }
    }

	public function log($name=null){
		if($name != null){
		    $where['account'] = trim($name);
		}
		$count = M('issue_log')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('issue_log')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();

	}


}
?>