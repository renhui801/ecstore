<?php
/**
 * cps_thirdparty_cancel
 * 订单取消，第三方CPS订单记录操作
 * 
 * @uses
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_thirdparty_cancel Aug 2, 2011  11:54:08 AM ever $
 */
class cps_thirdparty_cancel {
    
    /**
     * 第三方CPS订单记录变为无效
     * @param array $order
     * @return boolean
     * @version 1 Aug 2, 2011
     */
    public function order_notify($order) {
        //返回变量
        $rtn = false;
        //第三方CPS订单记录模型
        $mdlTpo = kernel::single('cps_mdl_thirdparty_orders');
        //查找订单记录存在
        $tpo = $mdlTpo->dump($order['order_id'], 'order_id');
        
        //记录存在，修改状态为无效
        if ($tpo['order_id']) {
            $data = array(
                'order_id' => $order['order_id'],
                'status' => '1',
            );
            
            //修改记录状态
            $rtn = $mdlTpo->save($data);
        }
        return $rtn;
    }
}