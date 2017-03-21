<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['type_brand']=array (
  'columns' => 
  array (
    'type_id' => 
    array (
      'type' => 'table:goods_type',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('商品类型ID'),
    ),
    'brand_id' => 
    array (
      'type' => 'table:brand',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('品牌ID'),
    ),
    'brand_order' => 
    array (
      'type' => 'number',
      'editable' => false,
      'comment' => app::get('b2c')->_('排序'),
    ),
  ),
  'version' => '$Rev: 40654 $',
  'comment' => app::get('content')->_('类型和品牌关联表'),
);
