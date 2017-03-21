<?php
/**
* @table sell_logs;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['sell_logs']=array (
  'columns' =>
  array (
    'log_id' =>
    array (
      'type' => 'mediumint(8)',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      'comment' => app::get('b2c')->_('销售日志ID'),
    ),
    'member_id' =>
    array (
      'type' => 'table:members',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('会员ID'),
    ),
    'order_id' =>
    array (
      'type' => 'bigint unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('订单号'),
    ),
    'name' =>
    array (
      'type' => 'varchar(50)',
      'default' => '',
      'editable' => false,
      'comment' => app::get('b2c')->_('会员名称'),
    ),
    'price' =>
    array (
      'type' => 'money',
      'default' => '0',
      'editable' => false,
      'comment' => app::get('b2c')->_('货品价格'),
    ),
    'product_id' =>
    array (
      'type' => 'mediumint(8)',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('货品ID'),
    ),
    'goods_id' =>
    array (
      'type' => 'table:goods',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('商品ID'),
    ),
    'product_name' =>
    array (
      'type' => 'varchar(200)',
      'default' => '',
      'editable' => false,
      'comment' => app::get('b2c')->_('货品名称'),
    ),
    'spec_info' =>
    array (
      'type' => 'varchar(200)',
      'default' => '',
      'editable' => false,
      'comment' => app::get('b2c')->_('商品规格'),
    ),
    'number' =>
    array (
      'type' => 'number',
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('商品购买数量'),
    ),
    'createtime' =>
    array (
      'type' => 'time',
      'editable' => false,
      'comment' => app::get('b2c')->_('记录创建时间'),
    ),
  ),
  'index' =>
  array (
    'ind_goods_id' =>
    array (
      'columns' =>
      array (
        0 => 'member_id',
        1 => 'product_id',
        2 => 'goods_id',
      ),
    ),
  ),
  'comment' => app::get('b2c')->_('商品销售记录表'),
);
