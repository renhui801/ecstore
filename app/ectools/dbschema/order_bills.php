<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['order_bills']=array (
  'columns' => 
  array (
    'rel_id' => 
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'pkey' => true,
      'default' => 0,
      'editable' => false,
    ),
    'bill_type' => 
    array (
      'type' => 
      array (
        'payments' =>  app::get('ectools')->_('付款单'),
        'refunds' =>  app::get('ectools')->_('退款单'),
      ),
      'default' => 'payments',
      'required' => true,
      'label' => app::get('ectools')->_( '单据类型'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'comment' => app::get('ectools')->_('单据类型'),
    ),
    'pay_object' => 
    array (
      'type' => 
      array (
        'order' =>  app::get('ectools')->_('订单支付'),
        'recharge' =>  app::get('ectools')->_('预存款充值'),
        'joinfee' =>  app::get('ectools')->_('加盟费'),
		'prepaid_recharge' => app::get('ectools')->_('消费卡'),
      ),
      'default' => 'order',
      'required' => true,
      'label' =>  app::get('ectools')->_('支付类型'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'bill_id' => 
    array (
      'type' => 'varchar(20)',
      'pkey' => true,
      'required' => true,
      'label' =>  app::get('ectools')->_('关联退款/付款单号'),
      'width' => 110,
      'editable' => false,
      'searchtype' => 'has',
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'money' => 
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => app::get('ectools')->_('金额'),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 40912 $',
  'comment' => app::get('ectools')->_('订单钱款单据主表'),
  
);
