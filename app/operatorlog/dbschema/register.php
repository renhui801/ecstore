<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['register']=array (
    'columns' =>
    array (
        'id' =>
        array (
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
        ),
        'app' =>
        array (
            'type' => 'varchar(50)',
            'required' => true,
            'label' => app::get('operatorlog')->_('程序目录'),
        ),
        'ctl' =>
        array (
            'type' => 'varchar(50)',
            'required' => true,
            'label' => app::get('operatorlog')->_('控制器'),
        ),
        'act' =>
        array (
            'type' => 'varchar(50)',
            'required' => false,
            'label' => app::get('operatorlog')->_('动作'),
        ),
        'method' =>
        array (
          'type' =>
          array (
            'post' => app::get('b2c')->_('post方法'),
            'get' => app::get('b2c')->_('get方法'),
          ),
          'default' => 'post',
          'required' => true,
          'label' => app::get('b2c')->_('提交方法'),
        ),
        'module' =>
        array (
            'type' => 'varchar(255)',
            'required' => true,
            'label' => app::get('operatorlog')->_('日志模块'),
        ),
        'operate_type' =>
        array (
            'type' => 'varchar(255)',
            'required' => true,
            'label' => app::get('operatorlog')->_('操作类型'),
        ),
        'template' =>
        array (
            'type' => 'varchar(255)',
            'required' => false,
            'label' => app::get('operatorlog')->_('模板'),
        ),
        'param' =>
        array (
            'type' => 'varchar(255)',
            'required' => false,
            'label' => app::get('operatorlog')->_('参数'),
        ),
        'prk' =>
        array (
            'type' => 'varchar(255)',
            'required' => false,
            'default' => '0',
            'label' => app::get('operatorlog')->_('修改项唯一值'),
        ),
    ),
    'index' =>
    array (
        'ind_index' =>
        array (
          'columns' =>
          array (
            0 => 'app',
            1 => 'ctl',
            2 => 'act',
          ),
          'prefix' => 'UNIQUE',
        ),
    ),
);
