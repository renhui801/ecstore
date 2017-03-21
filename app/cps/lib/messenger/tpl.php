<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class cps_messenger_tpl{
    
    public function __construct($app) {
        $this->app = $app;
    }

    /**
     * 获取当前消息模板的加载app
     * @access public
     * @param string $aTmpl 模板key值
     * @param string $app_id app标示
     * @return null
     * @version 1 Jun 28, 2011 创建
     */
    public function get_app_id($aTmpl,&$app_id){

        $actions = kernel::single('cps_service_firevent_action')->get_type();
        foreach($actions as $key =>$val){
            if($key == $aTmpl){
                $app_id = $this->app->app_id;
                break;
            }
        }
    }

}