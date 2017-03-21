<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class b2c_order_pay extends b2c_api_rpc_request
{    
    /**
     * 公开构造方法
     * @params app object
     * @return null
     */
    public function __construct($app)
    {        
        parent::__construct($app);
    }
    
    /**
     * 最终的克隆方法，禁止克隆本类实例，克隆是抛出异常。
     * @params null
     * @return null
     */
    final public function __clone()
    {
        trigger_error(app::get('b2c')->_("此类对象不能被克隆！"), E_USER_ERROR);
    }

    /**
     * 订单支付后的处理
     * @params array 支付完的信息
     * @params 支付时候成功的信息
     */
    public function pay_finish(&$sdf, $status='succ',&$msg='')
    {
        // redirect to payment list page.
        $arrOrderbillls = $sdf['orders'];
        $is_success = true;
        $str_op_id = "";
        $str_op_name =  "";
        $objMath = kernel::single('ectools_math');

        foreach ($arrOrderbillls as $rel_id=>$objOrderbills)
        {
            switch ($objOrderbills['bill_type'])
            {
                case 'payments':
                    switch ($objOrderbills['pay_object'])
                    {
                        case 'order':
                            if ($status == 'succ' || $status == 'progress')
                                $this->__order_payment($objOrderbills['rel_id'], $sdf, $status,$msg);
                            break;
                        case 'recharge':
                            // 预存款充值
                            $obj_joinfee = kernel::servicelist('b2c.other_joinfee.pay_finish');
                            $sdf['status'] = $status;
                            if ($obj_joinfee)
                            {
                                foreach ($obj_joinfee as $obj)
                                {
                                    if ($obj->get_type() == $objOrderbills['pay_object'] && $status=='succ')
                                    {
                                        $obj->generate_bills($sdf, $objOrderbills, $sdf['pay_type'], $this->str_op_id, $this->str_op_name, $errorMsg);
                                    }
                                }
                            }
                            break;
                        case 'joinfee':                            
                            break;
                        default:
                            // 其他充值方式
                            $obj_joinfee = kernel::servicelist('b2c.other_joinfee.pay_finish');
                            $sdf['status'] = $status;
                            if ($obj_joinfee)
                            {
                                foreach ($obj_joinfee as $obj)
                                {
                                    if ($obj->get_type() == $objOrderbills['pay_object'])
                                    {
                                        $obj->generate_bills($sdf, $objOrderbills, $sdf['pay_type'], $this->str_op_id, $this->str_op_name, $errorMsg);
                                    }
                                }
                            }
                            break;
                    }
                    break;
                case 'refunds':
                    // 只支持预存款
                    $objAdvance = $this->app->model("member_advance");
                    $sdf_order = $this->dump($objOrderbills['rel_id'], '*');

                    // Order information update.
                    if ($sdf['cur_money'] < $sdf_order['cur_amount'] && $status != 'failed')
                        $pay_status = '4';
                    else if ($status == 'succ')
                        $pay_status = '5';
                    else
                        $pay_status = '2';

                    $arrOrder = array(
                        'order_id' => $objOrderbills['rel_id'],
                        'pay_app_id' => $sdf['pay_app_id'],
                        'payed' => $objMath->number_minus(array($sdf_order['payed'], $sdf['cur_money'])) < 0 ? 0 : $objMath->number_minus(array($sdf_order['payed'], $sdf['cur_money'])),
                        'pay_status' => $pay_status,
                    );
                    $this->save($arrOrder);

                    $status = $objAdvance->add($sdf_order['member_id'], $sdf['payed'], app::get('b2c')->_('后台订单退款'), $errorMsg, $sdf['payment_id'], '', 'deposit', $sdf_order['memo']);
                    break;
            }

            // 改变日志操作结果
            if (is_object($this->app) && $this->app)
            {
                $objOrderLog = $this->app->model("order_log");
                if ($status == 'succ' || $status === true || $status == 'progress')
                    $status_log = 'SUCCESS';
                else
                    $status_log = 'FAILURE';

                $log_text[] = array(
                    'txt_key'=>'买家已经付款，订单<span class="siteparttitle-orage">%s</span>付款<span class="siteparttitle-orage">%s</span>元',
                    'data'=>array(
                        0=>$objOrderbills['rel_id'],
                        1=>$sdf['cur_money'],
                    ),
                );
                $log_text = serialize($log_text);

                //获取当前后台操作员
                $back_str_op_name = kernel::single('desktop_user')->get_login_name();
                $sdf_order_log = array(
                    'rel_id' => $objOrderbills['rel_id'],
                    'op_id' => ($this->from == 'Back') ? $sdf['op_id'] : $this->str_op_id,
                    'op_name' => ($this->from == 'Back') ? $back_str_op_name : $this->str_op_name,
                    'alttime' => time(),
                    'bill_type' => $objOrderbills['pay_object'],
                    'behavior' => $objOrderbills['bill_type'],
                    'result' => $status_log,
                    'log_text' => $log_text,
                );

                $log_id = $objOrderLog->save($sdf_order_log);
            }

            if ($status_log == 'FAILURE')
                $is_success = false;
        }

        return $is_success;
    }

    private function __order_payment($rel_id, &$sdf, &$status='succ',&$msg='')
    {
        $objMath = kernel::single('ectools_math');
        $obj_orders = $this->app->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $sdf_order = $obj_orders->dump($rel_id, '*', $subsdf);
        $order_items = array();

        if ($sdf_order)
        {
            if ($sdf_order['member_id'])
            {
                $arr_members = kernel::single('b2c_user_object')->get_pam_data('*',$sdf_order['member_id']);
                if( isset($arr_members['local']) ){
                    $login_name = $arr_members['local'];
                }elseif(isset($arr_members['email'])){
                    $login_name = $arr_members['email'];
                }else{
                    $login_name = $arr_members['mobile'];
                }
                $this->str_op_id = $sdf_order['member_id'];
                $this->str_op_name = $login_name;
            }
            else
            {
                $this->str_op_id = '0';
                $this->str_op_name = '';
            }

            if ($sdf_order['pay_status'] == '1')
            {
                $msg = app::get('b2c')->_('该订单已经支付，无需重新支付！');
                $status = 'succ';
                return true;
			}else if($sdf_order['pay_status'] == '2' && $status == "progress"){
				$msg = app::get('b2c')->_('该订单已经支付担保方！');
                $status = 'failed';
                return false;
			}

            // Order information update.
            if ($objMath->number_plus(array($sdf_order['payed'], $sdf['cur_money'])) < $sdf_order['cur_amount'] && $status != 'failed')
                $pay_status = '3';
            else if ($status == 'succ' || $status == 'progress')
            {
                if ($status == 'succ')
                    $pay_status = '1';
                else
                    $pay_status = '2';
            }
            else
            {
                if ($objMath->number_plus(array($sdf_order['payed'], $sdf['cur_money'])) > $sdf_order['cur_amount'])
                {
                    $msg = app::get('b2c')->_('支付金额超过需要支付的总金额！');
                    $status = 'failed';
                    return false;
                }
                $pay_status = '0';
            }

            if ($sdf['status'] != 'progress' && $objMath->number_plus(array($sdf_order['payed'], $sdf['cur_money'])) > $sdf_order['cur_amount'])
            {
                $msg = app::get('b2c')->_('支付金额超过需要支付的总金额，不能支付！');
                $status = 'failed';
                return false;
            }
            $arrOrder = array(
                'order_id' => $rel_id,
                'payment' => $sdf['pay_app_id'],
                'payed' => ($objMath->number_plus(array($sdf_order['payed'], $sdf['cur_money'])) > $sdf_order['cur_amount']) ? $sdf_order['cur_amount'] : $objMath->number_plus(array($sdf_order['payed'], $sdf['cur_money'])),
                'pay_status' => $pay_status,
            );

            // 支付完了，预存款
            if ($sdf['pay_app_id'] == 'deposit')
            {
                $objAdvance = $this->app->model("member_advance");
                $is_frontend = ($this->from == 'Back') ? false: true;
                $status = $objAdvance->deduct($sdf_order['member_id'], $sdf['money'], app::get('b2c')->_('预存款支付订单'), $msg, $sdf['payment_id'], $rel_id, 'deposit', $sdf_order['memo'],$is_frontend);
                $error_Msg = $msg;
                if (!$status)
                {
                    return false;
                }
            }
            else
            {
                $error_Msg = ($status == 'succ' || $status === true) ? (app::get('b2c')->_("订单号：") . $rel_id . ' ' . $arrPayments['app_name'] . app::get('b2c')->_("支付交易号: ") . $sdf['trade_no'] . app::get('b2c')->_("，交易成功！")) : app::get('b2c')->_("订单号：") . $rel_id . ' ' . $arrPayments['app_name'] . app::get('b2c')->_("支付交易失败！");
            }

            $is_save = $obj_orders->update($arrOrder,array('order_id'=>$rel_id));
            if (!$is_save){
                $msg = app::get('b2c')->_('订单支付状态保存失败！');
                return false;
            }
            if (!$obj_orders->db->affect_row()){
                $msg = app::get('b2c')->_('订单重复支付！');
                return false;
            }

           $errorMsg[] = $error_Msg; 
            // 为会员添加积分
            if (isset($sdf_order['member_id']) && $sdf_order['member_id'] && $arrOrder['payed'] == $sdf_order['cur_amount'] && $pay_status==1)
            {
                $arr_orders = $obj_orders->getList('*', array('order_id'=>$rel_id));
                $arr_orders[0]['pay_status'] = '1';
                $is_change_point = true;
                // 扣除积分，使用积分
                $obj_reducte_point = kernel::service('b2c_member_point_reducte');
                $operator = ($this->from == 'Back') ? $sdf['op_id'] : $sdf_order['member_id'];
                $policy_stage = $this->app->getConf("site.consume_point.stage");
                if ($arr_orders[0]['pay_status'] == '1' && $arr_orders[0]['ship_status'] == '1' && $policy_stage == '2')
                    $stage = '1';
                elseif ($arr_orders[0]['pay_status'] == '1' && $policy_stage == '1')
                    $stage = '1';
                else
                    $stage = '0';
                /** end **/
                if ($stage)
                    $is_change_point = $obj_reducte_point->change_point($sdf_order['member_id'], 0 - intval($sdf_order['score_u']), $msg, 'order_pay_use', 1, $stage, $rel_id, $operator);

                if (!$is_change_point)
                {
                    $status = 'failed';
                    return false;
                }
                $policy_stage = $this->app->getConf("site.get_policy.stage");
                if ($arr_orders[0]['pay_status'] == '1' && $arr_orders[0]['ship_status'] == '1' && $policy_stage == '2')
                    $stage = '1';
                elseif ($arr_orders[0]['pay_status'] == '1' && $policy_stage == '1')
                    $stage = '1';
                else
                    $stage = '0';

                // 获得积分
                $obj_add_point = kernel::service('b2c_member_point_add');
                if ($stage)
                    $obj_add_point->change_point($sdf_order['member_id'], intval($sdf_order['score_g']), $msg, 'order_pay_get', 2, $stage, $rel_id, $operator);

                // 增加经验值
                $obj_member = $this->app->model('members');
				if($status == "succ"){
					$obj_member->change_exp($sdf_order['member_id'], floor($sdf_order['cur_amount']));
				}
            }

            if ($pay_status == '1')
                $sdf['pay_status'] = 'PAY_FINISH';
            else if ($pay_status == '2')
                $sdf['pay_status'] = 'PAY_TO_MEDIUM';
            else if ($pay_status == '3')
                $sdf['pay_status'] = 'PAY_PART';
            else
                $sdf['pay_status'] = 'FAILED';

            $sdf['order_id'] = $rel_id;

            // 冻结库存
            if ($arrOrder['payed'] == $sdf_order['cur_amount'])
            {
                $store_mark = $this->app->getConf('system.goods.freez.time');

                // 所有的goods type 处理的服务的初始化.
                $arr_service_goods_type_obj = array();
                $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
                foreach ($arr_service_goods_type as $obj_service_goods_type)
                {
                    $goods_types = $obj_service_goods_type->get_goods_type();
                    $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
                }
                $arr_common_type = array('goods', 'gift');

                if ($store_mark == '2')
                {
                    $objGoods = $this->app->model('goods');
                    if ($sdf_order['order_objects'])
                        foreach ($sdf_order['order_objects'] as $k=>$v)
                        {
                            if (in_array($v['obj_type'], $arr_common_type))
                                $order_items = array_merge($order_items,$v['order_items']);
                            else
                            {
                                // 扩展区块的商品预占库存处理
                                $str_service_goods_type_obj = $arr_service_goods_type_obj[$v['obj_type']];
                                $is_freeze = $str_service_goods_type_obj->freezeGoods($v);
                                if (!$is_freeze)
                                {
                                    $status = 'failed';
                                    $msg = app::get('b2c')->_('商品库存不足！');
                                    return false;
                                }
                            }
                        }

                    // 判断是否已经发过货.
                    if ($sdf_order['ship_status'] == '1' || $sdf_order['ship_status'] == '2')
                    {
                        foreach ($order_items as $key=>$dinfo)
                        {
                            if ($dinfo['products']['sendnum'] < $dinfo['products']['nums'])
                            {
                                $semds = $objMath->number_plus(array($dinfo['nums'], $dinfo['sendnum']));
                                if ($semds > 0)
                                {
                                    $arr_params = array(
                                        'goods_id' => $dinfo['goods_id'],
                                        'product_id' => $dinfo['products']['product_id'],
                                        'quantity' => $semds,
                                    );
                                    if ($dinfo['item_type'] == 'product')
                                        $dinfo['item_type'] = 'goods';
                                    $str_service_goods_type_obj = $arr_service_goods_type_obj[$dinfo['item_type']];
                                    $is_freeze = $str_service_goods_type_obj->freezeGoods($arr_params);
                                    if (!$is_freeze)
                                    {
                                        $status = 'failed';
                                        $msg = app::get('b2c')->_('商品库存不足！');
                                        return false;
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        foreach ($order_items as $key=>$dinfo)
                        {
                            $arr_params = array(
                                'goods_id' => $dinfo['goods_id'],
                                'product_id' => $dinfo['products']['product_id'],
                                'quantity' => $dinfo['quantity'],
                            );
                            if ($dinfo['item_type'] == 'product')
                                $dinfo['item_type'] = 'goods';
                            $str_service_goods_type_obj = $arr_service_goods_type_obj[$dinfo['item_type']];
                            $is_freeze = $str_service_goods_type_obj->freezeGoods($arr_params);
                            if (!$is_freeze)
                            {
                                $status = 'failed';
                                $msg = app::get('b2c')->_('商品库存不足！');
                                return false;
                            }
                        }
                    }
                }

                //支付，处理其他app自身业务逻辑
                $arr_service_pay = kernel::servicelist("order_pay_operation");
                foreach((array)$arr_service_pay as $obj_service_order_pay) {
                    if(method_exists($obj_service_order_pay, "check_order_info")) {
                        if(!$obj_service_order_pay->check_order_info($sdf_order, $message)) {
                            $status = 'failed';
                            $msg = $message;
                            return false;
                        }
                    }
                }
            }

            // 与中心交互
            $is_need_rpc = false;
            $obj_rpc_obj_rpc_request_service = kernel::servicelist('b2c.rpc_notify_request');
            foreach ($obj_rpc_obj_rpc_request_service as $obj)
            {
                if ($obj && method_exists($obj, 'rpc_judge_send'))
                {
                    if ($obj instanceof b2c_api_rpc_notify_interface)
                        $is_need_rpc = $obj->rpc_judge_send($sdf_order);
                }

                if ($is_need_rpc) break;
            }

            //if (app::get('b2c')->getConf('site.order.send_type') == 'false'&&$is_need_rpc){
            if ($is_need_rpc){
                system_queue::instance()->publish('b2c_tasks_matrix_sendpayments', 'b2c_tasks_matrix_sendpayments', $sdf);
            }

            $aUpdate['order_id'] = $rel_id;
            $aUpdate['paytime'] = date('Y-m-d', time());
            $aUpdate['money'] = $sdf['cur_money'];
            $aUpdate['email'] = (!$sdf_order['member_id']) ? $sdf_order['consignee']['email'] : $arr_members['email'];
            $aUpdate['pay_status'] = $sdf['pay_status'];
            $aUpdate['is_frontend'] = ($this->from == 'Back') ? false: true;
            $aUpdate['pay_account'] = $login_name;


            $obj_orders->fireEvent("payed", $aUpdate, $sdf_order['member_id']);
        }
        else
        {
            $msg = app::get('b2c')->_('需要支付的订单号不存在！');
            $status = 'failed';
            return false;
        }
    }

    public function order_pay_finish(&$sdf, $status='succ', $from='Back',&$msg='')
    {
        $this->from = $from;
        return $this->pay_finish($sdf, $status,$msg);
    }

    public function order_payment_change($sdf)
    {
        $arr_data = array();
        /*$arr_data['tid'] = $sdf['order_id'];
        $arr_data['payment_tid'] = $sdf['payinfo']['pay_app_id'];
        $obj_payment_cfgs = app::get('ectools')->model('payment_cfgs');
        $arr_payments = $obj_payment_cfgs->getPaymentInfo($sdf['payinfo']['pay_app_id']);
        $arr_data['payment_type'] = $arr_payments['app_display_name'];  
        $arr_data['tariff'] = $sdf['payinfo']['cost_payment'];      
        
        $arr_callback = array(
            'class' => 'b2c_api_callback_app', 
            'method' => 'callback',
            'params' => array(
                'method' => 'store.trade.payment_type.update',
                'tid' => $arr_data['tid'],
            ),
        );
        
        parent::request('store.trade.payment_type.update', $arr_data, $arr_callback, 'Payment Change', 1);*/

        $arr_data['order_id'] = $sdf['order_id'];

        //新的版本控制api
        $obj_apiv = kernel::single('b2c_apiv_exchanges_request');
        $obj_apiv->rpc_caller_request($arr_data, 'orderpaymentchange');
    }

    public function order_pay_finish_extends($sdf)
    {
        if( !is_array($sdf['orders']) ) return;

        $objOrders = $this->app->model('orders');
        foreach( $sdf['orders'] as $row )
        {
            $order_id = $row['rel_id'];
            $tmp = $objOrders->getList('pay_status', array('order_id'=>$order_id));
            $sdf_order = $tmp[0];
        }
        if (!$sdf_order) return;

        $obj_payment_extends_op = kernel::servicelist('b2c.order_payment_extend.options');
        if ($obj_payment_extends_op)
        {
            foreach ($obj_payment_extends_op as $obj)
                $obj->order_pay_extends($sdf, $sdf_order);
        }
    }
}
