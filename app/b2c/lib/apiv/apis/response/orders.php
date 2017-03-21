<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


/**
 * b2c order interactor with center
 * shopex team
 * dev@shopex.cn
 */
class b2c_apiv_apis_response_orders
{

    /**
     * app object
     */
    public $app;

    /**
     * ectools_math object
     */
    public $objMath;

    /**
     * 订单状态转换值
     */
    private $arr_status = array(
        'TRADE_ACTIVE'=>'active',
        'TRADE_CLOSED'=>'dead',
        'TRADE_FINISHED'=>'finish',
    );

    /**
     * 订单支付状态转换值
     */
    private $arr_pay_status = array(
        'PAY_NO'=>'0',
        'PAY_FINISH'=>'1',
        'PAY_TO_MEDIUM'=>'2',
        'PAY_PART'=>'3',
        'REFUND_PART'=>'5',
        'REFUND_ALL'=>'4',
    );

    /**
     * 订单发货状态转化
     */
    private $arr_ship_status = array(
        'SHIP_NO'=>'0',
        'SHIP_PREPARE'=>'0',
        'SHIP_PART'=>'2',
        'SHIP_FINISH'=>'1',
        'RESHIP_PART'=>'3',
        'RESHIP_ALL'=>'4',

    );

    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->objMath = kernel::single('ectools_math');
    }

    /**
     * 订单创建
     * @param array sdf
     * @param string member indent
     * @param string message
     * @return boolean success or failure
     */
    public function create(&$sdf, &$thisObj)
    {
        @$sdf['consignee'] = json_decode($sdf['consignee'], true);
        @$sdf['payinfo'] = json_decode($sdf['payinfo'], true);
        @$sdf['order_pmt'] = json_decode($sdf['order_pmt'], true);
        @$sdf['shipping'] = json_decode($sdf['shipping'], true);
        @$sdf['order_objects'] = json_decode($sdf['order_objects'], true);

        $sdf['is_delivery'] = 'Y';

        $obj_products = $this->app->model('products');
        $obj_specification = $this->app->model('specification');
        $obj_spec_values = $this->app->model('spec_values');
        foreach($sdf['order_objects'] as $ok => $oo)
        {
            foreach($oo['order_items'] as $tk => $ot)
            {
                if(($product_id = $ot['products']['product_id']))
                {
                    $strAddon = $arrAddon = '';
                    if(($spec_desc  = $obj_products->getRow('spec_desc', array('product_id' => $product_id))))
                    {
                        $spec_desc = $spec_desc['spec_desc'];
                        if(is_array($spec_desc))
                        {
                            foreach ($spec_desc['spec_value_id'] as $spec_key=>$str_spec_value_id)
                            {
                                $arr_spec_value = $obj_spec_values->dump($str_spec_value_id);
                                $arr_specification = $obj_specification->dump($arr_spec_value['spec_id']);
                                $arrAddon['product_attr'][$spec_key] = array(
                                    'label' => $arr_specification['spec_name'],
                                    'value' => $arr_spec_value['spec_value'],
                                );
                            }

                            $strAddon = serialize($arrAddon);
                        }
                    }
                    $sdf['order_objects'][$ok]['order_items'][$tk]['addon'] = $strAddon;
                }
            }
        }


        // 创建订单是和中心的交互
        $order = $this->app->model('orders');
        $result = $order->save($sdf);//todo order_items表product_id字段未插入

        if (!$result)
        {
            trigger_error(app::get('b2c')->_('订单生成失败！'), E_USER_ERROR);
        }
        else
        {
            return true;
        }
    }



    public function search( $params, &$service )
    {
        //校验参数
        if( !( $start_time = $params['start_time'] ) )
            $service->send_user_error('7001', '开始时间不能为空！');
        if( ($start_time = strtotime(trim($start_time))) === false || $start_time == -1 )
            $service->send_user_error('7002', '开始时间不合法！');

        if( !( $end_time = $params['end_time'] ) )
            $service->send_user_error('7003', '结束时间不能为空！');
        if( ($end_time = strtotime(trim($end_time))) === false || $end_time == -1 )
            $service->send_user_error('7004', '结束时间不合法！');

        $page_no = 1;
        if( $params['page_no'] != '' ){
            if( !is_numeric($params['page_no']) || $params['page_no'] < 1 )
                $service->send_user_error('7005', 'page_no不合法！');
            else
                $page_no = intval($params['page_no']);
        }

        $page_size = 40;
        if( $params['page_size'] != '' ){
            if( !is_numeric($params['page_size']) || $params['page_size'] < 1 || $params['page_size'] > 100 )
                $service->send_user_error('7006', 'page_size不合法！');
            else
                $page_size = intval($params['page_size']);
        }

        /**
         * 支付状态数组
         */

        $arr_pay_status = array(
            '0'=>'PAY_NO',
            '1'=>'PAY_FINISH',
            '2'=>'PAY_TO_MEDIUM',
            '3'=>'PAY_PART',
            '4'=>'REFUND_PART',
            '5'=>'REFUND_ALL',
        );

        $obj_orders = app::get('b2c')->model('orders');

        $where = '';
        if( $start_time != '' )
            $where .= "AND last_modified > '" . $start_time . "' ";
        if( $end_time != '' )
            $where .= "AND last_modified <= '" . $end_time . "' ";
        if( $where != '' )
            $where = 'WHERE ' . substr($where, 4);

        $sql	=	"SELECT ### FROM " .
            $obj_orders->table_name(1) . ' ' .
            $where .
            "ORDER BY last_modified ASC";

        //获取总数
        $total_results = $obj_orders->db->select( str_replace('###', 'count(*) cc', $sql) );
        if( $total_results )
            $total_results = $total_results[0]['cc'];
        else
            $total_results = 0;
        if($total_results == 0) {
            return $this->search_response(array());
        }

        //计算分页
        $offset = ($page_no-1) * $page_size;
        $limit = $page_size;

        $has_next = $total_results > ($offset+$limit) ? 'true' : 'false';

        $sdf = $obj_orders->db->selectLimit( str_replace('###', 'order_id, status, pay_status, ship_status, last_modified', $sql), $limit, $offset );

        if(!$sdf){		
            return $this->search_response(array());
        }

        $trades = array();
        $index = 0;
        foreach( $sdf as $row )
        {
            $trades[$index]['tid'] = $row['order_id'];
            $trades[$index]['status'] = ($row['status'] == 'active') ? 'TRADE_ACTIVE' : 'TRADE_CLOSED';
            $trades[$index]['pay_status'] =  ($row['pay_status'] == '0' || !$row['pay_status']) ? 'PAY_NO' : $arr_pay_status[$row['pay_status']];
            $trades[$index]['ship_status'] = ($row['ship_status'] == '0' || !$row['ship_status']) ? 'SHIP_NO' : 'SHIP_FINISH';
            $trades[$index]['modified'] = date('Y-m-d H:i:s', $row['last_modified']);
            $index++;
        }

        return $this->search_response($trades, $total_results, $has_next);
    }

    private function search_response($trades, $total_results=0, $has_next='false'){

        return array(
            'trades' => $trades,
            'total_results' => $total_results,
            'has_next' => $has_next,
        );

    }

    /**
     * 订单留言
     * @param array sdf
     * @param string message
     * @return boolean success or failure
     */
    public function leave_message(&$sdf, &$thisObj)
    {
        // 订单留言是和中心的交互
        if (isset($sdf['order_bn']) && $sdf['order_bn'])
        {
            $order = $this->app->model('orders');
            $arrOrder = $order->dump($sdf['order_bn'], 'member_id');
            $arr_memo = json_decode($sdf['message'], true);
            if (!$arr_memo)
            {
                $thisObj->send_user_error(app::get('b2c')->_('留言内容格式不正确！'), array('tid'=>$sdf['order_bn']));
            }

            if ($arrOrder)
            {
                //ajx  修改ocs、淘管同步订单附言为订单中买家备注
                $arrData['memo'] = $arr_memo['op_content'];
                $arrData['order_id'] = $sdf['order_bn'];

                if (!$order->save($arrData))
                {
                    $thisObj->send_user_error(app::get('b2c')->_('订单留言保存失败！'), array('tid'=>$sdf['order_bn']));
                }
                else
                {
                    return array('tid'=>$sdf['order_bn']);
                }
            }
            else
            {
                $thisObj->send_user_error(app::get('b2c')->_('订单不存在！'), array('tid'=>$sdf['order_bn']));
            }
        }
        else
        {
            $thisObj->send_user_error(app::get('b2c')->_('订单号未发送！'), array('tid'=>$sdf['order_bn']));
        }
    }



    public function detail( $params, &$service )
    {
        if( !( $order_id = $params['tid'] ) ){
            return $service->send_user_error('7001', 'tid不能为空！');
        }

        $order_detail = kernel::single('b2c_order_full')->get($order_id);
        return $order_detail;
    }


    /**
     * 订单备注
     * @param array sdf
     * @param string message
     * @return boolean success or failure
     */
    public function remark(&$sdf, &$thisObj)
    {
        // 备注订单是和中心的交互
        $order = $this->app->model('orders');
        $arr_order = $order->dump($sdf['order_bn']);

        if ($arr_order)
        {
            if ($arr_order['mark_text'])
                $arr_order['mark_text'] = unserialize($arr_order['mark_text']);
            $mem_info = json_decode($sdf['memo'], true);
            $data['order_id'] = $sdf['order_bn'];
            $arr_order['mark_text'][] = array(
                'mark_text' => $mem_info['op_content'],
                'add_time' => $mem_info['op_time'],
                'op_name' => $mem_info['op_name'],
            );
            $data['mark_text'] = serialize($arr_order['mark_text']);
            $data['mark_type'] = $sdf['mark_type'];

            $is_success = $order->save($data);
            if ($is_success)
            {
                return array('tid'=>$sdf['order_bn']);
            }
            else
            {
                $thisObj->send_user_error(app::get('b2c')->_('订单备注保存失败！'), array('tid'=>$sdf['order_bn']));
            }
        }
        else
        {
            $thisObj->send_user_error(app::get('b2c')->_('此订单不存在！'), array('tid'=>$sdf['order_bn']));
        }
    }

    /**
     * 订单状态更新
     * @param array sdf
     * @return boolean true or false.
     */
    public function status_update(&$sdf, &$thisObj)
    {
        // 取消订单是和中心的交互


        $order = $this->app->model('orders');
        $arr_data['status'] = $sdf['status'];
        $arr_data['order_id'] = $sdf['order_bn'];

        $arr_order = $order->dump($sdf['order_bn']);
        $db = kernel::database();
        if ($arr_order){

            //事务处理

            $transaction_status = $db->beginTransaction();

            //订单作废，释放冻结库存
            if($sdf['status'] == 'dead') {
                $obj_checkorder = kernel::service('b2c_order_apps', array('content_path'=>'b2c_order_checkorder'));
                $arrStatus = $obj_checkorder->checkOrderFreez('cancel', $arr_order['order_id']);
                if($arrStatus['unfreez']) {
                    $is_unfreeze = true;
                    $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
                    $sdf_order = $order->dump($arr_order['order_id'], 'order_id,status,pay_status,ship_status', $subsdf);

                    // 所有的goods type 处理的服务的初始化.
                    $arr_service_goods_type_obj = array();
                    $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
                    foreach ($arr_service_goods_type as $obj_service_goods_type){
                        $goods_types = $obj_service_goods_type->get_goods_type();
                        $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
                    }

                    $objGoods = $this->app->model('goods');
                    foreach($sdf_order['order_objects'] as $k => $v){
                        if ($v['obj_type'] != 'goods' && $v['obj_type'] != 'gift'){
                            foreach( kernel::servicelist('b2c.order_store_extends') as $object ) {
                                if( $object->get_goods_type()!=$v['obj_type'] ) continue;
                                $obj_extends_store = $object;
                                if ($obj_extends_store){
                                    $obj_extends_store->store_change($v, 'cancel');
                                }
                            }
                            continue;
                        }

                        foreach ($v['order_items'] as $arrItem){
                            if ($arrItem['item_type'] == 'product')  $arrItem['item_type'] = 'goods';
                            $arr_params = array(
                                'goods_id' => $arrItem['products']['goods_id'],
                                'product_id' => $arrItem['products']['product_id'],
                                'quantity' => $arrItem['quantity'],
                            );
                            $str_service_goods_type_obj = $arr_service_goods_type_obj[$arrItem['item_type']];
                            $is_unfreeze = $str_service_goods_type_obj->unfreezeGoods($arr_params);
                        }
                    }
                }
            }


            $is_save = $order->save($arr_data);

            //订单作废，释放冻结积分
            if($sdf['status'] == 'dead') {
                $obj_order_operations = kernel::servicelist('b2c.order_point_operaction');
                if ($obj_order_operations){
                    $arr_data = array(
                        'member_id'  => $arr_order['member_id'],
                        'score_g'       => $arr_order['score_g'],
                        'score_u'       => $arr_order['score_u'],
                    );
                    foreach ($obj_order_operations as $obj_operation){
                        $obj_operation->gen_member_point($arr_data, $reason);
                    }
                }
            }

            if ($is_save){
                //事务提交
                $db->commit($transaction_status);

                //触发邮件短信事件
                if ($sdf['status'] == 'dead'){
                    $aUpdate['order_id'] = $sdf['order_bn'];
                    //$sdf_order = $order->dump($sdf['order_bn']);
                    $sdf_order = $arr_order;
                    if ($sdf_order['member_id']){
                        $pamMembers = app::get('pam')->model('members');
                        $arr_member = $pamMembers->getList('login_account',array('member_id'=>$sdf_order['member_id'],'login_type'=>'email'));
                    }
                    $aUpdate['email'] = (!$sdf_order['member_id']) ? $sdf_order['consignee']['email'] : $arr_member[0]['login_account'];
                    $order->fireEvent("cancel", $aUpdate, $sdf_order['member_id']);

                    foreach( kernel::servicelist("b2c_order_cancel_finish") as $object ) {
                        if( !is_object($object) ) continue;
                        if( !method_exists($object,'order_notify') ) continue;
                        $object->order_notify($arr_order);
                    }
                }

                // 记录订单日志
                $objorder_log = $this->app->model('order_log');
                $log_text = app::get('b2c')->_("订单状态修改！");
                $sdf_order_log = array(
                    'rel_id' => $sdf['order_bn'],
                    'op_id' => '1',
                    'op_name' => 'admin',
                    'alttime' => time(),
                    'bill_type' => 'order',
                    'behavior' => 'updates',
                    'result' => 'SUCCESS',
                    'log_text' => $log_text,
                );
                $log_id = $objorder_log->save($sdf_order_log);

                //ajx  添加当同时联通了crm和ocs或erp时，ocs或erp取消订单时触发取消接口同步到crm
                if($order_object = kernel::service('b2c_order_rpc_async')){                                                                                     
                    $order_object->modifyActive($sdf['order_bn']);
                }
                //ajx end

                return array('tid'=>$sdf['order_bn']);

            }else{
                //事件回滚
                $db->rollback();
                $thisObj->send_user_error(app::get('b2c')->_('订单状态修改失败！'), array('tid'=>$sdf['order_bn']));
            }

        }else{
            //事件回滚
            $db->rollback();
            $thisObj->send_user_error(app::get('b2c')->_('订单不存在！'), array('tid'=>$sdf['order_bn']));
        }
    }

    /**
     * 订单修改
     * @param array sdf
     * @return boolean sucess of failure
     */
    public function update(&$sdf, &$thisObj)
    {
        // 修改订单是和中心的交互

        if (!isset($sdf['order_bn']) || !$sdf['order_bn'])
        {
            $thisObj->send_user_error(app::get('b2c')->_('需要更新的库存不存在！'), array('tid'=>''));
        }
        else
        {
            $objOrder = $this->app->model('orders');
            $arr_order = $objOrder->dump($sdf['order_bn']);

            if ($arr_order)
            {
                $arr_data_receive = json_decode($sdf['consignee'], true);

                if (!$arr_data_receive)
                {
                    $thisObj->send_user_error(app::get('b2c')->_('订单收货地址为空！'), array('tid'=>$sdf['order_bn']));
                }
                else
                {
                    $obj_regions = app::get('ectools')->model('regions');
                    $arr_regions = $obj_regions->dump(array('local_name' => $arr_data_receive['distinct']));

                    $arr_data['order_id'] = $sdf['order_bn'];
                    if (isset($sdf['last_modified']) && $sdf['last_modified'])
                        $arr_data['last_modified'] = $sdf['last_modified'];
                    if (isset($sdf['is_tax']) && $sdf['is_tax'])
                    {
                        $arr_data['is_tax'] = $sdf['is_tax'];
                        $arr_data['tax_title'] = $sdf['tax_title'];
                        $arr_data['cost_tax'] = $sdf['cost_tax'];
                    }
                    if (isset($sdf['cost_item']) && $sdf['cost_item'])
                        $arr_data['cost_item'] = $sdf['cost_item'];
                    if (isset($sdf['total_amount']) && $sdf['total_amount'])
                        $arr_data['total_amount'] = $sdf['total_amount'];
                    if (isset($sdf['discount']) && $sdf['discount'])
                        $arr_data['discount'] = $sdf['discount'];
                    //if (isset($sdf['payed']) && $sdf['payed'])
                    //    $arr_data['payed'] = $sdf['payed'];
                    if (isset($sdf['currency']) && $sdf['currency'])
                        $arr_data['currency'] = $sdf['currency'];
                    if (isset($sdf['cur_rate']) && $sdf['cur_rate'])
                        $arr_data['cur_rate'] = $sdf['cur_rate'];
                    if (isset($sdf['cur_amount']) && $sdf['cur_amount'])
                        $arr_data['cur_amount'] = $sdf['cur_amount'];
                    if (isset($sdf['score_u']) && $sdf['score_u'])
                        $arr_data['score_u'] = $sdf['score_u'];
                    if (isset($sdf['score_g']) && $sdf['score_g'])
                        $arr_data['score_g'] = $sdf['score_g'];
                    if (isset($sdf['shipping']) && $sdf['shipping'])
                    {
                        $arr_data['shipping'] = json_decode($sdf['shipping'], true);
                    }
                    if (isset($sdf['payinfo']) && $sdf['payinfo'])
                    {
                        $arr_data['payinfo'] =json_decode($sdf['payinfo'], true);
                    }
                    if ($arr_regions)
                        $arr_data['consignee'] = array(
                            'name' => $arr_data_receive['name'],
                            'addr' => $arr_data_receive['addr'],
                            'zip' => $arr_data_receive['zip'],
                            'telephone' => $arr_data_receive['telephone'],
                            'mobile' => $arr_data_receive['mobile'],
                            'email' => $arr_data_receive['email'],
                            'area' => $arr_regions['package'] . ":" . $arr_data_receive['states'] . "/" . $arr_data_receive['city'] . "/" . $arr_data_receive['distinct'] . ":" . $arr_regions['region_id'],
                        );
                    else
                        $arr_data['consignee'] = array(
                            'name' => $arr_data_receive['name'],
                            'addr' => $arr_data_receive['addr'],
                            'zip' => $arr_data_receive['zip'],
                            'telephone' => $arr_data_receive['telephone'],
                            'mobile' => $arr_data_receive['mobile'],
                            'email' => $arr_data_receive['email'],
                            'area' => "",
                        );

                    $result = $objOrder->save($arr_data);//订单基本信息更改

                    if (!$result)
                    {
                        $thisObj->send_user_error(app::get('b2c')->_('订单基本信息修改失败！'), array());
                    }

                    // 记录订单日志
                    $objorder_log = $this->app->model('order_log');
                    $log_text = app::get('b2c')->_("订单收货人信息修改！");
                    $sdf_order_log = array(
                        'rel_id' => $sdf['order_bn'],
                        'op_id' => '1',
                        'op_name' => 'admin',
                        'alttime' => time(),
                        'bill_type' => 'order',
                        'behavior' => 'updates',
                        'result' => 'SUCCESS',
                        'log_text' => $log_text,
                    );
                    $log_id = $objorder_log->save($sdf_order_log);

                    return array('tid'=>$sdf['order_bn']);
                }
            }
            else
            {
                $thisObj->send_user_error(app::get('b2c')->_('订单不存在！'), array('tid'=>$sdf['order_bn']));
            }
        }
    }

    /**
     * 订单发货状态更新接口
     * @param array sdf
     * @return boolean true or false
     */
    public function ship_status_update(&$sdf, &$thisObj)
    {
        $order = $this->app->model('orders');
        $arr_data['ship_status'] = $sdf['ship_status'];
        $arr_data['order_id'] = $sdf['order_bn'];

        $arr_order = $order->dump($sdf['order_bn']);
        if ($arr_order['ship_status'] == '1')
        {
            return array('tid'=>$sdf['order_bn']);
        }

        if ($arr_order)
        {
            $is_save = $order->save($arr_data);

            if ($is_save)
            {
                // 记录订单日志
                $objorder_log = $this->app->model('order_log');
                $log_text = app::get('b2c')->_("订单发货状态修改！");
                $sdf_order_log = array(
                    'rel_id' => $sdf['order_bn'],
                    'op_id' => '1',
                    'op_name' => 'admin',
                    'alttime' => time(),
                    'bill_type' => 'order',
                    'behavior' => 'updates',
                    'result' => 'SUCCESS',
                    'log_text' => $log_text,
                );
                $log_id = $objorder_log->save($sdf_order_log);

                return array('tid'=>$sdf['order_bn']);
            }
            else
            {
                $thisObj->send_user_error(app::get('b2c')->_('订单发货状态修改失败！'), array('tid'=>$sdf['order_bn']));
            }
        }
        else
        {
            $thisObj->send_user_error(app::get('b2c')->_('订单不存在！'), array('tid'=>$sdf['order_bn']));
        }
    }

    /**
     * 修改订单明细 - order items
     * @param array sdf
     * @return boolean sucess of failure
     */
    public function update_items(&$sdf, &$thisObj)
    {

        if (!isset($sdf['order_bn']) && !$sdf['order_bn'])
        {
            return $thisObj->send_user_error(app::get('b2c')->_('订单不存在！'), array('tid'=>$sdf['order_bn']));
        }
        else
        {
            $objOrder = $this->app->model('orders');
            $obj_order_item = $this->app->model('order_items');
            $obj_order_object = $this->app->model('order_objects');
            $tmp = $objOrder->getList('*',array('order_id'=>$sdf['order_bn']));
            if (!$tmp)
            {
                return $thisObj->send_user_error(app::get('b2c')->_('订单不存在！'), array('tid'=>$sdf['order_bn']));
            }
            else
            {
                $db = kernel::database();
                $transaction_status = $db->beginTransaction();

                $sdf_order = $tmp[0];
                if ($sdf_order['status'] == 'dead' || $sdf_order['status'] == 'finish')
                {
                    return $thisObj->send_user_error(app::get('b2c')->_('订单已经作废或完成，不能再编辑！'), array('tid'=>$sdf['order_bn']));
                }
                $obj_regions = app::get('ectools')->model('regions');
                $arr_regions = $obj_regions->dump(array('local_name' => $sdf['receiver_district']));
                $sdf_order = array(
                    'last_modified'=>$sdf['modified'],
                    'is_tax'=>$sdf['has_invoice'],
                    'tax_company'=>$sdf['invoice_title'],
                    'cost_tax'=>$sdf['invoice_fee'],
                    'cost_item'=>$sdf['total_goods_fee'],
                    'total_amount'=>$sdf['total_trade_fee'],
                    'discount'=>$sdf['discount_fee'],
                    'payed'=>$sdf['payed_fee'],
                    'currency'=>$sdf['currency'],
                    'cur_rate'=>$sdf['currency_rate'],
                    'final_amount'=>$sdf['total_currency_fee'],
                    'score_g'=>$sdf['buyer_obtain_point_fee'],
                    'score_u'=>$sdf['point_fee'],
                    'weight'=>$sdf['total_weight'],
                    'ship_time'=>$sdf['receiver_time'],
                    'shipping'=>$sdf['shipping_type'],
                    'cost_freight'=>$sdf['shipping_fee'],
                    'is_protect'=>$sdf['is_protect'],
                    'cost_protect'=>$sdf['protect_fee'],
                    'ship_name'=>$sdf['receiver_name'],
                    'ship_email'=>$sdf['receiver_email'],
                    'ship_mobile'=>$sdf['receiver_mobile'],
                    //'ship_area'=>$arr_regions['package'] . ":" . $arr_data_receive['states'] . "/" . $arr_data_receive['city'] . "/" . $arr_data_receive['distinct'] . ":" . $arr_regions['region_id'],
                    'ship_addr'=>$sdf['receiver_address'],
                    'ship_zip'=>$sdf['receiver_zip'],
                    'ship_tel'=>$sdf['receiver_phone'],
                    'cost_payment'=>$sdf['commission_fee'],
                    'memo'=>$sdf['trade_memo'],
                );

                if ($this->arr_status[$sdf['status']])
                    $sdf_order['status'] = $this->arr_status[$sdf['status']];
                if ($this->arr_pay_status[$sdf['pay_status']])
                    $sdf_order['pay_status'] = $this->arr_pay_status[$sdf['pay_status']];
                if ($this->arr_ship_status[$sdf['ship_status']])
                    $sdf_order['ship_status'] = $this->arr_ship_status[$sdf['ship_status']];

                // 判断下目前订单所处的状态
                if ($sdf_order['payed'] >= $sdf_order['final_amount'])
                {
                    // 全额支付
                    $sdf_order['pay_status'] = '1';
                }
                else
                {
                    $obj_order_bills = app::get('ectools')->model('order_bills');
                    $tmp = $obj_order_bills->count(array('rel_id'=>$sdf['order_bn'], 'bill_type'=>'refunds'));
                    if ($tmp > 0)
                    {
                        if ($sdf_order['payed'] == 0)
                            $sdf_order['pay_status'] = '5';
                        else
                            $sdf_order['pay_status'] = '4';
                    }
                    else
                    {
                        if ($sdf_order['payed'] == 0)
                            $sdf_order['pay_status'] = 0;
                        else
                            $sdf_order['pay_status'] = '3';
                    }
                }

                if ($objOrder->update($sdf_order, array('order_id'=>$sdf['order_bn'])))
                {
                    $is_save = true;
                    $arr_order_object = json_decode($sdf['orders'], 1);

                    if ($arr_order_object['order'])
                    {
                        $obj_spec_values = $this->app->model('spec_values');
                        $obj_products = $this->app->model('products');
                        $obj_goods = $this->app->model('goods');
                        $obj_specification = $this->app->model('specification');
                        $arr_exception_style = array('pkg');
                        //print_r($arr_order_object['order'] );
                        foreach ($arr_order_object['order'] as $arr_obj)
                        {

                            if ($arr_obj['order_items'])
                            {
                                $obj_bn = '';
                                $obj_id = 0;
                                $sdf_arr_item_bns = '';
                                $arr_item_bns = '';
                                $goods_id = 0;
                                $goods_price = 0;
                                $sdf_arr_items = array();
                                $sdf_item_total_score = 0;
                                $obj_bn = $arr_obj['orders_bn'];

                                /** 找到相应的obj_id **/
                                if (!$arr_obj['iid'])
                                {

                                    foreach ($arr_obj['order_items']['item'] as $arr_item)
                                    {

                                        // 目前不认识的类型
                                        if (in_array($arr_item['item_type'], $arr_exception_style))
                                        {
                                            $db->rollback();
                                            $thisObj->send_user_error(app::get('b2c')->_('编辑的订单中含有ecstore不认识的商品类型！'), array('tid'=>$sdf['order_bn']));
                                        }

                                        $sdf_arr_item_bns[] = $arr_item['bn'];
                                        $sdf_arr_items[] = $arr_item;
                                    }

                                    asort($sdf_arr_item_bns);
                                    $row = $obj_order_object->getList('*', array('order_id'=>$sdf['order_bn'],'bn'=>$obj_bn));
                                    if (count($row) > 1)
                                    {
                                        foreach ($row as $arr_objs)
                                        {
                                            $row_item = $obj_order_item->getList('*', array('order_id'=>$sdf['order_bn'],'obj_id'=>$arr_objs['obj_id']));
                                            if ($row_item)
                                            {
                                                foreach ($row_item as $arr_item)
                                                {
                                                    $arr_item_bns[$arr_objs['obj_id']][] = $arr_item['bn'];
                                                }
                                                asort($arr_item_bns[$arr_objs['obj_id']]);
                                            }
                                        }
                                        if ($arr_item_bns)
                                        {
                                            foreach ($arr_item_bns as $key=>$arr_item_bn)
                                            {
                                                if ($sdf_arr_item_bns == $arr_item_bn)
                                                {
                                                    $arr_obj['iid'] = $key;
                                                }
                                            }
                                        }
                                    }
                                    elseif (count($row) > 0)
                                    {
                                        //$arr_obj['iid'] = $row[0]['obj_id'];
                                        $row_item = $obj_order_item->getList('*', array('order_id'=>$sdf['order_bn'],'obj_id'=>$row[0]['obj_id']));
                                        if ($row_item)
                                        {
                                            foreach ($row_item as $arr_item)
                                            {
                                                $arr_item_bns[$row[0]['obj_id']][] = $arr_item['bn'];
                                            }
                                            asort($arr_item_bns);
                                        }
                                        if ($arr_item_bns)
                                        {
                                            foreach ($arr_item_bns as $key=>$arr_item_bn)
                                            {
                                                if ($sdf_arr_item_bns == $arr_item_bn)
                                                {
                                                    $arr_obj['iid'] = $key;
                                                }
                                            }
                                        }
                                    }
                                }
                                /** end **/

                                if (!$arr_obj['iid'])
                                {
                                    $is_all_normal = false;
                                    $is_has_product = false;
                                    $is_product_true = false;
                                    foreach ($arr_obj['order_items']['item'] as $arr_item)
                                    {
                                        if ($arr_item['item_type'] == 'product')
                                        {
                                            $is_has_product = true;
                                            if ($arr_item['item_status'] == 'normal')
                                                $is_product_true = true;
                                        }
                                        else
                                        {
                                            if ($arr_item['item_status'] == 'normal')
                                                $is_all_normal = true;
                                        }
                                    }
                                    if ($is_has_product && !$is_product_true)
                                    {
                                        $is_all_normal = false;
                                    }
                                    else
                                    {
                                        $is_all_normal = true;
                                    }

                                    $order_items = array();
                                    $obj_price= 0;
                                    foreach ($arr_obj['order_items']['item'] as $arr_item)
                                    {
                                        // 目前不认识的类型
                                        if (in_array($arr_item['item_type'], $arr_exception_style))
                                        {
                                            $db->rollback();
                                            $thisObj->send_user_error(app::get('b2c')->_('编辑的订单中含有ecstore不认识的商品类型！'), array('tid'=>$sdf['order_bn']));
                                        }

                                        // 此区块为新增的区块
                                        switch ($arr_obj['type'])
                                        {
                                        case 'goods':
                                            $obj_alias = app::get('b2c')->_('商品区块');
                                            break;
                                        case 'gift':
                                            $obj_alias = app::get('b2c')->_('赠品区块');
                                            break;
                                        default:
                                            $obj_alias = app::get('b2c')->_('捆绑销售');
                                            break;
                                        }

                                        $tmp = $obj_products->getList('goods_id,price', array('bn'=>$obj_bn));
                                        if ($tmp)
                                        {
                                            $goods_id = $tmp[0]['goods_id'];
                                            $goods_price = $tmp[0]['price'];
                                        }
                                        else
                                        {
                                            $goods_id = 0;
                                            $goods_price = 0;
                                        }
                                        $strAddon = '';
                                        $arrAddon = array();
                                        $tmp = $obj_products->getList('goods_id,product_id,spec_desc', array('bn'=>$arr_item['bn']));

                                        $tmp_goods = $obj_goods->getList('type_id', array('goods_id'=>$tmp[0]['goods_id']));
                                        if ($tmp[0]['spec_desc'])
                                        {
                                            $tmp[0]['spec_desc'] = unserialize($tmp[0]['spec_desc']);
                                            if (isset($tmp[0]['spec_desc']) && $tmp[0]['spec_desc'] && is_array($tmp[0]['spec_desc']))
                                            {
                                                foreach ($tmp[0]['spec_desc'] as $spec_key=>$str_spec_value_id)
                                                {
                                                    $arr_spec_value = $obj_spec_values->dump($str_spec_value_id);
                                                    $arr_specification = $obj_specification->dump($arr_spec_value['spec_id']);
                                                    $arrAddon['product_attr'][$spec_key] = array(
                                                        'label' => $arr_specification['spec_name'],
                                                        'value' => $arr_spec_value['spec_value'],
                                                    );
                                                }

                                                $strAddon = serialize($arrAddon);
                                            }
                                        }
                                        if ($arr_item['item_status'] == 'normal')
                                            $price = $arr_item['sale_price']/$arr_item['num'];
                                        if($arr_item['item_type']!='adjunct')
                                        {
                                            $obj_price+= $price;
                                        }
                                        $order_items[] = array(
                                            'products'=>array('product_id'=>$tmp[0]['product_id']),
                                            'goods_id'=>$tmp[0]['product_id']['goods_id'],
                                            'order_id' => $sdf['order_bn'],
                                            'item_type'=>$arr_item['item_type'],
                                            'bn'=>$arr_item['bn'],
                                            'name'=>$arr_item['name'],
                                            'type_id'=>$tmp_goods[0]['type_id'],
                                            'g_price'=>$arr_item['price'],
                                            'quantity'=>$arr_item['num'],
                                            'sendnum'=>$arr_item['sendnum'],
                                            'amount'=>$arr_item['sale_price'],
                                            'score' => $arr_item['score'],
                                            'price'=>$price,
                                            'weight'=>$arr_item['weight'],
                                            'addon'=>$strAddon,
                                        );

                                    }
                                    $sdf_order_object = array(
                                        'order_id' => $sdf['order_bn'],
                                        'obj_type' => $arr_obj['type'],
                                        'obj_alias' => $obj_alias,
                                        'goods_id' => $goods_id,
                                        'bn' => $obj_bn,
                                        'name' => $arr_obj['title'],
                                        'price' => $obj_price,
                                        'quantity'=> $arr_obj['items_num'],
                                        'amount'=> $arr_obj['total_order_fee'],
                                        'weight'=> $arr_obj['weight'],
                                        'score'=> $sdf_item_total_score,
                                        'order_items' => $order_items,
                                    );

                                    if ($is_all_normal)
                                        if (!$obj_order_object->save($sdf_order_object))
                                            $is_save = false;
                                }
                                else
                                {
                                    // 区块不是新增

                                    $is_all_item_cancel = true;
                                    $obj_price = 0;
                                    foreach ($arr_obj['order_items']['item'] as $arr_item)
                                    {
                                        if ($arr_item['item_status'] == 'normal')
                                        {
                                            $price = $arr_item['sale_price']/$arr_item['num'];
                                            if($arr_item['item_type']!='adjunct')
                                            {
                                                $obj_price+= $price;
                                            }
                                            $is_all_item_cancel = false;
                                            $sdf_order_item = array(
                                                'g_price'=>$arr_item['price'],
                                                'nums'=>$arr_item['num'],
                                                'sendnum'=>$arr_item['sendnum'],
                                                'amount'=>$arr_item['sale_price'],
                                                'score' => $arr_item['score'],
                                                'price'=>$price,
                                                'weight'=>$arr_item['weight'],
                                            );
                                            $item_exist = $obj_order_item->dump(array('order_id'=>$sdf['order_bn'],'obj_id'=>$arr_obj['iid'],'bn'=>$arr_item['bn']),'*');
                                            if($item_exist){
                                                $is_save = $obj_order_item->update($sdf_order_item, array('order_id'=>$sdf['order_bn'],'obj_id'=>$arr_obj['iid'],'bn'=>$arr_item['bn']));
                                            }else{
                                                $sdf_order_item['order_id'] = $sdf['order_bn'];
                                                $sdf_order_item['obj_id'] = $arr_obj['iid'];
                                                $sdf_order_item['bn'] = $arr_item['bn'];
                                                $sdf_order_item['name'] = $arr_item['name'];
                                                $is_save = $obj_order_item->insert($sdf_order_item);
                                            }

                                        }
                                        else
                                        {
                                            $is_save = $obj_order_item->delete(array('order_id'=>$sdf['order_bn'],'obj_id'=>$arr_obj['iid'],'bn'=>$arr_item['bn']));
                                        }
                                    }
                                    if ($is_all_item_cancel)
                                    {
                                        $is_save = $obj_order_object->delete(array('order_id'=>$sdf['order_bn'],'obj_id'=>$arr_obj['iid']));
                                    }
                                }
                                /** end **/
                            }
                        }

                        if ($is_save)
                        {
                            $db->commit($transaction_status);
                        }
                        else
                        {
                            $db->rollback();
                            $thisObj->send_user_error(app::get('b2c')->_('订单修改失败！'), array('tid'=>$sdf['order_bn']));
                        }

                        $db = kernel::database();
                        $transaction_status = $db->beginTransaction();

                        $arr_order_items = $obj_order_item->getList('nums,sendnum', array('order_id'=>$sdf['order_bn']));
                        $order_ship_status = '1';
                        $is_finish_ship = false;
                        $is_part_ship = false;
                        if ($arr_order_items)
                        {
                            foreach ($arr_order_items as $arr_item)
                            {
                                if ($arr_item['nums'] > $arr_item['sendnum'])
                                {
                                    if ($arr_item['sendnum'] > 0)
                                    {
                                        $is_part_ship = true;
                                        break;
                                    }
                                    else
                                    {
                                        $is_part_ship = false;
                                    }
                                    $is_finish_ship = false;
                                }
                                else
                                {
                                    $is_finish_ship = true;
                                }
                            }
                        }

                        if ($is_finish_ship)
                            $order_ship_status = '1';
                        else
                        {
                            if ($is_part_ship)
                                $order_ship_status = '2';
                            else
                                $order_ship_status = '0';
                        }
                        $is_save = $objOrder->update(array('ship_status'=>$order_ship_status),array('order_id'=>$sdf['order_bn']));

                        if ($is_save)
                        {
                            $db->commit($transaction_status);
                        }
                        else
                        {
                            $db->rollback();
                            $thisObj->send_user_error(app::get('b2c')->_('订单发货状态修改失败！'), array('tid'=>$sdf['order_bn']));
                        }

                        return array('tid'=>$sdf['order_bn']);

                    }
                    else
                    {
                        $db->rollback();
                        $thisObj->send_user_error(app::get('b2c')->_('修改订单的明细数据有误！'), array('tid'=>$sdf['order_bn']));
                    }
                }
                else
                {
                    $db->rollback();
                    $thisObj->send_user_error(app::get('b2c')->_('订单信息更新失败！'), array('tid'=>$sdf['order_bn']));
                }
            }
        }
    }



    public function iframe_url( $params, &$service )
    {
        if( !( $order_id = $params['tid'] ) ){
            return $service->send_user_error('7001', 'tid不能为空！');
        }
        if( !( $notify_url = $params['notify_url'] ) ){
            return $service->send_user_error('7002', 'notify_url不能为空!');
        }

        base_kvstore::instance('b2c.iframe')->fetch('iframe.whitelist', $whitelist);
        if( !$whitelist )
            $whitelist = array();

        $random = md5(time() . mt_rand()) . '.' . time();
        array_push($whitelist, $random);

        $url_params = array(
            'tid' => $order_id,
            'secret_key' => $random,
            'notify_url' => $notify_url,
        );

        base_kvstore::instance('b2c.iframe')->store('iframe.whitelist', $whitelist);


        $url = kernel::openapi_url('openapi.b2c.iframe.order.edit', 'edit', $url_params);

        return $url;
    }

    /**
     * 根据订单id获取详情
     * @param $order_id
     * return $order_detial
     */
    private function check_accesstoken($accesstoken,$member_id){
        $_GET['sess_id'] = $accesstoken;
        kernel::single("base_session")->start();
        $userObject = kernel::single('b2c_user_object');
        $id = $userObject->get_member_id();
        if( empty($id) || $member_id != $id ){
            return false;
        }
        return true;
    }

    public function get_wap_order_detail($params,&$service){
        if(!$this->check_accesstoken($params['accesstoken'],$params['member_id']) ){
            return $service->send_user_error('100001','accesstoken fail');
        }
        if (!isset($params['order_id']) || !$params['order_id'])
        {
            $msg = app::get('b2c')->_('订单id不能为空，必要参数！');
            return false;
        }
        //获取订单model
        $objOrder = $order = $this->app->model('orders');
        //组织查询条件
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('product_id,name,price,score,nums,item_type',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        //$subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        //获取订单的详细信息数据
        $sdf_order = $objOrder->dump($params['order_id'], '*', $subsdf);
      
        if($sdf_order['member_id']!=$params['member_id']){
            return array('status'=>'false','message'=>app::get('b2c')->_('该会员不存在'));
        }
       
        // 处理收货人地区
        
        $arr_consignee_area = array();
        $arr_consignee_regions = array();
        if (strpos($sdf_order['consignee']['area'], ':') !== false)
        {
            $arr_consignee_area = explode(':', $sdf_order['consignee']['area']);
            if ($arr_consignee_area[1])
            {
                if (strpos($arr_consignee_area[1], '/') !== false)
                {
                    $arr_consignee_regions = explode('/', $arr_consignee_area[1]);
                }
            }

            $sdf_order['consignee']['area'] = (is_array($arr_consignee_regions) && $arr_consignee_regions) ? $arr_consignee_regions[0] . $arr_consignee_regions[1] . $arr_consignee_regions[2] : $sdf_order['consignee']['area'];
        }
        
        $data['order_id']=$sdf_order['order_id'];
        $data['total_amount']=$sdf_order['total_amount'];
        $data['payed']=$sdf_order['payed'];
        $data['createtime']=$sdf_order['createtime'];
        //订单状态
        switch ($sdf_order['status']) {
            case 'active':
                $data['orderStatus']='活动订单';
                break;
            case 'dead':
                $data['orderStatus']='已作废';
                break;
            case 'finish':
                $data['orderStatus']='已完成';
                break;
           
            default:
                break;
        }
        //支付状态
        switch ($sdf_order['pay_status']) {
            case 0:
                $data['payStatus']='未支付';
                break;
            case 1:
                $data['payStatus']='已支付';
                break;
            case 2:
                $data['payStatus']='已付款至到担保方';
                break;
            case 3:
                $data['payStatus']='部分付款';
                break;
            case 4:
                $data['payStatus']='部分退款';
                break;
            case 5:
                $data['payStatus']='全额退款';
                break;
            default:
                break;
        }
        //发货状态
        switch ($sdf_order['ship_status']) {
            case 0:
                $data['shipStatus']='未发货';
                break;
            case 1:
                $data['shipStatus']='已发货';
                break;
            case 2:
                $data['shipStatus']='部分发货';
                break;
            case 3:
                $data['shipStatus']='部分退货';
                break;
            case 4:
                $data['shipStatus']='已退货';
                break;
            default:
                break;
        }
        
        $data['consignee']=$sdf_order['consignee'];
    
        $data['shipping']['shipping_name']=$sdf_order['shipping']['shipping_name'];
        $data['shipping']['cost_shipping']=$sdf_order['shipping']['cost_shipping'];
        $data['shipping']['is_protect']=$sdf_order['shipping']['is_protect'];
       
        $data['payinfo']=$sdf_order['payinfo'];
      //return $data['payinfo'];
        //发票类型
        if(isset($sdf_order['tax_type']))
        {
            switch ($sdf_order['tax_type']) {
            case 'false':
                $datas['tax_type']='不需发票';
                break;
            case 'personal':
                $datas['tax_type']='个人发票';
                break;
            case 'company':
                $datas['tax_type']='公司发票';
                break;
           
            default:
                break;
            }
        }
        //发票信息
        $data['taxinfo']=array(
            'tax_type'=>$datas['tax_type'],
            'tax_title'=>$sdf_order['tax_title'],
            'tax_content'=>$sdf_order['tax_content'],
        );
        //结算信息
        $data['total']=array(
            'cost_item'=>$sdf_order['cost_item'],
            'cost_freight'=>$sdf_order['shipping']['cost_shipping'],
            'cost_protect'=>$sdf_order['shipping']['cost_protect'],
            'discountPrice'=>$sdf_order['pmt_order'],
            'cost_payment'=>$sdf_order['payinfo']['cost_payment'],
            'cost_tax'=>$sdf_order['cost_tax'],
            'consumeScore'=>$sdf_order['score_u'],
            'totalGainScore'=>$sdf_order['score_g'],
            'total_amount'=>$sdf_order['total_amount'],
        );
        //return  $data['total'];
        $data['member_id']=$sdf_order['member_id'];
     
        //$data['order_objects']=$sdf_order['order_objects'];
       
        
       //获取商品信息
        $order_items=$this->app->model('order_items')->getList('goods_id,product_id,item_id,name,nums,price,item_type,score',array('order_id'=>$params['order_id']));
        foreach ($order_items as $key => $value) {
            $fmt_items[$value['product_id']]['product_id']=$value['product_id'];
            $fmt_items[$value['product_id']]['goods_id']=$value['goods_id'];
            $fmt_items[$value['product_id']]['name']=$value['name'];
            $fmt_items[$value['product_id']]['score']=$value['score'];
            $fmt_items[$value['product_id']]['nums']=$value['nums'];
            $fmt_items[$value['product_id']]['price']=$value['price'];
            $fmt_items[$value['product_id']]['item_id']=$value['item_id'];
            $fmt_items[$value['product_id']]['item_type']=$value['item_type'];
        }
        //return $order_items;

        foreach ($order_items as $key => $value) {
            $product_id[$key]=$value['product_id'];
        }
        //获取货品详情
        $product_items=$this->app->model('products')->getList('goods_id,product_id,spec_info,price,store',array('product_id|in'=>$product_id));
        //return $product_items;
        foreach ($product_items as $key => $value) {
            $fmt_product[$value['product_id']]['product_id']=$value['product_id'];
            $fmt_product[$value['product_id']]['goods_id']=$value['goods_id'];
            $fmt_product[$value['product_id']]['spec_info']=$value['spec_info'];
            //$fmt_product[$value['product_id']]['store']=$value['store'];
            $fmt_product[$value['product_id']]['goodsprice']=$value['price'];
        }
        //return  $fmt_product;
        foreach ($fmt_items as $key => $value) {
           $order_pmf[$value['product_id']]['product_id']=$value['product_id'];
           $order_pmf[$value['product_id']]['goods_id']=$value['goods_id'];
           $order_pmf[$value['product_id']]['name']=$value['name'];
           $order_pmf[$value['product_id']]['score']=$value['score'];
           $order_pmf[$value['product_id']]['nums']=$value['nums'];
           $order_pmf[$value['product_id']]['price']=$value['price'];
           $order_pmf[$value['product_id']]['item_type']=$value['item_type'];
           //$order_pmf[$value['product_id']]['store']=$fmt_product[$value['product_id']]['store'];
           $order_pmf[$value['product_id']]['goodsprice']=$fmt_product[$value['product_id']]['goodsprice'];
           $order_pmf[$value['product_id']]['spec_info']=$fmt_product[$value['product_id']]['spec_info'];
        }
        //return $order_pmf;
        foreach ($order_pmf as $key => $value) {
            if($order_pmf[$key]['item_type']=='product'){
                //$goods[$value['product_id']]=$value['product_id'];
                $goods['goods'][$value['product_id']]['product_id']=$value['product_id'];
                $goods['goods'][$value['product_id']]['goods_id']=$value['goods_id'];
                $goods['goods'][$value['product_id']]['goods_name']=$value['name'];
                $goods['goods'][$value['product_id']]['score']=$value['score'];
                $goods['goods'][$value['product_id']]['quantity']=$value['nums'];
                $goods['goods'][$value['product_id']]['item_type']=$value['item_type'];
               // $goods['goods'][$value['product_id']]['store']=$value['store'];
                $goods['goods'][$value['product_id']]['price']=$value['price'];
                //$goods['goods'][$value['product_id']]['goodsprice']=$value['goodsprice'];
                $goods['goods'][$value['product_id']]['spec_info']=$value['spec_info'];
                $goods['goods'][$value['product_id']]['discount_price']=$sdf_order['pmt_order'];
                $goods['goods'][$value['product_id']]['totle_price']=$value['nums']*$value['price']-$sdf_order['pmt_order'];

            }
            if($order_pmf[$key]['item_type']=='gift'){
                $gift['gift'][$value['product_id']]['product_id']=$value['product_id'];
                $gift['gift'][$value['product_id']]['gift_name']=$value['name'];
                $gift['gift'][$value['product_id']]['score']=$value['score'];
                $gift['gift'][$value['product_id']]['quantity']=$value['nums'];
                $gift['gift'][$value['product_id']]['price']=$value['goodsprice'];
                $gift['gift'][$value['product_id']]['spec_info']=$value['spec_info'];
            }
            
        }
         //组织优惠数据
        //return $order_pmf;
        foreach ($sdf_order['order_pmt'] as $key => $value) {
            $date['promotion'][$value['pmt_type']]['tag']=$value['pmt_tag'];
            $date['promotion'][$value['pmt_type']]['name']=$value['pmt_memo'];
        }

        //组织每个商品的赠品信息
        foreach ($goods['goods'] as $key => $value) {
            $goods['goods'][$value['product_id']]['promotion']=$date['promotion'];
            $goods['goods'][$value['product_id']]['gift']=$gift['gift'];
        }
        //return $goods;
        $this->pagedata['order'] = $data;
        

        // 支付方式的解析变化
        $obj_payments_cfgs = app::get('ectools')->model('payment_cfgs');
        $arr_payments_cfg = $obj_payments_cfgs->getPaymentInfo($sdf_order['payinfo']['pay_app_id']);
       // return $arr_payments_cfg;
        $this->pagedata['order']['payinfo'] = array(
            'payid'=>$arr_payments_cfg['app_id'],
            'payname'=>$arr_payments_cfg['app_name'],

        );
       
        $this->pagedata['order']['goodsinfo']=$goods;
        return  $this->pagedata['order'];
    }

}
