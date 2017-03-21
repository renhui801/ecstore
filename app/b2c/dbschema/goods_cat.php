<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['goods_cat']=array (
  'columns' =>
  array (
    'cat_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('b2c')->_('分类ID'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'parent_id' =>
    array (
      'type' => 'number',
      'label' => app::get('b2c')->_('分类ID'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'parent_id'=>true,
    ),
    'cat_path' =>
    array (
      'type' => 'varchar(100)',
      'default' => ',',
      'label' => app::get('b2c')->_('分类路径(从根至本结点的路径,逗号分隔,首部有逗号)'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'is_leaf' =>
    array (
      'type' => 'bool',
      'required' => true,
      'default' => 'false',
      'label' => app::get('b2c')->_('是否叶子结点（true：是；false：否）'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'type_id' =>
    array (
      'type' => 'mediumint',
      'label' => app::get('b2c')->_('类型序号'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'cat_name' =>
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'is_title' => true,
      'default' => '',
      'label' => app::get('b2c')->_('分类名称'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'gallery_setting' =>
    array(
        'type' => 'serialize',
        'label' => app::get('b2c')->_('商品分类设置'),
        'deny_export' => true,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'label' => app::get('b2c')->_('是否屏蔽（true：是；false：否）'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'p_order' =>
    array (
      'type' => 'number',
      'label' => app::get('b2c')->_('排序'),
      'width' => 110,
      'editable' => false,
      'default' => 0,
      'in_list' => true,
    ),
    'goods_count' =>
    array (
      'type' => 'number',
      'label' => app::get('b2c')->_('商品数'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'tabs' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('子视图'),
    ),
    'finder' =>
    array (
      'type' => 'longtext',
      'label' => app::get('b2c')->_('渐进式筛选容器'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'addon' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('附加项'),
    ),
    'child_count' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('子类别数量'),
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
  'index' =>
  array (
    'ind_cat_path' =>
    array (
      'columns' =>
      array (
        0 => 'cat_path',
      ),
    ),
    'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
      ),
    ),
    'ind_last_modify' =>
    array (
      'columns' =>
      array (
        0 => 'last_modify',
      ),
    ),
  ),
  'version' => '$Rev: 41329 $',
  'comment' => app::get('b2c')->_('类别属性值有限表'),
);
