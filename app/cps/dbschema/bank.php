<?php
/**
 * 网站联盟开户银行表
 * 
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:bank Jun 20, 2011  10:14:09 AM ever $
 */
$db['bank'] = array(
    'columns' => array(
        'b_id' => array(
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
        'b_name' => array(
            'type' => 'varchar(100)',
            'required' => true,
            'default' => '',
            'width' => 200,
            'label' => app::get('cps')->_('银行名称'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'is_use' => array(
            'type' => array(
                'false' => '停用',
                'true' => '启用',
            ),
            'required' => true,
            'default' => 'true',
            'width' => 100,
            'label' => app::get('cps')->_('是否启用'),
            'editable' => false,
            'in_list' => false,
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
        'ind_is_use' => array(
            'columns' => array('is_use'),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev: 1 $',
);