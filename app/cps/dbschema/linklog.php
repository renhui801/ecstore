<?php
/**
 * 推广链接会员订单关联表
 * 
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:linklog Jun 28, 2011  10:14:09 AM ever $
 */
$db['linklog'] = array(
    'columns' => array(
        'linklog_id' => array(
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
        'refer_id' => array(
            'type' => 'varchar(10)',
            'required' => true,
            'default' => '',
            'width' => 100,
            'label' => app::get('cps')->_('首次联盟商户推广ID'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'refer_url' => array(
            'type' => 'varchar(200)',
            'required' => true,
            'default' => '',
            'width' => 300,
            'label' => app::get('cps')->_('首次来源URL'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'refer_time' => array(
            'type' => 'time',
            'required' => true,
            'default' => 0,
            'width' => 100,
            'label' => app::get('cps')->_('首次来源时间'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'c_refer_id' => array(
            'type' => 'varchar(10)',
            'required' => true,
            'default' => '',
            'width' => 100,
            'label' => app::get('cps')->_('本次联盟商户推广ID'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'c_refer_url' => array(
            'type' => 'varchar(200)',
            'required' => true,
            'default' => '',
            'width' => 300,
            'label' => app::get('cps')->_('本次来源URL'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'c_refer_time' => array(
            'type' => 'time',
            'required' => true,
            'default' => 0,
            'width' => 120,
            'label' => app::get('cps')->_('本次来源时间'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'target_id' => array(
            'type' => 'varchar(32)',
            'required' => true,
            'default' => '',
            'width' => 100,
            'label' => app::get('cps')->_('会员ID/订单ID'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'target_type' => array(
            'type' => 'varchar(50)',
            'required' => true,
            'default' => '',
            'width' => 100,
            'label' => app::get('cps')->_('类型标记'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
    ),
    'index' => array(
        'ind_target_id' => array(
            'columns' => array('target_id'),
        ),
        'ind_target_type' => array(
            'columns' => array('target_type'),
        ),
        'ind_refer_id' => array(
            'columns' => array('refer_id'),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev: 1 $',
);