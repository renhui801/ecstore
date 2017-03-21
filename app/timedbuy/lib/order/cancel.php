<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * 修改订单信息
 * @package default
 * @author kxgsy163@163.com
 */
class timedbuy_order_cancel
{
    
    function __construct($app)
    {
        $this->app = $app;
    }
    
    /*
     * 处理订单信息 service 注册到b2c
     * $sdf, $sdf_order
     */
    public function order_notify( $sdf )
    {
        $order_id = $sdf['order_id'];
        if( !$order_id ) return true;
        $o = $this->app->model('objitems');
        $o->delete( array('order_id'=>$order_id) );
    }
    #End Func
}