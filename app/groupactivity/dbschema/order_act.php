<?php
/**
 * //======================
 * extension_code
 * 分为三种情况
 * 1、create：表示为团购预订单创建
 * 2、succ：表示为已经生成正式订单（已支付)
 * 3、fail：表示为生成正式订单失败
 * //======================
 */
$db['order_act']=array(
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
    'act_id' =>
    array (
      'type' => 'mediumint(8)',
      'default' => '0',
      'label' => __('团购ID'),
      'width' => 75,
    ), 
    'extension_code' =>
    array(
        'type' => 'varchar(30)',
        'default'=>'create',
        'editable' => false,
    ),
    'group_total_amount'=>array(
        'type' =>'money',
        'default'=>'0',
        'required' => true,
        'filtertype'=>'number',
    ),
    'last_change_time'=>array(
      'type' => 'int(11)',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'quantity'=>array(
      'type' => 'int(8)',
      'default' => 0,
      'editable' => false,
    ),
    'member_id'=>array(
      'type' => 'table:members@b2c',
      'label' => '会员id',
      'width' => 110,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
	'createtime' =>
    array (
      'type' => 'time',
      'editable' => false,
    ),
  ),
  'index' =>
  array(
    'ind_order'=>
    array(
         'columns' =>
         array(
            0=>'order_id'
         )
    ),
    'ind_act'=>
    array(
        'columns' =>
        array(
            0=>'act_id'
        )
    )
  ),
);