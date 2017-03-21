<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author afei, braynt
 */
$db['partition'] = array(
    'columns' => array(
        'id' => array(
            'type' => 'int unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
        ),
        'app' => array(
            'type' => 'varchar(32)',
            'required' => true,
            'label' => app::get('archive')->_('app应用'),
        ),
        'table' => array(
            'type' => 'varchar(255)',
            'required' => true,
            'label' => app::get('archive')->_('分区表'),
        ),
        'method' =>  array(
            'type' => array(
                'hash' => app::get('archive')->_('hash'),
                'range' => app::get('archive')->_('range'),
            ),
            'default' => 'hash',
            'required' => true,
        ),
        'nums' => array(
            'type' =>'int(4)',
            'label' => app::get('archive')->_('hash分区数目'),
        ),
        'expr' => array(
            'type' =>'varchar(255)',
            'required' => true,
            'label' => app::get('archive')->_('分区表达式'),
        ),
        'last' => array(
            'type' => 'time',
            'required' => true,
            'label' => app::get('archive')->_('最后更新时间'),
        ),
    ),
    'index' =>
    array (
        'ind_index' =>
        array (
          'columns' =>
          array (
            0 => 'app',
            1 => 'table',
          ),
          'prefix' => 'UNIQUE',
        ),
    ),
    'version' => '$Rev: 41137 $',
    'comment' => app::get('archive')->_('mysql分区维护表'),
);
