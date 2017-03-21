<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_tasks_cleancartobject extends base_task_abstract implements base_interface_task{
    public function exec($params=null){
        $time = strtotime('-7 days');
        $sql = "DELETE FROM sdb_b2c_cart_objects WHERE member_id='-1' AND time<=$time";
        app::get('b2c')->model('cart_objects')->db->exec( $sql );
    }
}