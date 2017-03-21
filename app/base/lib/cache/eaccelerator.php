<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

/*
 * @package base
 * @copyright Copyright (c) 2010, shopex. inc
 * @author edwin.lzh@gmail.com
 * @license 
 * 仅支持0.9.5.3之前的eaccelerator
 */

class base_cache_eaccelerator extends base_cache_abstract implements base_interface_cache 
{
    
    function __construct() 
    {
        $this->check_vary_list();
    }//End Function

    public function fetch($key, &$result) 
    {
        $result = eaccelerator_get($key);
        return !is_null($result);
    }//End Function

    public function store($key, $value) 
    {
        return eaccelerator_put($key, $value, 0);
    }//End Function

}//End Class