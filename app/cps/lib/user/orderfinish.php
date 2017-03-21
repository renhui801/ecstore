<?php
/**
 * cps_user_orderfinish
 * CPS订单完成处理类
 *
 * @uses
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_user_orderfinish Jul 12, 2011  11:49:16 AM ever $
 */
class cps_user_orderfinish {
    /**
     * 订单完成后修改userorderprofit表关联信息
     * @access public
     * @param array $order_data 订单信息数组
     * @return bool
     * @version 3 Jul 13, 2011
     */
    public function generate($order_data) {
        //佣金订单模型
        $mdlUop = kernel::single('cps_mdl_userorderprofit');
        //查询佣金订单
        $uop = $mdlUop->db->selectrow('SELECT profit_id FROM sdb_cps_userorderprofit WHERE order_id = ' . $order_data['order_id']);
        //佣金订单存在进行修改状态
        if ($uop) {
            $yam = date('Ym', time());
            
            //已支付并发货订单为有效订单
            if ($order_data['pay_status'] == 1 && $order_data['ship_status'] == 1) {
                $state = 2;
            } else {
                $state = 1;
            }
            
            //修改佣金状态与yam
            $rs = $mdlUop->db->exec('UPDATE sdb_cps_userorderprofit SET yam = ' . $yam . ', state = \'' . $state . '\' WHERE profit_id = ' . $uop['profit_id']);
            return $rs['rs'];
        } else {
            return false;
        }
    }
}