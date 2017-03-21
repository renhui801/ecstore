<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.com/license/gpl GPL License
 */
 
class base_ctl_default extends base_controller{
    
    function index(){
        $this->pagedata['project_name'] = '';
        $this->display('default.html');
    }
    
}