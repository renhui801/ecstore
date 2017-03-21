<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 购物车处理接口
 * $ 2010-04-28 20:30 $
 */
interface b2c_interface_cart_process{
    public function process($aData,&$aResult=array(),$aConfig=array());
}
?>
