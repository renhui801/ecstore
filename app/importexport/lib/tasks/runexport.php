<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class importexport_tasks_runexport extends base_task_abstract implements base_interface_task{

    public function exec($params=null){
        $taskModel = app::get('importexport')->model('task'); 
        //执行队列，更新队列状态
        $taskModel->update(array('status'=>1),array('key'=>$params['key'])); 

        //连接导出文件上传的服务器
        $policyObj = kernel::single('importexport_policy');
        $ret = $policyObj->connect();
        if ( $ret !== true ){
            $taskModel->update( array('status'=>3,'complete_date'=>time(),'message'=>$ret), array('key'=>$params['key']) );exit;
        }

        //实例化导出数据类
        $dataObj = kernel::single('importexport_data_object',$params['model']);
        //实例化导出文件类型类
        $filetypeObj = kernel::single('importexport_type_'.$params['filetype']);

        $remote_file_name = $policyObj->create_remote_file($params);
        if( !$policyObj->create_local_file() ){
            $msg = app::get('importexport')->_('本地文件创建失败，请检查/tmp文件夹权限'); 
            $taskModel->update( array('status'=>3,'complete_date'=>time(),'message'=>$msg), array('key'=>$params['key']) );exit;
        }

        //加入文件头部数据
        $fileHeader = $filetypeObj->fileHeader();
        if( $fileHeader )
        {
            $policyObj->write_local_file($fileHeader);
            $policyObj->push();
        }

        $filter = $dataObj->getIdFilter($params['filter']);

        //导出数据写到本地文件
        $offset = 0;
        while( $listFlag = $dataObj->fgetlist($data,$filter,$offset) ){
            $offset++;
            $rs = $filetypeObj->arrToExportType($data);
            if( !$policyObj->write_local_file($rs) ){
                $msg = app::get('importexport')->_('本地写入文件失败，请检查/tmp文件夹权限'); 
                $taskModel->update( array('status'=>3,'complete_date'=>time(),'message'=>$msg), array('key'=>$params['key']) );exit;
            }

            //本地文件上传到远程
            if( !$policyObj->push() ){
                $msg = app::get('importexport')->_('文件上传失败'); 
                $taskModel->update( array('status'=>3,'complete_date'=>time(),'message'=>$msg), array('key'=>$params['key']) );exit;
            }
        }

        //加入文件尾部数据
        $fileFoot = $filetypeObj->fileFoot();
        if( $fileFoot )
        {
            $policyObj->write_local_file($fileFoot);
            $policyObj->push();
        }

        $is_forma=true;//文件大小格式化 B kB MB
        $file_size = $policyObj->size_local_file($is_forma);
        //导出结束
        $taskModel->update( array('status'=>2,'complete_date'=>time(),'message'=>$file_size), array('key'=>$params['key']) );

        $policyObj->close_local_file();
        return true;
    }

}
