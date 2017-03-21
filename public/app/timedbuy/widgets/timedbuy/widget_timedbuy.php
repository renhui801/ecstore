<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
    function widget_timedbuy(&$setting,&$smarty){
        if( !$setting['goods_id'] ) return false;
        $arr_goods = app::get('b2c')->model('goods')->getList( '*',array('goods_id'=>$setting['goods_id']) );
        
        if( is_array($arr_goods) ) {
            reset( $arr_goods );
            $arr_goods = current( $arr_goods );
        } else {
            return false;
        }
        
        $order = kernel::single('b2c_cart_prefilter_promotion_goods')->order();
        $solution = app::get('b2c')->model('goods_promotion_ref')->getList( 
                                                                        'action_solution,from_time,to_time',
                                                                        array('goods_id'=>$setting['goods_id'],'rule_id'=>$setting['rule_id'],'status'=>'true'),
                                                                        0,
                                                                        -1,
                                                                        $order 
                                                                    );
        $to_time = $solution[0]['to_time'];
        $from_time = $solution[0]['from_time'];
        $solution = @unserialize($solution[0]['action_solution']);
        
        if( $to_time<time() ) return false;

        if( !$solution || !is_array($solution) ) return false;
        if( key($solution)!='timedbuy_promotion_solution_timedbuy' ) return false;
        $solution = current( $solution );
        
        
        $arr = array('price'=>floatval($solution['price']));
        $arr['image'] = $arr_goods['image_default_id'];
        if( !$arr['image'] ) {
            $imageDefault = app::get('image')->getConf('image.set');
            $arr['image'] = $imageDefault['M']['default_image'];
        }
        
        if( (int)$arr_goods['price'] )
            $arr['sales'] = round($arr['price']/$arr_goods['price'],2)*10;
        
        if( !$arr_goods['spec_desc'] || !is_array($arr_goods['spec_desc']) || count($arr_goods['spec_desc'])<1 )
            unset($arr_goods['spec_desc']);
        
        $arr['goods'] = $arr_goods;
        $arr['request_time_now'] = app::get('site')->router()->gen_url( array('app'=>'timedbuy','ctl'=>'site_timedbuy','act'=>'request_time_now') );
        $arr['to_time'] = $to_time;
        $arr['from_time'] = $from_time;
        $arr['request_url'] = app::get('site')->router()->gen_url( array('app'=>'timedbuy','ctl'=>'site_timedbuy','act'=>'get_goods_spec') );
        	
        	$oGoods = kernel::single('b2c_goods_model')->getGoods( $setting['goods_id'] );
    		$store = isset($oGoods['store']) ? $oGoods['store'] : 99999999;
            $store = ($store>$solution['quantity'])?$solution['limit']:$store;
            $arr['_buy_status'] = 2;
            $row = kernel::single('timedbuy_info')->get_sales_goods_info( $setting['goods_id'] );
            //获取购买信息
            kernel::single('timedbuy_cart_object_goods')->_get_kvstore( $row,$setting['goods_id'],$member_num,$num,$tmp );
            if( $tmp['quantity'] && $tmp['quantity']<=$num ) {
            	//抢购数量已满
                $arr['_buy_status'] = 4;
            }
        
        return $arr;
    }
