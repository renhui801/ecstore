<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
* @table goods_virtual_cat;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['goods_virtual_cat']=array (
  'columns' => 
  array (
    'virtual_cat_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('b2c')->_('虚拟分类ID'),
      'width' => 110,
      'editable' => false,
    ),
    'virtual_cat_name' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'label' => app::get('b2c')->_('虚拟分类名称'),
      'width' => 110,
      'editable' => false,
    ),
    'filter' => 
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('过滤条件'),
    ),
    'addon' => 
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('额外序列化值'),
    ),
    'type_id' => 
    array (
      'type' => 'int(10)',
      'label' => app::get('b2c')->_('类型'),
      'width' => 110,
      'editable' => false,
      'comment' => app::get('b2c')->_('商品类型ID'),
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
    'parent_id' => 
    array (
      'type' => 'number',
      'default' => 0,
      'label' => app::get('b2c')->_('虚拟分类父ID'),
      'width' => 110,
      'editable' => false,
    ),
    'cat_id' => 
    array (
      'type' => 'int(10)',
      'editable' => false,
      'comment' => app::get('b2c')->_('商品类别ID'),
    ),
    'p_order' => 
    array (
      'type' => 'number',
      'label' => app::get('b2c')->_('排序'),
      'width' => 110,
      'editable' => false,
    ),
    'cat_path' => 
    array (
      'type' => 'varchar(100)',
      'default' => ',',
      'editable' => false,
      'comment' => app::get('b2c')->_('类别路径(从根至本结点的路径,逗号分隔,首部有逗号) 序号(5位),类别号(6位):....'),
    ),
    'child_count' => 
    array (
      'type' => 'number',
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('虚拟自分类数量'),
    ),
    'url' => array(
        'type' => 'varchar(200)',
        'default' => '',
        'required' => true,
        'width' => 110,
        'editable' => false,
        'comment' => app::get('b2c')->_('url'),
    )
  ),
  'index' => 
  array (
    'ind_disabled' => 
    array (
      'columns' => 
      array (
        0 => 'disabled',
      ),
    ),
    'ind_p_order' => 
    array (
      'columns' => 
      array (
        0 => 'p_order',
      ),
    ),
    'ind_cat_path' => 
    array (
      'columns' => 
      array (
        0 => 'cat_path',
      ),
    ),
  ),
  'comment' => app::get('b2c')->_('商品虚拟分类表'),
);
