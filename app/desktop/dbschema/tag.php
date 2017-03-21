<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['tag']=array (
  'columns' =>
  array (
    'tag_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      'comment' => app::get('desktop')->_('tag ID'),
    ),
    'tag_name' =>
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => '',
      'label' => app::get('desktop')->_('标签名'),
      'width' => 200,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
      'is_title' => true,
    ),
    'tag_mode' =>
    array (
      'type' =>
      array (
        'normal' => app::get('desktop')->_('普通标签'),
        'filter' => app::get('desktop')->_('自动标签'),
      ),
      'default' => 'normal',
      'label' => app::get('desktop')->_('标签类型'),
      'required' => true,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'app_id' =>
    array (
      'type' => 'varchar(32)',
      'label' => app::get('desktop')->_('应用'),
      'required' => true,
      'width' => 100,
      'in_list' => true,
      'comment' => app::get('desktop')->_('app(应用)ID'),
    ),
    'tag_type' =>
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => '',
      'label' => app::get('desktop')->_('标签对应的model(表)'),
      'editable' => false,
      'in_list' => true,
    ),
    'tag_abbr' =>
    array (
      'type' => 'varchar(150)',
      'required' => true,
      'default' => '',
      'label' => app::get('desktop')->_('标签备注'),
      'editable' => false,
      'in_list' => true,
    ),
    'tag_bgcolor' =>
    array (
      'type' => 'varchar(7)',
      'required' => true,
      'default' => '',
      'label' => app::get('desktop')->_('标签背景颜色'),
      'editable' => false,
      'in_list' => true,
    ),
    'tag_fgcolor' =>
    array (
      'type' => 'varchar(7)',
      'required' => true,
      'default' => '',
      'label' => app::get('desktop')->_('标签字体颜色'),
      'editable' => false,
      'in_list' => true,
    ),
    'tag_filter' =>
    array (
      'type' => 'varchar(255)',
      'required' => true,
      'default' => '',
      'label' => app::get('desktop')->_('标签条件'),
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),
    'rel_count' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => app::get('desktop')->_('关联的个数'),
    ),
  ),
  'index' =>
  array (
    'ind_type' =>
    array (
      'columns' =>
      array (
        0 => 'tag_type',
      ),
    ),
    'ind_name' =>
    array (
      'columns' =>
      array (
        0 => 'tag_name',
      ),
    ),
  ),
  'version' => '$Rev: 42201 $',
  'comment' => app::get('desktop')->_('finder tag(标签)表'),
);
