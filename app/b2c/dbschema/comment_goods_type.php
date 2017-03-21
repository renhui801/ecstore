<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['comment_goods_type']=array (
  'columns' => 
  array (
    'type_id' => array (
        'type' => 'number',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
        'label' => 'ID',
        'width' => 110,
        'editable' => false,
        'default_in_list' => true,
    ),
    'name' => array (
        'type' => 'varchar(100)',
        'label' => app::get('b2c')->_('评论类型名称'),
         'required' => true,
    ),
    'addon' => 
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('序列化'),
    ),
  ),
   'engine' => 'innodb',
   'version' => '$Rev$',
   'comment' => app::get('b2c')->_('商品评论类型表'),  
);
