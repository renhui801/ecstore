<?php

class starbuy_special_order{

    /*
     *将带有团购商品的订单加入过期取消表
     *@$orderdata 订单相关的数据
     */
    function cancel($order_id,&$msg){
        $mdl_cancelorder = app::get('starbuy')->model('cancelorder');
        $mdl_orderitem = app::get('b2c')->model('order_items');
        $order_items = $mdl_orderitem->getList('product_id,nums',array('order_id'=>$order_id));
        $timeout = 0;
        foreach($order_items as $value){
            $gtimeout = $this->_get_special_goods_timeout($value['product_id'],$value['nums']);
            $timeout = ($timeout == 0 ||$gtimeout < $timeout ) ? $gtimeout : $timeout;
        }
        if($timeout){
            $msg = "您的订单中包含活动货品，超过".$timeout."小时后未付款订单将自动关闭，请及时付款";
            $data = array(
                'order_id'=>$order_id,
                'canceltime'=>strtotime('+'.$timeout.' hour',time()),
            );
            $mdl_cancelorder->save($data);
        }
    }

    function _get_special_goods_timeout($product_id,$nums){
        $filter['end_time|bthan'] = time();
        $filter['begin_time|sthan'] = time();
        $filter['status'] = 'true';
        $filter['product_id'] = $product_id;
        $mdl_special_goods = app::get('starbuy')->model('special_goods');
        $result = $mdl_special_goods->getRow("timeout",$filter);
        if($result){
            $mdl_special_goods->db->exec('UPDATE sdb_starbuy_special_goods SET initial_num = initial_num+'.$nums.' where product_id='.$product_id);
            return $result['timeout'];
        }
        return false;
    }


    function check_order($orders){
        $cancel_mdl = app::get('starbuy')->model('cancelorder');
        $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
        foreach($orders as $key=>$val){
            $oid = $val['order_id'];
            if($obj_checkorder->check_order_cancel($oid)){
                $sdf['order_id'] = $oid;
                $sdf['op_id'] =1;// $this->user->user_id;
                $sdf['opname'] = "admin";//$this->user->user_data['account']['login_name'];
                $sdf['account_type'] = "shopadmin";//$this->user->account_type;

                $sdf['op_id'] = $this->user->user_id;
                $sdf['opname'] = $this->user->user_data['account']['login_name'];
                $sdf['account_type'] = $this->user->account_type;


                $b2c_order_cancel = kernel::single("b2c_order_cancel");

                if ($b2c_order_cancel->generate($sdf,$null, $message)){
                    if($order_object = kernel::service('b2c_order_rpc_async')){
                        $order_object->modifyActive($sdf['order_id']);
                    }
                    $cancel_mdl->delete(array('order_id'=>$oid));
                }

            }
        }
    }
}
