<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['archive_orders_members']=array (
  'columns' =>
  array (
    'order_id' =>
    array (
      'type' => 'table:archive_orders:order_id',
      'required' => true,
      'default' => 0,
      'label' => app::get('b2c')->_('订单号'),
      'is_title' => true,
      'width' => 110,
      'searchtype' => 'has',
      'editable' => false,
      'filtertype' => 'custom',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
   'createtime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('下单时间'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'orderby' => true,
    ),
   'member_id' =>
    array (
      'type' => 'table:members',
      'label' => app::get('b2c')->_('会员用户名'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
  ),
  'index' =>
  array (
    'ind_createtime' =>
    array (
      'columns' =>
      array (
        0 => 'createtime',
      ),
    ),
  ),
  'version' => '$Rev: 42376 $',
  'comment' => app::get('b2c')->_('订单会员订单归档关联表'),
);
