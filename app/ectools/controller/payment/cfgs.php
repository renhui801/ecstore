<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class ectools_ctl_payment_cfgs extends desktop_controller{

    var $workground = 'ectools_ctl_payment_cfgs';

    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
        $this->finder('ectools_mdl_payment_cfgs',array(
                'title'=>app::get('ectools')->_('支付方式'),
                'use_buildin_recycle'=>false,
                'use_view_tab'=>true,
            )
        );
    }

    public function _views(){
        $o = $this->app->model('payment_cfgs');
        $all_filter = array('is_frontend' => false);
        $pc_filter = array('platform'=>'ispc', 'is_frontend' => false);
        $mobile_filter = array('platform'=>'iswap', 'is_frontend' => false);

        $all_num = count($o->getList('*',$all_filter));
        $pc_num = count($o->getList('*',$pc_filter));
        $mobile_num = count($o->getList('*',$mobile_filter));
        $show_menu = array(
            1=>array('label'=>app::get('ectools')->_('全部'),  'optional'=>false,'addon'=>$all_num,    'filter'=>$all_filter),
            2=>array('label'=>app::get('ectools')->_('标准版'),'optional'=>false,'addon'=>$pc_num,     'filter'=>$pc_filter),
            3=>array('label'=>app::get('ectools')->_('触屏版'),'optional'=>false,'addon'=>$mobile_num, 'filter'=>$mobile_filter)
        );
        return $show_menu;
    }

    function setting($pkey){


        if(!$pkey){
            return false;
        }

        if ($_POST['setting'])
        {
            $this->begin('javascript:finderGroup["'.$_POST['finder_id'].'"].refresh();');
            $payment = new $pkey($this->app);
            $setting = $payment->setting();

            foreach ($setting as $key=>$setting_item)
            {
                if ($setting_item['type'] == 'pecentage')
                {
                    $_POST['setting'][$key] = $_POST['setting'][$key] * 0.01;
                }
            }
            $data['setting'] = $_POST['setting'];
            $data['status'] = $_POST['status'];
            $data['pay_type'] = $_POST['pay_type'];
            $data['platform'] = $_POST['platform'];//支付平台

            // 是否有文件上传
            if ( $_FILES ) {
                $pos = strpos( $pkey, '_' );
                $bankName = substr( $pkey, $pos+1 );
                $destination = DATA_DIR . '/cert/' . $bankName;
                if ( !file_exists( $destination ) ) {
                    utils::mkdir_p( $destination, 0755 );
                }
                foreach ( $_FILES['setting']['error'] as $evalue ){
                    if ( $evalue == UPLOAD_ERR_OK ) {
                        foreach ( $_FILES['setting']['name'] as $nkey=>$nvalue ) {
                            $data['setting'][$nkey] = $nvalue;
                            foreach ( $_FILES['setting']['tmp_name'] as $tkey=>$tvalue ) {
                                if ( is_uploaded_file( $tvalue )) {
                                    if ( $nkey == $tkey ) {
                                        $destination = DATA_DIR . '/cert/' . $bankName . '/' . $nvalue;
                                        move_uploaded_file( $tvalue, $destination );
                                    }
                                }
                            }
                        }
                    }else{
                        $val = app::get('ectools')->getConf($pkey);
                        $val = unserialize($val);
                        foreach($_FILES['setting']['name'] as $nkey=>$nvalue){
                            $data['setting'][$nkey] = $val['setting'][$nkey];
                        }
                    }
                }
            }
            $this->app->setConf($pkey,serialize($data));
            $this->end(true, app::get('ectools')->_('支付方式修改成功！'));
        }
        else
        {
            $payment = new $pkey($this->app);
            $setting = $payment->setting();
            if($setting){
                $val = $this->app->getConf($pkey);
                $val = unserialize($val);
                $render = $this->app->render();
                $render->pagedata['admin_info'] = $payment->admin_intro();
                $render->pagedata['settings'] = $setting;
                foreach ($setting as $k=>$v)
                {
                    $render->pagedata['settings'][$k]['value'] = $val['setting'][$k] ? $val['setting'][$k] : $val[$k];
                    if ($v['type'] == 'pecentage')
                        $render->pagedata['settings'][$k]['value'] = $render->pagedata['settings'][$k]['value'] * 100;
                    if (strpos($v['type'], 'cur') !== false)
                    {
                        // 得到所有的货币
                        $currency_mdl = $this->app->model('currency');
                        $arr_curs = $currency_mdl->getSysCur(false, '', false);

                        foreach ($arr_curs as &$str_currency)
                        {
                            if (strpos($str_currency, '，') !== false)
                            {
                                $str_currency = substr($str_currency, strpos($str_currency, '，')+3);
                            }
                        }

                        if ($payment->supportCurrency)
                        {
                            foreach ($payment->supportCurrency as $key=>$str_support_cur)
                            {
                                $render->pagedata['settings'][$k]['cur_value'] .= $arr_curs[$key];
                            }
                        }
                    }
                }
                $render->pagedata['classname'] = $pkey;
                $render->display('payments/cfgs/cfgs.html');
            }else{
                echo '<div class="note">'.app::get("ectools")->_("不需要设置参数").'</div>';
            }
        }
    }
}
