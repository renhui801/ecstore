<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['member_systmpl']=array (
  'columns' => 
  array (
    'tmpl_name' => array (
       'type' => 'varchar(100)',
        'pkey' => true,
      'required' => true,
       'comment' => app::get('b2c')->_('模版名称'),
    ),
    'content' => array(
        'type'=>'longtext',
        'label' =>app::get('b2c')->_('内容'),
        'default' => 0,
        'comment' => app::get('b2c')->_('模板内容'),
    ),
    'edittime' => array (
      'type' => 'int(10) ',
      'required' => true,
      'comment' => app::get('b2c')->_('编辑时间'),
    ),
    'active' => array(
        'type'=>"enum('true', 'false')",
        'default' => 'true',
        'comment' => app::get('b2c')->_('是否激活'),
    ),
   
  ),   
  'comment' => app::get('b2c')->_('会员消息模版表'),
   'engine' => 'innodb',
   'version' => '$Rev$',
);
