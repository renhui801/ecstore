<?php
class apiactionlog_router_payments{
    function get_all($data){
        $task_name = app::get('apiactionlog')->_('同步支付方式');
        $status = "running";
        $params = array(
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
