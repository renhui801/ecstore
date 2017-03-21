<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

#站点
class operatorlog_site{

    function __construct(){
        $this->objlog = kernel::single('operatorlog_service_desktop_controller');
    }

    // 记录站点配置日志
    function logSiteConfigInfo($confinName,$pre_config,$now_config){
        $memo .= '配置 ' . $confinName . ' 由 '. $pre_config . ' 修改为 ' . $now_config;
        $this->objlog->logs('site', '站点配置', $memo);
    }

}//End Class
