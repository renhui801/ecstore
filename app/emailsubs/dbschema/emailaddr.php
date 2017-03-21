<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['emailaddr'] = array(
    'columns' => array(
        'ea_id' => array(
            'type' => 'mediumint(8) unsigned',
            'pkey' => true,
            'required' => true,
            'extra' => 'auto_increment',
            'label' => 'ID',
            'hidden' => true,
            'editable' => false,
            'in_list' => false,
        ),
        'ea_email' => array(
            'type' => 'varchar(200)',
            'label' => 'EMAIL',
            'width' => 110,
            'required' => 1,
            'searchtype' => 'has',
            'editable' => false,
            'filtertype' => 'normal',
            'filterdefault' => 'true',
            'in_list' => false,
            'default_in_list' => false,
        ),
        'member_id' => array(
            'type' => 'table:members@b2c',
            'label' => app::get('emailsubs')->_('会员ID'),
            'default' => 0,
            'editable' => false,
        ),
        'uname' => array(
            'type' => 'varchar(100)',
            'label' => app::get('emailsubs')->_('用户名'),
            'default' => '',
            'editable' => false,
        ),
    ),
    /*
    'index' => array(
        'ind_member_id' => array('columns' => array(0 => 'member_id'))
    ),*/
    'comment' => app::get('emailsubs')->_('用户邮件地址表'),
    'engine' => 'innodb',
    'version' => '$Rev: 44513 $',
);
