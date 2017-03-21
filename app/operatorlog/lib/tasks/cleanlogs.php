<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class operatorlog_tasks_cleanlogs extends base_task_abstract implements base_interface_task{
    public function exec($params=null){
        $time = strtotime('-30 days');
        $sql = "DELETE FROM sdb_operatorlog_normallogs WHERE dateline<=$time";
        app::get('operatorlog')->model('normallogs')->db->exec( $sql );
    }
}
