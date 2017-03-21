<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['brand']=array (
  'columns' =>
  array (
    'brand_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('b2c')->_('品牌id'),
      'width' => 150,
      'comment' => app::get('b2c')->_('品牌id'),
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),
    'brand_name' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('品牌名称'),
      'width' => 180,
      'is_title' => true,
      'required' => true,
      'comment' => app::get('b2c')->_('品牌名称'),
      'editable' => true,
      'searchtype' => 'has',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'brand_url' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('品牌网址'),
      'width' => 350,
      'comment' => app::get('b2c')->_('品牌网址'),
      'editable' => true,
      'searchtype' => 'has',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'brand_desc' =>
    array (
      'type' => 'longtext',
      'comment' => app::get('b2c')->_('品牌介绍'),
      'editable' => false,
      'label' => app::get('b2c')->_('品牌介绍'),
    ),
    'brand_logo' =>
    array (
      'type' => 'varchar(255)',
      'comment' => app::get('b2c')->_('品牌图片标识'),
      'editable' => false,
      'label' => app::get('b2c')->_('品牌图片标识'),
     'in_list' => false,
      'default_in_list' => false,
    ),
    'brand_keywords' =>
    array (
      'type' => 'longtext',
      'label' => app::get('b2c')->_('品牌别名'),
      'width' => 150,
      'comment' => app::get('b2c')->_('品牌别名'),
      'editable' => false,
      'searchtype' => 'has',
       'in_list' => true,
      'default_in_list' => true,
    ),
    'brand_setting' =>
    array(
        'type' => 'serialize',
        'label' => app::get('b2c')->_('品牌设置'),
        'deny_export' => true,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'comment' => app::get('b2c')->_('失效'),
      'editable' => false,
      'label' => app::get('b2c')->_('失效'),
      'in_list' => false,
      'deny_export' => true,
    ),
    'ordernum' =>
    array (
      'type' => 'number',
      'label' => app::get('b2c')->_('排序'),
      'width' => 150,
      'comment' => app::get('b2c')->_('排序'),
      'editable' => true,
      'in_list' => true,
    ),
    'last_modify' =>
    array (
      'type' => 'last_modify',
      'label' => app::get('b2c')->_('更新时间'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'orderby' => true,
    ),
  ),
  'index' =>
  array (
    'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
      ),
    ),
    'ind_ordernum' =>
    array (
      'columns' =>
      array (
        0 => 'ordernum',
      ),
    ),
  ),
  'version' => '$Rev: 40654 $',
  'comment' => app::get('b2c')->_('商品品牌表'),
);
