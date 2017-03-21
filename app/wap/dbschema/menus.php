<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['menus']=array (
    'columns' =>
    array (
        'id' =>
        array (
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'comment' => app::get('wap')->_('导航菜单表'),
        ),
        'title' =>
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'default' => '',
            'label'=>app::get('wap')->_('标题'),
            'width'=>100,
            'default_in_list'=>true,
            'in_list'=>true,
        ),
        'app' =>
        array (
            'type' => 'varchar(50)',
            'default' => '',
            'label' => app::get('wap')->_('程序目录'),
            'width'=>80,
            'in_list'=>true,
        ),
        'ctl' =>
        array (
            'type' => 'varchar(50)',
            'default' => '',
            'label' => app::get('wap')->_('控制器'),
            'width'=>80,
            'in_list'=>true,
        ),
        'act' => 
        array (
            'type' => 'varchar(50)',
            'default' => '',
            'label' => app::get('wap')->_('动作'),
            'width'=>80,
            'in_list'=>true,
        ),
        'custom_url' =>
        array (
            'type' => 'varchar(200)',
            'default' => '',
            'label' => app::get('wap')->_('自定义链接'),
            'width' => 160,
            'default_in_list'=>true,
            'in_list'=>true,
        ),
        'display_order' => 
        array (
            'type' => 'tinyint(4) unsigned',
            'required' => true,
            'default' => 0,
            'width'=>80,
            'label' => app::get('wap')->_('排序'),
            'default_in_list'=>true,
            'in_list' => true,
        ),
        'target_blank' =>
        array (
            'type' => 'bool',
            'required' => true,
            'default' => 'false',
            'label'=>app::get('wap')->_('是否新开窗口'),
            'width' => 100,
            'default_in_list' => true,
            'in_list' => true,
        ),
        'hidden' =>
        array (
            'type' => 'bool',
            'required' => true,
            'default'=>'false',
            'label'=>app::get('wap')->_('在菜单上隐藏'),
            'width'=>100,
            'default_in_list'=>true,
            'in_list'=>true,
        ),
        'params' =>
        array (
            'type' => 'serialize',
            'default' => '',
            'label' => app::get('wap')->_('参数'),
        ),
        'config' =>
        array (
            'type' => 'serialize',
            'default' => '',
            'label' => app::get('wap')->_('配置'),
        ),
        'update_modified' => 
        array (
          'type' => 'time',
          'editable' => false,
          'comment' => app::get('wap')->_('更新时间'),
        ),
    ),
    'comment' => app::get('wap')->_('导航菜单表'),
);
