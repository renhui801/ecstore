<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
interface base_interface_cache{
    
    public function store($key, $value);
    public function fetch($key, &$result);
    public function set_modified($type, $key, $time=null);
    public function get_modified($type, $key);
}
