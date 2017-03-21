<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_tasks_sendemail extends base_task_abstract implements base_interface_task{
    public function exec($params=null){
        $obj_emailconf = kernel::single('desktop_email_emailconf');
        $aTmp = $obj_emailconf->get_emailConfig();
        $acceptor =  $params['acceptor'];    //收件人邮箱
        $aTmp['shopname'] = app::get('site')->getConf('site.name');
        $subject = $params['title'];
        $body = $params['body'];
        $email = kernel::single('desktop_email_email');
        $email->ready($aTmp);
        $res = $email->send($acceptor,$subject,$body,$aTmp);

        if($params['gnotify_id']) {
            $member_goods = app::get('b2c')->model('member_goods');
            $sdf = $member_goods->dump($params['gnotify_id']);
            $sdf['status'] = "send";
            $sdf['send_time'] = time();
            $member_goods->save($sdf);
        }
    }
}

