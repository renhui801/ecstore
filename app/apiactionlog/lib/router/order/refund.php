<?php
class apiactionlog_router_order_refund{
    function create($data){

        $params_data = $data['params'];
        $original_bn = $params_data['order_bn'];
        $task_name = app::get('apiactionlog')->_('退款单添加');
        $status = "running";
        $params = array(
            'original_bn'=>$original_bn,
            'task_name'=>$task_name,
            'status'=>$status,
            'log_type'=>'order',
            'createtime'=>time(),
            'last_modified'=>time(),
        );
        $api_data = array_merge($data,$params);
        return $api_data;
    }

    function update($data){
        $params_data = $data['params'];
        $original_bn = $params_data['order_bn'];
        $task_name = app::get('apiactionlog')->_('退款单更新');
        $status = "running";
        $params = array(
            'original_bn'=>$original_bn,
            'task_name'=>$task_name,
            'status'=>$status,
            'log_type'=>'order',
            'createtime'=>time(),
            'last_modified'=>time(),
        );
        $api_data = array_merge($data,$params);
        return $api_data; 
    }

}
