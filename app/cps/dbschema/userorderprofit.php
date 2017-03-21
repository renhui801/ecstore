<?php
/**
 * 网站联盟联盟商订单佣金表
 * 
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:userorderprofit Jun 20, 2011  10:14:09 AM ever $
 */
$db['userorderprofit'] = array(
    'columns' => array(
        'profit_id' => array (
            'type' => 'number',
            'required' => true,
            'pkey' => true,
            'width' => 100,
            'label' => app::get('cps')->_('ID'),
            'editable' => false,
            'extra' => 'auto_increment',
            'in_list' => true,
            'default_in_list' => false,
        ),
        'order_id' => array (
            'type' => 'bigint unsigned',
            'required' => true,
            'default' => 0,
            'width' => 120,
            'label' => app::get('cps')->_('订单号'),
        	'searchtype' => 'has',
            'filtertype' => 'string',
            'filterdefault' => true,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'u_name' => array(
            'type' => 'varchar(50)',
            'required' => true,
            'default' => '',
            'width' => 100,
            'label' => app::get('cps')->_('联盟商用户名'),
            'editable' => false,
        	'searchtype' => 'has',
            'filtertype' => 'string',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'addtime' => array (
            'type' => 'time',
            'required' => true,
            'default' => 0,
            'width' => 120,
            'label' => app::get('cps')->_('下单时间'),
            'editable' => false,
            'filtertype' => 'time',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'refer_url' => array (
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
        	'filtertype' => 'money',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'money' => array (
            'type' => 'money',
            'required' => true,
            'default' => 0,
            'width' => 100,
            'label' => app::get('cps')->_('佣金金额'),
            'editable' => false,
        	'filtertype' => 'money',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'state' => array (
            'type' => array(
                '0' => '新增',
                '1' => '无效',
                '2' => '有效',
            ),
            'required' => true,
            'default' => '0',
            'width' => 100,
            'label' => app::get('cps')->_('状态'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'u_id' => array (
            'type' => 'table:users',
            'required' => true,
            'default' => 0,
            'width' => 100,
            'label' => app::get('cps')->_('联盟商ID'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'yam' => array (
            'type' => 'number',
            'required' => true,
            'default' => 0,
            'width' => 100,
            'label' => app::get('cps')->_('订单完成年月'),
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
        ),
        'disabled' => array (
            'type' => array(
                'false' => '有效',
                'true' => '无效',
            ),
            'required' => true,
            'default' => 'false',
            'width' => 100,
            'label' => app::get('cps')->_('是否有效'),
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
        ),
    ),
    'index' => array(
        'ind_disabled' => array(
            'columns' => array('disabled'),
        ),
        'ind_addtime' => array(
            'columns' => array('addtime'),
        ),
        'ind_uname' => array(
            'columns' => array('u_name'),
        ),
        'ind_orderid' => array(
            'columns' => array('order_id'),
        ),
        'ind_u_id' => array(
            'columns' => array('u_id'),
        ),
        'ind_yam' => array(
            'columns' => array('yam'),
        ),
        'ind_state' => array(
            'columns' => array('state'),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev: 1 $',
);