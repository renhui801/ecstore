<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['normallogs']=array (
    'columns' =>
    array (
        'id' =>
        array (
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
        ),
        'username' =>
        array (
            'type' => 'varchar(50)',
            'required' => true,
            'label' => app::get('operatorlog')->_('操作员'),
            'searchtype' => 'has',
            'filtertype' => 'yes',
            'filterdefault' => true,
            'width' => 70,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'module' =>
        array (
            'type' => 'varchar(50)',
            'required' => true,
            'label' => app::get('operatorlog')->_('模块'),
            'searchtype' => 'has',
            'filtertype' => 'yes',
            'filterdefault' => true,
            'width' => 70,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'operate_type' =>
        array (
            'type' => 'varchar(255)',
            'required' => true,
            'label' => app::get('operatorlog')->_('操作类型'),
            'width' => 200,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'dateline' =>
        array (
            'type' => 'time',
            'required' => true,
            'label' => app::get('operatorlog')->_('操作时间'),
            'filtertype' => 'time',
            'filterdefault' => true,
            'width' => 120,
            'in_list' => true,
            'default_in_list' => true,
            'orderby' => true,
        ),
        'memo' =>
        array (
            'type' => 'longtext',
            'label' => app::get('operatorlog')->_('日志内容'),
            'width' => 200,
            'in_list' => true,
            'default_in_list' => true,
        ),
    ),
    'index' =>
    array (
        'ind_dateline' =>
        array (
          'columns' =>
          array (
            0 => 'dateline',
          ),
        ),
    ),
);
