<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['shop']=array (
  'columns' => 
  array (
    'shop_id' => 
    array (
        'type' => 'int(8)',
        'required' => true,
        'pkey' => true,
        'label' => 'id',
        'editable' => false,
        'extra' => 'auto_increment',
        'comment' => app::get('b2c')->_('绑定联通表'),
    ),
    'name' =>
    array (
      'type' => 'varchar(255)',
      'required' => true,
      'label' => app::get('b2c')->_('店铺名称'),
      'editable' => false,
      'searchtype' => 'has',
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'is_title' => true,
    ),
    'node_id' =>
    array (
      'type' => 'varchar(32)',
      'label' => app::get('b2c')->_('对方节点id'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'is_title' => true,
      'comment' => app::get('b2c')->_('店铺对应的中心证书的节点'),
    ),
    'node_type' =>
    array (
      'type' => 'varchar(128)',
      'label' => app::get('b2c')->_('对方节点类型'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'is_title' => true,
    ),
    'status' => 
    array(
      'type' => 
      array (
        'bind' => app::get('b2c')->_('绑定'),
        'unbind' => app::get('b2c')->_('未绑定'),
      ),
      'default' => 'unbind',
      'label' => app::get('b2c')->_('绑定状态'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'is_title' => true,
    ),
    'node_apiv' =>
    array (
      'type' => 'varchar(8)',
      'label' => app::get('b2c')->_('对方api版本'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'is_title' => true,
    ),
  ),
  
  'comment' => app::get('b2c')->_('绑定联通表'),
  'engine' => 'innodb',
  'version' => '$Rev:  $',
);
