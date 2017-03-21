<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author afei, bryant
 */


class system_ctl_admin_queue extends desktop_controller {

    var $workground = 'system.workground.setting';
    function index() {
        $params = array (
            'title' => app::get('desktop')->_('队列管理'),
        );

        $queue_controller_name = system_queue::get_controller_name();
        $support_queue_controller_name = 'system_queue_adapter_mysql';

        if ($queue_controller_name == $support_queue_controller_name) {
            $this->finder('system_mdl_queue_mysql', $params);
        }else{
            $this->pagedata['queue_controller_name'] = $queue_controller_name;
            $this->pagedata['support_queue_controller_name'] = $support_queue_controller_name;
            
            $this->page('admin/queue.html');
        }
    }
}