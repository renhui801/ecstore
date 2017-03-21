<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

$db['menus'] = array (
    'columns' =>
    array (
        'menu_id' =>array (
            'type' => 'number',
            'required' => true,
            'label'=> app::get('weixin')->_('节点id'),
            'pkey' => true,
            'extra' => 'auto_increment',
            'width' => 10,
            'editable' => false,
            'in_list' => true,
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
        'menu_depth' => array(
            'type' => 'tinyint(1)',
            'required' => true,
            'default' => 0,
            'label' => app::get('weixin')->_('节点深度'),
            'editable' => false,
        ),
        'bind_id' => array(
          'type' => 'table:bind',
          'required' => true,
          'default' => 0,
          'editable' => false,
          'comment' => app::get('weixin')->_('公众账号ID'),
        ),
        'menu_theme' => array(
          'type' => 
          array (
            '1' => app::get('weixin')->_('微信自定义菜单1'),
            '2' => app::get('weixin')->_('微信自定义菜单2'),
            '3' => app::get('weixin')->_('微信自定义菜单3'),
          ),
          'default' => '1',
          'required' => true,
          'editable' => false,
          'comment' => app::get('weixin')->_('自定义菜单模板'),
        ),
        'menu_name' =>array (
            'type' => 'varchar(50)',
            'required' => true,
            'default'=>'',
            'label'=> app::get('weixin')->_('菜单名称'),
            'is_title' => true,
            'editable' => true,
            'default_in_list' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'content_type' => array(
          'type' => 
          array (
            'msg_url' => app::get('weixin')->_('自定义链接'),
            'msg_text' => app::get('weixin')->_('文字消息'),
            'msg_image' => app::get('weixin')->_('图文信息'),
          ),
          'default' => 'msg_url',
          'required' => true,
          'editable' => false,
          'comment' => app::get('weixin')->_('回复类型'),
        ),
        'msg_url' =>array (
            'type' => 'text',
            'label'=> app::get('weixin')->_('自定义链接'),
            'editable' => false,
        ),
        'msg_text' =>array (
            'type' => 'table:message_text',
            'label'=> app::get('weixin')->_('文字信息'),
            'editable' => false,
        ),
        'msg_image' =>array (
            'type' => 'table:message_text',
            'label'=> app::get('weixin')->_('图文信息'),
            'editable' => false,
        ),
        'menu_path'=>array (
            'type' => 'varchar(200)',
            'label'=> app::get('weixin')->_('节点路径'),
            'editable' => false,
            'in_list' => false,
        ),
        'has_children' => array(
            'type' => 'bool',
            'default' => 'false',
            'required' => true,
            'label' => app::get('weixin')->_('是否存在子节点'),
            'editable' => false,
            'in_list' => false,
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
        'disabled' => array(
            'type' => 'bool',
            'required' => true,
            'default' => 'false',
            'editable' => true,
        ),
    ),
    'index' => array (
        'ind_disabled' => array(
            'columns' => array(
                0 => 'disabled',
            ),
        ),
        'ind_ordernum' => array(
            'columns' => array(
                0 => 'ordernum',
            ),
        ),
    ),
    'version' => '$Rev$',
    'comment' => app::get('weixin')->_('自定义菜单表'),
);
