<?php
/**
 * 网站联盟月度佣金表
 * 
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:usermonthprofit Jun 20, 2011  10:14:09 AM ever $
 */
$db['usermonthprofit'] = array(
    'columns' => array(
        'ump_id' => array(
            'type' => 'number',
            'required' => true,
            'width' => 100,
            'label' => app::get('cps')->_('ID'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'pkey' => true,
            'extra' => 'auto_increment',
        ),
        'u_name' => array(
            'type' => 'varchar(50)',
            'required' => true,
            'default' => '',
            'width' => 100,
            'label' => app::get('cps')->_('用户名'),
            'editable' => false,
        	'searchtype' => 'has',
            'filtertype' => 'string',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'state' => array(
            'type' => array(
                '1' => '未发放',
                '2' => '已发放',
            ),
            'required' => true,
            'default' => '1',
            'width' => 100,
            'label' => app::get('cps')->_('发放状态'),
            'editable' => false,
            'filtertype' => 'has',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'money_sum' => array (
            'type' => 'money',
            'required' => true,
            'default' => 0,
            'width' => 100,
            'label' => app::get('cps')->_('佣金总额'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'cost_sum' => array(
            'type' => 'money',
            'required' => true,
            'default' => 0,
            'width' => 100,
            'label' => app::get('cps')->_('订单总额'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'order_sum' => array (
            'type' => 'number',
            'required' => true,
            'default' => 0,
            'width' => 100,
            'label' => app::get('cps')->_('订单量'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'year' => array(
            'type' => 'number',
            'required' => true,
            'default' => 1900,
            'width' => 100,
            'label' => app::get('cps')->_('年份'),
            'editable' => false,
        	'searchtype' => 'has',
            'filtertype' => 'string',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'month' => array(
            'type' => 'number',
            'required' => true,
            'default' => 1,
            'width' => 100,
            'label' => app::get('cps')->_('月份'),
            'editable' => false,
        	'searchtype' => 'has',
            'filtertype' => 'string',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'u_id' => array(
            'type' => 'table:users',
            'required' => true,
            'default' => 0,
            'width' => 100,
            'label' => app::get('cps')->_('联盟商ID'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'disabled' => array(
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
        'ind_u_id' => array(
            'columns' => array('u_id'),
        ),
        'ind_year' => array(
            'columns' => array('year'),
        ),
        'ind_year' => array(
            'columns' => array('year'),
        ),
        'ind_state' => array(
            'columns' => array('state'),
        ),
        'ind_u_name' => array(
            'columns' => array('u_name'),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev: 1 $',
);