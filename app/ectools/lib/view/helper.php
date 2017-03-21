<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class ectools_view_helper{

    function __construct($app){
        $this->app = $app;
    }
    function modifier_barcode($data){
        return kernel::single('ectools_barcode')->get($data);
    }

    function modifier_payname($data){
        return $this->app->model('payment_cfgs')->get_app_display_name($data);
    }
}
