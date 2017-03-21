<?php
class b2c_tasks_archive extends base_task_abstract implements base_interface_task{

    // 每个队列执行100条订单信息
    var $limit = 100;

    function __construct(){

    }

    public function exec($params=null){

        $filter = array(
            'status'=>array('finish','dead'),
            'createtime|lthan'=>strtotime(date('Y-m-d 23:59:59',strtotime('-3 month'))),
        );
        $offset = 0;
        while( $listFlag = $this->get_order_ids($limit_order_ids, $filter, $offset) ){
            $offset++;
             // 把分页得到的订单id加入相关队列
            $this->archive($limit_order_ids);
        }
        logger::info("归档创建时间小于 ".date('Y-m-d H:i:s',$filter['createtime|lthan'])." 的订单");

    }

    /**
     * 分页获取订单id
     * @param  array $limit_order_ids 引用获取一页订单号
     * @param  array $filter          订单过滤条件
     * @param  int $offset          页数
     * @return bool                  [description]
     */
    function get_order_ids(&$limit_order_ids , $filter, $offset){

        if( !$new_order_ids = app::get('b2c')->model('orders')->getList('order_id',$filter,$offset*$this->limit,$this->limit) ){
            return false;
        }

        $limit_order_ids = array();
        foreach($new_order_ids as $v){
            $limit_order_ids[] = $v['order_id'];
        }
        return true;

    }


    function archive($order_ids){

        // 分割发货单和退货单id
        $orderBills_ids = app::get('ectools')->model('order_bills')->getList('bill_id, bill_type',array('rel_id'=>$order_ids));
        $payments_ids = array();
        $refunds_ids = array();
        foreach($orderBills_ids as $v){
            if($v['bill_type'] == 'payments'){
                $payments_ids[] = $v['bill_id'];
            }elseif($v['bill_type'] == 'refunds'){
                $refunds_ids[] = $v['bill_id'];
            }
        }

        $order_delivery_ids = app::get('b2c')->model('order_delivery')->getList('dly_id, dlytype',array('order_id'=>$order_ids));
        $delivery_ids = array();
        $reship_ids = array();
        foreach($order_delivery_ids as $v){
            if($v['dlytype'] == 'delivery'){
                $delivery_ids[] = $v['dly_id'];
            }elseif($v['dlytype'] == 'reship'){
                $reship_ids[] = $v['dly_id'];
            }
        }


        $db = kernel::database();
        $transaction_status = $db->beginTransaction();
        $insert_error_code = 0;
        $delete_error_code = 0;
        // 归档数据到新表
        try {
            $this->op_archive($order_ids, $delivery_ids, $reship_ids, $payments_ids, $refunds_ids, false);
        } catch ( Exception $e ) {
            $insert_error_code = $e->getCode();
        }

        if($insert_error_code == 30002){
            $db->rollback();
        } else {
            // 删除老数据
            try {
                $this->op_archive($order_ids, $delivery_ids, $reship_ids, $payments_ids, $refunds_ids, true);
            } catch ( Exception $e ) {
                $delete_error_code = $e->getCode();
            }

            if($delete_error_code == 30003){
                $db->rollback();
            } else {
                $db->commit($transaction_status);
            }
        }

    }


    function op_archive($order_ids, $delivery_ids, $reship_ids, $payments_ids, $refunds_ids, $delete=false){
        // 1.订单明细队列
        $this->order_items($order_ids, $delete);
        // 2.订单日志队列
        $this->order_log($order_ids, $delete);
        // 3.订单商品对象表队列
        $this->order_objects($order_ids, $delete);
        // 4.订单应用的促销规则表队列
        $this->order_pmt($order_ids, $delete);
        // 5.订单队列
        $this->orders($order_ids, $delete);
        // 6.发货单队列
        $this->delivery($order_ids, $delete);
        // 7.退货单队列
        $this->reship($order_ids, $delete);
        // 8.发货单明细队列
        $this->delivery_items($delivery_ids, $delete);
        // 9.退货单明细队列
        $this->reship_items($reship_ids, $delete);
        // 10.订单发/退货单据主表队列
        $this->order_delivery($order_ids, $delete);
        // 11.收款单队列
        $this->payments($payments_ids, $delete);
        // 12.退款单队列
        $this->refunds($refunds_ids, $delete);
        // 13.收/退款单据主表队列
        $this->order_bills($order_ids, $delete);
    }


// start-------以下为具体业务归档操作方法-------------------------------------------start
    public function delivery($order_ids=null, $delete=false){

        $objDelivery = app::get('b2c')->model('delivery');
        $objDeliveryArchive = app::get('b2c')->model('archive_delivery');

        if($delete){
            if(!$objDelivery->delete( array('order_id'=>$order_ids) ) ){
                    throw new Exception("delete archive delivery failue", 30003);
            }
        }else{
            $delivery = $objDelivery->getList( '*', array('order_id'=>$order_ids) );
            foreach($delivery as $v){
                if(!$objDeliveryArchive->insert($v)){
                    throw new Exception("insert archive delivery failue", 30002);
                }
            }
        }

    }

    public function order_delivery($order_ids=null, $delete=false){

        $objOrderDelivery = app::get('b2c')->model('order_delivery');
        $objOrderDeliveryArchive = app::get('b2c')->model('archive_order_delivery');

        if($delete){
            if(!$objOrderDelivery->delete( array('order_id'=>$order_ids) ) ){
                    throw new Exception("delete archive order_delivery failue", 30003);
            }
        }else{
            $orderDelivery = $objOrderDelivery->getList( '*', array('order_id'=>$order_ids) );
            foreach($orderDelivery as $v){
                if(!$objOrderDeliveryArchive->insert($v)){
                    throw new Exception("insert archive order_delivery failue", 30002);
                }
            }
        }

    }

    public function order_items($order_ids=null, $delete=false){

        $objOrderItems = app::get('b2c')->model('order_items');
        $objOrderItemsArchive = app::get('b2c')->model('archive_order_items');

        if($delete){
            if(!$objOrderItems->delete( array('order_id'=>$order_ids) ) ){
                    throw new Exception("delete archive order_items failue", 30003);
            }
        }else{
            $orderItems = $objOrderItems->getList( '*', array('order_id'=>$order_ids) );
            foreach($orderItems as $v){
                if(!$objOrderItemsArchive->insert($v)){
                    throw new Exception("insert archive order_items failue", 30002);
                }
            }
        }

    }

    public function order_log($order_ids=null, $delete=false){

        $objOrderLog = app::get('b2c')->model('order_log');
        $objOrderLogArchive = app::get('b2c')->model('archive_order_log');
        if($delete){
            if(!$objOrderLog->delete( array('rel_id'=>$order_ids) ) ){
                    throw new Exception("delete archive order_log failue", 30003);
            }
        }else{
            $orderLog = $objOrderLog->getList( '*', array('rel_id'=>$order_ids) );
            foreach($orderLog as $v){
                if(!$objOrderLogArchive->insert($v)){
                    throw new Exception("insert archive order_log failue", 30002);
                }
            }
        }

    }

    public function order_objects($order_ids=null, $delete=false){

        $objOrderObjects = app::get('b2c')->model('order_objects');
        $objOrderObjectsArchive = app::get('b2c')->model('archive_order_objects');
        if($delete){
            if(!$objOrderObjects->delete( array('order_id'=>$order_ids) ) ){
                    throw new Exception("delete archive order_objects failue", 30003);
            }
        }else{
            $orderObjects = $objOrderObjects->getList( '*', array('order_id'=>$order_ids) );
            foreach($orderObjects as $v){
                if(!$objOrderObjectsArchive->insert($v)){
                    throw new Exception("insert archive order_objects failue", 30002);
                }
            }
        }

    }

    public function order_pmt($order_ids=null, $delete=false){

        $objOrderPmt = app::get('b2c')->model('order_pmt');
        $objOrderPmtArchive = app::get('b2c')->model('archive_order_pmt');
        if($delete){
            if(!$objOrderPmt->delete( array('order_id'=>$order_ids) ) ){
                    throw new Exception("delete archive order_pmt failue", 30003);
            }
        }else{
            $orderPmt = $objOrderPmt->getList( '*', array('order_id'=>$order_ids) );
            foreach($orderPmt as $v){
                if(!$objOrderPmtArchive->insert($v)){
                    throw new Exception("insert archive order_pmt failue", 30002);
                }
            }
        }

    }

    public function orders($order_ids=null, $delete=false){

        $objOrders = app::get('b2c')->model('orders');
        $objOrdersArchive = app::get('b2c')->model('archive_orders');
        $objOrdersMembersArchive = app::get('b2c')->model('archive_orders_members');

        if($delete){
            if(!$objOrders->delete( array('order_id'=>$order_ids) ) ){
                    throw new Exception("delete archive orders failue", 30003);
            }
        }else{
            $orders = $objOrders->getList( '*', array('order_id'=>$order_ids) );
            $mem_rel_ord = array();
            foreach($orders as $v){
                if(!$objOrdersArchive->insert($v)){
                    throw new Exception("insert archive orders  failue", 30002);
                }

                $mem_rel_ord['order_id'] = $v['order_id'];
                $mem_rel_ord['createtime'] = $v['createtime'];
                $mem_rel_ord['member_id'] = $v['member_id'];
                if(!$objOrdersMembersArchive->insert($mem_rel_ord)){
                    throw new Exception("insert archive orders_members failue", 30002);
                }
            }
        }

    }

    public function reship($order_ids=null, $delete=false){

        $objReship = app::get('b2c')->model('reship');
        $objReshipArchive = app::get('b2c')->model('archive_reship');

        if($delete){
            if(!$objReship->delete( array('order_id'=>$order_ids) ) ){
                throw new Exception("delete archive reship failue", 30003);
            }
        }else{
            $reship = $objReship->getList( '*', array('order_id'=>$order_ids) );
            foreach($reship as $v){
                if(!$objReshipArchive->insert($v)){
                    throw new Exception("insert archive reship failue", 30002);
                }
            }
        }

    }

    public function delivery_items($delivery_ids=null, $delete=false){

        $objDeliveryItems = app::get('b2c')->model('delivery_items');
        $objDeliveryItemsArchive = app::get('b2c')->model('archive_delivery_items');

        if($delete){
            if(!$objDeliveryItems->delete( array('delivery_id'=>$delivery_ids) ) ){
                throw new Exception("delete archive delivery_items failue", 30003);
            }
        }else{
            $deliveryItems = $objDeliveryItems->getList( '*', array('delivery_id'=>$delivery_ids) );
            foreach($deliveryItems as $v){
                if(!$objDeliveryItemsArchive->insert($v)){
                    throw new Exception("insert archive delivery_items failue", 30002);
                }
            }
        }

    }

    public function reship_items($reship_ids=null, $delete=false){

        $objReshipItems = app::get('b2c')->model('reship_items');
        $objReshipItemsArchive = app::get('b2c')->model('archive_reship_items');
        if($delete){
            if(!$objReshipItems->delete( array('reship_id'=>$reship_ids) ) ){
                throw new Exception("delete archive reship_items failue", 30003);
            }
        }else{
            $reshipItems = $objReshipItems->getList( '*', array('reship_id'=>$reship_ids) );
            foreach($reshipItems as $v){
                if(!$objReshipItemsArchive->insert($v)){
                    throw new Exception("insert archive reship_items failue", 30002);
                }
            }
        }

    }

    public function order_bills($order_ids=null, $delete=false){

        $objOrderBills = app::get('ectools')->model('order_bills');
        $objOrderBillsArchive = app::get('ectools')->model('archive_order_bills');
        if($delete){
            if(!$objOrderBills->delete( array('rel_id'=>$order_ids) ) ){
                    throw new Exception("delete archive order_bills failue", 30003);
            }
        }else{
            $orderBills = $objOrderBills->getList( '*', array('rel_id'=>$order_ids) );
            foreach($orderBills as $v){
                if(!$objOrderBillsArchive->insert($v)){
                    throw new Exception("insert archive order_bills failue", 30002);
                }
            }
        }

    }

    public function payments($payments_ids=null, $delete=false){

        $objPayments = app::get('ectools')->model('payments');
        $objPaymentsArchive = app::get('ectools')->model('archive_payments');

        if($delete){
            if(!$objPayments->delete( array('payment_id'=>$payments_ids) ) ){
                throw new Exception("delete archive payments failue", 30003);
            }
        }else{
            $payments = $objPayments->getList( '*', array('payment_id'=>$payments_ids) );
            foreach($payments as $v){
                if(!$objPaymentsArchive->insert($v)){
                    throw new Exception("insert archive payments failue", 30002);
                }
            }
        }

    }

    public function refunds($refunds_ids=null, $delete=false){

        $objRefunds = app::get('ectools')->model('refunds');
        $objRefundsArchive = app::get('ectools')->model('archive_refunds');

        if($delete){
            if(!$objRefunds->delete( array('refund_id'=>$refunds_ids) ) ){
                throw new Exception("delete archive refunds failue", 30003);
            }
        }else{
            $refunds = $objRefunds->getList( '*', array('refund_id'=>$refunds_ids) );
            foreach($refunds as $v){
                if(!$objRefundsArchive->insert($v)){
                    throw new Exception("insert archive refunds failue", 30002);
                }
            }
        }

    }

// end-------以上为具体业务归档操作方法-------------------------------------------end

}