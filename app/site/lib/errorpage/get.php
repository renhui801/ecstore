<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 */
class site_errorpage_get
{



    public function getConf($key='') {
        if( $key )
            return app::get('site')->getConf($key);
        else return false;
    }
}
