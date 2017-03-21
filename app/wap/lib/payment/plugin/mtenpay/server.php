<?php
    class wap_payment_plugin_mtenpay_server extends ectools_payment_app {

        public $sec_id='MD5';

        public function callback($recv)
        {
            $objMath = kernel::single('ectools_math');
            $money = $objMath->number_multiple(array($recv['total_fee'], 0.01));
            #键名与pay_setting中设置的一致
            $mer_id = $this->getConf('mer_id', substr(__CLASS__, 0, strrpos(__CLASS__, '_')));
            $mer_key = $this->getConf('mer_key', substr(__CLASS__, 0, strrpos(__CLASS__, '_')));
            if($this->is_return_vaild($recv,$mer_key,$this->sec_id)){
                $ret['payment_id'] = $recv['sp_billno'];
                $ret['account'] = $mer_id;
                $ret['bank'] = app::get('wap')->_('手机手机财付通');
                $ret['pay_account'] = app::get('wap')->_('付款帐号');
                $ret['currency'] = 'CNY';
                $ret['money'] = $money;
                $ret['paycost'] = '0.000';
                $ret['cur_money'] = $money;
                $ret['trade_no'] = $recv['transaction_id'];
                $ret['t_payed'] = strtotime($recv['time_end']) ? strtotime($recv['time_end']) : time();
                $ret['pay_app_id'] = "mtenpay";
                $ret['pay_type'] = 'online';
                $ret['memo'] = $recv['body'];

                if($recv['pay_result'] == '0') {
                    $ret['status'] = 'succ';
                }else {
                    $ret['status'] =  'failed';
                }
            }else{
                $message = 'Invalid Sign';
                $ret['status'] = 'invalid';
            }
            return $ret;
        }

        public function is_return_vaild($form,$key,$secu_id){
            $_key      = $key;
            $sign_type = $secu_id;
            $get       = $this->para_filter($form);     //对所有GET反馈回来的数据去空
            $sort_get  = $this->arg_sort($get);         //对所有GET反馈回来的数据排序
            $mysign    = $this->build_mysign($sort_get,$_key,$sign_type);    //生成签名结果
            $mysign=strtoupper($mysign);
            if ($mysign == $form['sign']) {
                return true;
            }
            #记录返回失败的情况
            logger::error(app::get('wap')->_('支付单号：') . $form['out_trade_no'] . app::get('wap')->_('签名验证不通过，请确认！')."\n");
            logger::error(app::get('wap')->_('本地产生的加密串：') . $mysign);
            logger::error(app::get('wap')->_('手机财付通传递打过来的签名串：') . $form['sign']);
            $str_xml .= "<tenpayform>";
            foreach ($form as $key=>$value){
                $str_xml .= "<$key>" . $value . "</$key>";
            }
            $str_xml .= "</tenpayform>";

            return false;
        }

//↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓公共函数部分↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

    /**生成签名结果
     * $array要签名的数组
     * return 签名结果字符串
     */
    public function build_mysign($sort_array,$key,$sign_type = "MD5") {
        $prestr = $this->create_linkstring($sort_array,$key);         //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $mysgin = $this->sign($prestr,$sign_type = "MD5");                //把最终的字符串签名，获得签名结果
        return $mysgin;
    }

    /**把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * $array 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    public function create_linkstring($array,$_mer_key) {
        $arg  = "";
        while (list ($key, $val) = each ($array)) {
            $arg.=$key."=".$val."&";
        }

        $arg = substr($arg,0,count($arg)-2); //去掉最后一个&字符
        $arg = $arg.'&key='.$_mer_key;
        return $arg;
    }

    /**除去数组中的空值和签名参数
     * $parameter 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    public function para_filter($parameter) {
        $para = array();
        while (list ($key, $val) = each ($parameter)) {
            if($key == "sign" || $key == "chv" || $val == "")continue;
            else    $para[$key] = $parameter[$key];
        }
        return $para;
    }

    /**对数组排序
     * $array 排序前的数组
     * return 排序后的数组
     */
    public function arg_sort($array) {
        ksort($array);
        reset($array);
        return $array;
    }

    /**签名字符串
     * $prestr 需要签名的字符串
     * $sign_type 签名类型，也就是sec_id
     * return 签名结果
     */
    public function sign($prestr,$sign_type) {
        $sign='';
        if($sign_type == 'MD5') {
            $sign = md5($prestr);
        }else {
            die("财付通暂不支持".$sign_type."类型的签名方式");
        }
        return $sign;
    }

//↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑公共函数部分↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

}

?>