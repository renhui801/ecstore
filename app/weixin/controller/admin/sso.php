<?php
/**
 * 微信免登陆配置
 */
class weixin_ctl_admin_sso extends desktop_controller{

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
        $this->pagedata['bind_url'] = kernel::single('wap_frontpage')->gen_url(array('app'=>'b2c','ctl'=>'wap_passport','act'=>'loginbind','full'=>'1'));
        $this->pagedata['setting'] = app::get('weixin')->getConf('weixin_sso_setting');
        $this->page('admin/sso.html');
    }

    public function save_setting(){
        $this->begin();
        $weixin_sso_setting['sso_url_type'] = $_POST['sso_url_type']; 
        $weixin_sso_setting['noBindText'] = $_POST['noBindText']; 
        $weixin_sso_setting['bindSuccText'] = $_POST['bindSuccText']; 
        if( app::get('weixin')->setConf('weixin_sso_setting',$weixin_sso_setting) ){
            $this->end(true,app::get('weixin')->_('保存成功'));
        }else{
            $this->end(false,app::get('weixin')->_('保存失败'));
        }
    }
}
