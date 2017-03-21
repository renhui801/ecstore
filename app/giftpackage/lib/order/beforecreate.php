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
class giftpackage_order_beforecreate
{
    
    function __construct($app)
    {
        $this->app = $app;
    }
    
    /*
     * 修改订单信息
     */
    public function generate( &$sdf )
    {
        $o = $this->app->model('order_ref');
        $member_id = $sdf['member_id'];
        $order_id = $sdf['order_id'];
        foreach( (array)$sdf['order_objects'] as $row ) {
            if( $row['obj_type']!=kernel::single('giftpackage_cart_object_giftpackage')->get_type() ) continue;
            $aSave = array(
                'member_id'=>$member_id,
                'order_id'=>$order_id,
                'giftpackage_id'=>$row['goods_id'],
                'quantity'=>$row['quantity'],
            );
            $o->insert( $aSave );
        }
    }
    #End Func
}