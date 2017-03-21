<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['order_log']=array (
  'columns' => 
  array (
    'log_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      'comment' => app::get('b2c')->_('订单日志ID'),
    ),
    'rel_id' => 
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('对象ID'),
    ),
    'op_id' => 
    array (
      'type' => 'number',//'table:users@desktop',
      'label' => app::get('b2c')->_('操作员'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
      'comment' => app::get('b2c')->_('操作员ID'),
    ),
    'op_name' => 
    array (
      'type' => 'varchar(100)',
      'label' => app::get('b2c')->_('操作人名称'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'alttime' => 
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('操作时间'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
      'comment' => app::get('b2c')->_('操作时间'),
    ),
   'bill_type' => 
    array (
      'type' => 
      array (
        'order' => app::get('b2c')->_('订单支付'),
        'recharge' => app::get('b2c')->_('预存款充值'),
        'joinfee' => app::get('b2c')->_('加盟费'),
        'prepaid_recharge' => app::get('ectools')->_('消费卡'),
      ),
      'default' => 'order',
      'required' => true,
      'label' => app::get('b2c')->_('支付类型'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'comment' => app::get('b2c')->_('操作人员姓名'),
    ),
    'behavior' => 
    array (
      'type' => 
      array (
        'creates' => app::get('b2c')->_('创建'),
        'updates' => app::get('b2c')->_('修改'),
        'payments' => app::get('b2c')->_('支付'),
        'refunds' => app::get('b2c')->_('退款'),
        'delivery' => app::get('b2c')->_('发货'),
        'reship' => app::get('b2c')->_('退货'),
        'finish' => app::get('b2c')->_('完成'),
        'cancel' => app::get('b2c')->_('取消'),
      ),
      'default' => 'payments',
      'required' => true,
      'label' => app::get('b2c')->_('操作行为'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'comment' => app::get('b2c')->_('日志记录操作的行为'),
    ),
    'result' => 
    array (
      'type' => 
      array (
        'SUCCESS' => app::get('b2c')->_('成功'),
        'FAILURE' => app::get('b2c')->_('失败'),
      ),
      'required' => true,
      'label' => app::get('b2c')->_('操作结果'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'comment' => app::get('b2c')->_('日志结果'),
    ),
    'log_text' => 
    array (
      'type' => 'longtext',
      'editable' => false,
      'in_list' => true,
      'default_in_list' => false,
      'comment' => app::get('b2c')->_('操作内容'),
    ),
    'addon' => 
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('序列化数据'),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 46974 $',
  'comment' => app::get('b2c')->_('订单日志表'),
);
