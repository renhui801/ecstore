<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class logisticstrack_ctl_admin_tracker extends desktop_controller{

    public function __construct($app) {
        parent::__construct($app);
    }

    public function index(){
        if($_POST){
            $system_order_tracking = $_POST['system_order_tracking'];
            $kuaidi100Key = trim($_POST['kuaidi100Key']);
            app::get('b2c')->setConf('system.order.tracking',$system_order_tracking);
            app::get('logisticstrack')->setConf('kuaidi100Key',$kuaidi100Key);
            $this->pagedata['system_order_tracking'] = $system_order_tracking;
            $this->pagedata['kuaidi100Key'] = $kuaidi100Key;
        }else{
            $this->pagedata['system_order_tracking'] = app::get('b2c')->getConf('system.order.tracking');
            $this->pagedata['kuaidi100Key'] = app::get('logisticstrack')->getConf('kuaidi100Key');
        }
        $this->page('admin/setting.html');
    }

    public function pull($deliveryid) {
        header("cache-control: no-store, no-cache, must-revalidate");
        header('Expires: Fri, 16 Dec 2000 10:38:27 GMT');
        header('Content-Type: text/html; charset=UTF-8');

        if ( logisticstrack_puller::pull_logi($deliveryid, $data) ) {
            $this->pagedata['logi'] = $data['data'];
            $this->pagedata['logi_source'] = $data['source'];
        } else {
            $this->pagedata['logi_error'] = $data['msg'];
        }
        $this->display('admin/logistic_detail.html');
    }
}
