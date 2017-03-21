<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/**
 * @table regions;
 *
 * @package Schemas
 * @version $
 * @copyright 2003-2009 ShopEx
 * @license Commercial
 */

$db['regions']=array (
    'columns' =>
    array (
        'region_id' =>
        array (
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'editable' => false,
            'comment' => app::get('ectools')->_('区域序号'),
        ),
        'local_name' =>
        array (
            'type' => 'varchar(50)',
            'required' => true,
            'default' => '',
            'label'=>app::get('ectools')->_('地区名称'),
            'width'=>100,
            'default_in_list'=>true,
            'in_list'=>true,
            'editable' => false,
        ),
        'package' =>
        array (
            'type' => 'varchar(20)',
            'required' => true,
            'default' => '',
            'label'=>app::get('ectools')->_('数据包'),
            'width'=>100,
            'default_in_list'=>true,
            'in_list'=>true,
            'editable' => false,
            'comment' => app::get('ectools')->_('地区包的类别, 中国/外国等. 中国大陆的编号目前为mainland'),
        ),
        'p_region_id' =>
        array (
            'type' => 'int unsigned',
            'editable' => false,
            'comment' => app::get('ectools')->_('上一级地区的序号'),
        ),
        'region_path' =>
        array (
            'type' => 'varchar(255)',
            'width'=>300,
            'editable' => false,
            'comment' => app::get('ectools')->_('序号层级排列结构'),
        ),
        'region_grade' =>
        array (
            'type' => 'number',
            'editable' => false,
            'comment' => app::get('ectools')->_('地区层级'),
        ),
        'p_1' =>
        array (
            'type' => 'varchar(50)',
            'editable' => false,
            'comment' => app::get('ectools')->_('额外参数1'),
        ),
        'p_2' =>
        array (
            'type' => 'varchar(50)',
            'editable' => false,
            'comment' => app::get('ectools')->_('额外参数2'),
        ),
        'ordernum' =>
        array (
            'type' => 'number',
            'editable' => true,
            'comment' => app::get('ectools')->_('排序'),
        ),
        'disabled' =>
        array (
            'type' => 'bool',
            'default' => 'false',
            'editable' => false,
        ),
    ),
    'index' => 
  array (
    'ind_p_regions_id' =>
    array (
        'columns' =>
        array (
          0 => 'p_region_id',
          1 => 'region_grade',
          2 => 'local_name',
        ),
        'prefix' => 'unique',
    ),
  ),
    'comment' => app::get('ectools')->_('地区表'),
);
