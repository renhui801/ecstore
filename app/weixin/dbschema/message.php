<?php
/**
 * ShopEx licence
 *
* 微信自动回复内容
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$db['message']=array (
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
        'bind_id' => array(
          'type' => 'table:bind',
          'required' => true,
          'default' => 0,
          'editable' => false,
          'searchtype' => 'nequal',
          'filtertype' => false,
          'filterdefault' => 'true',
          'order'=>10,
          'label' => app::get('weixin')->_('公众账号'),
          'in_list' => true,
          'default_in_list' => true,
        ),
        'message_id' => 
        array (
            'type' => 'number',
            'label' => app::get('wap')->_('消息名称'),
            'order'=>20,
        ),
        'message_type'=>
        array(
            'type' =>
            array (
                'text' => app::get('weixin')->_('文字'),
                'image' => app::get('weixin')->_('图文'),
            ),
            'order'=>40,
            'required' => true,
            'default' => 'text',
            'label' => app::get('weixin')->_('消息类型'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'reply_type'=>
        array(
            'type' =>
            array (
                'attention' => app::get('weixin')->_('关注自动回复'),
                'message' => app::get('weixin')->_('消息自动回复'),
                'keywords' => app::get('weixin')->_('关键词自动回复'),
            ),
            'required' => true,
            'default' => 'attention',
            'label' => app::get('weixin')->_('自动回复类型'),
        ),
        'keywords' => 
        array (
            'type' => 'text',
            'label' => app::get('wap')->_('关键词'),
            'searchtype' => 'has',
            'filtertype' => 'yes',
            'filterdefault' => 'true',
            'in_list' => true,
            'default_in_list' => true,
            'order' => 90,
        ),
        'keywords_rule' => 
        array (
            'type' =>
            array (
                'nequal' => app::get('weixin')->_('完全匹配'),
                'has' => app::get('weixin')->_('包含'),
            ),
            'default'=>'nequal',
            'label' => app::get('wap')->_('关键词匹配规则'),
        ),
    ),
    'version' => '$Rev: 40918 $',
    'comment' => app::get('wap')->_('自动回复'),
);
