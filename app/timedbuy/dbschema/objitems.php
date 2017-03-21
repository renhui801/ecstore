<?php
$db['objitems']=array(
 'columns' =>
  array (
    'id' =>
    array (
      'type' => 'mediumint(8)',
      'required' => true,
      'pkey' => true,
      'extra'=>'auto_increment',
    ),
    'order_id' =>
    array (
      'type' => 'table:orders@b2c',
      'label' => __('订单号'),
      'width' => 110,
    ),
    'sales_rule_id' =>
    array (
      'type' => 'table:sales_rule_goods@b2c',
      'default' => 0,
      'label' => __('商品促销ID'),
      'width' => 75,
    ), 
    'goods_id' =>
    array(
        'type' => 'table:goods@b2c',
        'default'=>0,
        'editable' => false,
    ),
    'member_id'=>array(
        'type' =>'table:members@b2c',
        'default'=>'0',
        'required' => true,
        'filtertype'=>'number',
    ),
    'last_modify'=>array(
      'type' => 'last_modify',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'ctime'=>array(
      'type' => 'time',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'quantity'=>array(
      'type' => 'int(8)',
      'default' => 0,
      'editable' => false,
    ),
    'order_pay_status' => 
    array (
      'type' => 
      array (
        0 => app::get('b2c')->_('未支付'),
        1 => app::get('b2c')->_('已支付'),
        2 => app::get('b2c')->_('已付款至到担保方'),
        3 => app::get('b2c')->_('部分付款'),
        4 => app::get('b2c')->_('部分退款'),
        5 => app::get('b2c')->_('全额退款'),
      ),
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('付款状态'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
    ),
  ),
  'index'=>array(
    'ind_last_modify' =>
     array (
      'columns' =>
      array (
        0 => 'last_modify',
      ),
     ),
     'ind_order_pay_status' =>
     array (
      'columns' =>
      array (
        0 => 'order_pay_status',
      ),
     ),
    ),
);