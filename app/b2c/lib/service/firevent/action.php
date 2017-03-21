<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_service_firevent_action{

    function get_type(){
        $actions = array(
            'comments-discussreply'=>array('label'=>app::get('b2c')->_('会员商品评论被回复'),'level'=>9,'sendType'=>'notice','varmap'=>app::get('b2c')->_('用户名').'&nbsp;<{$uname}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('商品编号').'&nbsp;<{$goods_id}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('商品名称').'&nbsp;<{$goods_name}>'),
            'comments-gaskreply'=>array('label'=>app::get('b2c')->_('会员商品咨询被回复'),'level'=>9,'sendType'=>'notice','varmap'=>app::get('b2c')->_('用户名').'&nbsp;<{$uname}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('商品编号').'&nbsp;<{$goods_id}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('商品名称').'&nbsp;<{$goods_name}>'),
            'comments-delete'=>array('label'=>app::get('b2c')->_('会员评论/咨询/消息被删除'),'level'=>9,'sendType'=>'notice','b2c_messenger_sms'=>'false','varmap'=>app::get('b2c')->_('用户名').'&nbsp;<{$uname}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('商品链接').'&nbsp;<{$goodsurl}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('商品名称').'&nbsp;<{$goods_name}>'),
            'comments-membermsg'=>array('label'=>app::get('b2c')->_('管理员回复站内信'),'level'=>9,'sendType'=>'notice','varmap'=>app::get('b2c')->_('用户名').'&nbsp;<{$uname}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('姓名').'&nbsp;<{$name}>'),
        );
        return $actions;
    }
}
