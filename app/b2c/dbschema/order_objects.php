<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['order_objects']=array (
  'columns' => 
  array (
    'obj_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      'comment' => app::get('b2c')->_('订单商品对象ID'),
    ),
    'order_id' => 
    array (
      'type' => 'table:orders',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('订单ID'),
    ),
    'obj_type' => 
    array (
      'type' => 'varchar(50)',
      'default' => '',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('对象类型'),
    ),
    'obj_alias' => 
    array (
      'type' => 'varchar(100)',
      'default' => '',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('对象别名'),
    ),
    'goods_id' => 
    array (
      'type' => 'table:goods',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('商品ID'),
    ),
    'bn' => 
    array (
      'type' => 'varchar(40)',
      'editable' => false,
      'is_title' => true,
      'comment' => app::get('b2c')->_('品牌名'),
    ),
    'name' => 
    array (
      'type' => 'varchar(200)',
      'editable' => false,
      'comment' => app::get('b2c')->_('商品对象名字'),
    ),
    'price' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('商品对象单价'),
    ),
    'amount' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('商品对象总金额'),
    ),
    'quantity' => 
    array (
      'type' => 'float',
      'default' => 1,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('商品对象购买量'),
    ),
    'weight' => 
    array (
      'type' => 'number',
      'editable' => false,
      'comment' => app::get('b2c')->_('总重量'),
    ),
    'score' => 
    array (
      'type' => 'number',
      'editable' => false,
      'comment' => app::get('b2c')->_('获得积分'),
    ),
  ),
  'index' => 
  array (
    'ind_obj_bn' =>
    array (
        'columns' =>array(
            0 => 'bn',
        ),
        'type' => 'hash',
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 40912 $',
  'comment' => app::get('b2c')->_('订单商品对象表'),
);
