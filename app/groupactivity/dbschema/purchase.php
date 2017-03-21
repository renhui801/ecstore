<?php
/**
 * state状态说明：
 * 1：未开始（当前时间未到活动开始时间）
 * 2：进行中（当前时间在活动开始时间和结束时间区间内）
 * 3：已结束（成功）（当前时间在活动结束时间后or已预订商品数达到限购数量，并且预订商品数量达到或超过最小优惠价格阶梯要求）
 * 4：已结束，待处理（当前时间在活动结束时间后or已预订商品数达到限购数量,并且预订商品数量未达到最小优惠价格阶梯要求）
 * 5:已结束（失败）（团购订单作废）
 */
$db['purchase']=array(
    'columns' =>array (
        'act_id'=>array(
            'type'=>'mediumint(8)',
            'extra'=>'auto_increment',
            'pkey'=>'true',
            'label'=>__('序号'),
            'in_list'=>true,
            //'hidden'=>true,
        ),
        'gid'=>array(
            'type'=>'table:goods@b2c',
            'required'=>true,
            'label'=>__('活动商品名称'),
            'editable'=>false,
            'locked' => 1,
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'name'=>array(
            'type'=>'varchar(200)',
            'label'=>__('活动商品名称'),
            'editable'=>false,
            'locked' => 1,
            'is_title'=>true,
        ),
        'start_value'=>array(
            'type'=>'int(10)',
            'label'=>__('起始值'),
            'editable'=>false,
        ),
        'start_time'=>array(
            'type'=>'time',
            'label'=>__('开始时间'),
            'editable' => false,
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'end_time'=>array(
            'type'=>'time',
            'label'=>__('结束时间'),
            'editable' => false,
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'buy'=>array(
            'type' => 'mediumint(8)',
            'default' => 0,
            'label'=>__('已经购买量(已付款)'),
            'editable' => false,
        ),
        'min_buy'=>array(
            'type'=>'mediumint(8)',
            'label'=>__('最小购买量'),
            'default'=>'0',
            'editable'=>false,
            'filtertype'=>'number',
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'max_buy'=>array(
            'type'=>'mediumint(8)',
            'label'=>__('最大购买量（满足时团购结束）'),
            'default'=>'0',
            'editable'=>false,
            'filtertype'=>'number',
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'orderlimit'=>array(
            'type'=>'mediumint(8)',
            'label'=>__('每单限购'),
            'editable'=>false,
            'filtertype'=>'number',
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'userlimit'=>array(
            'type'=>'mediumint(8)',
            'label'=>__('每人限购'),
            'editable'=>false,
            'filtertype'=>'number',
            'in_list'=>true,
            'default_in_list'=>true,
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
        'score'=>array(
            'type'=>'mediumint(8)',
            'label'=>__('积赠送分'),
            'editable' => true,
            'filtertype'=>'number',
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'price'=>array(
            'type'=>'money',
            'default'=>0,
            'label'=>__('价格'),
            'editable'=>false,
            'hidden'=>true,
            'in_list'=>true,
            'default_in_list'=>true,
        ),
        'pro_type'=>array(
            'type'=>array(
                    1=>'达到一定费用免运费',
                    2=>'无邮费优惠',
                ),
            'default'=>2,
            'label'=>__('优惠类型'),
            'editable'=>false,
            'hidden'=>true,
            'in_list'=>true,
        ),
        'postage'=>array(
            'type'=>'number',
            'default'=>0,
            'label'=>__('邮费优惠'),
            'editable'=>false,
            'hidden'=>true,
            'in_list'=>true,
        ),
        'intro'=>array(
            'type'=>'longtext',
            'default'=>'null',
            'label'=>__('团购说明'),
            'editable' => false,
            'hidden'=>true,
            'filtertype'=>'normal',
        
        ),
        'state'=>array(
            'type'=>array(
                1=>__('未开始'),
                2=>__('进行中'),
                3=>__('已结束（成功）'),
                4=>__('已结束，待处理'),
                5=>__('已结束（失败）')
            ),
            'default'=>'1',
            'label'=>__('活动状态'),
            'editable'=>false,
            'in_list'=>true,
            'default_in_list' => true,
        ),
        'act_open'=>array(
            'type' => 'bool',
            'default' => 'false',
            'label'=>__('活动开启状态'),
            'editable' => false,
            'required' => false,
            'in_list'=>true,
            'default_in_list'=>true,
        ),
		'last_modified'=>array(
            'type' => 'time',
            'label'=>__('最后修改时间'),
            'editable' => false,
            'required' => false
        ),
    ),
    
    'index' =>
        array (
        'act_index' =>
            array (
                'columns' =>
                array (
                    0 => 'act_id',
                ),
            ),
        'gid_uni' =>
            array (
                'columns' =>
                    array (
                        0 => 'act_id',
                    ),
                'prefix' => 'UNIQUE',
            ),
    ),
);
