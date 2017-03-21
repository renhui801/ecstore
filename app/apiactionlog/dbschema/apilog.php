<?php
$db['apilog'] = array(
    'columns'=>array(
        'apilog_id' =>array (
            'type' => 'bigint unsigned',
            'required' => true,
            'pkey' => true,
            'extra' => 'auto_increment',
            'width' => 110,
            'hidden' => true,
            'editable' => false,
            'in_list' => false,
        ),
        'apilog'=>array(
            'type' => 'varchar(32)',
            'label' => '日志编号',
            'editable' => false,
            'in_list' => false,
            'default_in_list' => false,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'searchtype' => 'has',
        ),
        'original_bn'=>array(
            'type' => 'varchar(50)',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'searchtype' => 'has',
            'label' => '单据号',
            'width' => '90',
            'order'=>10,
        ),
        'msg_id'=>array(
            'type' => 'varchar(60)',
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'searchtype' => 'has',
            'label' => 'msg_id',
            'width' => 200,
            'edtiable' => false,
            'order'=>12,
        ),
        
        'task_name'=>array(
            'type' => 'varchar(255)',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'searchtype' => 'has',
            'label' => '任务名称',
            'width' => 150,
            'panel_id' => 'api_log_finder_top', 
            'order'=>11,
        ),
        'calltime'=>array(
            'type'=>'time',
            'label'=>app::get('apiactionlog')->_('请求时间'),
            'in_list' => true,
            'default_in_list' => true,
            'width'=>'100',
            'order'=>13,
        ),

        'status'=>array(
            'type' =>
            array (
                'running' => '运行中',
                'success' => '成功',
                'fail' => '失败',
                'sending' => '发起中',
            ),
            'required' => true,
            'default' => 'sending',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'label' => '状态',
            'width' => 60,
            'order'=>14,
        ),
        'worker'=>array(
            'type' => 'varchar(200)',
            'editable' => false,
            'in_list' => true,
            'default_in_list' => true,
            'label' => 'api方法名',
            'in_list' => false,
        ),
        'params'=>array(
            'type' => 'longtext',
            'editable' => false,
            'label' => '任务参数',
        ),
        'callback_url'=>array(
            'type' => 'longtext',
            'editable' => false,
            'label' => 'callback地址',
        ),

        'msg'=>array(
            'type' => 'text',
            'default_in_list' => true,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'editable' => false,
            'label' => '错误原因',
        ),
        'log_type'=>array(
            'type' => array(
                'order'=>'订单同步日志',
                'goods'=>'商品同步日志',
                'member'=>'会员同步日志',
                'payments'=>'支付方式同步日志',
                'coupon'=>'优惠券同步日志',
                'other'=>'其他同步日志',
            ),
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'editable' => false,
            'label' => '日志类型',
            'order'=>17,
        ),
        'api_type'=>array(
            'type' =>
            array (
                'response' => '响应',
                'request' => '请求',
            ),
            'editable' => false,
            'default' => 'request',
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'label' => '同步类型',
            'width' => 70,
            'order' => 15,
        ),
        'retry'=>array(
            'type' => 'number',
            'default' => '0',
            'in_list' => true,
            'default_in_list' => true,
            'width' => 70,
            'edtiable' => false,
            'in_list' => true,
            'label' => '重试次数',
            'order' => 16,
        ),
        'createtime'=>array(
            'type' => 'time',
            'label' => '发起同步时间',
            'width' => 130,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
            'filtertype' => 'time',
            'filterdefault' => true,
        ),
        'last_modified'=>array(
            'label' => '最后重试时间',
            'type' => 'last_modify',
            'width' => 130,
            'editable' => false,
            'in_list' => true,
            'default_in_list' => false,
        ),

    ),
    'index' =>
    array (
        'ind_status' =>
        array (
            'columns' =>
            array (
                0 => 'status',
            ),
        ),
        'ind_calltime' =>
        array (
            'columns' =>
            array (
                0 => 'calltime',
            ),
        ),
        'ind_api_type' =>
        array (
            'columns' =>
            array (
                0 => 'api_type',
            ),
        ),
    ),
    'comment' => 'api日志',
    'engine' => 'innodb',

);

