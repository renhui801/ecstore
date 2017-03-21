<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_finder_goods_share{
    function __construct(&$app){
        $this->app=$app;
    }

    var $column_edit = '配置';
    function column_edit($row){
        return '<a target="dialog::{width:0.4,height:0.4,title:\'配置\'}" href="index.php?app=b2c&ctl=admin_goods_share&act=setting&name='.$row['name'].'&finder_id='. $_GET['_finder']['finder_id'] . '">'.app::get('b2c')->_('配置').'</a>';
    }

}

