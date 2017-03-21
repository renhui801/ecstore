<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class weixin_finder_message_text{

    var $column_edit = '编辑';
    var $column_editbutton_order = 10;
    function column_edit($row){
        return '<a href="index.php?app=weixin&ctl=admin_message_text&act=text_view&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[id]='.$row['id'].'" target="dialog::{title:\''.app::get('weixin')->_('编辑文字消息').'\', width:600, height:500}">'.app::get('weixin')->_('编辑').'</a>';
    }
}
