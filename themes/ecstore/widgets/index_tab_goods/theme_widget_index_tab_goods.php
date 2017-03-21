<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_index_tab_goods(&$setting,&$render){
    $_return = false;

    switch ($setting['selector']) {
    case 'filter':
        parse_str($setting['goods_filter'],$goodsFilter);
        $goodsFilter['goodsOrderBy'] = $setting['goods_order_by'];
        $goodsFilter['goodsNum'] = $setting['limit'];
        $_return = b2c_widgets::load('Goods')->getGoodsList($goodsFilter);
        //$_return = array_values($_return['goodsRows']);
        break;

    case 'select':

        $goodsFilter['goods_id'] = explode(",", $setting["goods_select"]);
        $goodsFilter['goodsNum'] = $setting['limit'];
        $_return = b2c_widgets::load('Goods')->getGoodsList($goodsFilter);

        foreach (json_decode($setting['goods_select_linkobj'],1) as $obj) {
            if($_return['goodsRows'][$obj['id']]){
                if(trim($obj['pic'])!=""){
                    $_return['goodsRows'][$obj['id']]['goodsPicL'] =
                        $_return['goodsRows'][$obj['id']]['goodsPicM'] =
                        $_return['goodsRows'][$obj['id']]['goodsPicS'] = $obj['pic'];
                }
                if(trim($obj['nice'])!=""){
                    $_return['goodsRows'][$obj['id']]['goodsName'] = $obj['nice'];
                }
            }
        }

        break;

    }
    $gids = array_keys($_return['goodsRows']);

    #//商品标签
    #if(is_array($_return['goodsRows'])){
    #    foreach($_return['goodsRows'] as $apk=>$apv){
    #        $_return['goodsRows'][$apk]['goods_id'] = $apv['goodsId'];
    #    }
    #}
    #foreach( kernel::servicelist('tags_special.add') as $services ) {
    #    if ( is_object($services)) {
    #        if ( method_exists( $services, 'add') ) {
    #            $services->add( $gids, $_return['goodsRows'] );
    #        }
    #    }
    #}

    if(!$gids||count($gids)<1)return $_return;
    $pointModel = app::get('b2c')->model('comment_goods_point');
    $goods_point_status = app::get('b2c')->getConf('goods.point.status');
    $point_status = $goods_point_status ? $goods_point_status: 'on';
    if($point_status == 'on' && $setting['show_star'] == 'true'){
        $sdf_point = $pointModel->get_single_point_arr($gids);
    }else{
        $setting['show_star'] = 'false';
    }

    #$mdl_product = app::get('b2c')->model('products');
    #$products = $mdl_product ->getList('product_id, spec_info, price, freez, store, marketable, goods_id',array('goods_id'=>$gids,'marketable'=>'true','is_default'=>'true'));
    foreach ($gids as $gid) {
        $_return['goodsRows'][$gid]['star'] = $sdf_point[$gid]['avg_num'];
    }
    return $_return;
}
?>
