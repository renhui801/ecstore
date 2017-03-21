<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['archive_delivery_items']=array (
  'columns' => 
  array (
    'item_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'virtual_pkey' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('序号'),
    ),
    'delivery_id' => 
    array (
      'type' => 'table:archive_delivery:delivery_id',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('发货单号'),
    ),
    'order_item_id' => 
    array (
      'type' => 'table:order_items:item_id',
      'required' => false,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('发货明细订单号'),
    ),
    'item_type' => 
    array (
      'type' => 
      array (
        'goods' => app::get('b2c')->_('商品'),
        'gift' => app::get('b2c')->_('赠品'),
        'pkg' => app::get('b2c')->_('捆绑商品'),
        'adjunct'=>app::get('b2c')->_('配件商品'),
      ),
      'default' => 'goods',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('商品类型'),
    ),
    'product_id' => 
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('货品ID'),
    ),
    'product_bn' => 
    array (
      'type' => 'varchar(30)',
      'editable' => false,
      'is_title' => true,
      'comment' => app::get('b2c')->_('货品号'),
    ),
    'product_name' => 
    array (
      'type' => 'varchar(200)',
      'editable' => false,
      'comment' => app::get('b2c')->_('货品名称'),
    ),
    'number' => 
    array (
      'type' => 'float',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('发货数量'),
    ),
  ),
  'version' => '$Rev: 40654 $',
  'comment' => app::get('b2c')->_('发货/退货单明细表'),  
);
