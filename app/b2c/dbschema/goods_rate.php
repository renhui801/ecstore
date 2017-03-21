<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
* @table goods_rate;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['goods_rate']=array (
  'columns' => 
  array (
    'goods_1' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('关联商品ID'),
    ),
    'goods_2' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('被关联商品ID'),
    ),
    'manual' => 
    array (
      'type' => 
      array (
        'left' => app::get('b2c')->_('单向'),
        'both' => app::get('b2c')->_('关联'),
      ),
      'editable' => false,
      'comment' => app::get('b2c')->_('关联方式'),
    ),
    'rate' => 
    array (
      'type' => 'number',
      'default' => 1,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('关联比例'),
    ),
  ),
  'comment' => app::get('b2c')->_('相关商品表'),
);
