<?php
/**
 * cps_auto_orderprofitstatus
 * 订单佣金状态设置自动任务
 *
 * @uses
 * @package CPS
 * @author danny<danny@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_auto_orderprofitstatus Jan 31, 2012  16:31:00 PM ever $
 */
class cps_auto_orderprofitstatus {

    /**
     * 构造初始
     * @access public
     * @version 1 Jul 12, 2011
     */
    public function __construct() {}
    
    /**
     * 每分钟进行的任务
     * @access public
     * @version 1 Jul 13, 2011
     */
    public function minute() {}

    /**
     * 每小时进行任务
     * @access public
     * @version 1 Jul 12, 2011
     */
    public function hour() {
        $nowtime = time();
        $yam = date('Ym', time());
        //获取后台设置的佣金结算日
        $orderProfitDate = app::get('cps')->model('setting')->getValueByKey('orderProfitDate');
        $timelimit = $orderProfitDate*24*3600;
        
        //联盟商订单佣金模型
        $mdlUop = kernel::single('cps_mdl_userorderprofit');

        //获取上个月的有效订单佣金记录
        $strSql = "SELECT sd.pay_status , sd.ship_status , su.profit_id 
                FROM sdb_b2c_orders as sd LEFT JOIN sdb_cps_userorderprofit as su ON su.order_id = sd.order_id
                WHERE su.addtime <= ". ($nowtime-$timelimit). " AND su.state = '0' AND sd.pay_status >0 AND sd.ship_status > 0";
        $arrOrderProfit = $mdlUop->db->select($strSql);
        
        if(count($arrOrderProfit)>0){
            foreach($arrOrderProfit as $k =>$val){
                if($val['pay_status'] == 1 && $val['ship_status'] == 1){
                    $mdlUop->db->exec('UPDATE sdb_cps_userorderprofit SET yam = ' . $yam . ', state = \'2\' WHERE profit_id = ' . $val['profit_id']);
                }else{
                    $mdlUop->db->exec('UPDATE sdb_cps_userorderprofit SET yam = ' . $yam . ', state = \'1\' WHERE profit_id = ' . $val['profit_id']);
                }
            }
        }
    }
    
    /**
     * 每天进行任务
     * @access public
     * @version 1 Jul 13, 2011
     */
    public function day() {}
    
    /**
     * 每周进行任务
     * @access public
     * @version 1 Jul 13, 2011
     */
    public function week() {}
    
    /**
     * 每月进行任务
     * @access public
     * @version 1 Jul 13, 2011
     */
    public function month() {}

}