<?php
class ectools_payment_plugin_unionpay_server extends ectools_payment_app {
	//异步返回方法 和同步的方法一样
    public function callback(&$recv){
        $objMath = kernel::single('ectools_math');
        $money=$objMath->number_multiple(array($recv['orderAmount'], 0.01));
        $merid = $this->getConf('mer_id', substr(__CLASS__, 0, strrpos(__CLASS__, '_')));
        $mer_key=$this->getConf('mer_key', substr(__CLASS__, 0, strrpos(__CLASS__, '_')));
        $sign=$recv['signature'];
        $sign_method=$recv['signMethod'];
        $arrs=array(
            "version"=>$recv['version'],
            "charset"=>$recv['charset'],
            "transType"=>$recv['transType'],
            "respCode"=>$recv['respCode'],
            "respMsg"=>$recv['respMsg'],
            "merAbbr"=>$recv['merAbbr'],
            "merId"=>$recv['merId'],
            "orderNumber"=>$recv['orderNumber'],
            "traceNumber"=>$recv['traceNumber'],
            "traceTime"=>$recv['traceTime'],
            "qid"=>$recv['qid'],
            "orderAmount"=>$recv['orderAmount'],
            "orderCurrency"=>$recv['orderCurrency'],
            "respTime"=>$recv['respTime'],
            "settleCurrency"=>$recv['settleCurrency'],
            "settleDate"=>$recv['settleDate'],
            "settleAmount"=>$recv['settleAmount'],
            "exchangeDate"=>$recv['exchangeDate'],
            "exchangeRate"=>$recv['exchangeRate'],
            "cupReserved"=>$recv['cupReserved'],
        );
        $chkvalue = $this->sign($arrs, $sign_method,$mer_key);
        $ret['payment_id'] =$arrs['orderNumber'];
        $ret['account'] = $arrs['merId'];
        $ret['bank'] = app::get('unionpay')->_('银联');
        $ret['pay_account'] = app::get('unionpay')->_('付款帐号');
        $ret['currency'] = 'CNY';
        $ret['money'] = $money;
        $ret['paycost'] = '0.000';
        $ret['cur_money'] = $money;
        $ret['tradeno'] = $recv['traceNumber'];
        // $ret['t_payed'] = strtotime($recv['settleDate']) ? strtotime($recv['settleDate']) : time();
        $ret['t_payed'] = time();
        $ret['pay_app_id'] = 'unionpay';
        $ret['pay_type'] = 'online';
        $ret['memo'] = 'unionpay';
        if ($sign==$chkvalue && $recv['respCode']==00) {
             $ret['status'] = 'succ';
        }else{
            $ret['status']='failed';
        }

        return $ret;
    }

   
    private function sign($params, $sign_method,$mer_id)
    {
        if (strtoupper($sign_method) == "MD5") {
            ksort($params);
            $sign_str = "";
            foreach ($params as $key => $val) {
                if (in_array($key, array("bank",))) {
                    continue;
                }
                $sign_str .= sprintf("%s=%s&", $key, $val);
            }
            $sign=$sign_str. $sign_method($mer_id);
            return $sign_method($sign);
        } else {
            throw new Exception("Unknown sign_method set in quickpay_conf");
        }
    }
}
