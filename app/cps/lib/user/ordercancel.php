<?php
/**
 * cps_user_ordercancel
 * 订单作废佣金订单处理功能类
 * 
 * @uses
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_user_ordercancel Jul 15, 2011  10:19:38 AM ever $
 */
class cps_user_ordercancel {
    
    /**
     * 订单作废进行佣金订单处理
     * @access public
     * @param array $order
     * @return bool
     * @version 1 Jul 15, 2011
     */
    public function order_notify($order) {
        //佣金订单模型
        $mdlUop = kernel::single('cps_mdl_userorderprofit');
        //根据订单id查询佣金订单
        $uop = $mdlUop->db->selectrow('SELECT profit_id FROM sdb_cps_userorderprofit WHERE order_id = ' . $order['order_id']);
        
        //佣金订单存在进行修改状态为无效
        if ($uop) {
            $state = 1;
            //修改佣金状态
            $rs = $mdlUop->db->exec('UPDATE sdb_cps_userorderprofit SET state = \'' . $state . '\' WHERE profit_id = ' . $uop['profit_id']);
            return $rs['rs'];
        } else {
            return false;
        }
    }
}