<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class desktop_ctl_code extends base_controller
{
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }
    
    //激活码校验
    public function codecheck()
    {
        //if ($_POST['auth_code'] && preg_match("/^\d{19}$/", substr($_POST['auth_code'], 1)))
        if ($_POST['auth_code'])
        {
            $code = kernel::single('desktop_cert_certcheck');
            $shopexIdUrl = app::get('base')->getConf('certificate_code_url');
            if( $shopexIdUrl && $shopexIdUrl != kernel::base_url(1) ){
                $result['res'] = 'false';
            }else{
                $result = $code->check_code($_POST['auth_code']);
            }
            if ($result['res'] == 'succ' && $result)
            {
                $activation_arr = $_POST['auth_code'];
                app::get('desktop')->setConf('activation_code', $activation_arr);
                if( !app::get('base')->getConf('certificate_code_url') )
                    app::get('base')->setConf('certificate_code_url',kernel::base_url(1));

                $objArr = kernel::servicelist("desktop.cert.succ");
                foreach ($objArr as $obj)
                {
                    if(method_exists($obj , 'notify')){
                        $obj->notify($result);
                    }
                }

                header('Location:' .kernel::router()->app->base_url(1));
                exit;
            }
            else
            {
                switch ($result['msg'])
                {
                    case 'key_false_type':
                        $auth_error_msg = '激活码类型不对!';break;
                    case 'key_false_ac':
                        $auth_error_msg = '验证标签错误!';break;
                    case 'key_false_expir':
                        $auth_error_msg = '此激活码大于最大有效期限制!';break;
                    case 'key_false_times':
                        $auth_error_msg = '失败：您已经连续6次提交失败，为了您的网店安全，请3小时后再次尝试|';break;
                    case 'key_false_key':
                        $auth_error_msg = '无效的激活码，请您重新输入激活码以便正常使用。';break;
                    case 'key_false_actived':
                        $auth_error_msg = '您的激活码已经失效，请您重新输入激活码以便正常使用。';break;
                    case 'key_false_oem':
                        $auth_error_msg = '您的网店License与输入的激活码类型不一，请联系激活码销售商!';break;
                    case 'key_false_type_1':
                        $auth_error_msg = '您的网店License与输入的激活码类型不一，请联系激活码销售商!';break;
                    case 'key_false_type_2':
                        $auth_error_msg = '您的网店License与输入的激活码类型不一，请联系激活码销售商!';break;
                    case 'certificate_id_is_false':
                        $auth_error_msg = ' 您的网店证书有误，请查证!';break;
                    case 'temp_key_false':
                        $auth_error_msg = '临时激活码激活失败,此站点已经用正式激活码激活过了，不能再用临时激活码';break;                                                
                    case 'active_key_false':
                        $auth_error_msg = '激活错误,此激活码已被使用或者激活码输入错误';break;
                }

                die($this->error_view($auth_error_msg));
            }

            header("Location: index.php");
            exit();
        }
    }

    function error_info_view(){
        $render =  app::get('desktop')->render();
        $result = $_GET['result'];
        app::get('desktop')->setConf('activation_code_check', false);
        $render->pagedata['error_code'] = $result['msg'];
        $render->pagedata['shopexUrl'] = app::get('base')->getConf('certificate_code_url');
        $render->pagedata['shopexId'] = base_enterprise::ent_id();

        switch($result['msg']){
            case "invalid_version":
                $msg = "版本号有误，查看mysql是否运行正常"; break;
            case "RegUrlError":
                $msg = "你当前使用的域名与证书所绑定的域名不一致。";break;
            case "SessionError":
                $msg = "中心请求网店API失败!，请联系您的服务商，或找贵公司相关人员检测网络，以确保网络正常"; break;
            case "license_error":
                $msg = "证书号错误!"; 
                $Certi = base_certificate::get('certificate_id');
                if( !$Certi ){
                    $msg .= "查询不到证书，请确认config/certi.php文件是否存在"; 
                }
                break;
            case "method_not_exist":
                $msg = "接口方法不存在!"; break;
            case "method_file_not_exist":
                $msg = "接口文件不存在!"; break;
            case "NecessaryArgsError":
                $msg = "缺少必填参数!"; break;
            case "ProductTypeError":
                $msg = "产品类型错误!"; break;
            case "UrlFormatUrl":
                $msg = "URL格式错误!"; break;
            case "invalid_sign":
                $msg = "验签错误!"; break;
            default:
                $msg = null;break;
        }
        if($result == null){
            $msg = "请检测您的服务器域名解析是否正常！";
            $fp = fsockopen("service.shopex.cn", 80, $errno, $errstr, 30);
            if (!$fp) {
                $render->pagedata['fsockopen'] = 'fsockopen解析service.shopex.cn错误，请确认是否将fsockopen函数屏蔽</br>错误信息：'.$errstr;
            }
        }

        $url = $this->app->base_url(1);
        $code_url = $url.'index.php?app=desktop&ctl=code&act=error_view';
        $order_url = $url.'index.php/shopadmin/#app=b2c&ctl=admin_order&act=index';
        $cleanexpired_url = $url.'index.php/shopadmin/#ctl=adminpanel';
        $render->pagedata['msg'] = ($msg)?$msg:"";
        $render->pagedata['url'] = $url;
        $render->pagedata['order_url'] = $order_url;
        $render->pagedata['code_url'] = $code_url;
        $render->pagedata['cleanexpired_url'] = $cleanexpired_url;
        echo  $render->fetch('codetip.html');
        exit;
    }
    
    function error_view($auth_error_msg)
    {
        $Certi = base_certificate::get('certificate_id');
        if( !$Certi && !$auth_error_msg ){
            $auth_error_msg = "查询不到证书，请确认config/certi.php文件是否存在"; 
        }
        $render = app::get('desktop')->render();
        $shopexIdUrl = app::get('base')->getConf('certificate_code_url');
        if( $shopexIdUrl && $shopexIdUrl != kernel::base_url(1) ){
		    $render->pagedata['url'] = $shopexIdUrl;
        }
        $url = $this->app->base_url(1);
        $render->pagedata['post_url'] = $url.'index.php?app=desktop&ctl=code&act=codecheck';
        $render->pagedata['res_url'] = app::get('desktop')->res_url;
        $render->pagedata['auth_error_msg'] = $auth_error_msg;
        echo $render->display('active_code.html');
        exit;
    }
    
}
