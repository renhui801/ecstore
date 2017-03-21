<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class site_service_view_helper 
{
    function function_header($params, &$smarty)
    {
        return $smarty->fetch('header.html', app::get('site')->app_id);
    }//End Function

}//End Class