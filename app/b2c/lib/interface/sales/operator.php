<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/**
 * 促销规则操作符接口
 */
interface b2c_interface_sales_operator
{
    public function getOperators();
    public function getString($aCondition);
    // public function validate($aV)
}
?>
