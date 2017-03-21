<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['goods_spec_index']=array (
  'columns' =>
  array (
    'type_id' =>
    array (
      'type' => 'table:goods_type',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('商品类型ID'),
    ),
    'spec_id' =>
    array (
      'type' => 'table:specification',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('规格ID'),
    ),
    'spec_value_id' =>
    array (
      'type' => 'table:spec_values',
      'default' => 0,
      'required' => true,
      'pkey' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('规格值ID'),
    ),
    'goods_id' =>
    array (
      'type' => 'table:goods',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('商品ID'),
    ),
    'product_id' =>
    array (
      'type' => 'table:products',
      'default' => 0,
      'required' => true,
      'pkey' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('货品ID'),
    ),
    'last_modify' =>
    array (
      'type' => 'last_modify',
      'label' => app::get('b2c')->_('更新时间'),
      'width' => 110,
      'in_list' => true,
      'orderby' => true,
    ),
  ),
  'comment' => app::get('b2c')->_('商品规格索引表'),
  'version' => '$Rev: 40654 $',
);
