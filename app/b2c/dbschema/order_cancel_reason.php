<?php
$db['order_cancel_reason'] = array(
    'columns' => array(
        'order_id' => array(
            'type' => 'table:orders',
            'pkey' => true,
            'default' => 0,
            'editable' => false,
            'comment' => app::get('b2c')->_('订单ID'),
        ),
        'reason_type' => array(
            'type' => array(
                0 => app::get('b2c')->_('不想要了'),
                1 => app::get('b2c')->_('支付不成功'),
                2 => app::get('b2c')->_('价格较贵'),
                3 => app::get('b2c')->_('缺货'),
                4 => app::get('b2c')->_('等待时间过长'),
                5 => app::get('b2c')->_('拍错了'),
                6 => app::get('b2c')->_('订单信息填写错误'),
                7 => app::get('b2c')->_('其它'),
                ),
            'default' => '0',
            'required' => true,
            'label' => app::get('b2c')->_('取消原因类型'),
            'width' => 75,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'reason_desc' => array(
            'type' => 'varchar(150)',
            'label' => app::get('b2c')->_('其他原因'),
            'width' => 75,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'cancel_time' => array(
            'type' => 'time',
            'label' => app::get('b2c')->_('取消时间'),
            'width' => 75,
            'editable' => false,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
    ),
);

