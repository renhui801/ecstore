<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class serveradm_ctl_admin_xhprof extends desktop_controller
{
    var $workground = 'serveradm_ctl_admin_serveradm';
    
    public function index()
    {
        $this->finder('serveradm_mdl_xhprof',array(
            'title'=>app::get('serveradm')->_("XHProf"),
            'actions'=>array()
        ));
    }
    
    public function intro()
    {
        $this->page("/admin/intro.html");
    }
    
    public function doc()
    {
        $this->page("/admin/doc.html");
    }
    
    public function show($run_id){
        /*
        $oXHProf = $this->app->model('xhprof');
        $this->pagedata["data"] = $oXHProf->read_data($run_id);
        $this->display("/admin/show_data.html");*/
        echo "<iframe src='".kernel::base_url(1)."/app/serveradm/vendor/xhprof_html/index.php?run=".$run_id."&source=xhprof' width='100%' height='100%'></iframe>";
    }
}
