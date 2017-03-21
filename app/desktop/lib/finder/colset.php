<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class desktop_finder_colset{
    function run($task,$ctl){
        $user_id = $ctl->user->user_id;
        $finder_model = (key($task));
        $finder_cols = current($task);
        $old_cols_width = app::get('desktop')->getConf('colwith.'.$finder_model.'.'.$user_id);
        $old_cols_width = (array)$old_cols_width;
        $finder_cols = array_merge($old_cols_width,$finder_cols);
        app::get('desktop')->setConf('colwith.'.$finder_model.'.'.$user_id,$finder_cols);

    }
}
