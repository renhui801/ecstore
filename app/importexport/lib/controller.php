<?php
class importexport_controller extends desktop_controller{

    public function __construct($app)
    {
        parent::__construct($app);
    }

    /**
     * 导出所支持的格式
     *
     * @return array
     */
    public function export_support_filetype(){
        $filetype = array(
            'csv'=>'.csv',
        );
        return $filetype;
    }

    /**
     * 导入支持的格式
     *
     * @return array
     */
    public function import_support_filetype(){
        $filetype = array(
            'csv'=>'.csv',
        );
        return $filetype;
    }

    /**
     * 提供导入导出的存储方式
     *
     * @return array $storage 返回存储方式的参数
     */
    public function storage_policy(){
        return kernel::single('importexport_policy')->storage_policy();
    }

    /**
     * 保存存储方式的配置参数
     *
     * @params array $params
     * @return bool
     */
    public function set_storage_params($params){
        return kernel::single('importexport_policy')->set_storage_params($params);
    }

    /**
     * 获取存储方式的配置参数
     *
     * @return array $params
     */
    public function get_storage_params(){
        return kernel::single('importexport_policy')->get_storage_params();
    }

    /**
     * 检查导出,导入时，是否开启文件存储方式
     *
     * @return bool
     */
    public function check_policy(){
        if( !$this->get_storage_params() ){
            return false;
        }
        return true;
    }

    /**
     * 队列导出,导入，提供文件存储方式(默认提供ftp服务存储)
     */
    public function queue_policy(){
        $server = $this->storage_policy();
        return  $server['policy'];
    }

    /**
     * 导出队列唯一key,并且用于生成远程文件名称
     */
    public function gen_key($type='export'){
        $key = $type.'-'.time();
        return $key;
    }

    /**
     * 后台条件过滤和解析
     */
    public function view_filter($filter,$params){
        $ctl_class = $filter['app'].'_ctl_'.$filter['ctl'];
        $mdl_class = $params['app'].'_mdl_'.$params['mdl'];
        $_POST['view'] = $filter['view'];
        $view_filter = $this->get_view_filter($ctl_class,$mdl_class);
        $filter = array_merge($filter,$view_filter);
        return $filter;
    }

    public function import_message($status=false,$msg){
        if($status){
            $status_msg =app::get('importexport')->_('上传成功');
        }else{
            $status_msg =app::get('importexport')->_('上传失败');
        }
        header("content-type:text/html; charset=utf-8");
        echo "<script>top.MessageBox.success(\"".$status_msg."\");alert(\"".$msg."\");if(parent.$('import_form').getParent('.dialog'))parent.$('import_form').getParent('.dialog').retrieve('instance').close();if(parent.window.finderGroup&&parent.window.finderGroup['".$_GET['finder_id']."'])parent.window.finderGroup['".$_GET['finder_id']."'].refresh();</script>";
        exit;
    }

     //文件下载
    public function file_download(&$msg){
        $params = app::get('importexport')->model('task')->getList('*',array('task_id'=>$_GET['task_id']));
        $params = $params[0];

        if($params['status'] == '2' || $params['status'] =='6' || $params['status'] =='8'){
            //连接存储服务器
            $this->policyObj = kernel::single('importexport_policy');
            $msg = $this->policyObj->connect();
            if ( $msg !== true ){
                return false;
            }

            $filename = $this->policyObj->create_remote_file($params);
            if( !$this->policyObj->create_local_file() ){
                $msg = app::get('importexport')->_('本地文件创建失败，请检查/tmp文件夹权限'); 
                return false;
            }

            $range = $this->set_queue_header($filename);
            if( !$this->policyObj->pull(array('resume'=>$range),$msg) ){
               return false; 
            }

            $file = fopen($this->policyObj->local_file,"rb");
            $out = "\xEF\xBB\xBF";// 加上bom头，系统自动默认为UTF-8编码
            echo $out;
            while(!feof($file)){
                set_time_limit(0);
                print_r(fread($file,1024*8));
                ob_flush();
                flush();
            }
            $this->policyObj->close_local_file();
        }
        exit;
    }

    /**
     *下载文件支持断点续传header
     *
     * @params string $filename 下载文件名称
     */
    public function set_queue_header($filename){
        header("Cache-Control: public");
        header("Content-Type: application/force-download");
        header("Accept-Ranges: bytes");
        if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
            $iefilename = preg_replace('/\./', '%2e', $filename, substr_count($filename, '.') - 1);
            header("Content-Disposition: attachment; filename=\"$iefilename\"");
        } else {
            header("Content-Disposition: attachment; filename=\"$filename\"");
        }

        $size = $this->policyObj->remote_file_size($filename);
        if(isset($_SERVER['HTTP_RANGE'])) {
            list($a, $range)=explode("=",$_SERVER['HTTP_RANGE']);
            str_replace($range, "-", $range);
            $size2=$size-1;
            $new_length=$size2-$range+3;
            header("HTTP/1.1 206 Partial Content");
            header("Content-Length: $new_length");
            header("Content-Range: bytes $range$size2/$size");
        } else {
            $range = 0;
            $size2=$size-1;
            $size3=$size+3;
            header("Content-Range: bytes 0-$size2/$size");
            header("Content-Length: ".$size3);
        }
        return $range;
    }
}
