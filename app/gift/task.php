<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class gift_task 
{
    function post_install()
    {
        logger::info('Initial gift');
        kernel::single('base_initial', 'gift')->init();
    }//End Function
}//End Class
