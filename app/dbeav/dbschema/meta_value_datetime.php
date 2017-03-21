<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['meta_value_datetime']=array (
  'columns' => 
  array (
    'mr_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'comment' => app::get('dbeav')->_('meta注册主表id'),
    ),
    'pk' => 
    array (
      'type' => 'number',
      'required' => true, 
      'pkey' => true,
      'comment' => app::get('dbeav')->_('对应数据行的主键值'), 
    ),
    'value' => 
    array (
      'type' => 'datetime NOT NULL default \'0000-00-00 00:00:00\'',
      'required' => true,
      'comment' => app::get('dbeav')->_('meta值'),
    ),
  ),
  'index' => 
  array (
    'ind_value' => 
    array (
      'columns' => 
      array (
        0 => 'value',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 40912 $',
  'comment' => app::get('dbeav')->_('meta具体类型表(datetime类型)'),
);
