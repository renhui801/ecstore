<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['image']=array (
  'columns' => 
  array (
    'image_id' => 
    array (
      'type' => 'char(32)',
      'label'=>app::get('image')->_('图片ID'),
      'required' => true,
      'pkey' => true,
      'width'=>250,
      'in_list'=>true,
      'default_in_list'=>false,
    ),
    'storage'=>array(
      'label'=>app::get('image')->_('存储引擎'),
      'type' => 'varchar(50)',
      'default' => 'filesystem',
      'required' => true,
      'in_list'=>true,
      'width'=>100,
      'default_in_list'=>false,
    ),
    'image_name'=>array(
      'label'=>app::get('image')->_('图片名称'),
      'type' => 'varchar(50)',
      'required' => false,
      'width'=>100,
      'default_in_list'=>true,
    ),
    
    'ident'=>array(
      'type' => 'varchar(200)',
      'required' => true,
    ),
    'url'=>array(
      'label'=>app::get('image')->_('网址'),
      'type'=>'varchar(200)',
      'required' => true,
      'width'=>300,
      'in_list'=>false,
    ),
    'l_ident'=>array(
      'type' => 'varchar(200)',
	  'comment' => app::get('image')->_('大图唯一标识'),
    ),
    'l_url'=>array(
      'type' => 'varchar(200)',
	  'comment' => app::get('image')->_('大图URL地址'),
    ),
    'm_ident'=>array(
      'type' => 'varchar(200)',
	  'comment' => app::get('image')->_('中图唯一标识'),
    ),
    'm_url'=>array(
      'type' => 'varchar(200)',
	  'comment' => app::get('image')->_('中图URL地址'),
    ),
    's_ident'=>array(
      'type' => 'varchar(200)',
	  'comment' => app::get('image')->_('小图唯一标识'),
    ),
    's_url'=>array(
      'type' => 'varchar(200)',
	  'comment' => app::get('image')->_('小图URL地址'),
    ),    
    'width'=>array(
       'label'=>app::get('image')->_('宽度'),
      'type' => 'number',
      'in_list'=>true,
      'default_in_list'=>false,
    ),
    'height'=>array(
      'label'=>app::get('image')->_('高度'),
      'type' => 'number',
      'in_list'=>true,
      'default_in_list'=>false,
    ),
    'watermark'=>array(
        'type'=>'bool',
        'default' => 'false',
        'label'=>app::get('image')->_('有水印'),
        'in_list'=>true,
        'default_in_list'=>true,
    ),
    'last_modified' => array (
      'label'=>app::get('image')->_('更新时间'),
      'type' => 'last_modify',
      'width'=>180,
      'required' => true,
      'default' => 0,
      'editable' => false,
      'in_list'=>true,
      'default_in_list'=>true,
      'filtertype' => 'yes'
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 40913 $',
  'comment' => app::get('image')->_('图片表'),
);
