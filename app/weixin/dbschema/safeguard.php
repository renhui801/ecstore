<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['safeguard']=array (
    'columns' => array (
        'id' =>
        array(
          'type' => 'int unsigned',
          'required' => true,
          'pkey' => true,
          'extra' => 'auto_increment',
          'editable' => false,
          'comment' => app::get('weixin')->_('ID'),
        ),
        'appid' =>
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'default' => '',
            'label' => app::get('weixin')->_('公众号ID'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'openid' =>
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'default'=>'',
            'comment' => app::get('weixin')->_('用户ID'),
        ),
        'weixin_nickname' =>
        array (
            'type' => 'varchar(100)',
            'default'=>'',
            'comment' => app::get('weixin')->_('微信昵称'),
        ),
        'msgtype' =>
        array (
            'type' => array(
                'request'=>app::get('weixin')->_('用户提交投诉'),
                'confirm'=>app::get('weixin')->_('用户确认消除投诉'),
                'reject'=>app::get('weixin')->_('用户拒绝消除投诉'),
            ),
            'required' => true,
            'default' => 'request',
            'comment' => app::get('weixin')->_('通知类型'),
        ),
        'status' =>
        array (
            'type' => array(
                '1'=>app::get('weixin')->_('待处理'),
                '2'=>app::get('weixin')->_('处理中'),
                '3'=>app::get('weixin')->_('已解决'),
            ),
            'required' => true,
            'default' => '1',
            'label' => app::get('weixin')->_('处理状态'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'feedbackid' =>
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'default' => '',
            'order'=>10,
            'label' => app::get('weixin')->_('投诉单号'),
            'filtertype' => 'number',
            'searchtype' => 'has',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'transid' =>
        array (
            'type' => 'varchar(100)',
            'label' => app::get('b2c')->_('交易订单号'),
            'filtertype' => 'number',
            'searchtype' => 'has',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'reason' =>
        array (
            'type' => 'text',
            'label' => app::get('b2c')->_('用户投诉原因'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'solution' =>
        array (
            'type' => 'text',
            'label' => app::get('b2c')->_('用户希望解决方案'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'extinfo' =>
        array (
            'type' => 'text',
            'label' => app::get('b2c')->_('备注信息+电话'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'picurl' =>
        array (
            'type' => 'text',
            'label' => app::get('b2c')->_('用户上传的图片凭证,最多五张'),
        ),
        'timestamp' =>
        array (
            'type' => 'time',
            'label' => app::get('b2c')->_('创建时间'),
            'filtertype' => 'time',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
    ),
    'version' => '$Rev: 40918 $',
    'comment' => app::get('weixin')->_('维权信息表'),
);
