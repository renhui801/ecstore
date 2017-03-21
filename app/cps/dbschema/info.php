<?php
/**
 * 网站联盟消息表
 * 
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:info Jun 20, 2011  10:14:09 AM ever $
 */
$db['info'] = array(
    'columns' => array(
        'info_id' => array (
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
        'title' => array(
            'type' => 'varchar(200)',
            'required' => true,
            'default' => '',
            'width' => 300,
            'label' => app::get('cps')->_('文章标题'),
            'searchtype' => 'has',
            'filtertype' => 'string',
            'filterdefault' => true,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'ifpub' => array (
            'type' => array(
                'false' => '未发布',
                'true' => '已发布',
            ),
            'required' => true,
            'default' => 'false',
            'width' => 120,
            'label' => app::get('cps')->_('发布状态'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'pubtime' => array (
            'type' => 'time',
            'required' => true,
            'default' => 0,
            'width' => 120,
            'label' => app::get('cps')->_('发布时间'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'i_type' => array (
            'type' => array(
                '1' => '公告',
                '2' => '帮助',
            ),
            'required' => true,
            'default' => '1',
            'width' => 100,
            'label' => app::get('cps')->_('文章类型'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'content' => array (
            'type' => 'text',
            'required' => true,
            'default' => '',
            'width' => 300,
            'label' => app::get('cps')->_('文章内容'),
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
            'default' => 'true',
            'width' => 120,
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
        'ind_pubtime' => array(
            'columns' => array('pubtime'),
        ),
        'ind_i_type' => array(
            'columns' => array('i_type'),
        ),
        'ind_ifpub' => array(
            'columns' => array('ifpub'),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev: 1 $',
);
