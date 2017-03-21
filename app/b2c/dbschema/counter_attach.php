<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['counter_attach']=array (
  'columns' => 
  array (
    'counter_value' => 
    array (
      'type' => 'int unsigned',
      'label' => app::get('b2c')->_('计数值'),
      'width' => 110,
      'default' => 0,
      'editable' => true,
      'in_list' => true,
    ),

   'attach_id' => 
    array (
      'type' => 'number',
      'label' => app::get('b2c')->_('关联id'),
      'width' => 110,
      'pkey' => true,
      'editable' => true,
      'in_list' => true,
    ),
    'counter_id' => array(
        'type' => 'table:counter',
        'required' => true,
        'pkey' => true,
        'label' => '计数器ID',
    ),
  ),
    'index' =>
  array (
    'uni_value' =>
    array (
      'columns' =>
      array (
        0 => 'counter_value',
      ),
  ),
  ),
  'version' => '$Rev$',
  'comment' => app::get('b2c')->_('计数器值关联表(废弃)'),  
);
