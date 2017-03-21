<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class weixin_finder_alert{

    public function __construct($app)
    {
        $this->app = $app;
    }
     

    var $column_edit_order = 10;
    public function detail_basic($id){
        $render = $this->app->render();
        $data = app::get('weixin')->model('alert')->getRow('*',array('id'=>$id));

        $render->pagedata['data'] = $data;
        return $render->fetch('admin/business/alert_detail.html');
    }
}
