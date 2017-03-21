<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
* @table goods_lv_price;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/
$db['goods_lv_price']=array (
  'columns' =>
  array (
    'goods_id' =>
    array(
        'type' => 'table:goods',
        'default' => 0,
        'required' => true,
        'pkey' => true,
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
    'level_id' =>
    array (
      'type' => 'table:member_lv',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('会员等级ID'),
    ),
    'price' =>
    array (
      'type' => 'money',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('会员价'),
    ),
  ),
  'comment' => app::get('b2c')->_('商品会员等级价格'),
  'index' =>
  array (
    'index_goods_id' =>
    array (
      'columns' =>
      array (
        0 => 'goods_id',
      ),
    ),
    'index_product_id' =>
    array (
      'columns' =>
      array (
        0 => 'product_id',
      ),
    ),
    'index_level_id' =>
    array (
      'columns' =>
      array (
        0 => 'level_id',
      ),
    ),
  ),
);
