<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['link']=array (
 'columns' => array (
    'refer_id' =>
        array (
          'type' => 'varchar(50)',
          'label' => app::get('bdlink')->_('首次来源ID'),
          'width' => 75,
          'pkey' => true,
          'in_list' => true,
        ),
    'refer_url' =>
        array (
          'type' => 'varchar(200)',
          'label' => app::get('bdlink')->_('首次来源URL'),
          'width' => 150,
          'in_list' => true,
        ),
    'refer_time' =>
        array (
          'type' => 'time',
          'label' => app::get('bdlink')->_('首次来源时间'),
          'width' => 110,
          'in_list' => true,
        ),
    'c_refer_id' =>
        array (
          'type' => 'varchar(50)',
          'label' => app::get('bdlink')->_('本次来源ID'),
          'width' => 75,
          'in_list' => true,
        ),
    'c_refer_url' =>
        array (
          'type' => 'varchar(200)',
          'label' => app::get('bdlink')->_('本次来源URL'),
          'width' => 150,
          'in_list' => true,
        ),
    'c_refer_time' =>
        array (
          'type' => 'time',
          'label' => app::get('bdlink')->_('本次来源时间'),
          'width' => 110,
          'in_list' => true,
        ),
    'target_id' => 
        array(
          'type' => 'varchar(32)',
          'required' => true,
          'width' => 100,
          'pkey' => true,
          'comment' => app::get('bdlink')->_('target_type所关联的数据ID'),
        ),
    'target_type' => 
        array(
          'type' => 'varchar(50)',
          'required' => true,
          'label' => app::get('bdlink')->_('完成指标类型所关联的ID'),
          'width' => 100,
          'pkey' => true,
          'in_list' => true,
          'default_in_list' => true,
          'comment' => app::get('bdlink')->_('推广成功类型, 目前支持会员注册:member, 下单成功:order两种'),
        ),
   ),
  'version' => '$Rev: 41137 $',
  'engine' => 'innodb',
  'comment' => app::get('bdlink')->_('外部链接表(包括订单外部链接和会员外部链接)'),
);