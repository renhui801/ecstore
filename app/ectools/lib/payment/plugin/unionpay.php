<?php
final class ectools_payment_plugin_unionpay extends ectools_payment_app implements ectools_interface_payment_app{
    public $name = '中国银联';
    public $app_name = '中国银联支付接口';
    public $app_key = 'unionpay';
    /** 中心化统一的key **/
    public $app_rpc_key = 'unionpay';
    public $display_name = 'unionpay';
    public $curname = 'CNY';
    public $ver = '1.0';
    /**
     * @var array 扩展参数
     */
    public $supportCurrency = array("CNY"=>"1");
    /**
     * @var string 当前支付方式所支持的平台
     */
    public $platform = 'ispc';

    function is_fields_valiad()
    {
        return true;
    }

    
    function intro()
    {
        return '<b><h3>'.app::get('ectools')->_('中国银联支付（UnionPay）是银联电子支付服务有限公司主要从事以互联网等新兴渠道为基础的网上支付。').'</h3></b>';
    }

    function admin_intro()
    {
        return app::get('ectools')->_('中国银联支付（UnionPay）是银联电子支付服务有限公司主要从事以互联网等新兴渠道为基础的网上支付。');
    }

    public function __construct($app)
    {
        parent::__construct($app);

        $this->notify_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_unionpay_server', 'callback');
        if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->notify_url, $matches)){
            $this->notify_url = str_replace('http://','',$this->notify_url);
            $this->notify_url = preg_replace("|/+|","/", $this->notify_url);
            $this->notify_url = "http://" . $this->notify_url;
        } else {
            $this->notify_url = str_replace('https://','',$this->notify_url);
            $this->notify_url = preg_replace("|/+|","/", $this->notify_url);
            $this->notify_url = "https://" . $this->notify_url;
        }
        $this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_unionpay', 'callback');
        if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->callback_url, $matches)){
            $this->callback_url = str_replace('http://','',$this->callback_url);
            $this->callback_url = preg_replace("|/+|","/", $this->callback_url);
            $this->callback_url = "http://" . $this->callback_url;
        }else{
            $this->callback_url = str_replace('https://','',$this->callback_url);
            $this->callback_url = preg_replace("|/+|","/", $this->callback_url);
            $this->callback_url = "https://" . $this->callback_url;
        }

        // 按照相应要求请求接口网关改为一下地址
        // $this->submit_url = 'http://58.246.226.99/UpopWeb/api/Pay.action';
        $this->submit_url = 'https://unionpaysecure.com/api/Pay.action';
        $this->submit_method = 'POST';
        $this->submit_charset = 'utf-8';
    }

    public function setting()
    {
        return array(
            'pay_name'=>array(
                'title'=>app::get('ectools')->_('支付方式名称'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'mer_id'=>array(
                'title'=>app::get('ectools')->_('客户号'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'mer_key'=>array(
                'title'=>app::get('ectools')->_('私钥'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'order_by' =>array(
                'title'=>app::get('ectools')->_('排序'),
                'type'=>'string',
                'label'=>app::get('ectools')->_('整数值越小,显示越靠前,默认值为1'),
            ),
            'pay_fee'=>array(
                'title'=>app::get('ectools')->_('交易费率'),
                'type'=>'pecentage',
                'validate_type' => 'number',
            ),
            'support_cur'=>array(
                'title'=>app::get('ectools')->_('支持币种'),
                'type'=>'text hidden cur',
                'options'=>$this->arrayCurrencyOptions,
            ),
            'pay_desc'=>array(
                'title'=>app::get('ectools')->_('描述'),
                'type'=>'html',
                'includeBase' => true,
            ),
            'pay_type'=>array(
                'title'=>app::get('ectools')->_('支付类型(是否在线支付)'),
                'type'=>'radio',
                'options'=>array('false'=>app::get('ectools')->_('否'),'true'=>app::get('ectools')->_('是')),
                'name' => 'pay_type',
            ),
            'status'=>array(
                'title'=>app::get('ectools')->_('是否开启此支付方式'),
                'type'=>'radio',
                'options'=>array('false'=>app::get('ectools')->_('否'),'true'=>app::get('ectools')->_('是')),
                'name' => 'status',
            ),
        );
    }


    public function dopay($payment){
        $merId = $this->getConf('mer_id', __CLASS__);//客户号
        $mer_key=$this->getConf('mer_key', __CLASS__);//私钥

        //组织给银联打过去要签名的数据 $args
        $args = array(
            "version"=>"1.0.0",//消息版本号
            "charset"=>"UTF-8",//字符编码
            "transType"=>'01',//交易类型
            "merAbbr"=>$payment['shopName'],//商户名称
            "merId"=>$merId,//商户代码
            "merCode"=>"",//商户类型
            "backEndUrl"=>$this->notify_url,//通知的url
            "frontEndUrl"=> $this->callback_url,//返回的url
            "acqCode"=>"",//收单机构代码
            "orderTime"=>date('YmdHis', $payment['t_begin']),//交易开始日期时间
            "orderNumber"=>$payment['payment_id'],//订单号(这里写支付单号)
            "commodityName"=>"",//商品名称
            "commodityUrl"=>"",//商品的url
            "commodityUnitPrice"=>"",//商品单价
            "commodityQuantity"=>"",//商品数量
            "transferFee"=>"",//运输费用
            "commodityDiscount"=>"",//优惠信息
            "orderAmount"=>ceil($payment['cur_money'] * 100),//交易金额
            "orderCurrency"=>156,//交易币种
            "customerName"=>"",//持卡人姓名
            "defaultPayType"=>"",//默认支付方式
            "defaultBankNumber"=>"",//默认银行编码
            "transTimeout"=>"",//交易超时时间
            "customerIp"=>$_SERVER['REMOTE_ADDR'],//持卡人IP
            "origQid"=>"",//交易流水号
            "merReserved"=>"",//商户保留域
        );

        //生成签名
        $chkvalue = $this->sign($args, 'MD5',$mer_key);
        //循环给表单赋值
        foreach($args as $key=>$val) {
            $this->add_field($key, $val);
        }
        //再往表单里面添加签名方法，签名
        $this->add_field('signMethod', 'MD5');
        $this->add_field('signature', $chkvalue);
        if($this->is_fields_valiad()){
            echo $this->get_html();exit;
        }else{
            return false;
        }

    }

     //回调函数: 接受银联返回来的数据
    public function callback(&$recv){
        $objMath = kernel::single('ectools_math');
        $money=$objMath->number_multiple(array($recv['orderAmount'], 0.01));
        $merid = $this->getConf('mer_id', __CLASS__);//客户号
        $mer_key=$this->getConf('mer_key', __CLASS__);//私钥
        $sign=$recv['signature'];  //银联返回来的签名      
        $sign_method=$recv['signMethod'];//银联返回来的签名方法 
        //$arrs:银联返回来的数组  
        $arrs=array(
            "version"=>$recv['version'],//消息版本号
            "charset"=>$recv['charset'],//字符编码
            "transType"=>$recv['transType'],//交易类型
            "respCode"=>$recv['respCode'],//响应码
            "respMsg"=>$recv['respMsg'],//响应信息
            "merAbbr"=>$recv['merAbbr'],//商户名称
            "merId"=>$recv['merId'],//商户代码
            "orderNumber"=>$recv['orderNumber'],//订单号
            "traceNumber"=>$recv['traceNumber'],//系统跟踪号
            "traceTime"=>$recv['traceTime'],//系统跟踪时间
            "qid"=>$recv['qid'],//交易流水号
            "orderAmount"=>$recv['orderAmount'],//交易金额
            "orderCurrency"=>$recv['orderCurrency'],//交易币种
            "respTime"=>$recv['respTime'],//交易完成时间
            "settleCurrency"=>$recv['settleCurrency'],//清算币种
            "settleDate"=>$recv['settleDate'],//清算日期
            "settleAmount"=>$recv['settleAmount'],//清算金额
            "exchangeDate"=>$recv['exchangeDate'],//兑换日期
            "exchangeRate"=>$recv['exchangeRate'],//清算汇率
            "cupReserved"=>$recv['cupReserved'],//系统保留域
        );
        //生成签名
        $chkvalue = $this->sign($arrs, $sign_method,$mer_key);
        $ret['payment_id'] =$arrs['orderNumber'];
        $ret['account'] = $arrs['merId'];
        $ret['bank'] = app::get('unionpay')->_('银联');
        $ret['pay_account'] = app::get('unionpay')->_('付款帐号');
        $ret['currency'] = 'CNY';
        $ret['money'] = $money;
        $ret['paycost'] = '0.000';
        $ret['cur_money'] =$money;
        $ret['tradeno'] = $recv['traceNumber'];
        // $ret['t_payed'] = strtotime($recv['settleDate']) ? strtotime($recv['settleDate']) : time();
        $ret['t_payed'] = time();
        $ret['pay_app_id'] = 'unionpay';
        $ret['pay_type'] = 'online';
        $ret['memo'] = 'unionpay';
        //校验签名
        if ($sign==$chkvalue && $recv['respCode']==00) {
             $ret['status'] = 'succ';
        }else{
            $ret['status']='failed';
        }
        return $ret;
    }

   /*
   	签名方法 3个参数
   	$params:组织给银联发过去的数据
   	$sign_method:签名的加密方法
   	$mer_key:签名必须要附上商户密钥
   */
   private function sign($params, $sign_method,$mer_key)
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
              $sign=$sign_str. $sign_method($mer_key);
            return $sign_method($sign);
        }
        else {
            throw new Exception("Unknown sign_method set in quickpay_conf");
        }
    }



    public function gen_form()
    {
      $tmp_form='<a href="javascript:void(0)" onclick="document.applyForm.submit();">'.app::get('unionpay')->_('立即申请').'</a>';
      $tmp_form.="<form name='applyForm' method='".$this->submit_method."' action='" . $this->submit_url . "' target='_blank'>";
      // 生成提交的hidden属性
      foreach($this->fields as $key => $val)
      {    
            $tmp_form.="<input type='hidden' name='".$key."' value='".$val."'>";
      }

      $tmp_form.="</form>";

      return $tmp_form;

    }
}
