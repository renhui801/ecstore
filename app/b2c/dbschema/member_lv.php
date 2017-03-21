<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['member_lv']=array (
  'columns' =>
  array (
    'member_lv_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => 'ID',
      'width' => 110,
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),
    'name' =>
    array (
      'type' => 'varchar(100)',
      'is_title' => true,
      'required' => true,
      'default' => '',
      'label' => app::get('b2c')->_('等级名称'),
      'width' => 110,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'lv_logo' =>
    array (
      'type' => 'varchar(255)',
      'comment' => app::get('b2c')->_('会员等级LOGO'),
      'editable' => false,
      'label' => app::get('b2c')->_('会员等级LOGO'),
      'in_list' => false,
      'default_in_list' => false,
    ),
    'dis_count' =>
    array (
      'type' => 'decimal(5,2)',
      'default' => '1',
      'required' => true,
      'label' => app::get('b2c')->_('会员折扣率'),
      'width' => 110,
      'match' => '[0-9\\.]+',
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'pre_id' =>
    array (
      'type' => 'mediumint',
      'editable' => false,
      'comment' => app::get('b2c')->_('前一级别ID'),
    ),
    'default_lv' =>
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('是否默认'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'deposit_freeze_time' =>
    array (
      'type' => 'int',
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('保证金冻结时间'),
    ),
    'deposit' =>
    array (
      'type' => 'int',
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('所需保证金金额'),
    ),
    'more_point' =>
    array (
      'type' => 'int',
      'default' => 1,
      'editable' => false,
      'comment' => app::get('b2c')->_('会员等级积分倍率'),
    ),
    'lv_type' =>
    array (
      'type' =>
      array (
        'retail' => app::get('b2c')->_('零售'),
        'wholesale' => app::get('b2c')->_('批发'),
        'dealer' => app::get('b2c')->_('代理'),
      ),
      'default' => 'retail',
      'required' => true,
      'label' => app::get('b2c')->_('等级类型'),
      'width' => 110,
      'editable' => false,
      'in_list' => false,
      'default_in_list' => false,
    ),
    'point' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'label' => app::get('b2c')->_('所需积分'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'comment' => app::get('b2c')->_('所需积分'),
    ),

    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),
    'show_other_price' =>
    array (
      'type' => 'bool',
      'default' => 'true',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('查阅其他会员等级价格'),
    ),
    'order_limit' =>
    array (
      'type' => 'tinyint(1)',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('会员每次下单限制. 0不限制 1遵守批发规则中的最小起批数量和混批规则中的最小起批数量/金额 2 此等级会员每次下单必须达到'),
    ),
    'order_limit_price' =>
    array (
      'type' => 'money',
      'default' => '0.000',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('每次下单必须达到的金额'),
    ),
    'lv_remark' =>
    array (
      'type' => 'text',
      'editable' => false,
      'comment' => app::get('b2c')->_('会员等级备注'),
    ),
    'experience' =>
    array (
      'label' => app::get('b2c')->_('所需经验值'),
      'type' => 'int(10)',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'comment' => app::get('b2c')->_('经验值'),
    ),
    'expiretime' =>
    array (
      'type' => 'int(10)',
      'required' => true,
      'default' => 0,
      'editable' => false,
      'comment' => app::get('b2c')->_('积分过期时间'),
    ),
  ),
  'index' =>
  array (
    'ind_disabled' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
      ),
    ),
    'ind_name' =>
    array (
      'columns' =>
      array (
        0 => 'name',
      ),
      'prefix' => 'UNIQUE',
    ),

  ),
  'engine' => 'innodb',
  'version' => '$Rev: 44523 $',
  'comment' => app::get('b2c')->_('会员等级表'),
);
