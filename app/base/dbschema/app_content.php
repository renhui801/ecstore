<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['app_content']=array (
  'columns' => 
  array (
    'content_id'=>array(
      'type' => 'number',
      'pkey' => true,
      'extra' => 'auto_increment',
      'comment' => app::get('base')->_('序号'),      
    ),
    'content_type' => 
    array (
      'type' => 'varchar(80)',
      'required' => true,
      'width' => 100,
      'in_list' => true,
      'default_in_list' => true,
      'comment' => app::get('base')->_('service类型(service_category和service)'),
    ),
    'app_id' => 
    array (
      'type' => 'table:apps',
      'required' => true,
      'width' => 100,
      'in_list' => true,
      'default_in_list' => true,
      'comment' => app::get('base')->_('应用的app_id'),
    ),
    'content_name'=>array(
        'type'=>'varchar(80)',
        'comment' => app::get('base')->_('service category name - service id'),
    ),
    'content_title'=>array(
        'type'=>'varchar(100)',
        'is_title'=>true,
        'comment' => app::get('base')->_('optname'),
    ),
    'content_path'=>array(
        'type'=>'varchar(255)',
        'comment' => app::get('base')->_('class name只有type为service才有'),
    ),
	'ordernum' => 
    array (
      'type' => 'smallint(4)',
      'default' => 50,
      'label' => app::get('base')->_('排序'),
    ),
	'input_time' =>
    array (
      'type' => 'time',
      'label' => app::get('base')->_('加载时间'),
    ),
    'disabled'=>array(
        'type'=>'bool',
        'default'=>'false',
        'comment' => app::get('base')->_('是否有效'),
    )
  ),
  'index' => 
  array (
      'ind_content_type' => 
      array (
          'columns' => 
          array (
              0 => 'content_type',
          ),
      ),
  ),

  'version' => '$Rev: 44008 $',
  'comment' => app::get('base')->_('app资源信息表, 记录app的service信息'),
);
