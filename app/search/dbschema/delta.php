<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$db['delta']=array (
  'columns' =>
  array (
    'id' =>
    array (
      'type' => 'bigint unsigned',
      'pkey' => true,
      'label' => 'ID',
      'required' => true,
    ),
    'index_name' =>
    array (
      'type' => 'varchar(50)',
      'pkey' => true,
      'is_title'=>true,
      'label'=>app::get('site')->_('索引名称'),
      'width'=>'200',
      'required' => true,
      'in_list'=>true,
      'default_in_list'=>true,
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
    'ind_last_modify' =>
    array (
      'columns' =>
      array (
        0 => 'last_modify',
      ),
    ),
  ),
  'version' => '$Rev: 40918 $',
);
