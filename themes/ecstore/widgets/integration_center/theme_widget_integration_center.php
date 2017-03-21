<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_integration_center(&$setting,&$render){
    $filter = array(
        'marketable'=>'true',
        'goods_type' => array('gift','normal'),
        'to_time|than' => time()
    );

    $o_gift_ref = app::get('gift')->model('ref');
    $arr_gift_list = $o_gift_ref->get_list_finder('*', $filter, 0,$setting['limit']);

    $o = app::get('gift')->model('goods');        //商品类实例
    $imageDefault = app::get('image')->getConf('image.set');
    if( is_array($arr_gift_list) ) {
        foreach( $arr_gift_list as $key => &$row ) {
            if(!$row['goods_id']['image_default_id']){
                $row['goods_id']['image_default_id'] = $imageDefault['M']['default_image'];
            }
            $tmp = $row;
            $row['gift'] = $tmp;
        }
    }
    //echo "<pre>";print_r($arr_gift_list);
    return $arr_gift_list;


}
