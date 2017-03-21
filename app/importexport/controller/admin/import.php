<?php


class importexport_ctl_admin_import extends importexport_controller{

    /*
     *后台显示队列
     * */
    public function queue_import(){
        $params = array(
            'title'=>'导入任务队列',
            'use_buildin_recycle'=>true,
            'orderBy' => 'create_date desc',
            'base_filter'=>array('type'=>'import'),
        );
        $this->finder('importexport_mdl_task',$params);
    }

    /**
     * 下载模板文件
     */
    public function export_template(){

        //实例化导出数据类
        $dataObj = kernel::single('importexport_data_object',$_POST['mdl']);
        //实例化导出文件类型类
        $filetypeObj = kernel::single('importexport_type_'.$_POST['filetype']);

        $filetypeObj->export_header($_POST['name'].'.'.$_POST['filetype']);

        $data = $dataObj->get_template($_POST['group_col']);

        $rs = $filetypeObj->arrToExportType(array($data));

        $bom = "\xEF\xBB\xBF";
        echo $bom;
        echo $rs;
    }

    /*
     * 导入页面
     * */
    public function import_view(){
        $this->pagedata['check_policy'] = $this->check_policy();
        $this->pagedata['params'] = $_GET['_params'];
        //支持导出类型
        $this->pagedata['import_type'] = $this->import_support_filetype();
        $this->display('admin/import/import.html');
    }

    /*
     * 导入数据
     * */
    public function create_import(){
        #检查导入文件是否合法
        $this->check_import_file();

        #将导入文件上传到服务器
        $data = $this->push_file($_POST);

        $queue_params = array(
            'model'=>$_POST['mdl'],
            'filetype' => $data['filetype'],
            'policy' => $this->queue_policy(),
            'key'=> $data['key'],
        );

        system_queue::instance()->publish('importexport_tasks_runimport', 'importexport_tasks_runimport', $queue_params);
        app::get('importexport')->model('task')->create_task('import',$data);
        $echoMsg =app::get('desktop')->_('上传成功,已加入队列');
        $this->import_message(true,$echoMsg);
        #kernel::single('importexport_tasks_runimport')->exec($queue_params);
    }

    /**
     * 检查导入文件是否合法
     */
    private function check_import_file(){
        if( !$_FILES['import_file']['name'] ){
            $echoMsg =app::get('importexport')->_('未上传文件');
            $this->import_message(false,$echoMsg);
        }
        $filetype = strrchr($_FILES['import_file']['name'],'.');
        $import_support_filetype = $this->import_support_filetype();
        if(!in_array($filetype,$import_support_filetype)){
            $echoMsg =app::get('importexport')->_('导入格式不支持');
            $this->import_message(false,$echoMsg);
        }
    }

    /**
     * 将导入文件上传到服务器
     * @param array $data
     */
    private function push_file($params){

        $tmpFileHandle = fopen( $_FILES['import_file']['tmp_name'],"r" );

        $filetype = substr(strrchr($_FILES['import_file']['name'],'.'),1);

        //连接导入文件上传的服务器
        $policyObj = kernel::single('importexport_policy');
        $ret = $policyObj->connect();
        if ( $ret !== true ){
            $this->import_message(false,$ret);
        }

        //设置上传到服务器文件名称
        $remote_file_filter = array(
            'key'=>$this->gen_key('import'),
            'filetype'=>$filetype
        );
        $remote_file_name = $policyObj->create_remote_file($remote_file_filter);

        //创建本地文件
        if( !$policyObj->create_local_file() ){
            $msg = app::get('importexport')->_('本地文件创建失败，请检查/tmp文件夹权限');
            $this->import_message(false,$msg);
        }

        //实例化导入文件类型类
        $filetypeObj = kernel::single('importexport_type_'.$filetype);

        //实例化数据类
        $dataObj = kernel::single('importexport_data_object',$params['mdl']);

        $params = array(
            'key'=>$remote_file_filter['key'],
            'filetype'=>$filetype,
            'name'=>'导入:'.$_FILES['import_file']['name'],
            'status' => 0 
        );

        $line = 0;
        $ftell = 0;
        while ( feof($tmpFileHandle) === false )
        {
            $contents = array();
            $msg = null;
            fseek($tmpFileHandle,$ftell);
            while ( $dataObj->check_continue($contents,$line) )
            {
                $ftell = ftell($tmpFileHandle);

                //从上传文件中以每行读取数据
                if ( !$filetypeObj->fgethandle($tmpFileHandle,$contents,$line) ) {
                    break;
                }

                //处理读取出的数据，格式化数据,将数据中的值对应到字段中
                $contents = $dataObj->pre_import_data($contents,$line);
                if ( $contents === false) {
                    $msg = app::get('importexport')->_('数据格式不正确');
                    $this->import_message(false,$msg);
                }
            }

            if($contents){
                $rs = serialize($contents);
                $rs = $filetypeObj->arrToExportType($rs);

                if( !$policyObj->write_local_file($rs) ){
                    $msg = app::get('importexport')->_('本地写入文件失败，请检查/tmp文件夹权限'); 
                    $this->import_message(false,$msg);
                }

                //本地文件上传到远程
                if( !$policyObj->push() ){
                    $msg = app::get('importexport')->_('文件上传失败'); 
                    $this->import_message(false,$msg);
                }
            }
        }

        $policyObj->close_local_file();
        return $params;
    }

    public function queue_download(){
        $this->file_download();
        exit;
    }

}
