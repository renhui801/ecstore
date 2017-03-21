<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_tasks_sendmsg extends base_task_abstract implements base_interface_task{
    public function exec($params=null){
        $obj_memmsg = kernel::single('b2c_message_msg');
        $aData = $params['data'];
        $aData['member_id'] = 1;
        $aData['uname'] = app::get('b2c')->_('管理员');
        $aData['to_id'] = $params['member_id'];
        $aData['msg_to'] = $params['name'];
        $aData['subject'] = $aData['title']; 
        $aData['comment'] = $aData['content'];
        $aData['has_sent'] = 'true';
        $obj_memmsg->send($aData);

        if($params['gnotify_id']) {
            $member_goods = app::get('b2c')->model('member_goods');
            $sdf = $member_goods->dump($params['gnotify_id']);
            $sdf['status'] = "send";
            $sdf['send_time'] = time();
            $member_goods->save($sdf);
        }
    }
}

    
