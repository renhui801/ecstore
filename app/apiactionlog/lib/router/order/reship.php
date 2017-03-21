<?php
class apiactionlog_router_order_reship{

    function create($data){

        $params_data = $data['params'];
        $original_bn = $params_data['order_bn'];
        $task_name = app::get('apiactionlog')->_('添加退货单');
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
        $task_name = app::get('apiactionlog')->_('更新退货单');
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
