<?php
/**
 * ShopEx licence
 *
* 微信自动回复内容
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$db['message_image']=array (
    'columns' => array (
        'id' =>
        array(
          'type' => 'int unsigned',
          'required' => true,
          'pkey' => true,
          'extra' => 'auto_increment',
          'editable' => false,
          'comment' => app::get('weixin')->_('节点ID'),
        ),
        'name' => 
        array (
            'type' => 'varchar(255)',
            'required' => true,
            'defalut' => '',
            'order'=>10,
            'searchtype' => 'has',
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'label' => app::get('weixin')->_('消息名称'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'title' =>
        array (
            'type' => 'varchar(255)',
            'label' => app::get('weixin')->_('图文消息标题'),
            'order'=>20,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'description' =>
        array (
            'type' => 'longtext',
            'label' => app::get('wap')->_('图文消息描述'),
        ),
        'picurl' =>
        array (
            'type' => 'varchar(32)',
            'label' => app::get('wap')->_('图文图片'),
        ),
        'url' =>
        array (
            'type' => 'text',
            'label' => app::get('wap')->_('图片连接地址'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'parent_id' =>array (
            'type' => 'number',
            'required' => true,
            'default' => 0,
            'label'=> app::get('weixin')->_('父节点'),
            'width' => 10,
            'editable' => true,
            'in_list' => true,
        ),
        'message_depth' => array(
            'type' => 'tinyint(1)',
            'required' => true,
            'default' => 0,
            'label' => app::get('weixin')->_('节点深度'),
            'editable' => false,
        ),
        'has_children' => array(
            'type' => 'bool',
            'default' => 'false',
            'required' => true,
            'label' => app::get('weixin')->_('是否存在子节点'),
            'editable' => false,
        ),
        'ordernum'=> array (
            'type' => 'number',
            'required' => true,
            'default' => 0,
            'editable' => true,
            'label' => app::get('weixin')->_('排序'),
        ),
        'uptime'=> array (
            'type' => 'time',
            'editable' => true,
            'label' => app::get('weixin')->_('修改时间'),
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
    'comment' => app::get('wap')->_('图文消息'),
);
