<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_sales_charts(&$setting,&$render){
    $_return = false;

    switch ($setting['selector']) {
    case 'filter':
        parse_str($setting['goods_filter'],$goodsFilter);
        $goodsFilter['goodsOrderBy'] = $setting['goods_order_by'];
        $goodsFilter['goodsNum'] = ($setting['limit'])?$setting['limit']:5;
        $_return = b2c_widgets::load('Goods')->getGoodsList($goodsFilter);
        //$_return = array_values($_return['goodsRows']);
        break;

    case 'select':

        $goodsFilter['goods_id'] = explode(",", $setting["goods_select"]);
        $goodsFilter['goodsNum'] = ($setting['limit'])?$setting['limit']:count($goodsFilter['goods_id']);
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
    #if(!$gids||count($gids)<1)return $_return;
    #$mdl_product = app::get('b2c')->model('products');
    #$products = $mdl_product ->getList('product_id, spec_info, price, freez, store, marketable, goods_id',array('goods_id'=>$gids,'marketable'=>'true'));

    #foreach ($products as $product) {

    #    $_return['goodsRows'][$product['goods_id']]['products'][] = $product;
    #}
    return $_return;
}
?>
