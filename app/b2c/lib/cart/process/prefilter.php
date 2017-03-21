<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/**
 * 购物预过滤 second step 调用lib/cart/prefilter下的处理
 * $ 2010-04-28 20:29 $
 */
class b2c_cart_process_prefilter implements b2c_interface_cart_process {
    private $app;

    public function __construct(&$app){
        $this->app = $app;
    }
    
    public function get_order() {
        return 90;
    }

    public function process($aData,&$aResult = array(),$aConfig = array()){
        // servicelist('b2c_cart_prefilter_apps')=>
        // b2c_cart_prefilter_promotion_goods
        foreach(kernel::servicelist('b2c_cart_prefilter_apps') as $object) {
            if(!is_object($object)) continue;
            $object->filter($aResult,$aConfig);
        }

        $this->app->model('cart')->count_objects($aResult);
    }
}
?>
