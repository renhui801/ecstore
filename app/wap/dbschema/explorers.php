<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['explorers']=array (
    'columns' =>
    array (
        'id' =>
        array (
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'comment' => app::get('wap')->_('site地图表ID'),
        ),
        'app' =>
        array (
            'type' => 'varchar(50)',
            'required' => true,
            'default' => '',
            'label' => app::get('wap')->_('程序目录'),
            'width'=>80,
            'default_in_list'=>true,
            'in_list'=>true,
            'comment' => app::get('wap')->_('应用(app)名'),
        ),
        'title' =>
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'default' => '',
            'label'=>app::get('wap')->_('名称'),
            'width'=>120,
            'default_in_list'=>true,
            'in_list'=>true,
        ),
        'path' =>
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'default' => '',
            'label'=>app::get('wap')->_('目录'),
            'width'=>120,
            'default_in_list'=>true,
            'in_list'=>true,
            'comment' => app::get('wap')->_('路径'),
        ),
    ),
    'comment' => app::get('wap')->_('site地图表'),
);