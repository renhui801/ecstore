<?php
/**
 * 网站联盟第三方CPS订单记录表
 * 
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:adlink Jul 29, 2011  10:14:09 AM ever $
 */
$db['thirdparty_orders'] = array(
    'columns' => array(
        'order_id' => array (
            'type' => 'bigint unsigned',
            'required' => true,
            'pkey' => true,
            'width' => 120,
            'label' => app::get('cps')->_('订单号'),
            'editable' => false,
            'searchtype' => 'has',
            'filtertype' => 'string',
            'filterdefault' => true,
            'extra' => 'auto_increment',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'src' => array (
            'type' => 'varchar(20)',
            'required' => true,
            'default' => '',
            'width' => 80,
            'label' => app::get('cps')->_('来源'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'url' => array (
            'type' => 'text',
            'required' => true,
            'default' => '',
            'width' => 300,
            'label' => app::get('cps')->_('来源地址'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'order_cost' => array (
            'type' => 'money',
            'required' => true,
            'default' => 0,
            'width' => 100,
            'label' => app::get('cps')->_('订单金额'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'createtime' => array (
            'type' => 'time',
            'required' => true,
            'default' => 0,
            'width' => 130,
            'label' => app::get('cps')->_('下单时间'),
            'editable' => false,
            'filtertype' => 'time',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'status' => array (
            'type' => array(
                '0' => '新增',
                '1' => '无效',
                '2' => '有效',
            ),
            'required' => true,
            'default' => '0',
            'width' => 80,
            'label' => app::get('cps')->_('状态'),
            'editable' => false,
            //'filtertype' => 'has',
            //'filterdefault' => true,
            'in_list' => false,
            'default_in_list' => false,
        ),
        'params' => array (
            'type' => 'text',
            'required' => true,
            'default' => '',
            'width' => 100,
            'label' => app::get('cps')->_('参数'),
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
        ),
    ),
    'index' => array(
        'ind_createtime' => array(
            'columns' => array('createtime'),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev: 281 $',
);