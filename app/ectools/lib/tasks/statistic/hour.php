<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class ectools_tasks_statistic_hour extends base_task_abstract implements base_interface_task{
    public function exec($params=null){
        kernel::single('ectools_analysis_task')->analysis_hour();
    }
}
