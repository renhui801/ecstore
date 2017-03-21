<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$db['message_text']=array (
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
            'type' => 'varchar(255)',
            'required' => true,
            'defalut' => '',
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'order' => 40,
            'label' => app::get('weixin')->_('消息名称'),
        ),
        'content' => 
        array (
            'type' => 'longtext',
            'label' => app::get('wap')->_('消息内容'),
            'in_list' => true,
            'order' => 70,
            'default_in_list' => true,
        ),
        'is_check_bind' => 
        array (
            'type' => 'bool',
            'default' => 'false',
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('weixin')->_('是否需要验证绑定'),
            'comment' => app::get('weixin')->_('发生此消息前是否需要验证绑定'),
        ),
    ),
    'version' => '$Rev: 40918 $',
    'comment' => app::get('wap')->_('文字消息'),
);
