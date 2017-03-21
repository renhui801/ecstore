<?php
/**
 * cps_thirdparty_query
 * 第三方CPS记录查询类
 * 
 * @uses
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_thirdparty_query Jul 29, 2011  6:17:56 PM ever $
 */
class cps_thirdparty_query {
    
    /**
     * 模型属性
     * @access private
     * @var object
     */
    private $mdlTpo = null;
    
    /**
     * 构造方法
     * @access public
     * @version 1 Aug 1, 2011
     */
    public function __construct() {
        //赋值模型
        $this->mdlTpo = kernel::single('cps_mdl_thirdparty_orders');
    }
    
    /**
     * 亿起发查询接口
     * @access public 
     * @param array $params GET参数
     * @version 1 Jul 29, 2011
     */
    public function emar($params) {
        //获取所要查询的时间
        $tm = strtotime($params['d']);
        $minT = $tm - 1;
        $maxT = $tm + 86400;
        //获取查询的订单数据，订单号，下单时间，订单金额和接口参数
        $arrOrders = $this->mdlTpo->getList('order_id, createtime, order_cost, params', array('createtime|than' => $minT, 'createtime|lthan' => $maxT));
        //组装输出字段
        $str = '';
        foreach ((array)$arrOrders as $order) {
            //取出接口参数
            $arrTmp = unserialize($order['params']);
            $str .= $arrTmp['wi'] . '||';
            //格式化时间YYYYMMDDHHmmss
            $str .= date('YmdH:i:s', $order['createtime']) . '||';
            $str .= $order['order_id'] . '||';
            $str .= $order['order_cost'] . "\n";
        }
        
        echo $str;
    }
}