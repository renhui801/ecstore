<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

interface desktop_interface_passportpresent{
    //在登录页面时，验证码之后调用
    public function handle(&$object);
}
