<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class groupactivity_finder_orders { 

    var $detail_basic = '基本信息';
    var $detail_items = '商品';
    var $detail_bills = '收退款记录';
    //var $detail_pmt = '优惠方案';
    var $detail_mark = '订单备注';
    var $detail_logs = '订单日志';
    var $detail_msg = '顾客留言';
    
    public function __construct($app)
    {
        $this->parent = kernel::single("b2c_finder_orders");
    }
    
    public function detail_basic($order_id)
    {
        $this->parent->odr_action_buttons = array('pay','delivery','receivetime','finish','refund','reship','cancel','delete');
		$odr_action_is_all_disable = $this->parent->odr_action_is_all_disable;
		
		$obj_order = app::get('b2c')->model('orders');
		$tmp_order = $obj_order->getList('*',array('order_id'=>$order_id));
		// 判定是否绑定ome或者其他后端店铺
        $obj_b2c_shop = app::get('b2c')->model('shop');

        //ajx ecos.ocs
        $node_type=array('ecos.ome','ecos.ocs');
        $cnt = $obj_b2c_shop->count(array('status'=>'bind','node_type|in'=>$node_type));
		$binded = false;
		if ($cnt > 0)
			$binded = true;
        /**
		if ($tmp_order[0]['pay_status'] == 0 && $binded)
			$this->parent->odr_action_is_all_disable = false;
		*/

        $strHTML =  $this->parent->detail_basic( $order_id );
		$this->parent->odr_action_is_all_disable = $odr_action_is_all_disable;
		return $strHTML;
    }
    
    public function detail_items($order_id)
    {
        return $this->parent->detail_items($order_id);
    }
    
    private function get_goods_detail(&$aItems, &$order_items, &$gift_items)
    {
       return $this->parent->get_goods_detail($aItems,$order_items,$gift_items);
    }
    
    public function detail_bills($order_id)
    {
        return $this->parent->detail_bills($order_id);
    }
    
    
    
    
    private function get_pmt_lists(&$sdf_order, &$arr_pmt_lists)
    {
        return $this->parent->get_pmt_lists($sdf_order,$arr_pmt_lists);
    }
    
    public function detail_mark($order_id)
    {
        return $this->parent->detail_mark($order_id);
    }
    
    public function detail_logs($order_id){
        return $this->parent->detail_logs($order_id);
    }
    
    /**
     * 顾客留言
     * @params string order id
     * @return null
     */
    public function detail_msg($order_id)
    {
        return $this->parent->detail_msg($order_id);
    }
    
    
    
}
