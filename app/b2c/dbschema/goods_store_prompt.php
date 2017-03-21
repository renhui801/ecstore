<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['goods_store_prompt']=array (
  'columns' =>
  array (
    'prompt_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'extra' => 'auto_increment',
      'pkey' => true,
      'comment' => app::get('b2c')->_('商品库存提示规则ID'),
    ),
    'order_by' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'label' => app::get('b2c')->_('排序'),
      'in_list' => true,
      'default_in_list' => true,
    ),
    'name' =>
    array (
      'type' => 'varchar(100)',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'label' => app::get('b2c')->_('名称'),
      'in_list' => true,
      'default_in_list' => true,
    ),
    'default' =>
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'label' => app::get('b2c')->_('是否默认'),
      'width' => 110,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'values' =>
    array(
      'type' => 'longtext',
      'comment' => app::get('b2c')->_('规则值序列化'),
    ),
  ),
);
