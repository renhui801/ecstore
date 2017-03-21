<?php
final class ectools_payment_plugin_ibankinginner extends ectools_payment_app implements ectools_interface_payment_app
{
    //支付方式名称
    public $name = '网银在线(内卡)';

    //支付方式接口名称
    public $app_name = '网银在线内卡支付';

    //支付方式key（系统使用）
    public $app_key = 'ibankinginner';

    //中心化统一管理key（系统使用）
    public $app_rpc_key = 'ibankinginner';

    //显示名称
    public $display_name = '网银在线（内卡）';

    //货币名称
    public $curname = 'CNY';

    //版本号（系统使用）
    public $ver = '1.0';

    //支付方式是pc端还是wap端
    public $platform = 'ispc';

    //支持币种
    public $supportCurrency = array('CNY'=>'CNY');
    
    public $gateway="https://pay3.chinabank.com.cn/PayGate?encoding=UTF-8";



    //构造函数
    public function __construct($app)
    {
        parent::__construct($app);
        $this->notify_url = kernel::openapi_url('openapi.ectools_payment/parse/'.$this->app->app_id.'/ectools_payment_plugin_ibankinginner_server','callback');
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
        $this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_ibankinginner', 'callback');
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
        $this->submit_url = 'https://pay3.chinabank.com.cn/PayGate?encoding=UTF-8';
        $this->submit_method = 'POST';
        $this->submit_charset = 'utf-8';
    }

    //后台支付方式介绍
    public function admin_intro()
    {
        return app::get('ectools')->_( '<div><p >网银在线（北京）科技有限公司（以下简称网银在线）为京东商城（www.jd.com）全资子公司，是国内领先的电子支付解决方案提供商，专注于为各行业提供安全、便捷的综合电子支付服务。网银在线成立于2003年，现有员工200余人，由具有丰富的金融行业经验和互联网运营经验的专业团队组成，致力于通过创新型的金融服务，支持现代服务业的发展。凭借丰富的产品线、卓越的创新能力，网银在线受到各级政府部门和银行金融机构的高度重视和认可，于2011年5月3日首批荣获央行《支付业务许可证》，并任中国支付清算协会理事单位。</p><br /></div>');
    }

    public function intro()
    {
        return app::get('ectools')->_( '<div><p >网银在线（北京）科技有限公司（以下简称网银在线）为京东商城（www.jd.com）全资子公司，是国内领先的电子支付解决方案提供商，专注于为各行业提供安全、便捷的综合电子支付服务。网银在线成立于2003年，现有员工200余人，由具有丰富的金融行业经验和互联网运营经验的专业团队组成，致力于通过创新型的金融服务，支持现代服务业的发展。凭借丰富的产品线、卓越的创新能力，网银在线受到各级政府部门和银行金融机构的高度重视和认可，于2011年5月3日首批荣获央行《支付业务许可证》，并任中国支付清算协会理事单位。</p><br /></div>');
    }

    //后台配置
    public function setting()
    {
        return array(
                    'pay_name'=>array(
                        'title'=>app::get('ectools')->_('支付方式名称'),
                        'type'=>'string',
						'validate_type' => 'required',
                    ),
                    'mer_id'=>array(
                        'title'=>app::get('ectools')->_('商户号'),
                        'type'=>'string',
						'validate_type' => 'required',
                    ),
                    'mer_key'=>array(
                        'title'=>app::get('ectools')->_('交易安全校密钥(key)'),
                        'type'=>'string',
						'validate_type' => 'required',
                    ),
                    'order_by' =>array(
                        'title'=>app::get('ectools')->_('排序'),
                        'type'=>'string',
                        'label'=>app::get('ectools')->_('整数值越小,显示越靠前,默认值为1'),
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
						'includeBase'=>true,
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
						'name'=>'status',
					),
                );
    }

    public function dopay($payment)
    {
        $mer_id = $this->getConf('mer_id', __CLASS__);
        $mer_key = $this->getConf('mer_key', __CLASS__);
        $pay = array(
            'v_mid'=>$mer_id,
            'v_oid'=>$payment['payment_id'],
            'v_amount'=>$payment['cur_money'],
            'v_moneytype'=>$this->curname,
            'v_url'=>$this->callback_url,
            'remark1'=>$payment['shopName'],
            'remark2'=>'[url:='.$this->notify_url.']',
        );
        $pay['v_md5info'] = $this->make_sign($pay, $mer_key);
        foreach($pay as $k=>$v)
        {
            $this->add_field($k, $v);
        }
        echo $this->get_html();exit;
    }
        
    //验证方法
    public function is_fields_valiad(){
        return true;
    }

    public function callback(&$recv)
    {
        $mer_id = $this->getConf('mer_id', __CLASS__);
        $mer_key = $this->getConf('mer_key', __CLASS__);

        if($this->is_return_valiad($recv, $mer_key))
        {
            $ret['payment_id'] = $recv['v_oid'];
            $ret['account'] = $mer_id;
            $ret['bank'] = app::get('ectools')->_('网银在线');
            $ret['pay_account'] = app::get('ectools')->_('付款帐号');
            $ret['currency'] = $recv['v_moneytype'];
            $ret['money'] = $recv['v_amount'];
            $ret['paycost'] = '0.000';
            $ret['cur_money'] = $recv['v_amount'];
            $ret['trade_no'] = $recv['v_oid'];
            $ret['t_payed'] = time();
            $ret['pay_app_id'] = 'ibankinginner';
            $ret['pay_type'] = 'online';
            $ret['memo'] = $recv['v_pstring'];
            if($recv['v_pstatus'] == '20')
            {
 
                $ret['status'] = 'succ';
            }
            else
            {
                $ret['status'] = 'failed';
            }
        }else{
            $ret['message'] = 'Invalid Sign';
            $ret['status'] = 'invalid';
        }
        return $ret;
    }

    public function gen_form()
    {
        return '';
    }

    private function is_return_valiad($recv, $key)
    {
        $sign = $this->make_return_sign($recv, $key);
        return ($sign == $recv['v_md5str']) ? true : false;
    }

    private function make_return_sign($recv, $key)
    {
        $linkstring = $recv['v_oid'];
        $linkstring .= $recv['v_pstatus'];
        $linkstring .= $recv['v_amount'];
        $linkstring .= $recv['v_moneytype'];
        $linkstring .= $key;
        return strtoupper(md5($linkstring));
    }

    private function make_sign($payment, $mer_key)
    {
        $linkstring = $payment['v_amount'];
        $linkstring .= $payment['v_moneytype'];
        $linkstring .= $payment['v_oid'];
        $linkstring .= $payment['v_mid'];
        $linkstring .= $payment['v_url'];
        $linkstring .= $mer_key;
        return strtoupper(md5($linkstring));
    }
}

