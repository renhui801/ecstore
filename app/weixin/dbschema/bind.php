<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['bind']=array (
    'columns' => array (
        'id' =>
        array(
          'type' => 'int unsigned',
          'required' => true,
          'pkey' => true,
          'extra' => 'auto_increment',
          'editable' => false,
          'comment' => app::get('weixin')->_('ID'),
        ),
        'name' => 
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'default' => '',
            'is_title' => 'true',
            'label' => app::get('weixin')->_('公众账号名称'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'eid' => 
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'comment' => app::get('weixin')->_('微信公众账号api中标识'),
        ),
        'weixin_id' => 
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'default' => '',
            'comment' => app::get('weixin')->_('原始ID'),
        ),
        'weixin_account' => 
        array (
            'type' => 'varchar(20)',
            'required' => true,
            'default' => '',
            'label' => app::get('weixin')->_('微信号'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'status' =>
        array (
            'type' =>
            array (
                'active' => app::get('b2c')->_('启用'),
                'disabled' => app::get('b2c')->_('禁用'),
            ),
            'default' => 'active',
            'required' => true,
            'label' => app::get('b2c')->_('状态'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'weixin_type' => 
        array (
            'type' =>
            array (
                'subscription' => app::get('weixin')->_('订阅号'),
                'service' => app::get('weixin')->_('服务号'),
            ),
            'required' => true,
            'default' => 'subscription',
            'label' => app::get('weixin')->_('微信账号类型'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'email' => 
        array (
            'type' => 'varchar(30)',
            'required' => true,
            'label' => app::get('weixin')->_('登录邮箱'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'avatar' => 
        array (
            'type' => 'varchar(32)',
            'comment' => app::get('weixin')->_('头像'),
        ),
        'url' =>
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'comment' => app::get('weixin')->_('接口配置URL'),
        ),
        'token' =>
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'comment' => app::get('weixin')->_('接口配置token'),
        ),
        'appid' =>
        array (
            'type' => 'varchar(100)',
            'comment' => app::get('weixin')->_('AppId'),
        ),
        'appsecret' =>
        array (
            'type' => 'varchar(100)',
            'comment' => app::get('weixin')->_('AppSecret'),
        ),
        'qr' =>
        array (
            'type' => 'char(32)',
            'comment' => app::get('weixin')->_('二维码'),
        ),
    ),
    'index' => array (
        'eid' => array ('columns' => array ('eid'),'prefix' => 'UNIQUE'),
    ),
    'version' => '$Rev: 40918 $',
    'comment' => app::get('weixin')->_('微信公众账号绑定列表'),
);
