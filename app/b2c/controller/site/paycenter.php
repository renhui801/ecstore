<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_site_paycenter extends b2c_frontpage{

    var $noCache = true;

    public function __construct(&$app){
        parent::__construct($app);
        $this->_response->set_header('Cache-Control', 'no-store');
        if(!$this->action) $this->action = 'index';
        $this->action_view = $this->action.".html";
        $this->objMath = kernel::single("ectools_math");
    }

    /*
     *支付中心页面
     *
     * */
    public function index($order_id,$newOrder=false)
    {
        $objOrder = $this->app->model('orders');
        $sdf = $objOrder->dump($order_id);

        if(!$sdf){
            $this->_response->set_body(app::get('b2c')->_('订单号：') . $order_id . app::get('b2c')->_('不存在！'));
            kernel::single('site_router')->http_status(404);return;
            return;
        }

        if($sdf['pay_status'] == '1' || $sdf['pay_status'] == '2'){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'result_pay','arg0'=>$order_id,'arg1'=>'true'));
            header('Location:'.$url);
            exit;
        }

        $member_money = app::get('b2c')->model('members')->getList('advance',array('member_id'=>$sdf['member_id']));
        $this->pagedata['deposit_money'] = $member_money[0]['advance'] ? $member_money[0]['advance'] : 0;

        // 校验订单的会员有效性.
        $is_verified = ($this->_check_verify_member($sdf['member_id'])) ? $this->_check_verify_member($sdf['member_id']) : false;

        // 校验订单的有效性.
        if ($_COOKIE['ST_ShopEx-Order-Buy'] != md5($this->app->getConf('certificate.token').$order_id) && !$is_verified){
            $this->begin();
            $this->end(false,  app::get('b2c')->_('订单无效！'), $this->gen_url(array('app'=>'site','ctl'=>'default','act'=>'index')));
        }

        $sdf['cur_money'] = $this->objMath->number_minus(array($sdf['cur_amount'], $sdf['payed']));

        $this->pagedata['order'] = $sdf;
        $order_quantity = app::get('b2c')->model('order_items')->getList('sum(nums) as nums',array('order_id'=>$order_id));
        $this->pagedata['order']['quantity'] = $order_quantity[0]['nums'];

        $opayment = app::get('ectools')->model('payment_cfgs');
        $this->pagedata['payments'] = $opayment->getListByCode($sdf['currency']);//
        $system_money_decimals = $this->app->getConf('system.money.decimals');
        $system_money_operation_carryset = $this->app->getConf('system.money.operation.carryset');
        $pay_online = false;
        foreach ($this->pagedata['payments'] as $key=>&$arrPayments)
        {

            //判断是否有在线支付方式
            if(!$pay_online && $arrPayments['app_id'] != 'deposit' && $arrPayments['app_pay_type'] == 'true'){
                $pay_online = true;
            }

            if ($arrPayments['app_id'] == $this->pagedata['order']['payinfo']['pay_app_id'])
            {
                $this->pagedata['order']['payinfo']['pay_name'] = $arrPayments['app_name'];
                $this->pagedata['order']['payinfo']['pay_des'] = $arrPayments['app_des'];
                $this->pagedata['order']['payinfo']['platform'] = $arrPayments['app_platform'];
                $arrPayments['cur_money'] = $this->objMath->formatNumber($this->pagedata['order']['cur_money'], $system_money_decimals, $system_money_operation_carryset);
                $arrPayments['total_amount'] = $this->objMath->formatNumber($this->pagedata['order']['total_amount'], $system_money_decimals, $system_money_operation_carryset);
            }else{
                $arrPayments['cur_money'] = $this->pagedata['order']['cur_money'];
                if($this->pagedata['order']['payinfo']['pay_app_id'] == 'deposit' && $arrPayments['app_id'] != 'deposit'){
                    $temp_cur_money = $this->objMath->number_minus(array($arrPayments['cur_money'],$this->pagedata['deposit_money']));
                    $arrPayments['cur_money'] = $temp_cur_money ? $temp_cur_money : 0;
                }
                $cur_discount = $this->objMath->number_multiple(array($sdf['discount'], $this->pagedata['order']['cur_rate']));

                if ($this->pagedata['order']['payinfo']['cost_payment'] > 0)
                {
                    if ($this->pagedata['order']['cur_money'] > 0)
                        $cost_payments_rate = $this->objMath->number_div(array($arrPayments['cur_money'], $this->objMath->number_plus(array($this->pagedata['order']['cur_money'], $this->pagedata['order']['payed']))));
                    else
                        $cost_payments_rate = 0;

                    $cost_payment = $this->objMath->number_multiple(array($this->objMath->number_multiple(array($this->pagedata['order']['payinfo']['cost_payment'], $this->pagedata['order']['cur_rate'])), $cost_payments_rate));
                    $arrPayments['cur_money'] = $this->objMath->number_minus(array($arrPayments['cur_money'], $cur_discount));
                    $arrPayments['cur_money'] = $this->objMath->number_minus(array($arrPayments['cur_money'], $cost_payment));
                    $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $this->objMath->number_multiple(array($arrPayments['cur_money'], $arrPayments['pay_fee']))));
                    $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $cur_discount));
                }
                else
                {
                    $arrPayments['cur_money'] = $this->objMath->number_minus(array($arrPayments['cur_money'], $cur_discount));
                    $cost_payment = $this->objMath->number_multiple(array($arrPayments['cur_money'], $arrPayments['pay_fee']));
                    $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $cost_payment));
                    $arrPayments['cur_money'] = $this->objMath->number_plus(array($arrPayments['cur_money'], $cur_discount));
                }

                $arrPayments['total_amount'] = $this->objMath->formatNumber($this->objMath->number_div(array($arrPayments['cur_money'], $this->pagedata['order']['cur_rate'])), $system_money_decimals, $system_money_operation_carryset);
                $arrPayments['cur_money'] = $this->objMath->formatNumber($arrPayments['cur_money'], $system_money_decimals, $system_money_operation_carryset);
                if($this->pagedata['order']['payinfo']['pay_app_id'] == 'deposit' && $arrPayments['app_id'] != 'deposit'){
                    $arrPayments['cur_money'] = $this->objMath->number_div(array($arrPayments['cur_money'],$this->pagedata['order']['cur_rate']));
                    $payed = $this->objMath->number_div(array($this->pagedata['order']['payed'],$this->pagedata['order']['cur_rate']));
                    $arrPayments['total_amount'] = $this->objMath->number_plus(array($arrPayments['cur_money'],$payed,$this->pagedata['deposit_money']));
                }
            }
       }

        //将订单金额转换为基准货币值
        $this->pagedata['order']['cur_money'] = $this->objMath->number_div(array($this->pagedata['order']['cur_money'],$this->pagedata['order']['cur_rate']));
        $this->pagedata['order']['payed'] = $this->objMath->number_div(array($this->pagedata['order']['payed'],$this->pagedata['order']['cur_rate']));
        $this->pagedata['order']['total_amount'] = $this->objMath->number_plus(array($this->pagedata['order']['cur_money'],$this->pagedata['order']['payed']));
        //end

        if ($this->pagedata['order']['payinfo']['pay_app_id'] == '-1'){
            $this->pagedata['order']['payinfo']['pay_name'] = app::get('b2c')->_('货到付款');
        }

        $this->pagedata['combination_pay'] =  'false';
        if($this->pagedata['order']['payinfo']['pay_app_id'] == 'deposit'){
            $this->pagedata['combination_pay'] = $pay_online ? app::get('b2c')->getConf('site.combination.pay') : 'false';
        }

        $objCur = app::get('ectools')->model('currency');
        $aCur = $objCur->getFormat($this->pagedata['order']['currency']);
        $this->pagedata['order']['cur_def'] = $aCur['sign'];

        $this->pagedata['return_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'result_pay'));
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->pagedata['form_action'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'dopayment','arg0'=>'order'));
        $obj_order_payment_html = kernel::servicelist('b2c.order.pay_html');
        $app_id = 'b2c';
        if ($obj_order_payment_html)
        {
            foreach ($obj_order_payment_html as $obj)
            {
                $obj->gen_data($this, $app_id);
            }
        }
        $this->pagedata['newOrder'] = $newOrder;
        $this->set_tmpl('order_index');
        $this->page('site/paycenter/payments.html', false, $app_id);
    }

    /**
     * 生成支付单据处理
     * @params string - pay_object ('order','recharge','joinfee')
     * @return null
     */
    public function dopayment($pay_object='order',$combination_pay = false)
    {
        if ($pay_object)
        {
            $member_id = kernel::single('b2c_user_object')->get_member_id();
            $objOrders = $this->app->model('orders');
            $objPay = kernel::single('ectools_pay');
            $objMath = kernel::single('ectools_math');
            // 得到商店名称
            $shopName = app::get('site')->getConf('site.name');
            // Post payment information.
            $sdf = $_POST['payment'];
            unset($sdf['def_pay']);
            unset($sdf['other_online']);
            if($combination_pay == true){
                if($_POST['payment']['other_online']){
                    $sdf['pay_app_id'] = $_POST['payment']['other_online']['pay_app_id'];
                    $sdf['cur_money'] = $_POST['payment']['other_online']['cur_money'] ? $_POST['payment']['other_online']['cur_money'] : 0;
                }
            }else{
                if($_POST['payment']['def_pay']){
                    $sdf['pay_app_id'] = $_POST['payment']['def_pay']['pay_app_id'];
                    $sdf['cur_money'] = $_POST['payment']['def_pay']['cur_money'] ? $_POST['payment']['def_pay']['cur_money'] : 0;
                }
            }

            if ($member_id)
                $sdf['member_id'] = $member_id;

            if (!$sdf['pay_app_id']){
                $this->splash('failed',null, app::get('b2c')->_('支付方式不能为空！'));
            }

            $payment_cfgs=app::get('ectools')->model('payment_cfgs');
            if(!$payment_cfgs->check_payment_open($sdf['pay_app_id'])){
                $this->splash('failed',null,app::get('b2c')->_('不支持此支付方式'));
            }

            $order_items = app::get('b2c')->model('order_items')->getList('name',array('order_id'=>$sdf['order_id']),0,1);

            $sdf['body'] = $order_items[0]['name'];
            $sdf['pay_object'] = $pay_object;
            $sdf['shopName'] = $shopName;

            switch ($sdf['pay_object'])
            {
                case 'order':
                    $orderMemberId = app::get('b2c')->model('orders')->getList('member_id',array('order_id'=>$sdf['order_id']));
                    if(!$orderMemberId[0]['member_id'] ||  $orderMemberId[0]['member_id'] != $member_id ){
                        $this->splash('failed',null,app::get('b2c')->_('无效订单，不能支付'));
                    }
                    $arrOrders = $objOrders->dump($sdf['order_id'], '*');

                    //线下支付
                    if ($sdf['pay_app_id'] == 'offline')
                    {
                        if (isset($sdf['member_id']) && $sdf['member_id'])
                            $this->begin(array('app'=>'b2c','ctl'=>'site_member','act'=>'orderdetail', 'arg0'=>$sdf['order_id']));
                        else
                            $this->begin(array('app'=>'b2c','ctl'=>'site_order','act'=>'index', 'arg0'=>$sdf['order_id']));
                    }

                    if ($arrOrders['payinfo']['pay_app_id'] != $sdf['pay_app_id'])
                    {
                        $class_name = "";
                        $obj_app_plugins = kernel::servicelist("ectools_payment.ectools_mdl_payment_cfgs");
                        foreach ($obj_app_plugins as $obj_app)
                        {
                            $app_class_name = get_class($obj_app);
                            $arr_class_name = explode('_', $app_class_name);
                            if (isset($arr_class_name[count($arr_class_name)-1]) && $arr_class_name[count($arr_class_name)-1])
                            {
                                if ($arr_class_name[count($arr_class_name)-1] == $sdf['pay_app_id'])
                                {
                                    $pay_app_ins = $obj_app;
                                    $class_name = $app_class_name;
                                }
                            }
                            else
                            {
                                if ($app_class_name == $sdf['pay_app_id'])
                                {
                                    $pay_app_ins = $obj_app;
                                    $class_name = $app_class_name;
                                }
                            }
                        }
                        $strPaymnet = app::get('ectools')->getConf($class_name);
                        $arrPayment = unserialize($strPaymnet);

                        $sdf['cur_money'] = $objMath->number_multiple(array($sdf['cur_money'], $arrOrders['cur_rate']));
                        $cost_payment = $objMath->number_multiple(array($objMath->number_minus(array($objMath->number_minus(array($arrOrders['total_amount'], $arrOrders['payinfo']['cost_payment'])),$arrOrders['payed'])), $arrPayment['setting']['pay_fee']));
                        $total_amount = $objMath->number_plus(array($objMath->number_minus(array($arrOrders['total_amount'], $arrOrders['payinfo']['cost_payment'])), $cost_payment));
                        $cur_money = $objMath->number_multiple(array($total_amount, $arrOrders['cur_rate']));

                        // 更新订单支付信息
                        $arr_updates = array(
                            'order_id' => $sdf['order_id'],
                            'payinfo' => array(
                                'pay_app_id' => $sdf['pay_app_id'],
                                'cost_payment' => $objMath->number_multiple(array($cost_payment, $arrOrders['cur_rate'])),
                            ),
                            'currency' => $arrOrders['currency'],
                            'cur_rate' => $arrOrders['cur_rate'],
                            'total_amount' => $total_amount,
                            'cur_amount' => $cur_money,
                        );

                        $changepayment_services = kernel::servicelist('b2c_order.changepayment');
                        foreach($changepayment_services as $changepayment_service)
                        {
                            $changepayment_service->generate($arr_updates);
                        }

                        $objOrders->save($arr_updates);

                        $arrOrders = $objOrders->dump($sdf['order_id'], '*');
                        /** 需要想中心发送支付方式修改的动作 **/
                        $obj_b2c_pay = kernel::single('b2c_order_pay');
                        $obj_b2c_pay->order_payment_change($arrOrders);
                    }

                    //ajx 防止恶意修改支付金额，导致支付状态异常
                    if($pay_object == 'order' && (!isset($_POST['payment']['other_online']) || $combination_pay == true) ){
                        $orders = $objOrders->dump($sdf['order_id']);
                        $sdf['cur_amount'] = $objMath->number_minus(array($orders['cur_amount'], $orders['payed']));
                        $orders['total_amount'] = $objMath->number_div(array($orders['cur_amount'], $orders['cur_rate']));
                        $sdf['money'] = floatval($orders['total_amount'] - $orders['payed']);
                        $sdf['currency']=$orders['currency'];
                        $sdf['cur_money'] = $objMath->number_minus(array($orders['cur_amount'], $orders['payed']));
                        $sdf['cur_rate'] = $orders['cur_rate'];
                    }

                    // 检查是否能够支付
                    $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
                    $sdf_post = $sdf;
                    $sdf_post['money'] = $sdf['cur_money'];
                    if (!$obj_checkorder->check_order_pay($sdf['order_id'],$sdf_post,$message))
                    {
                        $this->result($arrOrders,$message);
                        return;
                    }

                    if ($sdf['pay_app_id'] == 'offline')
                    {
                        $this->result_pay($arrOrders['order_id'],true);
                        return;
                    }

                    if (!$sdf['pay_app_id'])
                        $sdf['pay_app_id'] = $arrOrders['payinfo']['pay_app_id'];

                    $sdf['currency'] = $arrOrders['currency'];
                    $sdf['total_amount'] = $arrOrders['total_amount'];
                    $sdf['payed'] = $arrOrders['payed'] ? $arrOrders['payed'] : '0.000';
                    $sdf['money'] = $objMath->number_div(array($sdf['cur_money'], $arrOrders['cur_rate']));

                    $sdf['payinfo']['cost_payment'] = $arrOrders['payinfo']['cost_payment'];

                    // 相关联的id.
                    $sdf['rel_id'] = $sdf['order_id'];
                    break;
                case 'recharge':
                    // 得到充值信息
                    $sdf['rel_id'] = $sdf['member_id'];
                    break;
                case 'joinfee':
                    // 得到加盟费信息
                    break;
                default:
                    // 其他的卡充值
                    $sdf['rel_id'] = $sdf['rel_id'];
                    break;
            }

            $payment_id = $sdf['payment_id'] = $objPay->get_payment_id($sdf['rel_id']);
            if ($sdf['pay_app_id'] == 'deposit'){
                $member_info = app::get('b2c')->model('members')->getList('advance,advance_freeze',array('member_id'=>$member_id));
                $sdf['return_url'] = "";
            }else{
                if (!$sdf['return_url'])
                    $sdf['return_url'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','act'=>'result_pay', 'arg0'=>$payment_id));
            }

            $sdf['status'] = 'ready';
            // 需要加入service给其他实体和虚拟卡
            $obj_prepaid = kernel::service('b2c.prepaidcards.add');
            $is_save_prepaid = false;
            if ($obj_prepaid)
            {
                $is_save_prepaid = $obj_prepaid->gen_charge_log($sdf);
            }
            $is_payed = $objPay->generate($sdf, $this, $msg);
            if ($is_save_prepaid && $is_payed)
            {
                $obj_prepaid->update_charge_log($sdf);
            }

            if ($sdf['pay_app_id'] == 'deposit')
            {
                if(!$combination_pay && $sdf['combination_pay'] == 'true'){
                    $this->dopayment('order',true);
                }

                if ($is_payed){
                    $this->result_pay($arrOrders['order_id'],true);
                }else
                    $this->result($arrOrders,$msg);
            }
        }
    }


    public function get_payment_money(){
        $pay_app_id = $_POST['payment']['other_online']['pay_app_id'];
        $cur_money = $_POST['payment']['other_online']['cur_money'];
        $payments = app::get('ectools')->model('payment_cfgs')->getPaymentInfo($pay_app_id);
        $objMath = kernel::single('ectools_math');
        $cost_payment = $objMath->number_multiple(array($cur_money, $payments['pay_fee']));
        $data['cur_money'] = $objMath->number_plus(array($cost_payment,$cur_money));
        $data['total_amount'] = $objMath->number_plus(array($data['cur_money'],$_POST['payment']['def_pay']['cur_money']));
        $data['cur_money'] = app::get('ectools')->model('currency')->changer($data['cur_money']);
        $data['total_amount'] = app::get('ectools')->model('currency')->changer($data['total_amount']);
        echo json_encode($data);
    }

    public function result_pay($data_id,$type=false){
        $this->objMath = kernel::single("ectools_math");
        $arrOrders = array();
        if($type){
            $order_id = $data_id;
        }else{
            $billList = app::get('ectools')->model('order_bills')->getList('*',array('bill_id'=>$data_id));
            $order_id = $billList[0]['rel_id'];
        }

		$member_id = kernel::single('b2c_user_object')->get_member_id();
        $arrOrders = app::get('b2c')->model('orders')->getList('order_id,payed,final_amount,cur_rate,currency,total_amount,ship_area,ship_addr,ship_zip,ship_time,ship_mobile,ship_name,pay_status,ship_tel',array('order_id'=>$order_id,'member_id'=>$member_id));
        $arrOrders = $arrOrders[0];
		if(!$arrOrders){
        	$this->splash('failed',null, app::get('b2c')->_('订单不存在'));
		}
        $order_quantity = app::get('b2c')->model('order_items')->getList('sum(nums) as nums',array('order_id'=>$arrOrders['order_id']));
        $arrOrders['quantity'] = $order_quantity[0]['nums'];
        $this->pagedata['order'] = $arrOrders;

        $this->pagedata['site_checkout_receivermore_open'] = $this->app->getConf('site.checkout.receivermore.open');
        $this->set_tmpl('splash');
        $this->page('site/paycenter/result_success.html');
    }

    public function result($arrOrders,$msg){
		$member_id = kernel::single('b2c_user_object')->get_member_id();
        $arrOrders = app::get('b2c')->model('orders')->getList('order_id,payed,final_amount,cur_rate,currency,total_amount,ship_area,ship_addr,ship_zip,ship_time,ship_mobile,ship_name,pay_status,ship_tel',array('order_id'=>$arrOrders['order_id'],'member_id'=>$member_id));
        $arrOrders = $arrOrders[0];
		if(!$arrOrders){
        	$this->splash('failed',null, app::get('b2c')->_('订单不存在'));
		}

        $order_quantity = app::get('b2c')->model('order_items')->getList('sum(nums) as nums',array('order_id'=>$arrOrders['order_id']));
        $arrOrders['quantity'] = $order_quantity[0]['nums'];

        $this->pagedata['order'] = $arrOrders;
        $this->pagedata['msg'] = $msg;
        $this->set_tmpl('splash');
        $this->page('site/paycenter/result_failure.html');
    }
}
