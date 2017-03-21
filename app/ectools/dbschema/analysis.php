<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

$db['analysis']=array (
  'columns' => 
  array (
    'id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      'comment' => app::get('ectools')->_('ectools统计ID'),
    ),
    'service' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'comment' => app::get('ectools')->_('对应的service'),
    ),
    'interval' => 
    array (
      'type' => 
          array (
            'hour' => 'hour',
            'day' => 'day',
            'comment' => app::get('ectools')->_('执行监控间隔时间'),
          ),
      'required' => true,
    ),
    'modify' => 
    array (
      'type' => 'time',
      'required' => true,
      'default' => 0,
      'comment' => app::get('ectools')->_('最后修改时间'),
    ),
  ),
  'engine' => 'innodb',
  'ignore_cache' => true,
  'comment' => app::get('ectools')->_('ectools app统计表'),
);
