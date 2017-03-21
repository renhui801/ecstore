<?php
class apiactionlog_router_goods{

    function updateStore($data){
        $params_data = $data['params'];
        $original_bn = $params_data['list_quantity']['bn'];
        $task_name = app::get('apiactionlog')->_('同步商品库存');
        $status = "running";
        $params = array(
            'original_bn'=>$original_bn,
            'task_name'=>$task_name,
            'status'=>$status,
            'log_type'=>'goods',
            'createtime'=>time(),
            'last_modified'=>time(),
        );
        $api_data = array_merge($data,$params);
        return $api_data;

    }
}
