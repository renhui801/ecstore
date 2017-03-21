<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class cps_service_firevent_action{

    /**
     * 获取新增的消息发送事件类型
     * @access public
     * @return array
     * @version 1 Jun 28, 2011 创建
     */
    public function get_type(){
          $actions = array(
            'users-lostPw'=>array('label'=>app::get('cps')->_('联盟商找回密码'),'level'=>9,'b2c_messenger_sms'=>'false','b2c_messenger_msgbox'=>'false','varmap'=>app::get('cps')->_('联盟商名').'&nbsp;<{$uname}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('cps')->_('密码').'&nbsp;<{$passwd}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('cps')->_('真实姓名').'&nbsp;<{$name}>'),
            'users-register'=>array('label'=>app::get('cps')->_('联盟商注册时'),'level'=>9,'b2c_messenger_sms'=>'false','b2c_messenger_msgbox'=>'false','varmap'=>app::get('cps')->_('联盟商名').'&nbsp;<{$uname}>&nbsp;&nbsp;&nbsp;&nbsp;email&nbsp;<{$email}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('cps')->_('密码').'&nbsp;<{$passwd}>'),
            'users-chgpass'=>array('label'=>app::get('cps')->_('联盟商更改密码时'),'level'=>9,'b2c_messenger_sms'=>'false','b2c_messenger_msgbox'=>'false','varmap'=>app::get('cps')->_('密码').'&nbsp;<{$passwd}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('cps')->_('联盟商名').'&nbsp;<{$uname}>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;email&nbsp;<{$email}>'),
        );
            return $actions;
    }
}