<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class site_finder_callback_modules 
{
    public function recycle($params) 
    {
        return kernel::single('site_module_base')->create_site_config();
    }//End Function

}//End Class
