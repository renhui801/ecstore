<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['member_point']=array (
  'columns' => 
  array (
    'id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      'comment' => app::get('b2c')->_('积分日志ID'),
    ),
    'member_id' => 
    array (
      'type' => 'table:members',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('会员ID'),
    ),
    'point' => 
    array (
      'type' => 'int(10)',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('积分阶段总结'),
    ),
     'change_point' => 
    array (
      'type' => 'int(10)',
      'required' => true,
      'default' => '0',
      'editable' => false,
      'comment' => app::get('b2c')->_('改变积分'),
    ),
    'consume_point' => 
    array (
      'type' => 'int(10)',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('单笔积分消耗的积分值'),
    ),
    'addtime' => 
    array (
      'type' => 'time',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('添加时间'),
    ),
    'expiretime' => 
    array (
      'type' => 'time',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('过期时间'),
    ),
    'reason' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => '',
      'editable' => false,
      'is_title' => true,
      'comment' => app::get('b2c')->_('理由'),
    ),
    'remark' => 
    array (
      'type' => 'varchar(100)',
      'required' => false,
      'default' => '',
      'editable' => false,
      'is_title' => true,
      'comment' => app::get('b2c')->_('备注'),
    ),
    'related_id' => 
    array (
      'type' => 'bigint unsigned',
      'editable' => false,
      'comment' => app::get('b2c')->_('积分关联对象ID'),
    ),
    'type' => 
    array (
      'type' => 'tinyint(1)',
      'required' => true,
      'default' => 1,
      'editable' => false,
      'comment' => app::get('b2c')->_('操作类型'),
    ),
    'operator' => 
    array (
      'type' => 'varchar(50)',
      'editable' => false,
      'comment' => app::get('b2c')->_('操作员ID'),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 43105 $',
  'comment' => app::get('b2c')->_('积分历史日志表'),  
);
