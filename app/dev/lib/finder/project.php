<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.com/license/gpl GPL License
 */
 
class dev_finder_project{

    var $detail_basic = '信息';
    
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function detail_basic($order_id)
    {
        $render = $this->app->render();
        return $render->fetch('project/detail.html');
    }
}
