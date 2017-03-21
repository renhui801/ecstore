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
 *
 */
class groupactivity_order_notify implements b2c_api_rpc_notify_interface
{
    
    function __construct($app)
    {
        $this->app = $app;
    }
    
    /*
     * 处理订单信息 service 注册到b2c
     * $sdf, $sdf_order
     */
    public function rpc_judge_send($sdf_order)
	{
		return ($sdf_order['order_refer']=='local_group') ? true : false;
	}
    #End Func
	
	public function rpc_notify($order_id, $sdf=array())
	{
		// 团购订单不做处理，只是实现这个接口。
		kernel::single('b2c_api_rpc_notify_common')->rpc_notify($order_id, $sdf);
	}
	#End Func
}
