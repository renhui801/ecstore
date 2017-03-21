<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 财付通手机支付接口
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
final class wap_payment_plugin_mtenpay extends ectools_payment_app implements ectools_interface_payment_app {
    public $name = '手机财付通';
    public $app_name = '手机财付通';
    public $app_key = 'mtenpay';
    public $app_rpc_key = 'mtenpay';
    public $display_name = '手机财付通';
    public $curname = 'CNY';
    public $ver = '2.0';
    public $platform = 'iswap';
    public $supportCurrency = array('CNY' => "1");
    public $bank_type = '0';
    public $format = "xml";    //http传输格式
    public $sec_id = 'MD5';    //签名方式 不需修改
    public $_input_charset_utf8 = '1';    //字符编码格式
    public $gateway="https://wap.tenpay.com/cgi-bin/wappayv2.0/wappay_gate.cgi?";
    public $gateway_init="https://wap.tenpay.com/cgi-bin/wappayv2.0/wappay_init.cgi?";

    /**
     * 构造方法
     * @param null
     * @return boolean
     */
    public function __construct($app){
        parent::__construct($app);

        $this->notify_url = kernel::openapi_url('openapi.ectools_payment/parse/wap/wap_payment_plugin_mtenpay_server', 'callback');
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
        $this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/wap/wap_payment_plugin_mtenpay', 'callback');
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
        $this->submit_url = $this->gateway;
        $this->submit_method = 'GET';
        $this->submit_charset = $this->_input_charset;
    }

    /**
     * 后台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    public function admin_intro(){
        return ' 财付通为手机支付提供简单高效的解决方案。用户通过手机在财付通合作网站上进行订单确认支付后，将接收财付通发送的验证码，回复验证码并确认即可完成订单支付。';
    }

     /**
     * 后台配置参数设置
     * @param null
     * @return array 配置参数列表
     */
    public function setting(){
        return array(
            'pay_name'=>array(
                'title'=>app::get('wap')->_('支付方式名称'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'mer_id'=>array(
                'title'=>app::get('wap')->_('商户号(bargainor)'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'mer_key'=>array(
                'title'=>app::get('wap')->_('商户签名(key)'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'support_cur'=>array(
                'title'=>app::get('wap')->_('支持币种'),
                'type'=>'text hidden cur',
                'options'=>$this->arrayCurrencyOptions,
            ),
            'pay_desc'=>array(
                'title'=>app::get('wap')->_('描述'),
                'type'=>'html',
                'includeBase' => true,
            ),
            'status'=>array(
                'title'=>app::get('wap')->_('是否开启此支付方式'),
                'type'=>'radio',
                'options'=>array('false'=>app::get('wap')->_('否'),'true'=>app::get('wap')->_('是')),
                'name' => 'status',
            ),
            'pay_type'=>array(
                'title'=>app::get('wap')->_('支付类型(是否在线支付)'),
                'type'=>'radio',
                'options'=>array('true'=>app::get('wap')->_('是')),
                'name' => 'pay_type',
                'validate_type' => 'requiredradio',
            ),
        );
    }

    public function dopay($payment)
    {

        $mer_id = $this->getConf('mer_id', __CLASS__);
        $mer_id = $mer_id;
        $mer_key = $this->getConf('mer_key', __CLASS__);
        $mer_key = $mer_key;
        $price = ceil($payment['cur_money'] * 100);
        $_pms1 = array(
            'ver' => $this->ver,
            'charset'=>$this->_input_charset_utf8,
            'bank_type'=>$this->bank_type,
            'desc'=>$payment['shopName'],
            'bargainor_id'=>$mer_id,
            'sp_billno'=>$payment['payment_id'],
            'total_fee'=>$price,
            'notify_url'=>$this->notify_url,
            'callback_url'=>$this->callback_url
            );
        $token_id=$this->tenpay_wap_trade_create_direct($_pms1, $mer_key);
        $this->add_field('token_id',$token_id);
        echo $this->get_html();exit;
    }

    /**
    *同步返回
    */
    public function callback(&$recv)
    {
        $objMath = kernel::single('ectools_math');
        $money=$objMath->number_multiple(array($recv['total_fee'], 0.01));
        $mer_key = $this->getConf('mer_key', __CLASS__);
        $mer_key = $mer_key;
        #键名与pay_setting中设置的一致
        if($this->is_return_vaild($recv,$mer_key,$this->sec_id)){
            $mer_id = $this->getConf('mer_id', __CLASS__);
            $mer_id = $mer_id;
            $ret['payment_id'] = $recv['sp_billno'];
            $ret['account'] = $mer_id;
            $ret['bank'] = app::get('wap')->_('手机财付通');
            $ret['pay_account'] = app::get('wap')->_('付款帐号');
            $ret['currency'] = 'CNY';
            $ret['money'] = $money;
            $ret['paycost'] = '0.000';
            $ret['cur_money'] = $money;
            $ret['trade_no'] = $recv['transaction_id'];
            $ret['t_payed'] = strtotime($recv['time_end']) ? strtotime($recv['time_end']) : time();
            $ret['pay_app_id'] = "mtenpay";
            $ret['pay_type'] = 'online';
            $ret['memo'] = $recv['attach'];
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

    /**
     * 检验返回数据合法性
     * @param mixed $form 包含签名数据的数组
     * @param mixed $key 签名用到的私钥
     * @access private
     * @return boolean
     */
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

    /**
     * 前台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    public function intro(){
        return app::get('wap')->_('财付通为手机支付提供简单高效的解决方案。用户通过手机在财付通合作网站上进行订单确认支付后，将接收财付通发送的验证码，回复验证码并确认即可完成订单支付。');
    }

    /**
     * 创建alipay.wap.trade.create.direct接口
     */
    public function tenpay_wap_trade_create_direct($pms1,$key){ 
        
        $sort_array = $this->arg_sort($pms1);    //得到从字母a到z排序后的签名参数数组
        $mysign     = $this->build_mysign($sort_array,$key ,$this->sign_type);    //生成签名
        $mysign     = strtoupper($mysign);
        $req_data   = $this->create_linkstring($pms1).'&sign='.urlencode($mysign);    //配置post请求数据，注意sign签名需要urlencode
     
        //Post提交请求
        $url = $this->gateway_init.$req_data;
        $res = kernel::single('base_httpclient')->get($url);
        $token= kernel::single('site_utility_xml')->xml2array($res);;
        return $token['root']['token_id'];
        //调用GetToken方法，并返回token_id
    }

    //public function tenpay_wap_trade_

//↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓工具函数部分↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
    public function is_fields_valiad(){
        return true;
    }

    public function gen_form(){

        return '';
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
        if(isset($_mer_key)) $arg = $arg.'&key='.$_mer_key;
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
            else $para[$key] = $parameter[$key];
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
