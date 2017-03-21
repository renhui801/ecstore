<?php
$db['giftpackage']=array(
    'columns' =>array (
        'id'=>
        array(
            'type'=>'mediumint(8)',
            'extra'=>'auto_increment',
            'pkey'=>'true',
            'label'=>__('礼包ID'),
            'hidden'=>true,
        ),
        'name' =>
        array (
            'type' => 'varchar(255)',
            'required'=>true,
            'default'=>'null',
            'label'=>__('礼包名称'),
            'in_list' => true,
            'default_in_list' => true,
            'editable' => true,
            'fuzzySearch' => 1,
        ),
        'amount' =>
        array (
            'type' =>'money',
            'required'=>true,
            'default'=>'0',
            'in_list' => true,
            'default_in_list' => true,
            'label'=>__('礼包总价'),
            'editable' => true,
        ),
        'store'=>
        array(
            'type'=>'mediumint(8) ',
            'default'=>'0',
            'label'=>__('礼包库存'),
            'in_list' => true,
            'default_in_list' => true,
            'editable' => true,
            'hidden' =>true,
        ),
        'freez'=>
        array(
            'type'=>'mediumint(8) ',
            'default'=>0,
            'label'=>__('冻结库存'),
        ),
        'goods_count'=>                 //礼包内的商品总数
        array(
            'type'=>'mediumint(10)',
            'required'=>true,
            'default'=>'0',
            'label'=>__('礼包商品数'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'type'=>
        array(
            'type'=>'enum(\'1\',\'2\')',
            'default'=>'1',
            'label'=>__('类型'),
            'hidden'=>true,
            'in_list' => true,
        ),
        'stime'=>
        array(
            'type'=>'time',
            //'default'=>'-',
            'label'=>__('开始时间'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'etime'=>
        array(
            'type'=>'time',
            //'default'=>'-',
            'label'=>__('结束时间'),
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'limitbuy_count'=>
        array(
            'type'=>'mediumint(8)',
            'default'=>'0',
            'label'=>__('限购数量'),
            'editable' => true,
            'in_list' => true,
        ),
        'weight'=>
        array(
            'type'=>'varchar(10)',
            'default'=>'0',
            'label'=>__('礼包重量'),
            'editable' => true,
            'in_list' => true,
        
        ),
        'score'=>
        array(
            'type'=>'mediumint(8)',
            'default'=>'0',
            'label'=>__('积分'),
            'editable' => true,
            'in_list' => true,
        ),
        'goods'=>array(
            'type'=>'serialize',
            'default'=>'',
            'editable' => false,
        ),
        'image'=>array(
            'type'=>'varchar(32)',
            'default'=>'',
            'label'=>__('图片'),
        ),
        'repeat' =>
        array (
            'type' => 'bool',
            'default' => 'false',
            'editable' => false,
            'required'=>true,
        ),
        'alluser' =>
        array (
            'type' => 'bool',
            'default' => 'true',
            'editable' => false,
            'required'=>true,
        ),
        'member_lv_ids' =>
        array (
            'type' => 'varchar(100)',
            'default' => '',
            'editable' => false,
            'required'=>true,
        ),
        'order'=>array(
            'type'=>'mediumint(8)',
            'default'=>0,
            'label'=>__('排序'),
            'editable' => true,
        ),
        'intro'=>
        array(
            'type'=>'longtext',
            'default'=>'null',
            'editable' => true,
            'label'=>__('描述'),
        ),
    ),
    
    'index' =>
        array (
        'g_index' =>
            array (
                'columns' =>
                array (
                    0 => 'name',
                ),
            ),
    ),
);