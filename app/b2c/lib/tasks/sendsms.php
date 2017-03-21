<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_tasks_sendsms extends base_task_abstract implements base_interface_task{
    public function exec($params=null){
        $obj_memmsm = kernel::single('b2c_messenger_sms');
        $objfilter = kernel::service('filter_sms_content');
        $title = $params['data']['title'];
        $message = $params['data']['content'];
        if(is_object($objfilter)){
            if(method_exists($objfilter,'get_filter_content')){
                $data = $objfilter->get_filter_content($title,$message);
                $title = $data['title'] ? $data['title'] : '';
                $message = $data['content'];
            }
        }
        $to = $params['mobile_number'];
        $config['shopname'] = app::get('site')->getConf('site.name');
        $config['use_reply'] = ($params['data']['use_reply']=='true') ? 1 : 0;
        $config['sendType'] = ($params['data']['sendType']=='fan-out') ? 'fan-out' : 'notice';
        if($obj_memmsm->ready($config)) $obj_memmsm->send($to,$title,$message,$config);
		
        if($params['gnotify_id']) {
            $member_goods = app::get('b2c')->model('member_goods');
            $sdf = $member_goods->dump($params['gnotify_id']);
            $sdf['status'] = "send";
            $sdf['send_time'] = time();
            $member_goods->save($sdf);
        }
    }
}


