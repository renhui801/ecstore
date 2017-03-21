<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['alert']=array (
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
        'appid' => 
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'default' => '',
            'label' => app::get('weixin')->_('公众号ID'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'errortype' => 
        array (
            'type' => 'int(10)',
            'required' => true,
            'default'=>0,
            'label' => app::get('weixin')->_('错误编码'),
            'in_list' => true,
            'default_in_list' => true,

        ),
        'description' => 
        array (
            'type' => 'longtext',
            'required' => true,
            'default' => '',
            'order'=>10,
            'label' => app::get('weixin')->_('错误描述'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'alarmContent' =>
        array (
            'type' => 'longtext',
            'label' => app::get('b2c')->_('错误详情'),
        ),
        'timestamp' =>
        array (
            'type' => 'time',
            'label' => app::get('b2c')->_('创建时间'),
            'in_list' => true,
            'default_in_list' => true,
        ),
    ),
    'version' => '$Rev: 40918 $',
    'comment' => app::get('weixin')->_('告警消息'),
);
