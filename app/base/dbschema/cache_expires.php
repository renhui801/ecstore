<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['cache_expires']=array (
  'columns' => 
  array (
    'type' => array(
        'type' => 'varchar(20)',
        'pkey' => true,
        'required' => true,
        'comment' => app::get('base')->_('类型,目前两种conf和db两种'),
    ),
    'name' => array(
        'type'=>'varchar(255)',
        'pkey' => true,
        'required'=>true,
        'comment' => app::get('base')->_('缓存名称'),
    ),
    'expire' => array(
        'type'=>'time',
        'required' => true,
        'comment' => app::get('base')->_('最后更新时间'),
    ),
    'app' => array(
        'type'=>'varchar(50)',
        'required'=>true,
        'comment' => app::get('base')->_('应用ID'),
    ),
  ),
  'index' => 
  array (
    'ind_name' => 
    array (
      'columns' => 
      array (
        0 => 'name',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 41137 $',
  'ignore_cache' => true,
  'comment' => app::get('base')->_('cache的过期判断-全页缓存'),
);
