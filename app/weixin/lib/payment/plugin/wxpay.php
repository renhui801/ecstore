<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * alipay支付宝手机支付接口
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
final class weixin_payment_plugin_wxpay extends ectools_payment_app implements ectools_interface_payment_app {

    /**
     * @var string 支付方式名称
     */
    public $name = '微信支付';
    /**
     * @var string 支付方式接口名称
     */
    public $app_name = '微信支付';
     /**
     * @var string 支付方式key
     */
    public $app_key = 'wxpay';
    /**
     * @var string 中心化统一的key
     */
    public $app_rpc_key = 'wxpay';
    /**
     * @var string 统一显示的名称
     */
    public $display_name = '微信支付';
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
    public $platform = 'iswap';

    /**
     * @var array 扩展参数
     */
    public $supportCurrency = array("CNY"=>"01");



    /**
     * 构造方法
     * @param null
     * @return boolean
     */
    public function __construct($app){
        parent::__construct($app);

        // $this->notify_url = kernel::openapi_url('openapi.ectools_payment/parse/weixin/weixin_payment_plugin_wxpay_server', 'callback');
        $this->notify_url = kernel::base_url(1).'/index.php/openapi/weixin/wxpay';
        #test
        // $this->notify_url = kernel::base_url(1).'/index.php/wap/paycenter-wxpay.html';
        if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->notify_url, $matches))
        {
            $this->notify_url = str_replace('http://','',$this->notify_url);
            $this->notify_url = preg_replace("|/+|","/", $this->notify_url);
            $this->notify_url = "http://" . $this->notify_url;
        }
        else
        {
            $this->notify_url = str_replace('https://','',$this->notify_url);
            $this->notify_url = preg_replace("|/+|","/", $this->notify_url);
            $this->notify_url = "https://" . $this->notify_url;
        }
        $this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/weixin/weixin_payment_plugin_wxpay', 'callback');
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
        // $this->submit_url = $this->gateway;
        // $this->submit_method = 'GET';
        $this->submit_charset = 'UTF-8';
        $this->signtype = 'sha1';
    }

    /**
     * 后台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    public function admin_intro(){
        $regIp = isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:$_SERVER['HTTP_HOST'];
        return '<img src="' . app::get('weixin')->res_url . '/payments/images/WXPAY.jpg"><br /><b style="font-family:verdana;font-size:13px;padding:3px;color:#000"><br>微信支付是由腾讯公司知名移动社交通讯软件微信及第三方支付平台财付通联合推出的移动支付创新产品，旨在为广大微信用户及商户提供更优质的支付服务，微信的支付和安全系统由腾讯财付通提供支持。</b>';
    }

     /**
     * 后台配置参数设置
     * @param null
     * @return array 配置参数列表
     */
    public function setting(){
        // 公众账号
        $publicNumbersInfo = app::get('weixin')->model('bind')->getList('appid,name',array('appid|noequal'=>''));
        $publicNumbers = array();
        foreach($publicNumbersInfo as $row){
            $publicNumbers[$row['appid']] = $row['name'];
        }
        return array(
            'pay_name'=>array(
                'title'=>app::get('weixin')->_('支付方式名称'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'public_number'=>array(
                'title'=>app::get('weixin')->_('选择公众账号'),
                'type'=>'select',
                'options'=>$publicNumbers
            ),
            'appId'=>array(
                'title'=>app::get('weixin')->_('appId'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'paySignKey'=>array(
                'title'=>app::get('weixin')->_('paySignKey'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            /*'appSecret'=>array( // app支付使用
                'title'=>app::get('weixin')->_('appSecret'),
                'type'=>'string',
                'validate_type' => 'required',
            ),*/
            'partnerId'=>array(
                'title'=>app::get('weixin')->_('partnerId'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'partnerKey'=>array(
                'title'=>app::get('weixin')->_('partnerKey'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'support_cur'=>array(
                'title'=>app::get('weixin')->_('支持币种'),
                'type'=>'text hidden cur',
                'options'=>$this->arrayCurrencyOptions,
            ),
            'pay_desc'=>array(
                'title'=>app::get('weixin')->_('描述'),
                'type'=>'html',
                'includeBase' => true,
            ),
            'pay_type'=>array(
                'title'=>app::get('weixin')->_('支付类型(是否在线支付)'),
                'type'=>'radio',
                'options'=>array('false'=>app::get('weixin')->_('否'),'true'=>app::get('weixin')->_('是')),
                'name' => 'pay_type',
            ),
            'status'=>array(
                'title'=>app::get('weixin')->_('是否开启此支付方式'),
                'type'=>'radio',
                'options'=>array('false'=>app::get('weixin')->_('否'),'true'=>app::get('weixin')->_('是')),
                'name' => 'status',
            ),
        );
    }

    /**
     * 前台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    public function intro(){
        return app::get('weixin')->_('微信支付是由腾讯公司知名移动社交通讯软件微信及第三方支付平台财付通联合推出的移动支付创新产品，旨在为广大微信用户及商户提供更优质的支付服务，微信的支付和安全系统由腾讯财付通提供支持。财付通是持有互联网支付牌照并具备完备的安全体系的第三方支付平台。');
    }

    /**
     * 提交支付信息的接口
     * @param array 提交信息的数组
     * @return mixed false or null
     */
    public function dopay($payment)
    {

        $appId      = trim($this->getConf('appId',      __CLASS__)); // appid
        $paySignKey = trim($this->getConf('paySignKey', __CLASS__)); // PaySignKey 对应亍支付场景中的 appKey 值
        // $appSecret  = $this->getConf('appSecret',  __CLASS__); // app支付时使用
        $partnerId  = trim($this->getConf('partnerId',  __CLASS__)); // 财付通商户身份的标识
        $partnerKey = trim($this->getConf('partnerKey', __CLASS__)); // 财付通商户权限密钥 Key

        $price = ceil($payment['cur_money'] * 100);

        $this->add_field("bank_type"          , "WX" );
        $this->add_field("body"               , strval( str_replace(' ', '', (isset($payment['body']) && $payment['body']) ? $payment['body'] : app::get('weixin')->_('网店订单') ) ) );
        $this->add_field("partner"            , strval( $partnerId ) );
        $this->add_field("out_trade_no"       , strval( $payment['payment_id'] ) );
        $this->add_field("total_fee"          , strval( ceil($payment['cur_money'] * 100) ) );
        $this->add_field("fee_type"           , "1" );
        $this->add_field("notify_url"         , strval( $this->notify_url ) );
        $this->add_field("spbill_create_ip"   , strval( $payment['ip'] ) );
        $this->add_field("input_charset"      , "UTF-8" );
        $this->add_field("create_biz_package" , $this->create_biz_package($appId, $paySignKey, $partnerId, $partnerKey) );

        // 用于微信支付后跳转页面传order_id,不作为传微信的字段
        $this->add_field("order_id"      , $payment['order_id'] );

        echo $this->get_html();exit;
    }


    /**
     * 支付后返回后处理的事件的动作
     * @params array - 所有返回的参数，包括POST和GET
     * @return null
     */
    function callback(&$in){
        $appId      = trim($this->getConf('appId',      __CLASS__)); // appid
        $paySignKey = trim($this->getConf('paySignKey', __CLASS__)); // PaySignKey 对应亍支付场景中的 appKey 值
        $partnerId  = trim($this->getConf('partnerId',  __CLASS__)); // 财付通商户身份的标识
        $partnerKey = trim($this->getConf('partnerKey', __CLASS__)); // 财付通商户权限密钥 Key

        $postData = $in['weixin_postdata'];
        unset($in['weixin_postdata']);

        ksort($in);
        $unSignParaString = weixin_util::formatQueryParaMap($in, false);
        $checksign = weixin_util::verifySignature($unSignParaString, $in['sign'], $partnerKey);

        $objMath = kernel::single('ectools_math');
        $ret = array();
        $ret['payment_id'] = $in['out_trade_no'];
        $ret['account'] = $in['partner'];
        $ret['bank'] = app::get('weixin')->_('微信支付');
        $ret['pay_account'] = app::get('weixin')->_('付款帐号');
        $ret['currency'] = 'CNY';
        $ret['money'] = $objMath->number_multiple(array($in['total_fee'], 0.01));
        $ret['paycost'] = '0.000';
        $ret['cur_money'] = $objMath->number_multiple(array($in['total_fee'], 0.01));
        $ret['trade_no'] = $in['transaction_id'];
        $ret['t_payed'] = strtotime($in['time_end']);
        $ret['pay_app_id'] = "wxpay";
        $ret['pay_type'] = 'online';
        $ret['memo'] = 'wxpay';
        $ret['thirdparty_account'] = $postData['OpenId'];

        //校验签名
        if ( $checksign && $in['trade_state']==0 ) {
             $ret['status'] = 'succ';
        }else{
            $ret['status']='failed';
        }

        return $ret;
    }

    /**
     * 支付成功回打支付成功信息给支付网关
     */
    function ret_result($paymentId){
        echo 'success';exit;
    }

    /**
     * 校验方法
     * @param null
     * @return boolean
     */
    public function is_fields_valiad(){
        return true;
    }

    /**
     * 生成支付表单 - 自动提交
     * @params null
     * @return null
     */
    public function gen_form(){
        return '';
    }

    protected function get_html(){
        // 微信提交支付,调用微信内置js
        header("Content-Type: text/html;charset=".$this->submit_charset);
        $success_url = app::get('wap')->router()->gen_url(array('app'=>'b2c','ctl'=>'wap_paycenter','act'=>'result_pay','full'=>1,'arg0'=>$this->fields['order_id'],'arg1'=>'true'));
        $failure_url = app::get('wap')->router()->gen_url(array('app'=>'b2c','ctl'=>'wap_paycenter','act'=>'index','full'=>1,'arg0'=>$this->fields['order_id']));
        $strHtml = '<html>
                    <script language="javascript">
                    function callpay()
                    {
                        WeixinJSBridge.invoke("getBrandWCPayRequest",' . $this->fields["create_biz_package"] . ',function(res){
                            if(res.err_msg == "get_brand_wcpay_request:ok"){
                                window.location.href = "' . $success_url . '";
                            }else{
                                alert("微信支付中断或未完成支付,请至微信中重新进行微信支付(需微信5.0以上版本)");
                                window.location.href = "' . $failure_url . '";
                            }
                        });
                    }
                    // 当微信内置浏览器完成内部初始化后会触发WeixinJSBridgeReady事件。
                    document.addEventListener("WeixinJSBridgeReady", function onBridgeReady() {
                        callpay();
                    }, false)
                    </script>
                    <body>
                    <button type="button" onclick="callpay()" style="display:none;">微信支付</button>
                    </body>
                    </html>';
        return $strHtml;
    }


//↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓公共函数部分↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

    private function check_cft_parameters() {
        if($this->fields["bank_type"] == null || $this->fields["body"] == null || $this->fields["partner"] == null ||
            $this->fields["out_trade_no"] == null || $this->fields["total_fee"] == null || $this->fields["fee_type"] == null ||
            $this->fields["notify_url"] == null || $this->fields["spbill_create_ip"] == null || $this->fields["input_charset"] == null
            ){
            return false;
        }
        return true;

    }

    private function get_cft_package($partnerKey){
        try {
            if (null == $partnerKey || "" == $partnerKey ) {
                throw new Exception("密钥不能为空！" . "<br>");
            }

            ksort($this->fields);
            $unSignParaString = weixin_util::formatQueryParaMap($this->fields, false);
            $paraString = weixin_util::formatQueryParaMap($this->fields, true);

            return $paraString . "&sign=" . weixin_util::sign($unSignParaString,weixin_util::trimString($partnerKey));
        } catch (Exception $e) {
            die($e->getMessage());
        }

    }

    private function get_biz_sign($bizObj, $paySignKey) {
        foreach ($bizObj as $k => $v){
            $bizParameters[strtolower($k)] = $v;
        }

        try {
            if($paySignKey == ""){
                throw new Exception("APPKEY为空！" . "<br>");
            }
            $bizParameters["appkey"] = $paySignKey;
            ksort($bizParameters);
            $bizString = weixin_util::formatBizQueryParaMap($bizParameters, false);

            return sha1($bizString);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    //生成jsapi支付请求json
    /*
    "appId" : "wxf8b4f85f3a794e77", //公众号名称，由商户传入
    "timeStamp" : "189026618", //时间戳这里随意使用了一个值
    "nonceStr" : "adssdasssd13d", //随机串
    "package" : "bank_type=WX&body=XXX&fee_type=1&input_charset=GBK&notify_url=http%3a%2f
    %2fwww.qq.com&out_trade_no=16642817866003386000&partner=1900000109&spbill_create_i
    p=127.0.0.1&total_fee=1&sign=BEEF37AD19575D92E191C1E4B1474CA9",
    //扩展字段，由商户传入
    "signType" : "SHA1", //微信签名方式:sha1
    "paySign" : "7717231c335a05165b1874658306fa431fe9a0de" //微信签名
    */
    private function create_biz_package($appId, $paySignKey, $partnerId, $partnerKey){
        try {
            if($this->check_cft_parameters() == false) {
                throw new Exception("生成package参数缺失！" . "<br>");
            }
            $nativeObj["appId"] = $appId;
            $nativeObj["package"] = $this->get_cft_package($partnerKey);
            $nativeObj["timeStamp"] = strval(time());
            $nativeObj["nonceStr"] = weixin_util::create_noncestr();
            $nativeObj["paySign"] = $this->get_biz_sign($nativeObj, $paySignKey);
            $nativeObj["signType"] = $this->signtype;

            return json_encode($nativeObj);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

//↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑公共函数部分↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

}
