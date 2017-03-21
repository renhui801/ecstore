<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 购物车postfilter接口
 * $ 2010-04-29 11:55 $
 */
interface b2c_interface_cart_prefilter{
    public function filter(&$aResult,$aConfig);
}
?>
