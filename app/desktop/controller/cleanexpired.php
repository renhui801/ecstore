<?php
class desktop_ctl_cleanexpired extends desktop_controller{
    var $certcheck = false;

    function index(){
        $this->page('cleanexpired.html');
    }

    function clean_data(){
        kernel::single('base_cleandata')->clean();
        //退出登录 
        $this->begin('javascript:Cookie.dispose("basicloginform_password");Cookie.dispose("basicloginform_autologin");location="'.kernel::router()->app->base_url(1).'"');
        $this->user->login();
        $this->user->logout();
        $auth = pam_auth::instance(pam_account::get_account_type($this->app->app_id));
        foreach(kernel::servicelist('passport') as $k=>$passport){
            if($auth->is_module_valid($k,$this->app->app_id))
                $passport->loginout($auth,$backurl);
        }
        kernel::single('base_session')->destory();
        $this->end('true',app::get('desktop')->_('已成功退出系统,正在转向...'));
    }
}
