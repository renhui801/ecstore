<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


interface base_interface_syscache_adapter{

    public function init_data();

    public function get($key);

    public function create($data);
}

