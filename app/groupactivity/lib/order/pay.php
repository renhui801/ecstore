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
 *
 */
class groupactivity_order_pay implements b2c_order_extends_interface
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
        $model = $this->app->model('orders');
		$o_order_act = $this->app->model('order_act');
        if( !is_array($sdf['orders']) ) return;
        if($sdf_order['pay_status'] == 1){
            //订单通中心类
           
            $o = kernel::single('groupactivity_order_notify');
            
            foreach( $sdf['orders'] as $row ) {
                $order_id = $row['rel_id'];
				$o_order_act->update( array('extension_code'=>'succ'), array('order_id'=>$order_id) );
                if(!kernel::single("groupactivity_purchase")->get_stauts($order_id)){
                    continue;
                }else{
                    
                    //当团购成功时，更改此团购下的购买数量
                    $sql_orders = "SELECT act_id FROM `sdb_groupactivity_order_act` WHERE `order_id`='".$order_id."' AND extension_code='succ'";
                    $orders = kernel::database()->select($sql_orders);
                    
                    $act_id = array_map('current',$orders);


                    $arr_order_list = $model->getList( 'order_id,order_refer,itemnum',array('order_id'=>$orderid) );
					
                    if( is_array($arr_order_list) ) {
                        foreach( $arr_order_list as $__orders ) {
                            if( $__orders['order_refer']=='local' )continue;
                            $o->rpc_notify( $__orders['order_id'], $sdf );
                            $aRs = $this->app->model('purchase')->getList('buy',array('act_id'=>$act_id));
                            $tmp_buy_times = intval($aRs[0]['buy'])+$arr_order_list[0]['itemnum'];
                            $this->app->model('purchase')->update(array('buy'=>$tmp_buy_times),array('act_id'=>$act_id));
                        }
                    }
                    
                    $sdf_order['order_refer'] = 'local_group';
                }
            }
             

        }
        return;
    }
    #End Func
}