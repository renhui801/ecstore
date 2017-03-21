<?php

class site_ctl_admin_secure_desktop extends desktop_controller
{
    public function index() 
    {
        $this->pagedata['enabled'] = app::get('site')->getConf('desktop.whitelist.enabled');
        $this->pagedata['ips'] = join(PHP_EOL ,app::get('site')->getConf('desktop.whitelist.ips'));
        $this->pagedata['error_code'] = app::get('site')->getConf('desktop.whitelist.error_code');
        $this->page('admin/secure/desktop/index.html');
    }

    public function save() 
    {
        $this->begin();
        $params = $_POST;
        if( isset($params['enabled']) && $params['enabled'] === '1') {
            $ips = explode(PHP_EOL, $params['ips']);
            if(!$params['ips'] || empty($ips)) {
                $this->end(false, app::get('site')->_('ip列表不能为空'));
            }
            app::get('site')->setConf('desktop.whitelist.enabled', true);
            $current_admin_ip = base_request::get_remote_addr();
            if( !in_array($current_admin_ip, $ips) ) {
                $ips []= $current_admin_ip;
            }
            app::get('site')->setConf('desktop.whitelist.ips', $ips);

            if(in_array( $params['error_code'], array('403', '404'))) {
                    app::get('site')->setConf('desktop.whitelist.error_code', $params['error_code']);
            } else {
                app::get('site')->setConf('desktip.whitelist.error_code', '403');
            }
        } else {
            app::get('site')->setConf('desktop.whitelist.enabled', false);
        }
            
        $this->end(true, '设置成功');
    }
}