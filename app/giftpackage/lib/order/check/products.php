<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class giftpackage_order_check_products
{    
    /**
     * 公开构造方法
     * @params app object
     * @return null
     */
    public function __construct($app)
    {   
        $this->app = app::get('b2c');
    }

    public function check_products($data,&$msg)
    {        
        $objGoods = $this->app->model('goods');
        $objProducts = $this->app->model('products');
        if(is_array($data['order_objects'])){
            foreach($data['order_objects'] as $dk=>$dv){
                 if($dv['obj_type'] == 'giftpackage'){
                     foreach($dv['order_items'] as $ik=>$iv){
                         $arr[$iv['products']['product_id']] += $iv['quantity'];  
                     }
                 }
            }
        }
        if(is_array($arr)){
            foreach($arr as $ak=>$av){
                if(!$objProducts->checkStore($ak,$av)){
                    $pname = $objProducts->dump($ak,'name,goods_id');
                    $nostore_sell = $objGoods -> dump($pname['goods_id'],'nostore_sell');
                    if($nostore_sell['$nostore_sell']){
                        return true;
                    }else{
                        $msg = '货品'.$pname['name'].'库存不足';
                        return false;
                    }

                }
            }
        }
        
        return true;
    }
    

}
