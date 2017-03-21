<?php
/**
 * cps_thirdparty_finish
 * 订单完成，第三方CPS订单记录操作
 *
 * @uses
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_thirdparty_finish Aug 2, 2011  11:42:18 AM ever $
 */
class cps_thirdparty_finish {

    /**
     * 第三方CPS订单记录变为有效
     * @param array $order 订单
     * @return boolean
     * @version 1 Aug 2, 2011
     */
    public function generate($order) {
        //返回变量
        $rtn = false;
        //第三方CPS订单记录模型
        $mdlTpo = kernel::single('cps_mdl_thirdparty_orders');
        //查找订单记录存在
        $tpo = $mdlTpo->dump($order['order_id'], 'order_id');

        //订单记录存在进行相应的状态变更
        if ($tpo['order_id']) {
            //记录状态
            $status = '1';
            //已付款已发货状态为有效
            if ($order['pay_status'] == 1 && $order['ship_status'] == 1) {
                $status = '2';
            }
            
            $data = array(
                'order_id' => $order['order_id'],
                'status' => $status,
            );
            //更新订单记录
            $rtn = $mdlTpo->save($data);
        }

        return $rtn;
    }
}