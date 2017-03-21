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
final class wap_payment_plugin_malipay extends ectools_payment_app implements ectools_interface_payment_app {

    /**
     * @var string 支付方式名称
     */
    public $name = '手机支付宝';
    /**
     * @var string 支付方式接口名称
     */
    public $app_name = '手机支付宝';
     /**
     * @var string 支付方式key
     */
    public $app_key = 'malipay';
    /**
     * @var string 中心化统一的key
     */
    public $app_rpc_key = 'malipay';
    /**
     * @var string 统一显示的名称
     */
    public $display_name = '手机支付宝';
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
     * @支付宝固定参数
     */
    public $Service_Paychannel = "mobile.merchant.paychannel";
    public $Service1 = "alipay.wap.trade.create.direct";    //接口1
    public $Service2 = "alipay.wap.auth.authAndExecute";    //接口2
    public $format = "xml";    //http传输格式
    public $sec_id = 'MD5';    //签名方式 不需修改
    public $_input_charset = 'utf-8';    //字符编码格式
    public $_input_charset_GBK = "GBK";
    public $v = '2.0';    //版本号
    public $gateway_paychannel="https://mapi.alipay.com/cooperate/gateway.do?";
    public $gateway="http://wappaygw.alipay.com/service/rest.htm?";


    /**
     * 构造方法
     * @param null
     * @return boolean
     */
    public function __construct($app){
        parent::__construct($app);

        $this->notify_url = kernel::openapi_url('openapi.ectools_payment/parse/wap/wap_payment_plugin_malipay_server', 'callback');
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
        $this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/wap/wap_payment_plugin_malipay', 'callback');
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
        $this->submit_url = $this->gateway . '_input_charset=' . $this->_input_charset;
        $this->submit_method = 'GET';
        $this->submit_charset = $this->_input_charset;
    }

    /**
     * 后台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    public function admin_intro(){
        $regIp = isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:$_SERVER['HTTP_HOST'];
        return '<img src="' . $this->app->res_url . '/payments/images/ALIPAY.gif"><br /><b style="font-family:verdana;font-size:13px;padding:3px;color:#000"><br>ShopEx联合支付宝推出优惠套餐：无预付/年费，单笔费率低至0.7%-1.2%，无流量限制。</b><div style="padding:10px 0 0 388px"><a  href="javascript:void(0)" onclick="document.ALIPAYFORM.submit();"><img src="' . $this->app->res_url . '/payments/images/alipaysq.png"></a></div><div>如果您已经和支付宝签约了其他套餐，同样可以点击上面申请按钮重新签约，即可享受新的套餐。<br>如果不需要更换套餐，请将签约合作者身份ID等信息在下面填写即可，<a href="http://www.shopex.cn/help/ShopEx48/help_shopex48-1235733634-11323.html" target="_blank">点击这里查看使用帮助</a><form name="ALIPAYFORM" method="GET" action="http://top.shopex.cn/recordpayagent.php" target="_blank"><input type="hidden" name="postmethod" value="GET"><input type="hidden" name="payagentname" value="支付宝"><input type="hidden" name="payagentkey" value="ALIPAY"><input type="hidden" name="market_type" value="from_agent_contract"><input type="hidden" name="customer_external_id" value="C433530444855584111X"><input type="hidden" name="pro_codes" value="6AECD60F4D75A7FB"><input type="hidden" name="regIp" value="'.$regIp.'"><input type="hidden" name="domain" value="'.$this->app->base_url(true).'"></form></div>';
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
                'title'=>app::get('wap')->_('合作者身份(parterID)'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'mer_key'=>array(
                'title'=>app::get('wap')->_('交易安全校验码(key)'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'seller_account_name'=>array(
                'title'=>app::get('wap')->_('支付宝账号'),
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
            'pay_type'=>array(
                'title'=>app::get('wap')->_('支付类型(是否在线支付)'),
                'type'=>'radio',
                'options'=>array('true'=>app::get('wap')->_('是')),
                'name' => 'pay_type',
                'validate_type' => 'requiredradio',
            ),
            'status'=>array(
                'title'=>app::get('wap')->_('是否开启此支付方式'),
                'type'=>'radio',
                'options'=>array('false'=>app::get('wap')->_('否'),'true'=>app::get('wap')->_('是')),
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
        return app::get('wap')->_('支付宝（中国）网络技术有限公司是国内领先的独立第三方支付平台，由阿里巴巴集团创办。支付宝致力于为中国电子商务提供“简单、安全、快速”的在线支付解决方案。').'
<a target="_blank" href="https://www.alipay.com/static/utoj/utojindex.htm">'.app::get('wap')->_('如何使用支付宝支付？').'</a>';
    }

    /**
     * 提交支付信息的接口
     * @param array 提交信息的数组
     * @return mixed false or null
     */
    public function dopay($payment)
    {
        $mer_id = trim($this->getConf('mer_id', __CLASS__));
        $mer_key = trim($this->getConf('mer_key', __CLASS__));
        $seller_account_name = trim($this->getConf('seller_account_name', __CLASS__));

        $subject = $payment['account'].$payment['payment_id'];
        $subject = str_replace("'",'`',trim($subject));
        $subject = str_replace('"','`',$subject);

        $merchant_url = '';
        if (isset($payment['subject']) && $payment['subject']){
            $subject_tmp = $payment['subject'];
        }else{
            $subject_tmp = $subject;
        }
        $price = number_format($payment['cur_money'],2,".","");

        $pms_0 = array (
            "_input_charset" => $this->_input_charset_GBK,
            "sign_type" => $this->sec_id,
            "service" => $this->Service_Paychannel,
            "partner" => $mer_id,
            "out_user" => ''
        );
        $result = $this->mobile_merchant_paychannel($pms_0,$mer_key);
        // if($result != "验签失败"){
            // 调用alipay_wap_trade_create_direct接口，并返回token返回参数
            $pms_1 = array (
                "req_data"      => '<direct_trade_create_req><subject>' . $subject_tmp . '</subject><out_trade_no>' .
               $payment['payment_id'] . '</out_trade_no><total_fee>' . $price  . "</total_fee><seller_account_name>" . $seller_account_name .
                "</seller_account_name><notify_url>" . $this->notify_url . "</notify_url><out_user>" . '' .
                "</out_user><merchant_url>" . $merchant_url . "</merchant_url><cashier_code>" . '' .
                "</cashier_code>" . "<call_back_url>" . $this->callback_url . "</call_back_url></direct_trade_create_req>",
                "service"       => $this->Service1,
                "sec_id"        => $this->sec_id,
                "partner"       => $mer_id,
                "req_id"        => date("Ymdhis"),
                "format"        => $this->format,
                "v"             => $this->v
            );
            $token=$this->alipay_wap_trade_create_direct($pms_1,$mer_key);

            // if($token != '签名不正确'){
                // 验证和发送信息与跳转手机支付宝收银台.
                $req_data = '<auth_and_execute_req><request_token>'.$token.'</request_token></auth_and_execute_req>';
                $pms2 = array (
                    "req_data"      => $req_data,
                    "service"       => $this->Service2,
                    "sec_id"        => $this->sec_id,
                    "partner"       => $mer_id,
                    "call_back_url" => $this->callback_url,
                    "format"        => $this->format,
                    "v"             => $this->v
                );
                $parameter = $this->para_filter($pms2);
                $mysign    = $this->build_mysign($this->arg_sort($parameter), $mer_key, $this->sec_id);
                $this->add_field('req_data',$req_data);
                $this->add_field('service',$this->Service2);
                $this->add_field('sec_id',$this->sec_id);
                $this->add_field('partner',$mer_id);
                $this->add_field('call_back_url',$this->callback_url);
                $this->add_field('format',$this->format);
                $this->add_field('v',$this->v);
                $this->add_field('sign',urlencode($mysign));

                echo $this->get_html();exit;
        //     }else{
        //         echo '签名不正确';
        //         return false;
        //     }
        // }else{
        //     return false;
        // }
    }

    /**
     * 创建mobile_merchant_paychannel接口
    */
    function mobile_merchant_paychannel($pms0, $merchant_key) {
        $_key = $merchant_key;                       //MD5校验码
        $sign_type    = $pms0['sign_type'];          //签名类型，此处为MD5
        $parameter = $this->para_filter($pms0);      //除去数组中的空值和签名参数
        $sort_array = $this->arg_sort($parameter);   //得到从字母a到z排序后的签名参数数组
        $mysign = $this->build_mysign($sort_array, $_key, $sign_type); //生成签名
        $req_data = $this->create_linkstring($parameter).'&sign='.urlencode($mysign).'&sign_type='.$sign_type;  //配置post请求数据，注意sign签名需要urlencode

        //模拟get请求方法
        //$result = $this->get($this->gateway_paychannel,$req_data);
        $url = $this->gateway_paychannel . $req_data;
        $result = kernel::single('base_httpclient')->get($url);
        //调用处理Json方法
        $alipay_channel = $this->getJson($result,$_key,$sign_type);
        return $alipay_channel;
    }

    /**
     * 验签并反序列化Json数据
     */
    function getJson($result,$m_key,$m_sign_type){
        //获取返回的Json
        // $json = $this->getDataForXML($result,'/alipay/response/alipay/result');
        $xmlData = $this->getDataForXML($result);
        $json = $xmlData['alipay']['response']['alipay']['result'];
        //拼装成待签名的数据
        $data = "result=" . $json . $m_key;
        //$json="{\"payChannleResult\":{\"supportedPayChannelList\":{\"supportTopPayChannel\":{\"name\":\"储蓄卡快捷支付\",\"cashierCode\":\"DEBITCARD\",\"supportSecPayChannelList\":{\"supportSecPayChannel\":[{\"name\":\"农行\",\"cashierCode\":\"DEBITCARD_ABC\"},{\"name\":\"工行\",\"cashierCode\":\"DEBITCARD_ICBC\"},{\"name\":\"中信\",\"cashierCode\":\"DEBITCARD_CITIC\"},{\"name\":\"光大\",\"cashierCode\":\"DEBITCARD_CEB\"},{\"name\":\"深发展\",\"cashierCode\":\"DEBITCARD_SDB\"},{\"name\":\"更多\",\"cashierCode\":\"DEBITCARD\"}]}}}}}";
        //获取返回sign
        // $aliSign = $this->getDataForXML($result,'/alipay/sign');
        $aliSign = $xmlData['alipay']['sign'];
        //转换待签名格式数据，因为此mapi接口统一都是用GBK编码的，所以要把默认UTF-8的编码转换成GBK，否则生成签名会不一致
        $data_GBK = mb_convert_encoding($data, "GBK", "UTF-8");
        //生成自己的sign
        $mySign = $this->sign($data_GBK,$m_sign_type);
        //判断签名是否一致
        if($mySign==$aliSign){
            //echo "签名相同";
            //php读取json数据
            return json_decode($json);
        }else{
            //echo "验签失败";
            return "验签失败";
        }
    }


    /**
     * 创建alipay.wap.trade.create.direct接口
     */
    public function alipay_wap_trade_create_direct($pms1, $merchant_key){
        $_key       = $merchant_key;                  //MD5校验码
        $sign_type  = $pms1['sec_id'];              //签名类型，此处为MD5
        $parameter  = $this->para_filter($pms1);      //除去数组中的空值和签名参数
        $req_data   = $pms1['req_data'];
        $format     = $pms1['format'];                //编码格式，此处为utf-8
        $sort_array = $this->arg_sort($parameter);    //得到从字母a到z排序后的签名参数数组
        $mysign     = $this->build_mysign($sort_array, $_key, $sign_type);    //生成签名
        $req_data   = $this->create_linkstring($parameter).'&sign='.urlencode($mysign);    //配置post请求数据，注意sign签名需要urlencode

        //Post提交请求
        //$res = $this->post($this->gateway,$req_data);
        $url = $this->gateway.$req_data;
        $res = kernel::single('base_httpclient')->get($url);
        //调用GetToken方法，并返回token
        return $this->getToken($res,$_key,$sign_type);
    }

    /**
     * 返回token参数
     * 参数 result 需要先urldecode
     */
    function getToken($result,$_key,$gt_sign_type){
        $result = urldecode($result);               //URL转码
        $Arr = explode('&', $result);               //根据 & 符号拆分

        $temp = array();                            //临时存放拆分的数组
        $myArray = array();                         //待签名的数组
        //循环构造key、value数组
        for ($i = 0; $i < count($Arr); $i++) {
            $temp = explode( '=' , $Arr[$i] , 2 );
            $myArray[$temp[0]] = $temp[1];
        }

        $sign = $myArray['sign'];                                               //支付宝返回签名
        $myArray = $this->para_filter($myArray);                                       //拆分完毕后的数组
        $sort_array = $this->arg_sort($myArray);                                       //排序数组
        $mysign = $this->build_mysign($sort_array,$_key,$gt_sign_type); //构造本地参数签名，用于对比支付宝请求的签名

        if($mysign == $sign)  //判断签名是否正确
        {
            $xmlData = $this->getDataForXML($myArray['res_data']);
            // return $this->getDataForXML($myArray['res_data'],'/direct_trade_create_res/request_token');    //返回token
            return $xmlData['direct_trade_create_res']['request_token'];    //返回token
        }else{
            echo('签名不正确');      //当判断出签名不正确，请不要验签通过
            return '签名不正确';
        }
    }


    /**
     * 支付后返回后处理的事件的动作
     * @params array - 所有返回的参数，包括POST和GET
     * @return null
     */
    public function callback(&$recv)
    {
        #键名与pay_setting中设置的一致
        $mer_id = trim( $this->getConf('mer_id', __CLASS__) );
        $mer_id = $mer_id;
        $mer_key = trim( $this->getConf('mer_key', __CLASS__) );
        $mer_key = $mer_key;

        if($this->is_return_vaild($recv,$mer_key,$this->sec_id)){
            $ret['payment_id'] = $recv['out_trade_no'];
            $ret['account'] = $mer_id;
            $ret['bank'] = app::get('wap')->_('手机支付宝');
            $ret['pay_account'] = app::get('wap')->_('付款帐号');
            $ret['currency'] = 'CNY';
            $ret['money'] = $recv['total_fee'];
            $ret['paycost'] = '0.000';
            $ret['cur_money'] = $recv['total_fee'];
            $ret['trade_no'] = $recv['trade_no'];
            $ret['t_payed'] = strtotime($recv['notify_time']) ? strtotime($recv['notify_time']) : time();
            $ret['pay_app_id'] = "malipay";
            $ret['pay_type'] = 'online';
            $ret['memo'] = $recv['body'];

            if($recv['result'] == 'success') {
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

        if ($mysign == $form['sign']) {
            return true;
        }
        #记录返回失败的情况
        logger::error(app::get('wap')->_('支付单号：') . $form['out_trade_no'] . app::get('wap')->_('签名验证不通过，请确认！')."\n");
        logger::error(app::get('wap')->_('本地产生的加密串：') . $mysign);
        logger::error(app::get('wap')->_('手机支付宝传递打过来的签名串：') . $form['sign']);
        $str_xml .= "<alipayform>";
        foreach ($form as $key=>$value){
            $str_xml .= "<$key>" . $value . "</$key>";
        }
        $str_xml .= "</alipayform>";

        return false;
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
    public function gen_form()
    {
      return '';
    }


//↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓公共函数部分↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

    /**生成签名结果
     * $array要签名的数组
     * return 签名结果字符串
     */
    public function build_mysign($sort_array,$key,$sign_type = "MD5") {
        $prestr = $this->create_linkstring($sort_array);         //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $prestr.$key;                            //把拼接后的字符串再与安全校验码直接连接起来
        $mysgin = $this->sign($prestr,$sign_type);                //把最终的字符串签名，获得签名结果
        return $mysgin;
    }


    /**把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * $array 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    public function create_linkstring($array) {
        $arg  = "";
        while (list ($key, $val) = each ($array)) {
            $arg.=$key."=".$val."&";
        }
        $arg = substr($arg,0,count($arg)-2);             //去掉最后一个&字符
        return $arg;
    }


    /**除去数组中的空值和签名参数
     * $parameter 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    public function para_filter($parameter) {
        $para = array();
        while (list ($key, $val) = each ($parameter)) {
            if($key == "sign" || $key == "sign_type" || $val == "")continue;
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
        }elseif($sign_type =='DSA') {
            //DSA 签名方法待后续开发
            die("DSA 签名方法待后续开发，请先使用MD5签名方式");
        }else {
            die("支付宝暂不支持".$sign_type."类型的签名方式");
        }
        return $sign;
    }

    /**
     * 通过节点路径返回字符串的某个节点值
     * $res_data——XML 格式字符串
     * 返回节点参数
     */
    function getDataForXML($res_data)
    {
        return kernel::single('site_utility_xml')->xml2array($res_data);
        // $xml = simplexml_load_string($res_data);
        // $result = $xml->xpath($node);

        // while(list( , $node) = each($result))
        // {
        //     return $node;
        // }
    }

//↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑公共函数部分↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

}
