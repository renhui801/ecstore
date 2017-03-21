<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_tasks_sendmessenger extends base_task_abstract implements base_interface_task{

    public function exec($params=null){
        app::get('b2c')->model('member_messenger')->queue_send($params);
    }

}
