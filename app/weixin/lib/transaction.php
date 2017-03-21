<?php
/**
 * 微信交易相关接口及逻辑类
 */
class weixin_transaction{

    /**
     * 发货之后通知到微信
     */
    public function generate($data){
        $order_id = $data['order_id'];
        $ordersData = app::get('b2c')->model('orders')->getRow('ship_status',array('order_id'=>$order_id));
        if( $ordersData['ship_status'] != '1' ){
            $msg = app::get('weixin')->_('未发货不需要同步到微信');
            logger::info($msg);
            return true;
        }
        $payments = app::get('ectools')->model('payments')->get_payments_by_order_id($order_id);
        if( empty($payments)  ){
            $msg = app::get('weixin')->_('未找到支付信息');
            logger::info($msg);
            return true;
        }

        if( $payments[0]['pay_app_id'] != 'wxpay' ){
            //$msg = app::get('weixin')->_('不是微信支付不需要通知到微信');
            return true;
        }

        $postData['openid'] = $payments[0]['thirdparty_account'];
        $postData['transid'] = $payments[0]['trade_no'];
        $postData['out_trade_no'] = $payments[0]['payment_id'];
        $postData['deliver_timestamp'] = strval(time());
        $postData['deliver_status'] = '1';
        $postData['deliver_msg'] = 'ok';
        kernel::single('weixin_wechat')->delivernotify($postData);
        return true;
    }
}
