<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class ectools_ctl_tools extends desktop_controller{

    function __construct($app) {
        parent::__construct($app);
		header("cache-control: no-store, no-cache, must-revalidate");
        $this->app = $app;
    }

    function selRegion()
    {
        //$arrGet = $this->_request->get_get();
        $path = $_GET['path'];
        $depth = $_GET['depth'];
        
        //header('Content-type: text/html;charset=utf8');
        $local = kernel::single('ectools_regions_select');
        $ret = $local->get_area_select($this->app,$path,array('depth'=>$depth));
        if($ret){
            echo '&nbsp;-&nbsp;'.$ret;exit;
        }else{
            echo '';exit;
        }
    }
}