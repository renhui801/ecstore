<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['goods_type']=array (
  'columns' =>
  array (
    'type_id' =>
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => app::get('b2c')->_('类型序号'),
      'width' => 110,
      'editable' => false,
      'in_list' => false,
    ),
    'name' =>
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'default' => '',
      'label' => app::get('b2c')->_('类型名称'),
      'is_title' => true,
      'width' => 150,
      'editable' => true,
      'in_list' => true,
      'default_in_list' => true,
    ),
    'floatstore' =>
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('小数型库存'),
    ),
    'alias' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('类型别名(|分隔,前后|)'),
    ),
    'is_physical' =>
    array (
      'type' => 'intbool',
      'default' => '1',
      'required' => true,
      'label' => app::get('b2c')->_('实体商品'),
      'width' => 75,
      'editable' => false,
      'in_list' => true,
    'default_in_list' => true,
    ),
    'schema_id' =>
    array (
      'type' => 'varchar(30)',
      'required' => true,
      'default' => 'custom',
      'hidden' => 1,
      'width' => 110,
      'editable' => false,
      'comment' => app::get('b2c')->_('供应商序号'),
    ),
    'setting' =>
    array (
      'type' => 'serialize',
      'comment' => app::get('b2c')->_('类型设置'),
      'width' => 110,
      'editable' => false,
      'label' => app::get('b2c')->_('类型设置'),

    ),
    'price' =>
    array (
      'type' => 'serialize',
      'editable' => false,
      'comment' => app::get('b2c')->_('设置价格区间，用于列表页搜索使用'),
    ),
    'minfo' =>
    array (
      'type' => 'serialize',
      'editable' => false,
      'comment' => app::get('b2c')->_('用户购买时所需输入信息的字段定义序列化数组方式 array(字段名,字段含义,类型(input,select,radio))'),
    ),
    'params' =>
    array (
      'type' => 'serialize',
      'editable' => false,
      'comment' => app::get('b2c')->_('参数表结构(序列化) array(参数组名=>array(参数名1=>别名1|别名2,参数名2=>别名1|别名2))'),
    ),
    'tab' =>
    array (
      'type' => 'serialize',
      'editable' => false,
      'comment' => app::get('b2c')->_('商品详情页的自定义tab设置'),
    ),
    'dly_func' =>
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('是否包含发货函数'),
    ),
    'ret_func' =>
    array (
      'type' => 'intbool',
      'default' => '0',
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('是否包含退货函数'),
    ),
    'reship' =>
    array (
      'default' => 'normal',
      'required' => true,
      'type' =>
      array (
        'disabled' => app::get('b2c')->_('不支持退货'),
        'func' => app::get('b2c')->_('通过函数退货'),
        'normal' => app::get('b2c')->_('物流退货'),
        'mixed' => app::get('b2c')->_('物流退货+函数式动作'),
      ),
      'editable' => false,
      'comment' => app::get('b2c')->_('退货方式 disabled:不允许退货 func:函数式'),
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'editable' => false,
    ),

    'is_def' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'label' => app::get('b2c')->_('类型标示'),
      'width' => 110,
      'editable' => false,
      'in_list' => false,
      'comment' => app::get('b2c')->_('是否系统默认'),
    ),
    'lastmodify' =>
    array (
      'label' => app::get('b2c')->_('供应商最后更新时间'),
      'width' => 150,
      'type' => 'time',
      'hidden' => 1,
      'in_list' => false,
      'comment' => app::get('b2c')->_('上次修改时间'),
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
  ),
  'version' => '$Rev: 40654 $',
  'comment' => app::get('b2c')->_('商品类型表'),
);
