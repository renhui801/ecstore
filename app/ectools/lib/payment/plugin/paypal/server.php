<?php                                                                                                                                                                                                               
class ectools_payment_plugin_paypal_server extends ectools_payment_app {
    
    
    public function callback(&$recv)
    {
        $mer_id = $this->getConf('mer_id', substr(__CLASS__, 0, strrpos(__CLASS__, '_')));
        $ret['payment_id'] = $recv['item_number'];
        $ret['account'] = $mer_id;
        $ret['bank'] = 'PayPal';
        $ret['pay_account'] = $recv['payer_id'];
        $ret['currency'] = $recv['mc_currency'];
        $ret['money'] = $recv['mc_gross'];
        $ret['paycost'] = '0.000';
        $ret['cur_money'] = $recv['mc_gross'];
        $ret['trade_no'] = $recv['txn_id'];
        $ret['t_payed'] = strtotime($recv['payment_date']);
        $ret['pay_app_id'] = "paypal";
        $ret['pay_type'] = 'online';
        $ret['memo'] = '';
        $money = $recv['mc_gross'];

        $url='https://www.paypal.com/cgi-bin/webscr';
        
        $recv['cmd']='_notify-validate';

        $item_name = $recv['item_name'];
        $item_number = $recv['item_number'];
        $payment_status = $recv['payment_status'];
        $payment_amount = $recv['mc_gross'];
        $payment_currency = $recv['mc_currency'];
        $txn_id = $recv['txn_id'];
        $receiver_email = $recv['receiver_email'];
        $payer_email = $recv['payer_email'];

        $core_http = kernel::single('base_httpclient');
        $response = $core_http->set_timeout(10)->post($url,$recv);

        if (strcmp ($response, "VERIFIED") == 0)
        {
            if ($recv['payment_status'] == "Completed" || $recv['payment_status'] == 'Processed')
                $succ="Y";
            else
                $succ="N";
            switch ($succ){
                case "Y":
                    $ret['status'] = 'succ';
                    break;
                case "N":
                    $ret['status'] = 'failed';
                    break;
            }
        }
        else if (strcmp ($res, "INVALID") == 0) 
        {
            $ret['status'] = 'failed';
        }else{
            $ret['status'] = 'failed';
        }

        return $ret;
    } 
}
