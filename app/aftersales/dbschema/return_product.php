<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['return_product']=array (
  'columns' => 
  array (
    'order_id' => 
    array (
      'type' => 'table:orders@b2c',
      'default' => '0',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => '3',
      'searchtype' => 'has',
      'filtertype' => 'yes',
      'label' => app::get('aftersales')->_('订单号'),
    ),
    'member_id' => 
    array (
      'type' => 'table:members@pam',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => '4',
      'label' => app::get('aftersales')->_('申请人'),
    ),
    'return_id' => 
    array (
      'type' => 'bigint(20)',
      'required' => true,
      'pkey' => true,
      'editable' => false,
      'in_list' => true,	  
      'searchtype' => 'has',
      'filtertype' => 'yes',
	  'default_in_list' => true,
      'label' => app::get('aftersales')->_('退货记录流水号'),
      'order' => '5',
    ),
    'return_bn' =>
    array (
      'type' => 'varchar(32)',
      'required' => false,
      'label' => app::get('aftersales')->_('退货记录流水号标识'),
      'comment' => app::get('aftersales')->_('退货记录流水号标识'),
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
      'is_title' => true,
    ),
    'title' => 
    array (
      'type' => 'varchar(200)',
      'required' => true,
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'order' => '2',
      'searchtype' => 'has',
      'filtertype' => 'yes',
      'label' => app::get('aftersales')->_('售后服务标题'),
    ),
    'content' =>
    array(
        'type' => 'longtext',
        'editable' => false,
        'label' => app::get('aftersales')->_('退货内容'),
    ),
    'type' => array(
        'type' => array(
            '1' => app::get('aftersales')->_('退货'),
            '2' => app::get('aftersales')->_('换货'),
            // '3' => app::get('aftersales')->_('保修'),
        ),
        'default' => '1',
        'required' => true,
        'comment' => app::get('aftersales')->_('售后服务类型'),
        'in_list' => true,
        'default_in_list' => true,
        'label' => app::get('aftersales')->_('售后服务类型'),
    ),
    'status' => 
    array (
      'type' => 
      array(
        '1' => app::get('aftersales')->_('未操作'),
        '2' => app::get('aftersales')->_('审核中'),
        '3' => app::get('aftersales')->_('接受申请'),
        '4' => app::get('aftersales')->_('完成'),
        '5' => app::get('aftersales')->_('拒绝'),
        '6' => app::get('aftersales')->_('已收货'),
        '7' => app::get('aftersales')->_('已质检'),
        '8' => app::get('aftersales')->_('补差价'),
        '9' => app::get('aftersales')->_('已拒绝退款'),
      ),
      'default' => '1',
      'required' => true,
      'comment' => app::get('aftersales')->_('退货记录状态'),
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'label' => app::get('aftersales')->_('售后服务状态'),
      'order' => '6',
    ),
    'image_file' =>
    array(
        'type' => 'varchar(255)',
        'label' => app::get('aftersales')->_('附件'),
        'width' => 75,
        'hidden' => true,
        'editable' => false,
        'in_list' => false,
    ),
    'product_data' =>
    array(
        'type' => 'longtext',
        'editable' => false,
        'label' => app::get('aftersales')->_('退货货品记录'),
    ),
    'comment' =>
    array(
        'type' => 'longtext',
        'editable' => false,
        'label' => app::get('aftersales')->_('管理员备注'),
    ),
    'add_time' =>
    array(
        'type' => 'time',
        'depend_col' => 'marketable:true:now',
        'label' => app::get('aftersales')->_('创建时间'),
        'width' => 110,
        'editable' => false,
        'in_list' => true,
        'default_in_list' => true,
        'order' => '7',
    ),
    'last_modify' => array(
      'type' => 'last_modify',
      'label' => app::get('b2c')->_('更新时间'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'orderby' => true,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
      'comment' => app::get('aftersales')->_('是否有效'),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 40912 $',
  'comment' => app::get('aftersales')->_('售后申请'),
);
