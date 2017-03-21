<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * 修改订单信息
 * @package default
 * @author kxgsy163@163.com
 */
class timedbuy_order_pay implements b2c_order_extends_interface
{
    
    function __construct($app)
    {
        $this->app = $app;
    }
    
    /*
     * 处理订单信息 service 注册到b2c
     * $sdf, $sdf_order
     */
    public function order_pay_extends( $sdf,&$sdf_order=array() )
    {
        $o = $this->app->model('objitems');
        if( !is_array($sdf['orders']) ) return;
        foreach( $sdf['orders'] as $row ) {
            $where['order_id'] = $row['rel_id'];
            $update['order_pay_status'] = '1';
            $o->update($update,$where);
        }
        return true;
    }

    /*
     * 检查订单是否可以支付
     */
    public function check_order_info($sdf_order, &$msg) {
        $order_id = $sdf_order['order_id'];
        $order = $this->app->model('objitems')->getList('*',array('order_id'=>$order_id));
        if(!$order) {
            return true;
        }

        $order = $order[0];
        $goods_id = $order['goods_id'];
        $sale_rule_id = $order['sales_rule_id'];
        $quantity = $order['quantity'];
        $member_id = $order['member_id'];

        //获取限购总数量
        $arr_sales_info = $this->get_sales_info_by_ruleid( $sale_rule_id, false );
        if(!$arr_sales_info) {
            $msg = $this->app->_('支付失败');
            return false;
        }
        $action_solution = unserialize($arr_sales_info['action_solution']);

        $config = $action_solution['timedbuy_promotion_solution_timedbuy'];//配置
        
        $filter= array();
        $filter['goods_id'] = $goods_id;
        $filter['sales_rule_id'] = $sale_rule_id;
        
        $stock_freez_time = app::get('b2c')->getConf('system.goods.freez.time');
        if($stock_freez_time == '1') {
            return true;
        }
        $data = $this->app->model('objitems')->getList('*',$filter);
        $num = 0;
        if($data) {
            foreach( $data as $row ) {
                if($row['order_id'] == $order_id) {
                    continue;
                }
                if($row['order_pay_status'] == '1') {
                    $num += $row['quantity'];
                }
            }
        }
        
        if( $config['quantity'] && $config['quantity']<$num+$quantity ) {
            $msg = '已超出限购库存！';
            return false;
        }

        return true;
    }

    public function get_sales_info_by_ruleid( $rule_id, $filter_time=true ) {
        $order = kernel::single('b2c_cart_prefilter_promotion_goods')->order();
        $time = time();
        $filter = array('rule_id'=>$rule_id, 'status'=>'true');
        if($filter_time) {
            $filter['from_time|sthan'] = $time;
            $filter['to_time|bthan'] = $time;
        }
        $arr = app::get('b2c')->model('goods_promotion_ref')->getList( '*',$filter,0,-1,$order );
        if( !$arr ) return false;
        $arr = $arr[0];
        $solution = @unserialize($arr['action_solution']);
        if( !is_array($solution) ) continue;
        if( !isset($solution['timedbuy_promotion_solution_timedbuy']) ) {
            return false;
        }
        return $arr;
    }
    #End Func
}