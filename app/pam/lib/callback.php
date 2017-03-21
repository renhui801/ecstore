<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
* 登录统一调用的类，该类执行验证已经验证后的跳转
*/
class pam_callback{

	/**
	* 登录调用的方法
	* @param array $params 认证传递的参数,包含认证类型，跳转地址等
	*/
    function login($params){
        $params['module'] = utils::_filter_input($params['module']);//过滤xss攻击
        $auth = pam_auth::instance($params['type']);
        $auth->set_appid($params['appid']);
        try{
            class_exists($params['module']);
        }catch (Exception $e){
            kernel::single('site_router')->http_status('p404');
        }
        if($params['module']){
            if(class_exists($params['module']) && ($passport_module = kernel::single($params['module']))){
                if($passport_module instanceof pam_interface_passport){
                    $module_uid = $passport_module->login($auth,$auth_data);
                    if($module_uid){
                        $auth_data['account_type'] = $params['type'];
                        $auth->account()->update($params['module'], $module_uid, $auth_data);
                    }
                    $log = array(
                        'event_time'=>time(),
                        'event_type'=>$auth->type,
                        'event_data'=>base_request::get_remote_addr().':'.$auth_data['log_data'].':'.$_SERVER['HTTP_REFERER'],

                    );
                    app::get('pam')->model('log_desktop')->insert($log);
                    if(!$module_uid)$_SESSION['last_error'] = $auth_data['log_data'];
                    $_SESSION['type'] = $auth->type;
                    $_SESSION['login_time'] = time();
                    $params['member_id'] = $_SESSION['account'][$params['type']];
                    $params['uname'] = $_POST['uname'];
                    foreach(kernel::servicelist('pam_login_listener') as $service)
                    {
                        $service->listener_login($params);
                    }
                    if($params['redirect'] && $module_uid){
                        $service = kernel::service('callback_infomation');
                        if(is_object($service)){
                            if(method_exists($service,'get_callback_infomation') && $module_uid){
                                $data = $service->get_callback_infomation($module_uid,$params['type']);
                                if(!$data) $url = '';
                                else $url = '?'.utils::http_build_query($data);
                            }

                        }
                    }

                    if($_COOKIE['autologin'] > 0){
                        kernel::single('base_session')->set_cookie_expires($_COOKIE['autologin']);
                        //如果自动登录，设置cookie过期时间，单位：分
                    }
                    if($_COOKIE['S']['SIGN']['AUTO'] > 0){
                        $minutes = 10*24*60;
                        kernel::single('base_session')->set_cookie_expires($minutes);
                    }

                    if($_SESSION['callback'] && !$module_uid){
                        $callback_url = $_SESSION['callback'];
                        unset($_SESSION['callback']);
                        header('Location:' .urldecode($callback_url));
                        exit;
                    }
                     else{
                          header('Location:' .base64_decode(str_replace('%2F','/',urldecode($params['redirect']))). $url);
                          exit;
                     }

                }
            }else{

            }
        }
    }

    function __destruct(){
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog.system')){
            if(method_exists($obj_operatorlogs,'logAdminLoginInfo')){
                $obj_operatorlogs->logAdminLoginInfo($this);
            }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
    }

}
