<?php                                                                                                                                                                   
/**
 * ShopEx licence
 * 订单接口请求crm路由器
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_apiv_exchanges_request_order extends b2c_apiv_exchanges_request
{

    //更新会员信息推送到crm
    public function modifyActive($order_id){
        if($order_id){
            $data['order_id'] = $order_id;
            $this->rpc_caller_request($data,'orderupdate');
        }
    }
}
