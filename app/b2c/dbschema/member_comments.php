<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['member_comments']=array (
  'columns' =>
  array (
    'comment_id' => array (
        'type' => 'number',
        'required' => true,
        'pkey' => true,
        'extra' => 'auto_increment',
        'label' => 'ID',
        'width' => 110,
        'editable' => false,
        'default_in_list' => true,
        'comment' => app::get('b2c')->_('评论ID'),
    ),
    'for_comment_id' => array (
        'type' => 'mediumint(8) ',
        'label' => app::get('b2c')->_('对m的回复'),
        'default' =>0,
    ),
    'type_id' => array(
        'type' => 'table:goods',
        'label' =>app::get('b2c')->_('名称'),
        'in_list' => true,
        'default_in_list' => true,
    ),
    'product_id' => array(
        'type' => 'number',
        'label' =>app::get('b2c')->_('货品ID'),
        'default' => 0,
    ),
    'order_id' => array(
        'type' => 'table:orders',
        'label' => app::get('b2c')->_('订单编号'),
        'in_list' => false,
        'default_in_list' => false,
    ),
    'object_type' => array (
        'type' => "enum('ask', 'discuss', 'buy', 'message', 'msg', 'order')",
        'label' => app::get('b2c')->_('类型'),
        'default' => 'ask',
        'required' => true,
    ),
    'author_id' => array(
        'type'=>'mediumint(8)',
        'in_list' => false,
        'label' => app::get('b2c')->_('作者ID'),
        'default' => 0,
        'default_in_list' => false,
    ),
    'author' => array (
        'type' => 'varchar(100)',
        'label' => app::get('b2c')->_('发表人'),
        'searchtype' => 'has',
        'filtertype' => 'normal',
        'filterdefault' => 'true',
        'in_list' => true,
    ),
    'contact' =>
    array (
        'type' => 'varchar(255)',
        'label' => app::get('b2c')->_('联系方式'),
        'width' => 110,
        'filtertype' => 'normal',
        'filterdefault' => 'true',
        'in_list' => true,
    ),
    'mem_read_status' => array (
        'type' => "enum('false', 'true')",
        'label' => app::get('b2c')->_('会员阅读标识'),
        'default'=>'false',
    ),
    'adm_read_status' => array (
        'type' => "enum('false', 'true')",
        'label' => app::get('b2c')->_('管理员阅读标识'),
        'default'=>'false',
    ),
    'time' => array (
        'type' => 'time',
        'in_list' => true,
        'filtertype' => 'normal',
        'filterdefault' => 'true',
        'label' => app::get('b2c')->_('时间'),
    ),
    'lastreply' => array (
        'type' => 'time',
        'label' => app::get('b2c')->_('最后回复时间'),
    ),
     'reply_name' => array(
        'type'=>'varchar(100)',
        'in_list' => true,
        'label' => app::get('b2c')->_('最后回复人'),
        'default_in_list' => true,
    ),
    'inbox' => array (
        'type' => 'bool',
        'label' => app::get('b2c')->_('收件箱'),
        'default'=>'true',
    ),
    'track' => array (
        'type' => 'bool',
        'label' => app::get('b2c')->_('发件箱'),
        'default'=>'true',
    ),
     'has_sent' => array (
        'type' => 'bool',
        'label' => app::get('b2c')->_('是否发送'),
        'default'=>'true',
        ),
    'to_id' => array (
        'type' => 'table:members',
        'default' =>0,
        'required' => true,
        'comment' => app::get('b2c')->_('目标会员序号ID'),
    ),
    'to_uname' => array(
        'type'=>'varchar(100)',
        'default_in_list' => true,
        'comment' => app::get('b2c')->_('目标会员姓名'),
    ),
     'title' => array(
        'type'=>'varchar(255)',
        'in_list' => true,
        'label' => app::get('b2c')->_('标题'),
        'is_title'=>true,
        'searchtype' => 'has',
        'filtertype' => 'normal',
        'filterdefault' => 'true',
        'default_in_list' => true,
    ),
    'comment' => array(
        'type'=>'longtext',
        'label' => app::get('b2c')->_('内容'),
        'in_list' => true,
        'searchtype' => 'has',
        'filtertype' => 'normal',
        'filterdefault' => 'true',
        'default_in_list' => true,
    ),
    'ip' => array(
        'type'=>'varchar(15)',
        'in_list' => true,
        'label' => 'IP',
        'default_in_list' => true,
        'comment' => app::get('b2c')->_('ip地址'),
    ),
    'display' => array(
        'type'=>'bool',
        'in_list' => true,
        'label' => app::get('b2c')->_('前台是否显示'),
        'filtertype' => 'bool',
        'default' =>'true',
        'default_in_list' => true,
    ),
    'gask_type' =>array(
      'type' => 'varchar(50)',
      'default' =>'',
      'editable' => false,
      'comment' => app::get('b2c')->_('留言类型 针对订单留言'),
    ),
     'addon' =>array(
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('序列化'),
    ),
    'p_index' => array(
        'type'=>'tinyint(2)',
        'label' => 'p_index',
        'default_in_list' => true,
        'comment' => app::get('b2c')->_('弃用'),
    ),
    'disabled' => array(
        'type'=> "enum('false', 'true')",
        'default' =>'false',
        'default_in_list' => true,
    ),
  ),
   'engine' => 'innodb',
   'version' => '$Rev$',
   'comment' => app::get('b2c')->_('咨询,评论,留言,短消息表'),
   'index' =>
   array (
       'index_for_comment_id' =>
       array (
           'columns' =>
           array (
               0 => 'for_comment_id',
           ),
       ),
   ),

);
