<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['syscache_resources']=array (
  'columns' => 
  array (
    'id' => array(
        'type' => 'number',
        'pkey' => true,
        'extra' => 'auto_increment',
        'comment' => app::get('base')->_('序号'),
    ),
    'type' => array(
        'type'=>'varchar(255)',
        'required'=>true,
        'comment' => app::get('base')->_('kvstore类型'),
    ),
    'key' => array(
        'type'=>'varchar(255)',
        'required'=>true,
        'comment' => app::get('base')->_('kvstore存储的键值'),
    ),
    'value' => array(
        'type'=>'serialize',
        'comment' => app::get('base')->_('kvstore存储值'),
    ),
    'dateline' => array(
        'type'=>'time',
        'comment' => app::get('base')->_('存储修改时间'),
    ),
    'ttl' => array(
        'type'=>'time',
        'default' => 0,
        'comment' => app::get('base')->_('过期时间,0代表不过期'),
    ),
  ),
  'index' => 
  array (
    'ind_prefix' => 
    array (
      'columns' => 
      array (
        0 => 'type',
      ),
    ),
    'ind_key' => 
    array (
      'columns' => 
      array (
        0 => 'key',
      ),
    ),
  ),
  'ignore_cache' => true,
  'comment' => app::get('base')->_('kvstore存储表'),
);
