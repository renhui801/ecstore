<?php
/**
 *
 *  微信商户功能基本配置
 */
class weixin_ctl_admin_business_setting extends desktop_controller{

    var $workground = 'wap.workground.weixin';

    /*
     * @param object $app
     */
    function __construct($app)
    {
        parent::__construct($app);
        $this->ui = new base_component_ui($this);
        $this->app = $app;
        header("cache-control: no-store, no-cache, must-revalidate");

    }//End Function

    public function index(){
        $this->pagedata['wxpay_classname']='weixin_payment_plugin_wxpay';
        $this->page('admin/business/setting.html');
    }

}
