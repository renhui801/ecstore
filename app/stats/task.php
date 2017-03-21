<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class stats_task  
{		
	/**
	 * before install
	 * @param null
	 * @return null
	 */
    public function post_install() 
    {		
		app::get('stats')->setConf('SHOPEX_STAT_ADMIN', array());
        app::get('stats')->setConf('site.rsc_rpc', 1);
    }//End Function
	
	public function post_uninstall()
	{
		app::get('stats')->setConf('SHOPEX_STAT_ADMIN', array());
        app::get('stats')->setConf('site.rsc_rpc', 0);
    }//End Function
}//End Class
