<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

interface site_interface_menu
{
    public function inputs($config=array());

    public function handle($post);

    public function get_params();

    public function get_config();
}
