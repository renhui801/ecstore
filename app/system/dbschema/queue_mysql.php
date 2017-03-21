<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2013 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['queue_mysql']=array (
    'columns' => array (
        'id' => array (
            'type' => 'bigint unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'comment' => app::get('system')->_('ID'),
        ),
        'queue_name' => array (
            'type' => 'varchar(100)',
            'comment' => app::get('system')->_('队列标识'),
            'label' => app::get('system')->_('队列标识'),
            'required' => true,
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'worker'=>array(
            'type' => 'varchar(100)',
            'required' => true,
            'comment' => app::get('system')->_('执行任务类'),
            'label' => app::get('system')->_('执行任务类'),
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'params'=>array(
            'type' => 'longtext',
            'required' => true,
            'comment' => app::get('system')->_('任务参数'),
            'label' => app::get('system')->_('任务参数'),
            'in_list'=>true,
        ),
        'create_time' => array (
            'type' => 'time',
            'default' => 0,
            'comment' => app::get('system')->_('进入队列的时间'),
            'label' => app::get('system')->_('进入队列的时间'),
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'last_cosume_time' => array(
            'type' => 'time',
            'default' => 0,
            'comment' => app::get('system')->_('任务开始执行时间'),
            'label' => app::get('system')->_('任务开始执行时间'),
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'owner_thread_id' => array (
            'type' => 'int',
            'default' => -1,
            'comment' => app::get('system')->_('mysql进程ID'),
            'label' => app::get('system')->_('mysql进程ID'),
            'in_list'=>true,
            'default_in_list'=>true,
        ),
    ),
    'index' => array (
        'ind_get' => 
        array (
            'columns' => 
            array (
                0 => 'queue_name',
                1 => 'owner_thread_id',
            ),
        ),
    ),
    'engine' => 'innodb',
    'version' => '$Rev: 40912 $',
    'ignore_cache' => true,
    'comment' => app::get('system')->_('队列-mysql实现表'),
);

