<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/**
* @table member_coupon;
*
* @package Schemas
* @version $
* @copyright 2010 ShopEx
* @license Commercial
*/

$db['member_coupon']=array (
  'columns' => 
  array (
    'memc_code' => 
    array (
      'type' => 'varchar(255)',
      'required' => true,
      'default' => '',
      'pkey' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('优惠券code'),
    ),
    'cpns_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('会员优惠券ID'),
    ),
    'member_id' => 
    array (
      'type' => 'table:members',
      'required' => true,
      'default' => 0,
      'pkey'=>true,
      'editable' => false,
      'comment' => app::get('b2c')->_('会员ID'),
    ),
    'memc_gen_orderid' => 
    array (
      'type' => 'varchar(15)',
      'editable' => false,
      'comment' => app::get('b2c')->_('优惠券产生订单号'),
    ),
    'memc_source' => 
    array (
      'type' => 
      array (
        'a' => app::get('b2c')->_('全体优惠券'),
        'b' => app::get('b2c')->_('会员优惠券'),
        'c' => app::get('b2c')->_('ShopEx优惠券'),
      ),
      'default' => 'a',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('优惠券来源(保留)'),
    ),
    'memc_enabled' => 
    array (
      'type' => 'bool',
      'default' => 'true',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('留做后用, 可单独取消某些已发放出的优惠券'),
    ),
    'memc_used_times' => 
    array (
      'type' => 'mediumint',
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('已使用次数'),
    ),
    'memc_gen_time' => 
    array (
      'type' => 'time',
      'editable' => false,
      'comment' => app::get('b2c')->_('优惠券产生时间'),
    ),
    'disabled' => 
    array (
      'type' => 'bool',
      'default' => 'false',
      'comment' => app::get('b2c')->_('无效'),
      'editable' => false,
      'label' => app::get('b2c')->_('无效'),
      'in_list' => false,
    ),
    'memc_isvalid' => 
    array (
      'type' => 'bool',
      'default' => 'true',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('会员优惠券是否当前可用'),
    ),
  ),
  'index' =>
  array (
    'ind_memc_gen_orderid' =>
    array (
      'columns' =>
      array (
        0 => 'memc_gen_orderid',
      ),
    ),
  ),
  'comment' => app::get('b2c')->_('用户优惠券表'),
);
