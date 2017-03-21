<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class site_task 
{
    function post_install() 
    {
        logger::info('Initial themes');
        kernel::single('site_theme_base')->set_last_modify();
        kernel::single('site_theme_install')->initthemes();
        $themes = kernel::single('site_theme_install')->check_install();
    }//End Function

    function post_update($params){
        $dbver = $params['dbver'];
        if(version_compare($dbver,'1.0.6','<')){
            //更新widgets css最后更新时间
            site_widgets::set_last_modify();

            //重新创建module sitemap
            kernel::single('site_module_base')->create_site_config();

            //缓存全部更新, 改造了缓存机制
            cachemgr::clean($msg);
        }        
    }
}//End Class
