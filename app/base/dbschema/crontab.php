<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author afei, braynt
 */
$db['crontab']=array (
    'columns' =>
    array (
        'id' => array(
            'type'=>'varchar(100)',
            'pkey'=> true,
            'required'=> true,
            'label' => app::get('base')->_('定时任务ID'),
            'editable'=> false,
            'is_title'=> true,
            'in_list'=> true,
            'default_in_list'=> false,
            'width' => 70,            
            'order' => 10,
        ),


        'description' => array(
            'required'=>true,
            'type'=>'varchar(255)',
            'label' => app::get('base')->_('描述'),
            'in_list' => true,
            'default_in_list' => true,
            'order' => 15,
        ),
        
        'enabled' => array(
            'type'=>'bool',
            'default'=>'true',
            'label' => app::get('base')->_('开启'),
            'required'=>true,
            'in_list' => true,
            'default_in_list' => true,
            'order' => 20,            
        ),
        
        'schedule' => array(
            'type'=>'varchar(255)',
            'label' => app::get('base')->_('规则'),
            'required'=>true,
            'in_list' => true,
            'default_in_list' => true,
            'order' =>30,
        ),
        'last' => array(
            'type'=>'time',
            'label' => app::get('base')->_('最后执行时间'),
            'required'=>true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'app_id' => array (
            'type' => 'varchar(32)',
            'required' => true,
            'width' => 50,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('base')->_('app应用'),        
        ),
        'class' => array(
            'type'=>'varchar(100)',
            'required'=>true,
            'label' => app::get('base')->_('定时任务类名'),
            'editable' => false,
            'in_list'=>true,
            'default_in_list'=>false,
            'order' => 100,
        ),
        'type' => array(
            'type'=> array(
                'custom' => '客户自定义',
                'system' => '系统内置'),
            'label' => app::get('base')->_('定时器类型'),
            'in_list' => true,
            'default_in_list' => false,
        ),
    ),
    'version' => '$Rev: 41137 $',
    'ignore_cache' => true,
    'comment' => app::get('base')->_('定时任务表'),
);
