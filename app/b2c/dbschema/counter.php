<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['counter']=array (
  'columns' => 
  array (
    'counter_id' => array(
        'type' => 'number',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
        'label' => 'ID',
    ),
    'counter_type' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'label' => app::get('b2c')->_('类型'),
      'width' => 110,
      'editable' => false,
      'hidden' => true,
    ),
    'counter_name' => 
    array (
      'type' => 'varchar(30)',
      'label' => app::get('b2c')->_('计数器名'),
      'editable' => false,
      'is_title' => true,
    ),

  ),
    'index' =>
  array (
    'uni_value' =>
    array (
      'columns' =>
      array (
        0 => 'counter_type',
        1=> 'counter_name'
      ),
  ),
  ),
  'version' => '$Rev$',
  'comment' => app::get('b2c')->_('计数器表(废弃)'),  
);
