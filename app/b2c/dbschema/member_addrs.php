<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['member_addrs']=array (
  'columns' =>
  array (
    'addr_id' =>
    array (
      'type' => 'int(10)',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      'comment' => app::get('b2c')->_('会员地址ID'),
    ),
    'member_id' =>
    array (
      'type' => 'table:members',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('会员ID'),
    ),
    'name' =>
    array (
      'is_title' => true,
      'type' => 'varchar(50)',
      'editable' => false,
      'comment' => app::get('b2c')->_('会员地址名称'),
    ),
    'lastname' =>
    array (
      'type' => 'varchar(50)',
      'editable' => false,
      'comment' => app::get('b2c')->_('姓名'),
    ),
    'firstname' =>
    array (
      'type' => 'varchar(50)',
      'editable' => false,
      'comment' => app::get('b2c')->_('姓名'),
    ),
    'area' =>
    array (
      'type' => 'region',
      'editable' => false,
      'comment' => app::get('b2c')->_('地区'),
    ),
    'addr' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'comment' => app::get('b2c')->_('地址'),
    ),
    'zip' =>
    array (
      'type' => 'varchar(20)',
      'sdfpath'=>'zipcode',
      'editable' => false,
      'comment' => app::get('b2c')->_('邮编'),
    ),
    'tel' =>
    array (
      'type' => 'varchar(50)',
      'sdfpath' => 'phone/telephone',
      'editable' => false,
      'comment' => app::get('b2c')->_('电话'),
    ),
    'mobile' =>
    array (
        'type' => 'varchar(50)',
        'sdfpath' => 'phone/mobile',
        'editable' => false,
        'comment' => app::get('b2c')->_('手机'),
    ),
    'day'=>
    array(
        'type'=>'varchar(255)',
        'default' => app::get('b2c')->_('任意日期'),
        'comment' => app::get('b2c')->_('上门日期'),
    ),
    'time'=>
    array(
        'type'=>'varchar(255)',
        'default' => app::get('b2c')->_('任意时间段'),
        'comment' => app::get('b2c')->_('上门时间'),
    ),
    'def_addr' =>
    array (
      'type' => 'tinyint(1)',
      'sdfpath' => 'default',
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('默认地址'),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 42752 $',
  'comment' => app::get('b2c')->_('会员地址表'),
);
