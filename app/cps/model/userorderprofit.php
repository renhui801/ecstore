<?php
/**
 * cps_mdl_userorderprofit
 * 网站联盟联盟商订单佣金模型
 * 
 * @uses dbeav_model
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_mdl_userorderprofit Jun 20, 2011  2:49:26 PM ever $
 */
class cps_mdl_userorderprofit extends dbeav_model {
    
    public $defaultOrder = 'profit_id DESC';

    /**
     * 初始化构造方法
     * @param object $app
     * @access public
     * @version Jun 21, 2011 创建
     */
    public function __construct($app) {
        parent::__construct($app);
    }
    
//    /**
//     * 列表项查询条件定义函数，如按联盟商、推广id查询
//     * @see dbeav_model::searchOptions()
//     * @access public
//     */
//    public function searchOptions() {
//        parent::searchOptions();
//    }
    
    /**
     * 根据订单id获取相应的订单佣金详情
     * @access public
     * @param int $orderId 订单id
     * @param array $aField 要获取的字段
     * @return array
     * @version Jun 21, 2011 创建
     */
    public function getProfitByOrder($orderId, $aField = array('*')) {
        //组装需要获取的字段
        $strCols = implode(',', $aField);
        //根据订单id获取订单佣金详情
        $arrOrdProfit = $this->dump(array('order_id' => $orderId), $strCols);
        return $arrOrdProfit;
    }
    
    /**
     * 根据时间段获取多条佣金信息
     * @access public
     * @param int $start 开始时间
     * @param int $end 结束时间
     * @return array
     * @version Jun 21, 2011 创建
     */
    public function getProfitList($start, $end) {
        //根据时间段获取多条佣金信息
        $arrProfitList = $this->getList('*', array('addtime|than' => $start, 'addtime|lthan' => $end));
        return $arrProfitList;
    }
}