<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/**
* @table order_coupon_user;
*
* @package Schemas
* @version $
* @copyright 2010 ShopEx
* @license Commercial
*/

$db['order_ref']=array (
  'columns' =>
  array (
    'id' =>
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => 'ID',
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ), 
    'order_id' =>
    array (
      'type' => 'table:orders@b2c',
      'required' => true,
      'default' => 0,
      'label' => app::get('giftpackage')->_('订单号'),
      'searchtype' => 'has',
      'filtertype' => 'yes',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'giftpackage_id' =>
    array (
      'type' => 'table:giftpackage',
      'label' => app::get('giftpackage')->_('礼包id'),
      'searchtype' => 'has',
      'filtertype' => 'yes',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'quantity' => 
    array (
      'type' => 'float unsigned',
      'label' => app::get('giftpackage')->_('数量'),
      'editable' => false,
      'in_list' => true,
    ),
    'member_id' => 
    array (
      'type' => 'table:account@pam',
      'label' => app::get('giftpackage')->_('会员 id'),
      'editable' => false,
    ),
  ),
  'comment' => app::get('giftpackage')->_('礼包购买记录'),
);
