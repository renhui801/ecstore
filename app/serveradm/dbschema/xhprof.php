<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['xhprof']=array (
  'columns' =>
  array (
    'run_id' =>
    array (
      'required' => true,
      'pkey' => true,
      'type' => 'varchar(100)',
      'label' => app::get('serveradm')->_('run_id'),
      'width' => 80,
      'is_title' => true,
      'required' => true,
      'comment' => app::get('serveradm')->_('run_id'),
      'editable' => false,
      //'searchtype' => 'has',
      'in_list' => false,
      'default_in_list' => false,
    ),
    'source' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('serveradm')->_('source'),
      'width' => 350,
      'comment' => app::get('serveradm')->_('source'),
      'editable' => false,
      //'searchtype' => 'has',
      'in_list' => false,
      'default_in_list' => false,
    ),
    'app' =>
    array (
      'type' => 'varchar(30)',
      'comment' => app::get('serveradm')->_('app'),
      'width' => 80,
      'editable' => false,
      'label' => app::get('serveradm')->_('app'),
      'in_list' => true,
      'default_in_list' => true,
    ),
    'ctl' =>
    array (
      'type' => 'varchar(100)',
      'comment' => app::get('serveradm')->_('controller'),
      'width' => 80,
      'editable' => false,
      'label' => app::get('serveradm')->_('controller'),
      'in_list' => true,
      'default_in_list' => true,
    ),
    'act' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('serveradm')->_('action'),
      'width' => 80,
      'comment' => app::get('serveradm')->_('action'),
      'editable' => false,
      //'searchtype' => 'has',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'request_uri' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('serveradm')->_('request_uri'),
      'width' => 300,
      'comment' => app::get('serveradm')->_('request_uri'),
      'editable' => false,
      //'searchtype' => 'has',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'params' =>
    array(
        'type' => 'serialize',
        'label' => app::get('serveradm')->_('params'),
        'deny_export' => true,
    ),
    'addtime' =>
    array(
      'type' => 'last_modify',
      'label' => app::get('serveradm')->_('addtime'),
      'width' => 130,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'wt' => 
    array (
      'type' => 'int(10) unsigned',
      'label' => app::get('serveradm')->_('Wall Time'),
      'required' => false,
      'editable' => false,
      'width' => 80,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'mu' => 
    array (
      'type' => 'int(10) unsigned',
      'label' => app::get('serveradm')->_('Memory Used'),
      'required' => false,
      'editable' => false,
      'width' => 80,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'pmu' => 
    array (
      'type' => 'int(10) unsigned',
      'label' => app::get('serveradm')->_('PeakMemUse'),
      'required' => false,
      'editable' => false,
      'width' => 80,
      'in_list' => true,
      'default_in_list' => true,
    ),
  ),
  'comment' => app::get('serveradm')->_('xphrof'),
  'version' => '$Rev: 40654 $',
);
