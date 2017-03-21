<?php
/**
 * 网站联盟推广链接图片明细表
 * 
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:adlinkpic Jun 20, 2011  10:14:09 AM ever $
 */
$db['adlinkpic'] = array(
    'columns' => array(
        'pic_id' => array (
            'type' => 'number',
            'required' => true,
            'pkey' => true,
            'width' => 50,
            'label' => app::get('cps')->_('图片ID'),
            'editable' => false,
            'extra' => 'auto_increment',
            'in_list' => true,
            'default_in_list' => false,
        ),
        'link_id' => array (
            'type' => 'table:adlink',
            'required' => true,
            'default' => 0,
            'width' => 50,
            'label' => app::get('cps')->_('推广链接ID'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'remote_img_url' => array (
            'type' => 'text',
            'required' => true,
            'default' => '',
            'width' => 300,
            'label' => app::get('cps')->_('远程图片地址'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'width' => array (
            'type' => 'int unsigned',
            'required' => true,
            'default' => 0,
            'width' => 50,
            'label' => app::get('cps')->_('图片宽度'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'height' => array (
            'type' => 'int unsigned',
            'required' => true,
            'default' => 0,
            'width' => 50,
            'label' => app::get('cps')->_('图片高度'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'suffix' => array (
            'type' => 'varchar(10)',
            'required' => true,
            'default' => '',
            'width' => 50,
            'label' => app::get('cps')->_('后缀'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
    ),
    'index' => array(
        'ind_link_id' => array(
            'columns' => array('link_id'),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev: 1 $',
);