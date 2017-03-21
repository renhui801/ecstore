<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['member_pwdlog']=array (
  'columns' => 
  array (
    'pwdlog_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => 'ID',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'member_id' => 
    array (
      'type' => 'table:members',
      'required' => true,  
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'comment' => app::get('b2c')->_('会员ID'),
    ),
    'secret' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'comment' => app::get('b2c')->_('临时秘钥'),
    ),
    'expiretime' => 
    array (
      'type' => 'time',
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
      'comment' => app::get('b2c')->_('过期时间'),
    ),
    'has_used' => 
    array (
      'type' => 'tinybool',
      'default' => 'N',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('是否使用过, 如果使用过将失效'),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 40654 $',
  'comment' => app::get('b2c')->_('忘记密码时临时秘钥表'),
  
);
