<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 */
class wap_errorpage_get
{
    
    
    
    public function getConf($key='') {
        if( $key )
            return app::get('wap')->getConf($key);
        else return false;
    }
}