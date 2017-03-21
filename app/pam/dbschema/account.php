<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['account'] = array(
    'columns'=>array(
        'account_id'=>array('type'=>'number','pkey'=>true,'extra' => 'auto_increment','comment' => app::get('pam')->_('账户序号ID'),),
        'account_type'=>array('type'=>'varchar(30)','comment' => app::get('pam')->_('账户类型(会员和管理员等)'),),
        'login_name'=>array('type'=>'varchar(100)','is_title'=>true,'required' => true, 'comment' => app::get('pam')->_('登录用户名'),),
        'login_password'=>array('type'=>'varchar(32)','required' => true,'comment' => app::get('pam')->_('登录密码'),),
        'disabled'=>array('type'=>'bool','default'=>'false', ),
        'createtime'=>array('type'=>'time', 'comment' => app::get('pam')->_('创建时间'),),
    ),
  'index' => array (
    'account' => array ('columns' => array ('account_type','login_name'),'prefix' => 'UNIQUE'),
  ),
  'engine' => 'innodb',
    'comment' => app::get('pam')->_('用户权限账户表'),
);
