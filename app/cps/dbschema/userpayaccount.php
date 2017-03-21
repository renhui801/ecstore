<?php
/**
 * 网站联盟商收款账户表
 * 
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:userpayaccount Jun 20, 2011  10:14:09 AM ever $
 */
$db['userpayaccount'] = array(
    'columns' => array(
        'u_id' => array (
            'type' => 'table:users',
            'required' => true,
            'pkey' => true,
            'width' => 100,
            'label' => app::get('cps')->_('ID'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'account' => array (
            'type' => 'varchar(100)',
            'required' => true,
            'width' => 300,
            'label' => app::get('cps')->_('开户账号'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'acc_bank' => array (
            'type' => 'varchar(100)',
            'required' => true,
            'width' => 100,
            'label' => app::get('cps')->_('开户银行'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'acc_bname' => array (
            'type' => 'varchar(100)',
            'required' => true,
            'width' => 100,
            'label' => app::get('cps')->_('支行名称'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'acc_cname' => array (
            'type' => 'varchar(100)',
            'required' => true,
            'default' => '',
            'width' => 100,
            'label' => app::get('cps')->_('公司名称'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'acc_person' => array (
            'type' => 'varchar(50)',
            'required' => true,
            'default' => '',
            'width' => 100,
            'label' => app::get('cps')->_('开户人姓名'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
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
            'in_list' => true,
            'default_in_list' => true,
        ),
    ),
    'index' => array(
        'ind_disabled' => array(
            'columns' => array('disabled'),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev: 1 $',
);