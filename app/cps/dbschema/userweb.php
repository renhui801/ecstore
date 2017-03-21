<?php
/**
 * 网站联盟商网站信息表
 * 
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:userweb Jun 20, 2011  10:14:09 AM ever $
 */
$db['userweb'] = array(
    'columns' => array(
        'web_id' => array (
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
        'u_id' => array (
            'type' => 'number',
            'required' => true,
            'default' => 0,
            'width' => 100,
            'label' => app::get('cps')->_('联盟商推广ID'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'webname' => array (
            'type' => 'varchar(100)',
            'required' => true,
            'default' => '',
            'width' => 200,
            'label' => app::get('cps')->_('网站名称'),
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
        ),
        'webtype' => array (
            'type' => array(
                '0' => '导航站',
        		'1' => '内容站',
        		'2' => '论坛',
        		'3' => '博客',
        		'4' => 'wap站点',
        		'5' => '其他',
            ),
            'required' => true,
            'default' => '0',
            'width' => 100,
            'label' => app::get('cps')->_('网站类型'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'weburl' => array (
            'type' => 'text',
            'required' => true,
            'default' => 'http://',
            'width' => 300,
            'label' => app::get('cps')->_('网站地址'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'webinfo' => array (
            'type' => 'varchar(1000)',
            'required' => true,
            'default' => '',
            'width' => 100,
            'label' => app::get('cps')->_('网站简介'),
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
        ),
        'visits' => array (
            'type' => 'varchar(100)',
            'required' => true,
            'default' => '',
            'width' => 100,
            'label' => app::get('cps')->_('访问量'),
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
        ),
        'alex_rank' => array (
            'type' => 'varchar(100)',
            'required' => true,
            'default' => '',
            'width' => 100,
            'label' => app::get('cps')->_('积分'),
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
        'ind_u_id' => array(
            'columns' => array('u_id'),
        ),
    ),
    'engine' => 'innodb',
);