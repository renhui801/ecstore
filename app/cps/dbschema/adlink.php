<?php
/**
 * 网站联盟推广链接主表
 * 
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:adlink Jun 20, 2011  10:14:09 AM ever $
 */
$db['adlink'] = array(
    'columns' => array(
        'link_id' => array (
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
        'url' => array (
            'type' => 'varchar(200)',
            'required' => true,
            'default' => '',
            'width' => 300,
            'label' => app::get('cps')->_('推广链接地址'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'title' => array (
            'type' => 'varchar(200)',
            'required' => true,
            'default' => '',
            'width' => 150,
            'label' => app::get('cps')->_('链接标题'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'addtime' => array (
            'type' => 'time',
            'required' => true,
            'default' => 0,
            'width' => 120,
            'label' => app::get('cps')->_('添加时间'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'a_type' => array (
            'type' => array(
                '1' => '图片',
                '2' => '文字',
            ),
            'required' => true,
            'default' => '1',
            'width' => 100,
            'label' => app::get('cps')->_('推广链接类型'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
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
        'ind_addtime' => array(
            'columns' => array('addtime'),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev: 1 $',
);