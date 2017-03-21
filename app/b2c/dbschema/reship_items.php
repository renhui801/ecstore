<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['reship_items']=array (
  'columns' => 
  array (
    'item_id' => 
    array (
      'type' => 'int unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      'comment' => app::get('b2c')->_('发/退货单明细ID'),
    ),
    'reship_id' => 
    array (
      'type' => 'table:reship',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('发/退货单ID'),
    ),
	'order_item_id' => 
    array (
      'type' => 'table:order_items',
      'required' => false,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('订单明细ID'),      
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
      'comment' => app::get('b2c')->_('退/换货商品类型'),
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
      'comment' => app::get('b2c')->_('货品品牌名'),
    ),
    'product_name' => 
    array (
      'type' => 'varchar(200)',
      'editable' => false,
      'comment' => app::get('b2c')->_('货品名'),
    ),
    'number' => 
    array (
      'type' => 'float',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('退/换货数量'),
    ),
  ),
  'version' => '$Rev: 40654 $',
  'comment' => app::get('b2c')->_('发货/退货单明细表'),  
);
