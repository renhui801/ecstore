<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['goods'] = array (
    'columns' => array (
        'primary_goods_id' => array (
            'type' => 'bigint unsigned',
            'required' => true,
            'comment' => app::get('recommended')->_('主商品ID'),
        ),
        'secondary_goods_id' => array(
            'type' => 'varchar(200)',
            'comment' => app::get('recommended')->_('推荐商品ID'),
        ),
        'last_modified' => array(
            'type' => 'time',
            'required' => true,
            'comment' => app::get('recommended')->_('最后更新时间'),
        ),
    ),
    
    'index' => array(
        'ind_goods_id' => array(
            'columns' => array(
                0 => 'primary_goods_id',
            ),
        ),
    ),
    'comment' => app::get('recommended')->_('商品推荐表, 用于订单数据统计计算 '),
);