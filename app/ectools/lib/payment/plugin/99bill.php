<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 快钱支付具体实现
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
final class ectools_payment_plugin_99bill extends ectools_payment_app {

	/**
	 * @var string 支付方式名称
	 */
    public $name = '快钱网上支付';//快钱网上支付
    /**
     * @var string 支付方式接口名称
     */
    public $app_name = '快钱支付接口';
    /**
     * @var string 支付方式key
     */
	public $app_key = '99bill';
	/**
	 * @var string 中心化统一的key
	 */
	public $app_rpc_key = '99bill';
	/**
	 * @var string 统一显示的名称
	 */
    public $display_name = '快钱';
    /**
	 * @var string 货币名称
	 */
    public $curname = 'CNY';
    /**
	 * @var string 当前支付方式的版本号
	 */
	public $ver = '1.0';
    /**
     * @var string 当前支付方式所支持的平台
     */
    public $platform = 'ispc';

	/**
	 * @var array 扩展参数
	 */
	public $supportCurrency = array("CNY"=>"1");

	/**
	 * 校验方法
	 * @param null
	 * @return boolean
	 */
    function is_fields_valiad(){
        return true;
    }

    /**
     * 前台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    function intro(){
        return '<b><h3>'.app::get('ectools')->_('ShopEx联合快钱推出：免费签约，1%优惠费率，更有超值优惠的信用卡支付。').'</h3></b><bR>'.app::get('ectools')->_('快钱是国内领先的独立第三方支付企业，旨在为各类企业及个人提供安全、便捷和保密的支付清算与账务服务，其推出的支付产品包括但不限于人民币支付，外卡支付，神州行卡支付，联通充值卡支付，VPOS支付等众多支付产品, 支持互联网、手机、电话和POS等多种终端, 以满足各类企业和个人的不同支付需求。截至2009年6月30日，快钱已拥有4100万注册用户和逾31万商业合作伙伴，并荣获中国信息安全产品测评认证中心颁发的“支付清算系统安全技术保障级一级”认证证书和国际PCI安全认证。<b><h3>注：本接口为银行直连，数据显示，可以提升78%的潜在消费者完成购买行为。').'</h3></b>';
    }

    /**
     * 后台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    function admin_intro(){
        return app::get('ectools')->_('ShopEx联合快钱推出：免费签约，1%优惠费率，更有超值优惠的信用卡支付。<bR>快钱是国内领先的独立第三方支付企业，旨在为各类企业及个人提供安全、便捷和保密的支付清算与账务服务，其推出的支付产品包括但不限于人民币支付，外卡支付，神州行卡支付，联通充值卡支付，VPOS支付等众多支付产品, 支持互联网、手机、电话和POS等多种终端, 以满足各类企业和个人的不同支付需求。截至2009年6月30日，快钱已拥有4100万注册用户和逾31万商业合作伙伴，并荣获中国信息安全产品测评认证中心颁发的“支付清算系统安全技术保障级一级”认证证书和国际PCI安全认证。');
    }

    /**
     * 构造方法
     * @param object 传递应用的app
     * @return null
     */
	public function __construct($app)
	{
		parent::__construct($app);

         //$this->callback_url = $this->app->base_url(true)."/apps/".basename(dirname(__FILE__))."/".basename(__FILE__);
		$this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_99bill', 'callback');
		if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->callback_url, $matches))
		{
			$this->callback_url = str_replace('http://','',$this->callback_url);
			$this->callback_url = preg_replace("|/+|","/", $this->callback_url);
			$this->callback_url = "http://" . $this->callback_url;
		}
		else
		{
			$this->callback_url = str_replace('https://','',$this->callback_url);
			$this->callback_url = preg_replace("|/+|","/", $this->callback_url);
			$this->callback_url = "https://" . $this->callback_url;
		}
        $this->submit_url = 'https://www.99bill.com/gateway/recvMerchantInfoAction.htm';
        $this->submit_method = 'POST';
        $this->submit_charset = 'utf-8';
    }

    /**
     * 提交支付信息的接口
     * @param array 提交信息的数组
     * @return mixed false or null
     */
	public function dopay($payment){
        $merId = $this->getConf('mer_id', __CLASS__);
        $ikey = $this->getConf('PrivateKey', __CLASS__);//私钥值，商户可上99BILL快钱后台自行设定
        $connecttype = $this->getConf('ConnectType', __CLASS__);
        if ($connecttype){
            $bankId = $payment['payExtend']['bankId'];
            $payType='10';
        }
        $payment['M_Amount']=ceil($payment['cur_money'] * 100);
        $orderTime = date('YmdHis',time());
        $return['inputCharset']="1";
        $return['bgUrl'] = $this->callback_url;
        $return['version'] = "v2.0";
        $return['language']="1";
        $return['signType']="1";
        $return['merchantAcctId'] = $merId;
        $return['payerName']=$payment['pay_name'];
        $return['payerContactType']="1";//支付人联系方式类型.固定选择值，目前只能为电子邮件
        $return['payerContact']=$payment['P_Email'];//支付人联系方式
        $return['orderId']= $payment['payment_id'];
        $return['orderAmount'] = $payment['M_Amount'];
        $return['orderTime'] = $orderTime;
        $return['productName'] = $payment['orders'][0]['rel_id'];
        $return['productNum'] = "1";
        $return['productId'] = "";
        $return['productDesc'] = $payment['M_Remark'];
        $return['ext1']= "";
        $return['ext2'] = "";
        $return['payType'] = $payType?$payType:"00";
        $return['bankId'] = $bankId?$bankId:'';
        $return['redoFlag'] = 1;//是否重复提交同一个订单
        $return['pid'] = "10017518267";//合作ID
        foreach($return as $k=>$v){
            if ($v)
                $str.=$k."=".$v."&";
        }
        $signMsg=strtoupper(md5(substr($str,0,strlen($str)-1)."&key=".$ikey));
        $return['signMsg']=$signMsg;
        foreach($return as $key=>$val) {
            $this->add_field($key,$val);
        }
        if($this->is_fields_valiad()){
            //header('Content-type: text/html;charset=gb2312',false);
            echo $this->get_html();exit;
        }else{
            return false;
        }
    }

    /**
     * 支付回调的方法
     * @param array 回调参数数组
     * @return array 处理后的结果
     */
    function callback(&$in){
		$objMath = kernel::single('ectools_math');
        $merchantAcctId=trim($in['merchantAcctId']);
        $version=trim($in['version']);
        $language=trim($in['language']);
        $signType=trim($in['signType']);
        $payType=trim($in['payType']);
        $orderId=trim($in['orderId']);
        $orderTime=trim($in['orderTime']);
        $bankId = trim($in['bankId']);
        //获取原始订单金额
        ///订单提交到快钱时的金额，单位为分。
        ///比方2 ，代表0.02元
        $orderAmount=trim($in['orderAmount']);
        $dealId=trim($in['dealId']); //获取该交易在快钱的交易号
        $bankDealId=trim($in['bankDealId']); //如果使用银行卡支付时，在银行的交易号。如不是通过银行支付，则为空
        $dealTime=trim($in['dealTime']);
        //获取实际支付金额
        ///单位为分
        ///比方 2 ，代表0.02元
        $payAmount=trim($in['payAmount']);
        //获取交易手续费
        ///单位为分
        ///比方 2 ，代表0.02元
        $fee=trim($in['fee']);
        //获取处理结果
        ///10代表 成功; 11代表 失败
        ///00代表 下订单成功（仅对电话银行支付订单返回）;01代表 下订单失败（仅对电话银行支付订单返回）
        $payResult=trim($in['payResult']);
        $errCode=trim($in['errCode']);
        $signMsg=trim($in['signMsg']);    //获取加密签名串
        $key=$this->getConf('PrivateKey', __CLASS__);

        //生成加密串。必须保持如下顺序。
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal, "merchantAcctId",$merchantAcctId);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal, "version",$version);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal, "language",$language);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal, "signType",$signType);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal, "payType",$payType);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal, "bankId",$bankId);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal, "orderId",$orderId);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal, "orderTime",$orderTime);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal, "orderAmount",$orderAmount);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal, "dealId",$dealId);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal, "bankDealId",$bankDealId);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal, "dealTime",$dealTime);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal, "payAmount",$payAmount);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal, "fee",$fee);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal, "ext1",$ext1);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal, "ext2",$ext2);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal, "payResult",$payResult);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal, "errCode",$errCode);
        $merchantSignMsgVal=$this->appendParam($merchantSignMsgVal, "key",$key);

		$ret = array();
		$ret['payment_id'] = $in['orderId'];
		$ret['account'] = $merchantAcctId;
		$ret['bank'] = $bankId;
		$ret['pay_account'] = app::get('ectools')->_('付款帐号');
		$ret['currency'] = 'CNY';
		$ret['money'] = $objMath->number_multiple(array($in['orderAmount'], 0.01));
		$ret['paycost'] = $objMath->number_multiple(array($fee, 0.01));
		$ret['cur_money'] = $objMath->number_multiple(array($in['orderAmount'], 0.01));
		$ret['trade_no'] = $in['dealId'];
		$ret['t_payed'] = strtotime($in['orderTime']);
		$ret['pay_app_id'] = "99bill";
		$ret['pay_type'] = 'online';
		$ret['memo'] = '99bill';

        $merchantSignMsg= md5($merchantSignMsgVal);
        $paymentId=$orderId;
        $money = $payAmount/100;
        $tradeno = $dealId;
        ///首先进行签名字符串验证
        if(strtoupper($signMsg) == strtoupper($merchantSignMsg)){
            switch($payResult){
                case "10":
                    $ret['status'] = 'succ';
                break;
                default:
                    $ret['status'] = 'failed';
                break;
            }
        }else{
            $message=app::get('ectools')->_("签名认证失败！");
            $ret['status'] = 'invalid';
        }

        return $ret;
    }

    /**
     * 支付成功回打支付成功信息给支付网关
     */
    function ret_result($paymentId){
        $rtnOk=1;
        $rtnUrl = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_paycenter','full'=>1,'act'=>'result_pay','arg'=>$paymentId));
        echo "<result>".$rtnOk."</result><redirecturl>".$rtnUrl."</redirecturl>";
        exit;
    }

    /**
     * 后台配置参数设置
     * @param null
     * @return array 配置参数列表
     */
    function setting(){
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
                'PrivateKey'=>array(
                        'title'=>app::get('ectools')->_('私钥'),
                        'type'=>'string',
						'validate_type' => 'required',
                ),
                'order_by' =>array(
                    'title'=>app::get('ectools')->_('排序'),
                    'type'=>'string',
                    'label'=>app::get('ectools')->_('整数值越小,显示越靠前,默认值为1'),
                ),
                'ConnectType'=>array(
                    'title'=>app::get('ectools')->_('顾客付款类型'),
                    'type'=>'radio',
                    'options'=>array('0'=>app::get('ectools')->_('登录快钱支付'),'1'=>app::get('ectools')->_('银行直接支付')),
                    'event'=>'showbank',
                    'eventscripts'=>'<script>function showbank(obj){if (obj.value==1){$(\'bankShow\').show();}else {$(\'bankShow\').hide();}}</script>',
                    'extendcontent'=>array(
                        array(
                            "property"=>array(
                                "type"=>"checkbox",//后台显示方式
                                "name"=>"bankId",
                                "size"=>6,
                                "extconId"=>"bankShow",
                                "display"=>0,
                                "fronttype"=>"radio", //前台显示方式
                                "frontsize"=>6,
                                "frontname"=>"showbank"
                            ),
                            "value"=>array(
                                array("value"=>"ICBC","imgname"=>"bank_icbc.gif","name"=>app::get('ectools')->_("中国工商银行")),
                                array("value"=>"CMB","imgname"=>"bank_cmb.gif","name"=>app::get('ectools')->_("招商银行")),
                                array("value"=>"ABC","imgname"=>"bank_abc.gif","name"=>app::get('ectools')->_("中国农业银行")),
                                array("value"=>"CCB","imgname"=>"bank_ccb.gif","name"=>app::get('ectools')->_("中国建设银行")),
                                array("value"=>"SPDB","imgname"=>"bank_spdb.gif","name"=>app::get('ectools')->_("上海浦东发展银行")),
                                array("value"=>"BCOM","imgname"=>"bank_bcom.gif","name"=>app::get('ectools')->_("交通银行")),
                                array("value"=>"CMBC","imgname"=>"bank_cmbc.gif","name"=>app::get('ectools')->_("中国民生银行")),
                                array("value"=>"SDB","imgname"=>"bank_sdb.gif","name"=>app::get('ectools')->_("深圳发展银行")),
                                array("value"=>"GDB","imgname"=>"bank_gdb.gif","name"=>app::get('ectools')->_("广东发展银行")),
                                array("value"=>"CITIC","imgname"=>"bank_citic.gif","name"=>app::get('ectools')->_("中信银行")),
                                array("value"=>"HXB","imgname"=>"bank_hxb.gif","name"=>app::get('ectools')->_("华夏银行")),
                                array("value"=>"CIB","imgname"=>"bank_cib.gif","name"=>app::get('ectools')->_("兴业银行")),
                                array("value"=>"GZRCC","imgname"=>"bank_gzrcc.gif","name"=>app::get('ectools')->_("广州市农村信用合作社")),
                                array("value"=>"GZCB","imgname"=>"bank_gzcb.gif","name"=>app::get('ectools')->_("广州市商业银行")),
                                array("value"=>"SHRCC","imgname"=>"bank_shrcc.gif","name"=>app::get('ectools')->_("上海农村商业银行")),
                                array("value"=>"POST","imgname"=>"bank_post.gif","name"=>app::get('ectools')->_("中国邮政储蓄")),
                                array("value"=>"BOB","imgname"=>"bank_bob.gif","name"=>app::get('ectools')->_("北京银行")),
                                array("value"=>"BOC","imgname"=>"bank_boc.gif","name"=>app::get('ectools')->_("中国银行")),
                                array("value"=>"CBHB","imgname"=>"bank_cbhb.gif","name"=>app::get('ectools')->_("渤海银行")),
                                array("value"=>"BJRCB","imgname"=>"bank_bjrcb.gif","name"=>app::get('ectools')->_("北京农村商业银行")),
                                array("value"=>"CEB","imgname"=>"bank_ceb.gif","name"=>app::get('ectools')->_("中国光大银行")),
                                array("value"=>"NJCB","imgname"=>"bank_njcb.gif","name"=>app::get('ectools')->_("南京银行")),
                                array("value"=>"BEA","imgname"=>"bank_bea.gif","name"=>app::get('ectools')->_("东亚银行")),
                                array("value"=>"NBCB","imgname"=>"bank_nbcb.gif","name"=>app::get('ectools')->_("宁波银行")),
                                array("value"=>"HZB","imgname"=>"bank_hzb.gif","name"=>app::get('ectools')->_("杭州银行")),
                                array("value"=>"PAB","imgname"=>"bank_pab.gif","name"=>app::get('ectools')->_("平安银行"))
                            )
                        )

                    )
                ),
				'support_cur'=>array(
					'title'=>app::get('ectools')->_('支持币种'),
					'type'=>'text hidden cur',
					'options'=>$this->arrayCurrencyOptions,
				),
                'pay_fee'=>array(
                    'title'=>app::get('ectools')->_('交易费率'),
                    'type'=>'pecentage',
					'validate_type' => 'number',
                ),
                'pay_brief'=>array(
                    'title'=>app::get('ectools')->_('支付方式简介'),
					 'type'=>'textarea',
                ),
                'pay_desc'=>array(
                    'title'=>app::get('ectools')->_('描述'),
					'type'=>'html',
					'includeBase' => true,
                ),
				'pay_type'=>array(
					 'title'=>app::get('ectools')->_('支付类型(是否在线支付)'),
					 'type'=>'hidden',
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

    /**
     * 生成各个参数的加密串
     * @param 结果字符串，引用值
     * @param 加密串键值
     * @param 加密串的值
     * @return 加密串结果
     */
    function appendParam($returnStr,$paramId,$paramValue){
        if($returnStr != ""){
            if($paramValue != ""){
                $returnStr.="&".$paramId."=".$paramValue;
            }
        }else{
            If($paramValue!=""){
                $returnStr=$paramId."=".$paramValue;
            }
        }
        return $returnStr;
    }

    /**
     * 生成form的方法
     * @param null
     * @return string html
     */
    function gen_form(){
          $certid=$this->app->getConf('certificate.id');
          $url=urlencode($this->app->base_url());

          $tmp_form='<a href="http://service.shopex.cn/checkcert.php?pay_id=2&certi_id='.$certid.'&url='.$url.'
" target="_blank"><img src="'.$this->app->base_url().'plugins/payment/images/99bill_apply.gif"></a>&nbsp;&nbsp;&nbsp;<font color="green">'.app::get('ectools')->_('绿卡用户申请快钱支付，费率更低，').'</font><a href="http://www.shopex.cn/shopex_price/sq/index.html" target="_blank"><font color="green">'.app::get('ectools')->_('点击了解绿卡').'</font></a>';

          return $tmp_form;

    }
}

?>
