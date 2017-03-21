<?php
/**
 * 网站联盟联盟协议表
 * 
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:agreement Jun 20, 2011  10:14:09 AM ever $
 */
$db['agreement'] = array(
    'columns' => array(
        'agree_id' => array (
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
        'agreement' => array (
            'type' => 'text',
            'required' => true,
            'default' => '',
            'width' => 300,
            'label' => app::get('cps')->_('协议内容'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'agree_type' => array (
            'type' => array(
                '0' => '加盟商',
            ),
            'required' => true,
            'default' => '0',
            'width' => 100,
            'label' => app::get('cps')->_('协议类型'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev: 1 $',
);