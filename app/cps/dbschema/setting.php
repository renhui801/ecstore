<?php
/**
 * 网站联盟基础信息表
 * 
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:setting Jun 20, 2011  10:14:09 AM ever $
 */
$db['setting'] = array(
    'columns' => array(
        'skey' => array(
            'type' => 'varchar(20)',
            'required' => true,
            'width' => 100,
            'label' => app::get('cps')->_('关键字'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'pkey' => true,
        ),
        'value' => array(
            'type' => 'text',
            'required' => true,
            'default' => '',
            'width' => 200,
            'label' => app::get('cps')->_('保存数据'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev: 1 $',
);