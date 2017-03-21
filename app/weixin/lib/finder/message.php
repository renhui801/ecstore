<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class weixin_finder_message{

    var $column_edit = '编辑';
    var $column_edit_order = 10;
    function column_edit($row){
        $messageData = app::get('weixin')->model('message')->getList('reply_type',array('id'=>intval($row['id'])));
        return '<a href="index.php?app=weixin&ctl=admin_autoreply&act=bind_message_view&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$messageData[0]['reply_type'].'&p[1]='.$row['id'].'" target="dialog::{title:\''.app::get('weixin')->_('编辑绑定消息').'\', width:480, height:260}">'.app::get('weixin')->_('编辑').'</a>';
    }

    var $column_message_name = '消息名称';
    var $column_message_name_order = 20;
    function column_message_name($row){
        $messageData = app::get('weixin')->model('message')->getList('message_id,message_type',array('id'=>$row['id']));
        if( isset($messageData[0]['message_type']) && $messageData[0]['message_type'] == 'text' ){
            $data = app::get('weixin')->model('message_text')->getList('name',array('id'=>$messageData[0]['message_id']));
        }else{
            $data = app::get('weixin')->model('message_image')->getList('name',array('id'=>$messageData[0]['message_id']));
        }
        return $data[0]['name'];
    }

}
