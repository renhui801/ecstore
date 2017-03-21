<?php
class apiactionlog_router_member{
    /*
     * 初始化会员信息(CRM调用)
     */
    function init($data){
        $params_data = $data['params'];
        $task_name = app::get('apiactionlog')->_('初始化会员信息');
        $status = "running";
        $params = array(
            'original_bn'=>$original_bn,
            'task_name'=>$task_name,
            'status'=>$status,
            'log_type'=>'member',
            'createtime'=>time(),
            'last_modified'=>time(),
        );
        $api_data = array_merge($data,$params);
        return $api_data;
    }

    /*
     * 获取到会员等级列表(CRM调用)
     */
    function get_member_lv_list($data){
        $params_data = $data['params'];
        $task_name = app::get('apiactionlog')->_('获取到会员等级列表');
        $status = "running";
        $params = array(
            'original_bn'=>$original_bn,
            'task_name'=>$task_name,
            'status'=>$status,
            'log_type'=>'member',
            'createtime'=>time(),
            'last_modified'=>time(),
        );
        $api_data = array_merge($data,$params);
        return $api_data;
    }
}
