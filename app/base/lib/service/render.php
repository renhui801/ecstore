<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class base_service_render 
{
    public function pre_display(&$content) 
    {
        $content = base_storager::image_storage($content);
    }//End Function

}//End Class