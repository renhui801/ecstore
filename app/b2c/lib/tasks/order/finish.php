<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_tasks_order_finish extends base_task_abstract implements base_interface_task{

    // 每个执行100条订单信息
    var $limit = 100;

    public function exec($params=null){

        $objOrder = app::get('b2c')->model('orders');
        $b2c_order_finish = kernel::single("b2c_order_finish");
        $this->app = app::get('b2c');

        $filter = array(
            'status' => 'active',
            'pay_status' => 1,
            'ship_status' => 1,
            'createtime|lthan' => strtotime('-3 month'),
        );

        $offset = 0;
        while( $order_ids = $objOrder->getList('order_id', $filter, $offset) ){
            $offset++;
             // 把分页得到的订单id进行 完成 操作
            $this->dofinish($order_ids, $b2c_order_finish, $this);
        }

    }

    public function dofinish($order_ids, &$b2c_order_finish, &$controller){

        foreach ($order_ids as $v) {
            $sdf['order_id'] = $v['order_id'];
            $sdf['op_id'] = '1';
            $sdf['opname'] = 'admin';

            $b2c_order_finish->generate($sdf, $controller, $message);
        }

    }

}