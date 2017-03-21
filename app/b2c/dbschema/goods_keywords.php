<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['goods_keywords']=array (
  'columns' =>
  array (
    'goods_id' =>
    array (
      'type' => 'table:goods',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('商品ID'),
    ),
    'keyword' =>
    array (
      'type' => 'varchar(40)',
      'default' => '',
      'required' => true,
      'pkey' => true,
      'editable' => false,
      'is_title' => true,
      'comment' => app::get('b2c')->_('搜索关键字'),
    ),
    'refer' =>
    array (
      'type' => 'varchar(255)',
      'default' => '',
      'required' => false,
      'editable' => false,
      'comment' => app::get('b2c')->_('来源'),
    ),
    'res_type' =>
    array (
      'type' => 'enum(\'goods\',\'article\')',
      'default' => 'goods',
      'required' => true,
      'pkey' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('搜索结果类型'),
    ),
    'last_modify' =>
    array (
      'type' => 'last_modify',
      'label' => app::get('b2c')->_('更新时间'),
      'width' => 110,
      'in_list' => true,
      'orderby' => true,
    ),
  ),
  'version' => '$Rev: 40654 $',
  'comment' => app::get('b2c')->_('商品搜索关键字表'),
);
