<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$db['members'] = array(
    'columns'=>
    array(
        'member_id'=>
        array(
            'type'=>'number',
            'pkey'=>true,
            'comment' => app::get('pam')->_('账户序号ID'),
        ),
        'login_account'=>
        array(
            'type'=>'varchar(100)',
            'is_title'=>true,
            'required' => true,
            'comment' => app::get('pam')->_('登录名'),
        ),
        'login_type'=>
        array(
            'pkey'=>true,
            'type'=>
            array(
                'local' => '用户名',
                'mobile' => '手机',
                'email' => '邮箱'
            ),
            'default'=>'local',
            'comment' => app::get('pam')->_('账户类型'),
        ),
        'login_password'=>
        array(
            'type'=>'varchar(32)',
            'required' => true,
            'comment' => app::get('pam')->_('登录密码'),
        ),
        'password_account'=>
        array(
            'type'=>'varchar(100)',
            'required' => true,
            'comment' => app::get('pam')->_('登录密码加密所用账号'),
        ),
        'disabled'=>
        array(
            'type'=>'bool',
            'default'=>'false',
        ),
        'createtime'=>
        array(
            'type'=>'time',
            'comment' => app::get('pam')->_('创建时间'),
        ),
    ),
    'engine' => 'innodb',
    'comment' => app::get('pam')->_('前台会员用户表'),
);
