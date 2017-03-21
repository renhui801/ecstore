<?php
class apiactionlog_router_coupon{

    /*
     * 获取优惠劵列表(CRM调用)
     */
    function get_coupon_list($data){
        $params_data = $data['params'];
        $task_name = app::get('apiactionlog')->_('获取优惠劵列表');
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

    /*
     * 对指定会员发放指定的B类优惠券
     */
    function create_coupon_to_member($data){
        $params_data = $data['params'];
        $task_name = app::get('apiactionlog')->_('对指定会员发放指定的B类优惠券');
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

    /*
     * 获取优惠券，只有B类优惠券需要调用
     */
    function get_coupon_number($data){
        $params_data = $data['params'];
        $task_name = app::get('apiactionlog')->_('获取优惠券码');
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

    /*
     * 获取优惠券使用记录
     */
    function get_coupon_use_log($data){
        $params_data = $data['params'];
        $task_name = app::get('apiactionlog')->_('获取优惠券使用记录');
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


