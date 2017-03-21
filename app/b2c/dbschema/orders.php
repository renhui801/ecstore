<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['orders']=array (
  'columns' =>
  array (
    'order_id' =>
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'default' => 0,
      'pkey' => true,
      'label' => app::get('b2c')->_('订单号'),
      'is_title' => true,
      'width' => 110,
      'searchtype' => 'has',
      'editable' => false,
      'filtertype' => 'custom',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'total_amount' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('商品默认货币总值'),
    ),
    'final_amount' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'sdfpath' => 'cur_amount',
      'label' => app::get('b2c')->_('订单总额'),
      'width' => 75,
      'filtertype' => 'number',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'orderby' => true,
      'comment' => app::get('b2c')->_('订单货币总值, 包含支付价格,税等'),
    ),
    'pay_status' =>
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
      'in_list' => true,
      'default_in_list' => true,
    ),
        'ship_status' =>
    array (
      'type' =>
      array (
        0 => app::get('b2c')->_('未发货'),
        1 => app::get('b2c')->_('已发货'),
        2 => app::get('b2c')->_('部分发货'),
        3 => app::get('b2c')->_('部分退货'),
        4 => app::get('b2c')->_('已退货'),
      ),
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('发货状态'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'is_delivery' =>
    array (
      'type' => 'tinybool',
      'default' => 'Y',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('是否需要发货'),
    ),
   'createtime' =>
    array (
      'type' => 'time',
      'label' => app::get('b2c')->_('下单时间'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'orderby' => true,
    ),
   'last_modified' =>
    array (
      'label' => app::get('b2c')->_('最后更新时间'),
      'type' => 'last_modify',
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'payment' =>
    array (
      'type' => 'varchar(100)',
      'label' => app::get('b2c')->_('支付方式'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'sdfpath' => 'payinfo/pay_app_id',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'shipping_id' =>
    array (
      'type' => 'table:dlytype',
      'label' => app::get('b2c')->_('配送方式'),
      'filtertype' => 'yes',
      'filterdefault' => true,
      'editable' => false,
      'sdfpath' => 'shipping/shipping_id',
      'in_list' => false,
    ),

    'shipping' =>
    array (
      'type' => 'varchar(100)',
      'label' => app::get('b2c')->_('配送方式'),
      'width' => 75,
      'editable' => false,
      'sdfpath' => 'shipping/shipping_name',
      'in_list' => true,
    ),
   'member_id' =>
    array (
      'type' => 'table:members',
      'label' => app::get('b2c')->_('会员用户名'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
     'status' =>
    array (
      'type' =>
      array (
        'active' => app::get('b2c')->_('活动订单'),
        'dead' => app::get('b2c')->_('已作废'),
        'finish' => app::get('b2c')->_('已完成'),
      ),
      'default' => 'active',
      'required' => true,
      'label' => app::get('b2c')->_('订单状态'),
      'width' => 75,
      'hidden' => true,
      'editable' => false,
      'in_list' => true,

    ),
    'confirm' =>
    array (
      'type' => 'tinybool',
      'default' => 'N',
      'required' => true,
      'label' => app::get('b2c')->_('确认状态'),
      'width' => 75,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'ship_area' =>
    array (
      'type' => 'region',
      'label' => app::get('b2c')->_('收货地区'),
      'width' => 180,
      'editable' => false,
      'filtertype' => 'yes',
      'sdfpath' => 'consignee/area',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'ship_name' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('收货人'),
      'sdfpath' => 'consignee/name',
      'width' => 75,
      'searchtype' => 'head',
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),

    'weight' =>
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => app::get('b2c')->_('订单总重量'),
    ),
    'tostr' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'sdfpath' => 'title',
      'comment' => app::get('b2c')->_('订单文字描述'),
    ),
    'itemnum' =>
    array (
      'type' => 'number',
      'editable' => false,
      'comment' => app::get('b2c')->_('订单子订单数量'),
    ),

    'ip' =>
    array (
      'type' => 'varchar(15)',
      'editable' => false,
      'comment' => app::get('b2c')->_('IP地址'),
      'label' => app::get('b2c')->_('IP地址'),
      'in_list' => true,
    ),

    'ship_addr' =>
    array (
      'type' => 'text',
      'label' => app::get('b2c')->_('收货地址'),
      'searchtype' => 'has',
      'width' => 180,
      'editable' => false,
      'filtertype' => 'normal',
      'sdfpath' => 'consignee/addr',
      'in_list' => true,
      'default_in_list' => true,
      'comment' => app::get('b2c')->_('收货地址'),
    ),
    'ship_zip' =>
    array (
      'type' => 'varchar(20)',
      'editable' => false,
      'sdfpath' => 'consignee/zip',
      'comment' => app::get('b2c')->_('收货人邮编'),
    ),
    'ship_tel' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('收货人电话'),
      'searchtype' => 'has',
      'width' => 75,
      'editable' => false,
      'filtertype' => 'normal',
      'filterdefault' => true,
      'sdfpath' => 'consignee/telephone',
      'in_list' => true,
      'default_in_list' => true,
      'comment' => app::get('b2c')->_('收货电话'),
    ),
    'ship_email' =>
    array (
      'type' => 'varchar(200)',
      'editable' => false,
      'sdfpath' => 'consignee/email',
      'comment' => app::get('b2c')->_('收货人email'),
    ),
    'ship_time' =>
    array (
      'type' => 'varchar(50)',
      'editable' => false,
      'sdfpath' => 'consignee/r_time',
      'comment' => app::get('b2c')->_('配送时间'),
    ),
    'ship_mobile' =>
    array (
      'label' => app::get('b2c')->_('收货人手机'),
      'hidden' => true,
      'searchtype' => 'has',
      'type' => 'varchar(50)',
      'editable' => false,
      'sdfpath' => 'consignee/mobile',
      'in_list' => true,
      'default_in_list' => true,
      'comment' => app::get('b2c')->_('收货人手机'),
    ),
    'cost_item' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('订单商品总价格'),
    ),
    'is_tax' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('是否要开发票'),
    ),
    'tax_type' =>
    array (
       'type' => array(
            'false' => app::get('b2c')->_('不需发票'),
            'personal' => app::get('b2c')->_('个人发票'),
            'company' => app::get('b2c')->_('公司发票'),
        ),
      'default' => 'false',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('发票类型'),
    ),
    'tax_content' =>
    array(
      'type' => 'varchar(255)',
      'editable' => false,
      'label' => app::get('b2c')->_('发票内容'),
    ),
    'cost_tax' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('订单税率'),
    ),
    'tax_company' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'sdfpath' => 'tax_title',
      'comment' => app::get('b2c')->_('发票抬头'),
    ),
    'is_protect' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
      'sdfpath' => 'shipping/is_protect',
      'comment' => app::get('b2c')->_('是否还有保价费'),
    ),
    'cost_protect' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'sdfpath' => 'shipping/cost_protect',
      'comment' => app::get('b2c')->_('保价费'),
    ),
    'cost_payment' =>
    array (
      'type' => 'money',
      'editable' => false,
      'sdfpath' => 'payinfo/cost_payment',
      'comment' => app::get('b2c')->_('支付费用'),
    ),
    'currency' =>
    array (
      'type' => 'varchar(8)',
      'editable' => false,
      'comment' => app::get('b2c')->_('订单支付货币'),
    ),
    'cur_rate' =>
    array (
      'type' => 'decimal(10,4)',
      'default' => '1.0000',
      'editable' => false,
      'comment' => app::get('b2c')->_('订单支付货币汇率'),
    ),
    'score_u' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('订单使用积分'),
    ),
    'score_g' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('订单获得积分'),
    ),
    'discount' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('订单减免'),
    ),
    'pmt_goods' =>
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => app::get('b2c')->_('商品促销优惠'),
    ),
    'pmt_order' =>
    array (
      'type' => 'money',
      'editable' => false,
      'comment' => app::get('b2c')->_('订单促销优惠'),
    ),
    'payed' =>
    array (
      'type' => 'money',
      'default' => '0',
      'editable' => false,
      'comment' => app::get('b2c')->_('订单支付金额'),
    ),
    'memo' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('订单附言'),
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'mark_type' =>
    array (
      'type' => 'varchar(2)',
      'default' => 'b1',
      'required' => true,
      'label' => app::get('b2c')->_('订单备注图标'),
      'hidden' => true,
      'width' => 150,
      'editable' => false,
      'in_list' => true,
    ),
    'mark_text' =>
    array (
      'type' => 'longtext',
      'label' => app::get('b2c')->_('订单备注'),
      'width' => 50,
      'editable' => false,
      //'searchtype' => 'has',
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    'cost_freight' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('配送费用'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'number',
      'sdfpath' => 'shipping/cost_shipping',
      'in_list' => true,
    ),
    'extend' =>
    array (
      'type' => 'varchar(255)',
      'default' => 'false',
      'editable' => false,
      'comment' => app::get('b2c')->_('订单扩展'),
    ),
    'order_refer' =>
    array (
      'type' => 'varchar(20)',
      'required' => true,
      'default' => 'local',
      'hidden' => true,
      'comment' => app::get('b2c')->_('订单来源'),
    ),
    'addon' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('订单附属信息(序列化)'),
    ),
    'source' =>
    array (
      'type' => array(
            'pc' =>app::get('b2c')->_('标准平台'),
            'wap' => app::get('b2c')->_('手机触屏'),
            'weixin' => app::get('b2c')->_('微信商城'),
       ),
      'required' => false,
      'label' => app::get('b2c')->_('平台来源'),
      'width' => 110,
      'editable' => false,
      'default' =>'pc',
      'in_list' => true,
      'default_in_list' => false,
      'filterdefault' => false,
      'filtertype' => 'yes',
    ),
  ),
  'index' =>
  array (
    'ind_ship_status' =>
    array (
      'columns' =>
      array (
        0 => 'ship_status',
      ),
    ),
    'ind_pay_status' =>
    array (
      'columns' =>
      array (
        0 => 'pay_status',
      ),
    ),
    'ind_status' =>
    array (
      'columns' =>
      array (
        0 => 'status',
      ),
    ),
    'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
      ),
    ),
    'ind_last_modified' =>
    array (
      'columns' =>
      array (
        0 => 'last_modified',
      ),
    ),
    'ind_createtime' =>
    array (
      'columns' =>
      array (
        0 => 'createtime',
      ),
    ),
    'idx_order_refer' =>
    array (
        'columns' =>
        array(
            0 => 'order_refer',
            ),
        ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 42376 $',
  'comment' => app::get('b2c')->_('订单主表'),
);
