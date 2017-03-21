<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_finder_goods_storePrompt{
    function __construct(&$app){
        $this->app=$app;
    }

    var $column_edit = '编辑';
    function column_edit($row){
        return '<a target="dialog::{width:700,height:400,title:\'编辑库存提示规则\'}" href="index.php?app=b2c&ctl=admin_goods_storePrompt&act=add&prompt_id='.$row['prompt_id'].'&finder_id='. $_GET['_finder']['finder_id'] . '">'.app::get('b2c')->_('编辑').'</a>';
    }

}

