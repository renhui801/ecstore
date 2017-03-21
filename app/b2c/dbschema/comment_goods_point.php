<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['comment_goods_point']=array (
  'columns' =>
  array (
    'point_id' => array (
        'type' => 'number',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
        'label' => 'ID',
        'width' => 110,
        'editable' => false,
        'default_in_list' => true,
    ),
     'goods_point' => array (
        'type' => 'decimal(2,1)',
        'label' => app::get('b2c')->_('分数'),
    ),
    'comment_id' => array (
        'type' => 'table:member_comments',
        'label' => app::get('b2c')->_('评论ID'),
    ),
    'type_id' => array(
        'type' => 'table:comment_goods_type',
        'label' =>app::get('b2c')->_('评论类型'),
        'default' => 1,
        'required' => true,
    ),
    'member_id' => array(
        'type' => 'table:members',
        'label' => app::get('b2c')->_('会员ID'),
        'default' => 0,
    ),
    'goods_id' => array (
        'type' => 'table:goods',
        'label' => app::get('b2c')->_('商品ID'),
        'default' => 0,
        'required' => true,
    ),
    'display'=> array(
        'type'=> "enum('false', 'true')",
        'default' =>'false',
        'default_in_list' => true,
        'label' => app::get('b2c')->_('前台是否显示'),
    ),
   'addon' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('额外序列化信息'),
    ),
    'disabled' => array(
        'type'=> "enum('false', 'true')",
        'default' =>'false',
        'default_in_list' => true,
    ),
  ),
  'index' =>
  array (
      'ind_point_avg' =>
      array (
          'columns' =>
          array (
              0 => 'goods_id',
              1 => 'type_id',
          ),
      ),
    ),
   'engine' => 'innodb',
   'version' => '$Rev$',
   'comment' => app::get('b2c')->_('商品评分表'),
);
