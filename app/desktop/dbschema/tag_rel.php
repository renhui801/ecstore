<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['tag_rel']=array (
  'columns' =>
  array (
    'tag_id' =>
    array (
      'type' => 'table:tag',
      'sdfpath' => 'tag/tag_id',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
      'comment' => app::get('desktop')->_('tag ID'),
    ),
    'rel_id' =>
    array (
      'type' => 'varchar(32)',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'editable' => false,
      'comment' => app::get('desktop')->_('对象ID'),
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
      'label' => app::get('desktop')->_('标签对象'),
      'editable' => false,
      'in_list' => true,
      'comment' => app::get('desktop')->_('标签对应的model(表)'),
    ),
    'last_modify' =>
    array (
      'type' => 'last_modify',
      'label' => app::get('b2c')->_('更新时间'),
      'width' => 110,
      'in_list' => true,
      'orderby' => true,
    ),
  ),
  'version' => '$Rev$',
  'comment' => app::get('desktop')->_('tag和对象关联表'),
);
