<?php
class apiactionlog_router_order{

    /*
     *拉去订单
     */

    #function search($data){
    #    $params_data = $data['params'];
    #    $original_bn = $params_data['order_bn'];
    #    $task_name = app::get('apiactionlog')->_('拉去订单');
    #    $status = "running";
    #    $params = array(
    #        'original_bn'=>$original_bn,
    #        'task_name'=>$task_name,
    #        'status'=>$status,
    #        'log_type'=>'order',
    #        'createtime'=>time(),
    #        'last_modified'=>time(),
    #    );
    #    $api_data = array_merge($data,$params);
    #    return $api_data;
    #    
    #}

    /*
     *拉去订单详情
     */
    function detail($data){
        $params_data = $data['params'];
        $original_bn = $params_data['order_bn'];
        $task_name = app::get('apiactionlog')->_('拉去订单详情');
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

    /*
     *修改订单状态
     */
    function status_update($data){
        $params_data = $data['params'];
        $original_bn = $params_data['order_bn'];
        $task_name = app::get('apiactionlog')->_('更新订单状态');
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
