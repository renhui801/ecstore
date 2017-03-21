<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_tasks_matrix_sendorders extends base_task_abstract implements base_interface_task{
    public function exec($params=null){
		// 与中心交互
		$is_need_rpc = false;
		$obj_rpc_obj_rpc_request_service = kernel::servicelist('b2c.rpc_notify_request');
		foreach ($obj_rpc_obj_rpc_request_service as $obj)
		{
			if ($obj && method_exists($obj, 'rpc_judge_send'))
			{
				if ($obj instanceof b2c_api_rpc_notify_interface)
					$is_need_rpc = $obj->rpc_judge_send($params);
			}

			if ($is_need_rpc) break;
		}

		if ($is_need_rpc)
		{
          /*$obj_rpc_request_service = kernel::service('b2c.rpc.send.request');

			if ($obj_rpc_request_service && method_exists($obj_rpc_request_service, 'rpc_caller_request'))
			{
				if ($obj_rpc_request_service instanceof b2c_api_rpc_request_interface)
					$obj_rpc_request_service->rpc_caller_request($params,'create');
			}
			else
			{
				$obj_order_create = kernel::single('b2c_order_rpc_recaller');
				$obj_order_create->rpc_caller_request($params);
                }*/
          //新的版本控制api
          $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
          $obj_apiv->rpc_caller_request($params, 'ordercreate');
		}
    }
}
