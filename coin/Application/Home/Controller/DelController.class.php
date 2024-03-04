<?php
/* 删除任务 */
namespace Home\Controller;

class DelController extends HomeController
{

    protected function _initialize()
    {
        parent::_initialize();	$allow_action=array("delRuntime","delFileByDir");
        if(!in_array(ACTION_NAME,$allow_action)){
            $this->error(L("非法操作"));
        }

    }

    // 删除文件
    public  function delFileByDir($dir) {
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if (is_dir($fullpath)) {
                    $this->delFileByDir($fullpath);
                }else{
                    unlink($fullpath);
                }
            }
        }
        closedir($dh);
    }

    /**
     * 删除 runtime 缓存文件
     */
    public function delRuntime() {
        $path=$_SERVER['DOCUMENT_ROOT'].'/Runtime';
        $this->delFileByDir($path);
        echo "Runtime删除缓存成功！".date('Y-m-d H:i:s',time());

    }



}
?>
