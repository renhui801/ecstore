<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

/** 
 * 后台桌面首页边上的面板
 * controller class
 */
class b2c_sidepanel_member extends desktop_controller{

    function __construct($app){
        $this->tag_type = 'members';
        $this->app = $app;
    }
}
