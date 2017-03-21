<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['emailcont'] = array(
    'columns' => array(
        'ec_id' => array(
            'type' => 'mediumint(8) unsigned',
            'pkey' => true,
            'required' => true,
            'extra' => 'auto_increment',
            'label' => 'ID',
            'hidden' => true,
            'editable' => false,
            'in_list' => false,
        ),
        'ec_title' => array(
            'type' => 'varchar(200)',
            'label' => app::get('emailsubs')->_('邮件标题'),
            'width' => 310,
            'in_list' => true,
            'default_in_list' => true,
            'editable' => true,
            'searchtype' => 'has',
            'filterdefault' => true,
            'filtertype' => 'custom',
            'filtercustom' =>array (
                'has' => app::get('emailsubs')->_('包含'),
                'tequal' => app::get('emailsubs')->_('等于'),
                'head' => app::get('emailsubs')->_('开头等于'),
                'foot' => app::get('emailsubs')->_('结尾等于'),
            ),
        ),
        'ec_content' => array(
            'type' => 'longtext',
            'label' => app::get('emailsubs')->_('邮件内容'),
            'hidden' => true,
            'editable' => false,
            'in_list' => false,
            'filtertype' => 'normal',
            'default_in_list' => false,
        ),
        'ec_addtime' => array(
            'type' => 'time',
            'label' => app::get('emailsubs')->_('邮件添加时间'),
            'width' => 200,
            'in_list' => true,
            'editable' => false,
            'default_in_list' => false,
            'default' => 0,
        ),
        'ec_sendtime' => array(
            'type' => 'time',
            'label' => app::get('emailsubs')->_('邮件发送时间'),
            'width' => 200,
            'in_list' => true,
            'editable' => false,
            'default_in_list' => false,
            'default' => 0,
        ),
        'ec_ifsend' => array(
            'type' => 'intbool',
            'label' => app::get('emailsubs')->_('邮件是否已经发送'),
            'width' => 100,
            'in_list' => true,
            'editable' => false,
            'default_in_list' => false,
            'filterdefault' => false,
            'filtertype' => 'yes',
            'default' => '0',
        ),
    ),
    'index' => array(
        'ind_ec_addtime' => array('columns' => array(0 => 'ec_addtime')),
        'ind_ec_sendtime' => array('columns' => array(0 => 'ec_sendtime')),
        //'ind_ec_ifsend' => array('columns' => array(0 => 'ec_ifsend')),
    ),
    'comment' => app::get('emailsubs')->_('订阅邮件表'),
    'engine' => 'innodb',
    'version' => '$Rev: 44513 $',
);