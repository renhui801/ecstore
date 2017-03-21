<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * alipay notify 验证接口
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
class ectools_payment_plugin_doubletenpay_server extends ectools_payment_app {

    /**
     * 支付后返回后处理的事件的动作
     * @params array - 所有返回的参数，包括POST和GET
     * @return null
     */
    public function callback(&$in)
    {

        $objMath = kernel::single('ectools_math');
        $trade_mode = $in['trade_mode'];
        $trade_state = $in['trade_state'];
        $pay_info = $in['pay_info'];  //此处即时到帐返回为空时，表示成功
        $transaction_id=$in["transaction_id"];   //财付通订单号
        $paymentId=$in['attach'];
        $pay_result=$in["pay_result"];
        $notify_id = $in['notify_id'];
        $total_fee = $in["total_fee"];                                                                                                                                 
        $money=$objMath->number_multiple(array($total_fee, 0.01));

        $sign=$in["sign"];
        $mac ="";
        $v_orderid = substr($v_order_no,-6);
        $ikey = $this->getConf('PrivateKey',substr(__CLASS__, 0, strrpos(__CLASS__, '_')));

        ksort($in);
        reset($in);
        foreach($in as $key => $val){
            if ($key<>'pay_time'&&$key<>'bankname'&&$key<>'sign'&&$val<>''){
                $str.=$key."=".urldecode(trim($val))."&";
            }
        }
        $str.="key=".$ikey;
        $md5mac=strtoupper(md5($str));
        $sdf = array(
            'payment_id'=>$in['out_trade_no'],
            'bank'=>app::get('ectools')->_('腾讯财付通'),
            'pay_account'=>app::get('ectools')->_('付款帐号'),
            'currency'=>'CNY',
            'money'=>$money,
            'paycost'=>'0.000',
            'cur_money'=>$money,
            't_payed'=>$in['date'],
            'pay_app_id'=>'doubletenpay',
            'pay_type'=>'online',
        );

        if($md5mac!=$sign)
        {
            $message = app::get('ectools')->_('签名认证失败,请立即与商店管理员联系');
            logger::info($message);
            $sdf['status'] =  'invalid';
            echo "invalid";
            return $sdf;
        }

        $arr = array(0,2,4,7,8);
        if(intval($trade_mode) == 2){

            if($trade_state==5){
                $sdf['status'] = 'succ';
                echo "success";
                return $sdf;
            }elseif(in_array($trade_state,$arr)){
                $message = app::get('ectools')->_('已支付到担保方').$pay_info;
                $sdf['status'] =  'progress';
                echo "success";
                return $sdf;
            }else{
                $message = app::get('ectools')->_('支付失败,请立即与商店管理员联系').$pay_info;
                $sdf['status'] =  'failed';
                echo "fail";
                return $sdf;

            }
        }elseif(intval($trade_mode) == 1){

            if($trade_state==0){
                $sdf['status'] = 'succ';
                echo "success";

                return $sdf;
            }else{
                $message = app::get('ectools')->_('支付失败,请立即与商店管理员联系').$pay_info;
                $sdf['status'] =  'failed';

                echo "fail";
                return $sdf;
            }
        }

    }

}
