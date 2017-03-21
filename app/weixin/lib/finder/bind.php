<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class weixin_finder_bind{

    var $column_edit = '编辑';
    function column_edit($row){
        return '<a href="index.php?app=weixin&ctl=admin_bind&act=bind_view&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[id]='.$row['id'].'" target="dialog::{title:\''.app::get('weixin')->_('编辑公众账号').'\', width:600, height:620}">'.app::get('weixin')->_('编辑').'</a>';
    }

    
}
