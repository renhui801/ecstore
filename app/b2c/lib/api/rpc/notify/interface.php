<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */ 

interface b2c_api_rpc_notify_interface
{
    /**
     * 中心是否需要发送订单同步请求
     * @param array sdf order
     */
    public function rpc_judge_send($sdf_order);
    
    /**
     * 给予需要发送订单的通知
     * @param string order id
     * @param mixed sdf payments
     * @return null
     */
    public function rpc_notify($order_id, $sdf=array());
}