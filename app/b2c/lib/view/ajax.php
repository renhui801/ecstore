<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_view_ajax{

    function get_html($html,$class_name,$method_name)
    {
        $obj = kernel::service('replace.ajax.html');
        if(is_object($obj))
        {
            if(method_exists($obj,'get_html'))
            {
                return $obj->get_html($html,$class_name,$method_name);
            }
            else
            {
                return $html;
            }
        }
        else
        {
            return $html;
        }
    }
}
