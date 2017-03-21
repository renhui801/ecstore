<?php
$db['server']=array (
  'columns' => 
  array (
    'id'=>array(
      'type' => 'number',
      'pkey' => true,
      'extra' => 'auto_increment',
      'label'=>app::get('suitclient')->_('产品ID'),
      'in_list' => true,
      'default_in_list' => true,
    ),
    'url'=>array(
        'type'=>'varchar(255)',
        'label'=>app::get('suitclient')->_('url地址'),
        'in_list' => true,
        'required' => true,
        'default_in_list' => true,
    ),
    'secret'=>array(
        'type'=>'varchar(32)',
        'label'=>app::get('suitclient')->_('私钥'),
        'in_list' => true,
        'default_in_list' => true,
        'required' => true,
    ),
  ),
  'comment' => app::get('suitclient')->_('套件联通产品表'),
);
