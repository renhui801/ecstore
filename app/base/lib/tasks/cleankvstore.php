<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class base_tasks_cleankvstore extends base_task_abstract implements base_interface_task{
    public function exec($params=null){
        base_kvstore::delete_expire_data();
    }
}
