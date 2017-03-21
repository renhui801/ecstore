<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['members']=array (
  'columns' =>
  array (
    'member_id' =>
    array (
      'type' => 'number',
      'extra' => 'auto_increment',
      'pkey' => true,
      'label' => app::get('b2c')->_('会员用户名'),
    ),
    'member_lv_id' =>
    array (
      'required' => true,
      'default' => 0,
      'label' => app::get('b2c')->_('会员等级'),
      'sdfpath' => 'member_lv/member_group_id',
      'width' => 75,
      'order' => 40,
      'type' => 'table:member_lv',
      'editable' => true,
      'filtertype' => 'bool',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'crm_member_id' =>
    array (
      'default' => 0,
      'label' => app::get('b2c')->_('联通CRM，存储CRM的member_id'),
      'type' => 'number',
    ),
    'name' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('姓名'),
      'width' => 75,
      'sdfpath' => 'contact/name',
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'is_title'=>true,
      'default_in_list' => false,
    ),
    'point' =>
    array (
      'type' => 'int(10)',
      'default' => 0,
      'required' => true,
      'sdfpath' => 'score/total',
      'label' => app::get('b2c')->_('积分'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'number',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'lastname' =>
    array (
      'sdfpath' => 'contact/lastname',
      'type' => 'varchar(50)',
      'editable' => false,
      'comment' => app::get('b2c')->_('姓氏'),
    ),
    'firstname' =>
    array (
      'sdfpath' => 'contact/firstname',
      'type' => 'varchar(50)',
      'editable' => false,
      'comment' => app::get('b2c')->_('名字'),
    ),
    'area' =>
    array (
      'label' => app::get('b2c')->_('地区'),
      'width' => 110,
      'type' => 'region',
      'sdfpath' => 'contact/area',
      'editable' => false,
      'filtertype' => 'yes',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => false,
    ),
    'addr' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('地址'),
      'sdfpath' => 'contact/addr',
      'width' => 110,
      'editable' => true,
      'filtertype' => 'normal',
      'in_list' => true,
      'default_in_list' => false,

    ),
    'mobile' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('手机'),
      'width' => 75,
      'sdfpath' => 'contact/phone/mobile',
      //'searchtype' => 'head',
      //'editable' => true,
      //'filtertype' => 'normal',
      //'filterdefault' => 'true',
      //'in_list' => true,
      //'default_in_list' => false,
    ),
    'tel' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('固定电话'),
      'width' => 110,
      'sdfpath' => 'contact/phone/telephone',
      'searchtype' => 'head',
      'editable' => true,
      'filtertype' => 'normal',
      'filterdefault' => 'true',
      'in_list' => true,
      'default_in_list' => false,
    ),
    'email' =>
    array (
      'type' => 'varchar(200)',
      'label' => 'EMAIL',
      'width' => 110,
      'sdfpath' => 'contact/email',
      'default' => '',
      //'searchtype' => 'has',
      //'editable' => true,
      //'filtertype' => 'normal',
      //'filterdefault' => 'true',
      //'in_list' => true,
      //'default_in_list' => false,
    ),
    'zip' =>
    array (
      'type' => 'varchar(20)',
      'label' => app::get('b2c')->_('邮编'),
      'width' => 110,
      'sdfpath' => 'contact/zipcode',
      'editable' => true,
      'filtertype' => 'normal',
      'in_list' => true,
    ),

    'order_num' =>
    array (
      'type' => 'number',
      'default' => 0,
      'label' => app::get('b2c')->_('订单数'),
      'width' => 70,
      'editable' => false,
      'hidden' => true,
      'in_list' => true,
      'order' => 100,
      'default_in_list' => true,
    ),
    'refer_id' =>
    array (
      'type' => 'varchar(50)',
      'label' => app::get('b2c')->_('来源ID'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => false,
    ),
    'refer_url' =>
    array (
      'type' => 'varchar(200)',
      'label' => app::get('b2c')->_('推广来源URL'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => false,
    ),
    'b_year' =>
    array (
        'label' => app::get('b2c')->_('生年'),
      'type' => 'smallint unsigned',
      'width' => 30,
      'editable' => false,
      'in_list'=>false,
    ),
    'b_month' =>
    array (
      'label' => app::get('b2c')->_('生月'),
      'type' => 'tinyint unsigned',
      'width' => 30,
      'editable' => false,
      'hidden' => true,
      'in_list' => false,
    ),
    'b_day' =>
    array (
      'label' => app::get('b2c')->_('生日'),
      'type' => 'tinyint unsigned',
      'width' => 30,
      'editable' => false,
      'hidden' => true,
      'in_list' => false,
    ),
    'sex' =>
    array (
      'type' =>
      array (
        0 => app::get('b2c')->_('女'),
        1 => app::get('b2c')->_('男'),
        2 => '-',
      ),
      'sdfpath' => 'profile/gender',
      'default' => 2,
      'required' => true,
      'label' => app::get('b2c')->_('性别'),
      'order' => 30,
      'width' => 40,
      'editable' => true,
      'filtertype' => 'yes',
      'in_list' => true,
      'default_in_list' => true,
    ),
    'addon' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('会员额外序列化信息'),
    ),
    'wedlock' =>
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('婚姻状况'),
    ),
    'education' =>
    array (
      'type' => 'varchar(30)',
      'editable' => false,
      'comment' => app::get('b2c')->_('教育程度'),
    ),
    'vocation' =>
    array (
      'type' => 'varchar(50)',
      'editable' => false,
      'comment' => app::get('b2c')->_('职业'),
    ),
    'interest' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('扩展信息里的爱好'),
    ),
    'advance' =>
    array (
      'type' => 'decimal(20,3) unsigned',
      'default' => '0.00',
      'required' => true,
      'label' => app::get('b2c')->_('预存款'),
      'sdfpath' => 'advance/total',
      'width' => 110,
      'editable' => false,
      'filtertype' => 'number',
      'in_list' => true,
      'comment' => app::get('b2c')->_('会员账户余额'),
    ),
    'advance_freeze' =>
    array (
      'type' => 'money',
      'default' => '0.00',
      'sdfpath' => 'advance/freeze',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('会员预存款冻结金额'),
    ),
    'point_freeze' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'sdfpath' => 'score/freeze',
      'editable' => false,
      'comment' => app::get('b2c')->_('会员当前冻结积分(暂时停用)'),
    ),
    'point_history' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('会员历史总积分(暂时停用)'),
    ),

    'score_rate' =>
    array (
      'type' => 'decimal(5,3)',
      'editable' => false,
      'comment' => app::get('b2c')->_('积分折换率'),
    ),
    'reg_ip' =>
    array (
      'type' => 'varchar(16)',
      'label' => app::get('b2c')->_('注册IP'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'comment' => app::get('b2c')->_('注册时IP地址'),
    ),
    'regtime' =>
    array (
      'label' => app::get('b2c')->_('注册时间'),
      'width' => 75,
      'type' => 'time',
      'editable' => false,
      'filtertype' => 'time',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'comment' => app::get('b2c')->_('注册时间'),
    ),
    'state' =>
    array (
      'type' => 'tinyint(1)',
      'default' => 0,
      'required' => true,
      'label' => app::get('b2c')->_('验证状态'),
      'width' => 110,
      'editable' => false,
      'in_list' => false,
      'comment' => app::get('b2c')->_('会员验证状态'),
    ),
    'pay_time' =>
    array (
      'type' => 'number',
      'editable' => false,
      'comment' => app::get('b2c')->_('上次结算时间'),
    ),
    'biz_money' =>
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('上次结算后到现在的所有因商业合作(推广人,代理)而产生的可供结算的金额'),
    ),
    /*
    'pw_answer' =>
    array (
      'label' => app::get('b2c')->_('回答'),
      'type' => 'varchar(250)',
      'sdfpath' => 'account/pw_answer',
      'editable' => false,
    ),
    'pw_question' =>
    array (
      'label' => app::get('b2c')->_('安全问题'),
      'type' => 'varchar(250)',
      'sdfpath' => 'account/pw_question',
      'editable' => false,
    ),*/
    'fav_tags' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('会员感兴趣的tag'),
    ),
    'custom' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('用户可根据自己的需要定义额外的会员注册信息,这里存的是序列化后的信息目前系统序列化进去的有： industry:工作行业 company:工作单位 co_addr:公司地址 salary:月收入'),
    ),
    'cur' =>
    array (
      'sdfpath' => 'currency',
      'type' => 'varchar(20)',
      'label' => app::get('b2c')->_('货币'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'comment' => app::get('b2c')->_('货币(偏爱货币)'),
    ),
    'lang' =>
    array (
      'type' => 'varchar(20)',
      'label' => app::get('b2c')->_('语言'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'comment' => app::get('b2c')->_('偏好语言'),
    ),
    'unreadmsg' =>
    array (
      'type' => 'smallint unsigned',
      'default' => 0,
      'required' => true,
      'label' => app::get('b2c')->_('未读信息'),
      'width' => 110,
      'editable' => false,
      'filtertype' => 'number',
      'in_list' => true,
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'remark' =>
    array (
      'label' => app::get('b2c')->_('备注'),
      'type' => 'text',
      'width' => 75,
      'in_list' => true,
    ),
    'remark_type' =>
    array (
      'type' => 'varchar(2)',
      'default' => 'b1',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('备注类型'),
    ),
    'login_count' =>
    array (
      'type' => 'int(11)',
      'default' => 0,
      'required' => true,
      'editable' => false,
    ),
    'experience' =>
    array (
      'label' => app::get('b2c')->_('经验值'),
      'type' => 'int(10)',
      'default' => 0,
      'editable' => false,
      'in_list' => true,
    ),
    'foreign_id' =>
    array (
      'type' => 'varchar(255)',
      'comment' => app::get('b2c')->_('foreign_id(弃用'),
    ),
    'resetpwd'=>
    array(
        'type'=>'varchar(255)',
        'comment'=>app::get('b2c')->_('找回密码唯一标示'),
    ),
    'resetpwdtime'=>
    array(
        'type'=>'time',
        'comment'=>app::get('b2c')->_('找回密码时间'),
    ),
    'member_refer' =>
    array (
      'type' => 'varchar(50)',
      'hidden' => true,
      'default' => 'local',
      'comment' => app::get('b2c')->_('会员来源(弃用)'),
    ),
  'source' =>
    array (
      'type' => array(
            'pc' =>app::get('b2c')->_('标准平台'),
            'wap' => app::get('b2c')->_('手机触屏'),
            'weixin' => app::get('b2c')->_('微信商城'),
            'api' => app::get('b2c')->_('API注册')
       ),
      'required' => false,
      'label' => app::get('b2c')->_('平台来源'),
      'width' => 110,
      'editable' => false,
      'default' =>'pc',
      'in_list' => true,
      'default_in_list' => false,
      'filterdefault' => false,
      'filtertype' => 'normal',
    ),
  ),
  'comment' => app::get('b2c')->_('商店会员表'),
  'index' =>
  array (
    'ind_email' =>
    array (
      'columns' =>
      array (
        0 => 'email',
      ),
    ),
    'ind_regtime' =>
    array (
      'columns' =>
      array (
        0 => 'regtime',
      ),
    ),
    'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 42798 $',
  'comment' => app::get('b2c')->_('会员信息主表'),
);
