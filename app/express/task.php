<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class express_task
{

    public function post_install()
    {
        logger::info('Initial express');
        kernel::single('base_initial', 'express')->init();
    }//End Function
}//End Class
