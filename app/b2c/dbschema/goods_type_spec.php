<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['goods_type_spec']=array (
  'columns' =>
  array (
    'spec_id' =>
    array (
      'type' => 'table:specification',
      'pkey' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('规格ID'),
    ),
    'type_id' =>
    array (
      'type' => 'table:goods_type',
      'default' => 0,
      'pkey' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('类型ID'),
    ),
    'spec_style' =>
    array (
      'type' =>
      array (
        'select' => app::get('b2c')->_('下拉'),
        'flat' => app::get('b2c')->_('平面'),
        'disabled' => app::get('b2c')->_('禁用'),
      ),
      'default' => 'flat',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('渐进式搜索时的样式'),
  ),
  'ordernum'=>array(
      'type'=>'number',
      'default'=>0,
      'required'=>true,
      'editable'=>false,
  ),
  ),
  'comment' => app::get('b2c')->_('商品规格表'),
  'version' => '$Rev: 40912 $',
);
