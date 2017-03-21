<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class importexport_tasks_runimport extends base_task_abstract implements base_interface_task{

    public function exec($params=null){
        $taskModel = app::get('importexport')->model('task'); 
        //执行队列，更新队列状态
        $taskModel->update(array('status'=>4),array('key'=>$params['key'])); 

        //连接导入文件上传的服务器
        $policyObj = kernel::single('importexport_policy');
        $ret = $policyObj->connect();
        if ( $ret !== true ){
            $taskModel->update( array('status'=>6,'complete_date'=>time(),'message'=>$ret), array('key'=>$params['key']) );exit;
        }

        //创建本地文件
        $filename = $policyObj->create_remote_file($params);
        if( !$policyObj->create_local_file() ){
            $msg = app::get('importexport')->_('本地文件创建失败，请检查/tmp文件夹权限'); 
            $taskModel->update(array('status'=>7,'message'=>$msg),array('key'=>$params['key']));
            return false;
        }

        if( !$policyObj->pull(array('resume'=>-1),$msg) ){
            $taskModel->update(array('status'=>7,'message'=>$msg),array('key'=>$params['key']));
            return false; 
        }

        //实例化数据类
        $dataObj = kernel::single('importexport_data_object',$params['model']);
        //实例化导入文件类型类
        $filetypeObj = kernel::single('importexport_type_'.$params['filetype']);

        $file = fopen($policyObj->local_file,"rb");
        $o = kernel::single($params['model']);

        $this->create_error_file($params);
        while( !feof($file) ){
            $msg = null;
            $contents = fgets($file);
            if($contents){
                $rows = unserialize($contents);

                $rs = $dataObj->dataToSdf($rows,$msg);
                if( $msg['error'] ){
                    $params['status'] = ($params['status'] == 8) ? 8 : 6;
                    $error = $filetypeObj->arrToExportType($msg['error']);

                    if( !$this->write_error_file($error) ){
                        $msg = app::get('importexport')->_('本地写入文件失败，请检查/tmp文件夹权限'); 
                        $taskModel->update(array('status'=>7,'message'=>$msg),array('key'=>$params['key']));
                        exit;
                    }

                    //本地文件上传到远程
                    if( !$policyObj->push(array('local'=>$this->error_local_file,'remote'=>$this->error_remote_file,'resume'=>$this->error_ftell)) ){
                        $msg = app::get('importexport')->_('文件上传失败'); 
                        $taskModel->update(array('status'=>7,'message'=>$msg),array('key'=>$params['key']));
                        exit;
                    }
                }else{
                    $flag = $o->save($rs);
                    if( !$flag ){
                        $msg = app::get('b2c')->_('数据库插入错误');
                        $taskModel->update(array('status'=>7,'message'=>$msg),array('key'=>$params['key']));
                        exit;
                    }else{
                        //如果是有错误过则是部分导入
                        $params['status'] = ($params['status'] == 6) ? 8 : 5;
                    }
                }
            }
        }

        $policyObj->close_local_file();
        unlink($this->error_local_file);
        $taskModel->update(array('status'=>$params['status'],'complete_date'=>time()),array('key'=>$params['key']));
    }

    public function create_error_file($params){
        $this->error_local_file  = tempnam(TMP_DIR,'importexport');
        $this->error_file = fopen($this->error_local_file,'w');
        $this->error_remote_file = $params['key'].'.'.$params['filetype'];
        return true; 
    }

    public function write_error_file($rs){
        $this->error_ftell = ftell($this->error_file);
        if( !fwrite($this->error_file, $rs) )
        {
            return false;
        }
        return true;
    }

}
