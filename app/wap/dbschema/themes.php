<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['themes']=array (
  'columns' => 
  array (
    'theme' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => '',
      'pkey' => true,
      'editable' => false,
      'is_title' => true,
      'label'=>app::get('wap')->_('目录'),
      'width'=>'90',
      'in_list'=>true,
      'default_in_list'=>true,
      'comment' => app::get('wap')->_('主题唯一英文名称'),
    ),
    'name' => 
    array (
      'type' => 'varchar(50)',
      'editable' => false,
      'is_title'=>true,
      'label'=>app::get('wap')->_('模板名称'),
      'width'=>'200',
      'in_list'=>true,
      'default_in_list'=>true,
      'comment' => app::get('wap')->_('主题名称'),
    ),
    'stime' => 
    array (
      'type' => 'int unsigned',
      'editable' => false,
      'comment' => app::get('wap')->_('开始使用时间'),
    ),
    'author' => 
    array (
      'type' => 'varchar(50)',
      'editable' => false,
      'label'=>app::get('wap')->_('作者'),
      'width'=>'100',
      'in_list'=>true,
      'default_in_list'=>true,
    ),
    'site' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
      'label'=>app::get('wap')->_('作者网址'),
      'width'=>'200',
      'in_list'=>true,
      'default_in_list'=>true,
    ),
    'version' => 
    array (
      'type' => 'varchar(50)',
      'editable' => false,
      'label'=>app::get('wap')->_('版本'),
      'width'=>'80',
      'in_list'=>true,
      'default_in_list'=>true,
    ),
    'info' => 
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'comment' => app::get('wap')->_('详细说明'),
    ),
    'config' => 
    array (
      'type' => 'serialize',
      'editable' => false,
      'comment' => app::get('wap')->_('配置信息'),
    ),
    'update_url' => 
    array (
      'type' => 'varchar(100)',
      'editable' => false,
      'comment' => app::get('wap')->_('更新网址'),
    ),
    'is_used' =>
    array (
      'type' => 'bool',
      'editable' => false,
      'default' => 'false',
      'comment' => app::get('wap')->_('是否启用'),
    ),
  ),
  'version' => '$Rev: 40918 $',
    'unbackup' => true,
  'comment' => app::get('wap')->_('模板表'),
);
