<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['auth'] = array(
    'columns'=>array(
        'auth_id'=>array('type'=>'number','pkey'=>true,'extra' => 'auto_increment','comment' => app::get('pam')->_('验证方式序号ID'),),
        'account_id'=>array('type'=>'table:members','comment' => app::get('pam')->_('账户序号ID'),),
        'module_uid'=>array('type'=>'varchar(50)','comment' => app::get('pam')->_('来源的用户名'),),
        'module'=>array('type'=>'varchar(50)', 'comment' => app::get('pam')->_('验证方式名称'),),
        'data'=>array('type'=>'text', 'comment' => app::get('pam')->_('扩展信息序列化'),),
    ),
  'index' => array (
    'account_id' => array ('columns' => array ('module','account_id'),'prefix' => 'UNIQUE'),
    'module_uid' => array ('columns' => array ('module','module_uid'),'prefix' => 'UNIQUE'),
  ),
    'comment' => app::get('pam')->_('其他登录方式表'),
);
