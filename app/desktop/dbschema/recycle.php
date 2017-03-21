<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['recycle']=array (
  'columns' => 
  array (
    'item_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      'comment' => app::get('desktop')->_('回收站表ID'),
    ),
    'item_title' => 
    array (
      'type' => 'varchar(200)',
      'label'=>app::get('desktop')->_('名称'),
      'required' => false,
      'is_title'=>true,
      'in_list'=>true,
      'width'=>200,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'default_in_list'=>true,
    ),
    'item_type'=>array(
      'label'=>app::get('desktop')->_('类型'),
      'type' => 'varchar(80)',
      'required' => true,
      'in_list'=>true,
      'width'=>100,
      'filtertype' => 'yes',
      'filterdefault' => true,

      'default_in_list'=>true,
    ),
    'app_key'=>array(
      'label'=>app::get('desktop')->_('应用'),
      'type' => 'varchar(80)',
      'required' => true,
      'in_list'=>true,
      'width'=>100,
      'default_in_list'=>true,
      'comment' => app::get('desktop')->_('app(应用)ID'),
    ),
    'drop_time'=>array(
      'type' => 'time',
      'label'=>app::get('desktop')->_('删除时间'),
      'required' => true,
      'in_list'=>true,
      'width'=>150,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'default_in_list'=>true,
    ),
    'item_sdf'=>array(
      'type' => 'serialize',
      'required' => true,
      'comment' => app::get('desktop')->_('记录值,sdf序列化'),
    ),
    'permission'=>array(
      'type'=>'varchar(80)',
      'label'=>app::get('desktop')->_('相关权限'),
      'in_list'=>false,
      'default_in_list'=>false,
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 40912 $',
  'comment' => app::get('desktop')->_('回收站表'),
);

//需要id从大到小的执行
