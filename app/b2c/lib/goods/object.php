<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_goods_object {

    public function __construct($app){
        $this->app = $app;
        $this->goodsModel= app::get('b2c')->model('goods');
    }

    public function get_goods_object($goodsId,$goodsInfo){
        if( empty($goodsInfo) ){
            $goodsInfo = $this->goodsModel->getList( '*', array('goods_id'=>$goodsId) );
            $goodsInfo = $goodsInfo[0];
        }
        $goodsObject = array();
        $goodsBasic = $this->get_goods_basic($goodsInfo['goods_id'],$goodsInfo);
        $goodsObject['basic'] = $goodsBasic;
        $this->get_goods_promotion($goodsInfo['goods_id']);
    }

    public function get_goods_basic($goodsId,$goodsInfo=array() ){
        if( empty($goodsInfo) ){
            $goodsInfo = $this->goodsModel->getList( '*', array('goods_id'=>$goodsId) );
            $goodsInfo = $goodsInfo[0];
        }

        $goodsBasic = array();
        //商品分类
        $goodsInfoCat   = $this->get_goods_cat($goodsInfo['goods_id'],$goodsInfo);
        //商品类型
        $goodsInfoType  = $this->get_goods_type($goodsInfo['goods_id'],$goodsInfo);
        //商品品牌
        $goodsInfoBrand = $this->get_goods_brand($goodsInfo['goods_id'],$goodsInfo);

        $goodsBasic['goods_id'] = $goodsInfo['goods_id'];
        $goodsBasic['bn'] = $goodsInfo['bn'];
        $goodsBasic['name'] = $goodsInfo['name'];
        $goodsBasic['type'] = $goodsInfoType;
        $goodsBasic['category'] = $goodsInfoCat;
        $goodsBasic['brand'] = $goodsInfoBrand;
        $goodsBasic['marketable'] = $goodsInfo['marketable'];

        return $goodsBasic;
    }

    public function get_goods_cat($goodsId,$goodsInfo=array() ){
        if( empty($goodsInfo) ){
            $goodsInfo = $this->goodsModel->getList( '*', array('goods_id'=>$goodsId) );
            $goodsInfo = $goodsInfo[0];
        }
        $goodsInfoCat = array();

        if(!empty($goodsInfo['cat_id'])){
            cachemgr::co_start();
            if(!cachemgr::get("goodsObjectCat".$goodsInfo['cat_id'], $goodsInfoCat)){
                $goodsInfoCat = app::get("b2c")->model("goods_cat")->getList('*',array('cat_id'=>$goodsInfo['cat_id']) );
                $goodsInfoCat = $goodsInfoCat[0];
                cachemgr::set("goodsObjectCat".$goodsInfo['cat_id'], $goodsInfoCat, cachemgr::co_end());
            }
        }
        return $goodsInfoCat;
    }

    public function get_goods_type($goodsId,$goodsInfo=array()){
        if( empty($goodsInfo) ){
            $goodsInfo = $this->goodsModel->getList( 'type_id', array('goods_id'=>$goodsId) );
            $goodsInfo = $goodsInfo[0];
        }
        $goodsInfoType= array();
        if(!empty($goodsInfo['type_id'])){
            cachemgr::co_start();
            if(!cachemgr::get("goodsObjectType".$goodsInfo['type_id'], $goodsInfoType)){
                $goodsInfoType = app::get("b2c")->model("goods_type")->dump2(array('type_id'=>$goodsInfo['type_id']) );
                cachemgr::set("goodsObjectType".$goodsInfo['type_id'], $goodsInfoType, cachemgr::co_end());
            }
        }
        return $goodsInfoType;

    }

    public function get_goods_type_info($arrProps,$goodsInfo){
        if( empty($arrProps) ){
            return null;
        }
        $goodsProps = array();
        for ($i=1;$i<=50;$i++){
            //1-20 select 21-50 input
            if ($goodsInfo['p_'.$i] ){
                $propsValueId = $goodsInfo['p_'.$i];
                if( $i <= 20){
                    $goodsProps[$i]['name'] = $arrProps[$i]['name'];
                    $goodsProps[$i]['value'] = $arrProps[$i]['options'][$propsValueId];
                }else{
                    $goodsProps[$i]['name'] = $arrProps[$i]['name'];
                    $goodsProps[$i]['value'] = $propsValueId;
                }

                //如果商品类型扩展属性改变，则商品中的设置需要重现设置，原先设置无效
                if(empty($goodsProps[$i]['name']) || empty($goodsProps[$i]['value']) ){
                    unset($goodsProps[$i]);
                    continue;
                }
            }
        }
        return $goodsProps;
    }

    public function get_goods_brand($goodsId,$goodsInfo=array()){
        if( empty($goodsInfo) ){
            $goodsInfo = $this->goodsModel->getList( 'brand_id', array('goods_id'=>$goodsId) ); $goodsInfo = $goodsInfo[0];
        }
        if(!empty($goodsInfo['brand_id'])){
            cachemgr::co_start();
            if(!cachemgr::get("goodsObjectBrand".$goodsInfo['brand_id'], $goodsInfoBrand)){
                $goodsInfoBrand = app::get("b2c")->model("brand")->getList('*',array('brand_id'=>$goodsInfo['brand_id']) );
                $goodsInfoBrand = $goodsInfoBrand[0];
                cachemgr::set("goodsObjectBrand".$goodsInfo['brand_id'], $goodsInfoBrand, cachemgr::co_end());
            }
        }
        return $goodsInfoBrand;
    }

    public function get_goods_promotion($goodsId){
        if(!$goodsId) return false;
        //商品促销
        $time = time();
        $order = kernel::single('b2c_cart_prefilter_promotion_goods')->order();
        $goodsPromotion = app::get('b2c')->model('goods_promotion_ref')->getList('*', array('goods_id'=>$goodsId, 'from_time|sthan'=>$time, 'to_time|bthan'=>$time,'status'=>'true'),0,-1,$order);
        if($goodsPromotion){
            foreach($goodsPromotion as $row) {
                $temp = is_array($row['action_solution']) ? $row['action_solution'] : @unserialize($row['action_solution']);
                $goodsInfoPromotion['goods'][] = $row;
                if( $row['stop_rules_processing']=='true' ) break;
            }
        }

        //订单促销
        $orderPromotion = $this->app->model('sales_rule_order')->getList('*',array('status'=>'true','from_time|lthan'=>time(),'to_time|than'=>time(),'rule_type'=>'N'),0,-1,'sort_order ASC');
        if($orderPromotion){
            foreach($orderPromotion as $row) {
                $goodsInfoPromotion['order'][] = $row;
                if( $row['stop_rules_processing']=='true' ) break;
            }
        }
        return $goodsInfoPromotion;
    }

    #public function get_goods_props($goodsId,$goodsInfo){
    #    if( empty($goodsInfo) ){
    #        $goodsInfo = $this->goodsModel->getList( '*', array('goods_id'=>$goodsId) );
    #        $goodsInfo = $goodsInfo[0];
    #    }
    #    $typeId = $goodsInfo['type_id'];
    #    $goodsProps = array();
    #    if($typeId){
    #    }
    #}

    public function get_goods_pics($goodsId,$goodsInfo=array() ){

    }

    public function get_goods_spec($goodsId,$goodsInfo=array() ){

    }

}
