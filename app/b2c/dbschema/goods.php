<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['goods']=array (
  'columns' =>
  array (
    'goods_id' =>
    array (
      'type' => 'bigint unsigned',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'label' => '商品ID',
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'bn' =>
    array (
      'type' => 'varchar(200)',
      'label' => app::get('b2c')->_('商品编号'),
      'width' => 110,
      'searchtype' => 'head',
      'editable' => true,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'name' =>
    array (
      'type' => 'varchar(200)',
      'required' => true,
      'default' => '',
      'label' => app::get('b2c')->_('商品名称'),
      'is_title' => true,
      'width' => 310,
      'searchtype' => 'has',
      'editable' => true,
      'filtertype' => 'custom',
      'filterdefault' => true,
      'filtercustom' =>
      array (
        'has' => app::get('b2c')->_('包含'),
        'tequal' => app::get('b2c')->_('等于'),
        'head' => app::get('b2c')->_('开头等于'),
        'foot' => app::get('b2c')->_('结尾等于'),
      ),
      'in_list' => true,
      'default_in_list' => true,
      'order'=>'1',
    ),
    'price' =>
    array (
      'type' => 'money',
      'sdfpath' => 'product[default]/price/price/price',
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('销售价'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'number',
      'filterdefault' => true,
      'in_list' => true,
      'orderby'=>true,

    ),
     'type_id' =>
    array (
      'type' => 'table:goods_type',
      'sdfpath' => 'type/type_id',
      'label' => app::get('b2c')->_('类型'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'yes',
      'in_list' => true,
      'default_in_list' => true,
    ),
     'cat_id' =>
    array (
      'type' => 'table:goods_cat',
      'required' => true,
      'sdfpath' => 'category/cat_id',
      'default' => 0,
      'label' => app::get('b2c')->_('分类'),
      'width' => 75,
      'editable' => true,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
      'default_in_list' => true,
      'orderby'=>true,
    ),
    'brand_id' =>
    array (
      'type' => 'table:brand',
      'sdfpath' => 'brand/brand_id',
      'label' => app::get('b2c')->_('品牌'),
      'width' => 75,
      'editable' => true,
      'hidden' => true,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'marketable' =>
    array (
      'type' => 'bool',
      'default' => 'true',
      'sdfpath' => 'status',
      'required' => true,
      'label' => app::get('b2c')->_('上架'),
      'width' => 30,
      'editable' => true,
      'filtertype' => 'yes',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'store' =>
    array (
      'type' => 'number',
      'label' => app::get('b2c')->_('库存'),
      'default'=>0,
      'width' => 30,
      'editable' => false,
      'filtertype' => 'number',
      'filterdefault' => true,
      'in_list' => true,
    ),
    'notify_num' =>
    array (
      'type' => 'number',
      'default' => 0,
      'required' => true,
      'label' => app::get('b2c')->_('缺货登记'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'uptime' =>
    array (
      'type' => 'time',
      'depend_col' => 'marketable:true:now',
      'label' => app::get('b2c')->_('上架时间'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'downtime' =>
    array (
      'type' => 'time',
      'depend_col' => 'marketable:false:now',
      'label' => app::get('b2c')->_('下架时间'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
    ),
    'last_modify' =>
    array (
      'type' => 'last_modify',
      'label' => app::get('b2c')->_('更新时间'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'orderby' => true,
    ),
    'p_order' =>
    array (
      'type' => 'number',
      'default' => 30,
      'required' => true,
      'label' => app::get('b2c')->_('排序'),
      'width' => 110,
      'editable' => false,
      'hidden' => true,
      'in_list' => false,
      'orderby'=>true,


    ),
    'd_order' =>
    array (
      'type' => 'number',
      'default' => 30,
      'required' => true,
      'label' => app::get('b2c')->_('动态排序'),
      'width' => 30,
      'editable' => true,
      'in_list' => true,
      'orderby'=>true,

    ),
    'score' =>
    array (
      'type' => 'number',
      'sdfpath' => 'gain_score',
      'label' => app::get('b2c')->_('积分'),
      'width' => 30,
      'editable' => false,
      'in_list' => true,
    ),
    'cost' =>
    array (
      'type' => 'money',
      'sdfpath' => 'product[default]/price/cost/price',
      'default' => '0',
      'required' => true,
      'label' => app::get('b2c')->_('成本价'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'number',
      'in_list' => true,
    ),
    'mktprice' =>
    array (
      'type' => 'money',
      'sdfpath' => 'product[default]/price/mktprice/price',
      'label' => app::get('b2c')->_('市场价'),
      'width' => 75,
      'editable' => false,
      'filtertype' => 'number',
      'in_list' => true,
    ),
   'weight' =>
    array (
      'type' => 'decimal(20,3)',
      'sdfpath' => 'product[default]/weight',
      'label' => app::get('b2c')->_('重量'),
      'width' => 75,
      'editable' => false,
      'in_list' => true,
    ),

    'unit' =>
    array (
      'type' => 'varchar(20)',
      'sdfpath' => 'unit',
      'label' => app::get('b2c')->_('单位'),
      'width' => 30,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
   'brief' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('商品简介'),
      'width' => 110,
      'hidden' => false,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => true,
    ),
    'goods_type' =>
    array (
      'type' =>
      array (
        'normal' => app::get('b2c')->_('普通商品'),
        'bind' => app::get('b2c')->_('捆绑商品'),
        'gift' => app::get('b2c')->_('赠品'),
      ),
      'sdfpath' => 'goods_type',
      'default' => 'normal',
      'required' => true,
      'label' => app::get('b2c')->_('销售类型'),
      'width' => 110,
      'editable' => false,
      'hidden' => true,
      'in_list' => false,

    ),
    'image_default_id' =>
    array (
      'type' => 'varchar(32)',
      'label' => app::get('b2c')->_('默认图片'),
      'width' => 75,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'udfimg' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'label' => app::get('b2c')->_('是否用户自定义图'),
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'thumbnail_pic' =>
    array (
      'type' => 'varchar(32)',
      'label' => app::get('b2c')->_('缩略图'),
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'in_list' => false,
    ),
    'small_pic' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'comment' => app::get('b2c')->_('小图'),
    ),
    'big_pic' =>
    array (
      'type' => 'varchar(255)',
      'editable' => false,
      'comment' => app::get('b2c')->_('大图'),
    ),

    'intro' =>
    array (
      'type' => 'longtext',
      'sdfpath' => 'description',
      'label' => app::get('b2c')->_('详细介绍'),
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'filtertype' => 'normal',
      'in_list' => false,
    ),
    'store_place' =>
    array (
      'type' => 'varchar(255)',
      'label' => app::get('b2c')->_('库位'),
      'sdfpath' => 'product[default]/store_place',
      'width' => 30,
      'editable' => false,
      'hidden'=>true,
    ),
    'min_buy' =>
    array (
      'type' => 'number',
      'label' => app::get('b2c')->_('起定量'),
      'width' => 30,
      'editable' => false,
    'in_list' => false,
    ),
   'package_scale' =>
    array (
      'type' => 'decimal(20,2)',
      'label' => app::get('b2c')->_('打包比例'),
      'width' => 30,
      'editable' => false,
    'in_list' => false,
    ),
   'package_unit' =>
    array (
      'type' => 'varchar(20)',
      'label' => app::get('b2c')->_('打包单位'),
      'width' => 30,
      'editable' => false,
    'in_list' => false,
    ),
    'package_use' =>
    array (
      'type' => 'intbool',
      'label' => app::get('b2c')->_('是否开启打包'),
      'width' => 30,
      'editable' => false,
    'in_list' => false,
    ),
    'score_setting' =>
    array (
      'type' =>
      array (
        'percent' => app::get('b2c')->_('百分比'),
        'number' => app::get('b2c')->_('实际值'),
      ),
      'default' => 'number',
      'editable' => false,
    ),
    'store_prompt' =>
    array (
      'type' => 'table:goods_store_prompt',
      'label' => app::get('b2c')->_('库存提示规则'),
      'width' => 30,
      'editable' => false,
    ),
    'nostore_sell' =>
    array (
      'type' => 'intbool',
      'default' => '0',
      'label' => app::get('b2c')->_('是否开启无库存销售'),
      'width' => 30,
      'editable' => false,
    ),
    'goods_setting' =>
    array(
        'type' => 'serialize',
        'label' => app::get('b2c')->_('商品设置'),
        'deny_export' => true,
    ),
    'spec_desc' =>
    array (
      'type' => 'serialize',
      'label' => app::get('b2c')->_('物品'),
      'width' => 110,
      'hidden' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('货品规格序列化'),
    ),
    'params' =>
    array (
      'type' => 'serialize',
      'editable' => false,
      'comment' => app::get('b2c')->_('商品规格序列化'),
    ),
    'disabled' =>
    array (
      'type' => 'bool',
      'default' => 'false',
      'required' => true,
      'editable' => false,
    ),
    'rank_count' =>
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('google page rank count'),
    ),
    'comments_count' =>
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('评论次数'),
    ),
    'view_w_count' =>
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('周浏览次数'),
    ),
    'view_count' =>
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('浏览次数'),
    ),
    'count_stat' =>
    array (
      'type' => 'longtext',
      'editable' => false,
      'comment' => app::get('b2c')->_('统计数据序列化'),
    ),
    'buy_count' =>
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('购买次数'),

    ),
    'buy_w_count' =>
    array (
      'type' => 'int unsigned',
      'default' => 0,
      'required' => true,
      'editable' => false,
      'comment' => app::get('b2c')->_('购买次数'),
    ),

    'p_1' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_1/value',
      'editable' => false,
    ),
    'p_2' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_2/value',
      'editable' => false,
    ),
    'p_3' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_3/value',
      'editable' => false,
    ),
    'p_4' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_4/value',
      'editable' => false,
    ),
    'p_5' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_5/value',
      'editable' => false,
    ),
    'p_6' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_6/value',
      'editable' => false,
    ),
    'p_7' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_7/value',
      'editable' => false,
    ),
    'p_8' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_8/value',
      'editable' => false,
    ),
    'p_9' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_9/value',
      'editable' => false,
    ),
    'p_10' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_10/value',
      'editable' => false,
    ),
    'p_11' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_11/value',
      'editable' => false,
    ),
    'p_12' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_12/value',
      'editable' => false,
    ),
    'p_13' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_13/value',
      'editable' => false,
    ),
    'p_14' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_14/value',
      'editable' => false,
    ),
    'p_15' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_15/value',
      'editable' => false,
    ),
    'p_16' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_16/value',
      'editable' => false,
    ),
    'p_17' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_17/value',
      'editable' => false,
    ),
    'p_18' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_18/value',
      'editable' => false,
    ),
    'p_19' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_19/value',
      'editable' => false,
    ),
    'p_20' =>
    array (
      'type' => 'number',
      'sdfpath' => 'props/p_20/value',
      'editable' => false,
    ),
    'p_21' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_21/value',
      'editable' => false,
    ),
    'p_22' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_22/value',
      'editable' => false,
    ),
    'p_23' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_23/value',
      'editable' => false,
    ),
    'p_24' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_24/value',
      'editable' => false,
    ),
    'p_25' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_25/value',
      'editable' => false,
    ),
    'p_26' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_26/value',
      'editable' => false,
    ),
    'p_27' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_27/value',
      'editable' => false,
    ),
    'p_28' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_28/value',
      'editable' => false,
    ),

    'p_29' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_29/value',
      'editable' => false,
    ),
    'p_30' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_30/value',
      'editable' => false,
    ),

    'p_31' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_31/value',
      'editable' => false,
    ),
    'p_32' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_32/value',
      'editable' => false,
    ),

    'p_33' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_33/value',
      'editable' => false,
    ),
    'p_34' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_34/value',
      'editable' => false,
    ),

    'p_35' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_35/value',
      'editable' => false,
    ),
    'p_36' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_36/value',
      'editable' => false,
    ),

    'p_37' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_37/value',
      'editable' => false,
    ),
    'p_38' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_38/value',
      'editable' => false,
    ),
    'p_39' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_39/value',
      'editable' => false,
    ),
    'p_40' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_40/value',
      'editable' => false,
    ),
        'p_41' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_41/value',
      'editable' => false,
    ),
        'p_42' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_42/value',
      'editable' => false,
    ),
        'p_43' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_43/value',
      'editable' => false,
    ),
        'p_44' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_44/value',
      'editable' => false,
    ),
        'p_45' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_45/value',
      'editable' => false,
    ),
        'p_46' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_46/value',
      'editable' => false,
    ),
        'p_47' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_47/value',
      'editable' => false,
    ),
        'p_48' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_48/value',
      'editable' => false,
    ),
        'p_49' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_49/value',
      'editable' => false,
    ),
        'p_50' =>
    array (
      'type' => 'varchar(255)',
      'sdfpath' => 'props/p_50/value',
      'editable' => false,
    ),

  ),
  'comment' => app::get('b2c')->_('商品表'),
  'index' =>
  array (
    'uni_bn' =>
    array (
      'columns' =>
      array (
        0 => 'bn',
      ),
      'prefix' => 'UNIQUE',
    ),
    'ind_p_1' =>
    array (
      'columns' =>
      array (
        0 => 'p_1',
      ),
    ),
    'ind_p_2' =>
    array (
      'columns' =>
      array (
        0 => 'p_2',
      ),
    ),
    'ind_p_3' =>
    array (
      'columns' =>
      array (
        0 => 'p_3',
      ),
    ),
    'ind_p_4' =>
    array (
      'columns' =>
      array (
        0 => 'p_4',
      ),
    ),
    'ind_p_5' =>
    array (
      'columns' =>
      array (
        0 => 'p_5',
      ),
    ),
    'ind_p_6' =>
    array (
      'columns' =>
      array (
        0 => 'p_6',
      ),
    ),
    'ind_p_7' =>
    array (
      'columns' =>
      array (
        0 => 'p_7',
      ),
    ),
    'ind_p_8' =>
    array (
      'columns' =>
      array (
        0 => 'p_8',
      ),
    ),
        'ind_p_9' =>
    array (
      'columns' =>
      array (
        0 => 'p_9',
      ),
    ),
    'ind_p_10' =>
    array (
      'columns' =>
      array (
        0 => 'p_10',
      ),
    ),
    'ind_p_11' =>
    array (
      'columns' =>
      array (
        0 => 'p_11',
      ),
    ),
    'ind_p_12' =>
    array (
      'columns' =>
      array (
        0 => 'p_12',
      ),
    ),
        'ind_p_13' =>
    array (
      'columns' =>
      array (
        0 => 'p_13',
      ),
    ),
    'ind_p_14' =>
    array (
      'columns' =>
      array (
        0 => 'p_14',
      ),
    ),
    'ind_p_15' =>
    array (
      'columns' =>
      array (
        0 => 'p_15',
      ),
    ),
    'ind_p_16' =>
    array (
      'columns' =>
      array (
        0 => 'p_16',
      ),
    ),
        'ind_p_17' =>
    array (
      'columns' =>
      array (
        0 => 'p_17',
      ),
    ),
    'ind_p_18' =>
    array (
      'columns' =>
      array (
        0 => 'p_18',
      ),
    ),
    'ind_p_19' =>
    array (
      'columns' =>
      array (
        0 => 'p_19',
      ),
    ),
    'ind_p_20' =>
    array (
      'columns' =>
      array (
        0 => 'p_20',
      ),
    ),
    'ind_p_5' =>
    array (
      'columns' =>
      array (
        0 => 'p_5',
      ),
    ),
    'ind_p_6' =>
    array (
      'columns' =>
      array (
        0 => 'p_6',
      ),
    ),
    'ind_p_7' =>
    array (
      'columns' =>
      array (
        0 => 'p_7',
      ),
    ),
    'ind_p_8' =>
    array (
      'columns' =>
      array (
        0 => 'p_8',
      ),
    ),
    'ind_p_23' =>
    array (
      'columns' =>
      array (
        0 => 'p_23',
      ),
    ),
    'ind_p_22' =>
    array (
      'columns' =>
      array (
        0 => 'p_22',
      ),
    ),
    'ind_p_21' =>
    array (
      'columns' =>
      array (
        0 => 'p_21',
      ),
    ),

    'ind_p_24' =>
    array (
      'columns' =>
      array (
        0 => 'p_24',
      ),
    ),
    'ind_p_25' =>
    array (
      'columns' =>
      array (
        0 => 'p_25',
      ),
    ),
    'ind_p_26' =>
    array (
      'columns' =>
      array (
        0 => 'p_26',
      ),
    ),
    'ind_p_27' =>
    array (
      'columns' =>
      array (
        0 => 'p_27',
      ),
    ),
    'ind_p_28' =>
    array (
      'columns' =>
      array (
        0 => 'p_28',
      ),
    ),
    'ind_p_29' =>
    array (
      'columns' =>
      array (
        0 => 'p_29',
      ),
    ),


    'ind_p_30' =>
    array (
      'columns' =>
      array (
        0 => 'p_30',
      ),
    ),

    'ind_p_31' =>
    array (
      'columns' =>
      array (
        0 => 'p_31',
      ),
    ),
    'ind_p_32' =>
    array (
      'columns' =>
      array (
        0 => 'p_32',
      ),
    ),
    'ind_p_33' =>
    array (
      'columns' =>
      array (
        0 => 'p_33',
      ),
    ),
    'ind_p_34' =>
    array (
      'columns' =>
      array (
        0 => 'p_34',
      ),
    ),
    'ind_p_35' =>
    array (
      'columns' =>
      array (
        0 => 'p_35',
      ),
    ),
    'ind_p_36' =>
    array (
      'columns' =>
      array (
        0 => 'p_36',
      ),
    ),
    'ind_p_37' =>
    array (
      'columns' =>
      array (
        0 => 'p_37',
      ),
    ),
    'ind_p_38' =>
    array (
      'columns' =>
      array (
        0 => 'p_38',
      ),
    ),
    'ind_p_39' =>
    array (
      'columns' =>
      array (
        0 => 'p_39',
      ),
    ),

    'ind_p_40' =>
    array (
      'columns' =>
      array (
        0 => 'p_40',
      ),
    ),
    'ind_p_41' =>
    array (
      'columns' =>
      array (
        0 => 'p_41',
      ),
    ),
    'ind_p_42' =>
    array (
      'columns' =>
      array (
        0 => 'p_42',
      ),
    ),
    'ind_p_43' =>
    array (
      'columns' =>
      array (
        0 => 'p_43',
      ),
    ),
    'ind_p_44' =>
    array (
      'columns' =>
      array (
        0 => 'p_44',
      ),
    ),
    'ind_p_45' =>
    array (
      'columns' =>
      array (
        0 => 'p_45',
      ),
    ),
    'ind_p_46' =>
    array (
      'columns' =>
      array (
        0 => 'p_46',
      ),
    ),
    'ind_p_47' =>
    array (
      'columns' =>
      array (
        0 => 'p_47',
      ),
    ),
    'ind_p_48' =>
    array (
      'columns' =>
      array (
        0 => 'p_48',
      ),
    ),
    'ind_p_49' =>
    array (
      'columns' =>
      array (
        0 => 'p_49',
      ),
    ),
    'ind_p_50' =>
    array (
      'columns' =>
      array (
        0 => 'p_50',
      ),
    ),
    'ind_frontend' =>
    array (
      'columns' =>
      array (
        0 => 'disabled',
        1 => 'goods_type',
        2 => 'marketable',
      ),
    ),
    'idx_goods_type' =>
    array(
        'columns' =>
        array (
            0 => 'goods_type',
            ),
        ),
    'idx_d_order' =>
    array(
        'columns' =>
        array (
            0 => 'd_order',
            ),
        ),
  'idx_goods_type_d_order' =>
    array(
        'columns' =>
        array (
            0 => 'goods_type',
            1 => 'd_order',
            ),
        ),
   'idx_marketable' =>
    array (
        'columns' =>
        array (
            0 => 'marketable',
            ),
        ),
      ),

  'engine' => 'innodb',
  'version' => '$Rev: 44513 $',
  'comment' => app::get('b2c')->_('商品主表'),
);
