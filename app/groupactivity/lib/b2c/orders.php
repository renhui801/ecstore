<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * @package default
 * @author kxgsy163@163.com
 */
class groupactivity_b2c_orders
{
    public function __construct( &$app ) {
        $this->app = $app;
    }
    
    function dorecycle( $orderid ) {
        if( !$orderid ) return true;
        $data = array('order_id'=>$orderid,'disabled'=>'true');
        $this->app->model('order_act')->save( $data );
        return true;
    }
    
    function dorestore( $filter ) {
        if( !$filter['order_id'] ) return true;
        $data = array('order_id'=>$filter['order_id'],'disabled'=>'false');
        $this->app->model('order_act')->save( $data );
        return true;
    }
    
}