<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * @package default
 * @author kxgsy163@163.com
 */
class b2c_goods_promotion_price
{

    function __construct( &$app )
    {
        $this->app = $app;
    }

    public function process( $arrGoods ) {
        $return = kernel::single('b2c_cart_prefilter_promotion_goods')->get_goods_sales( $arrGoods );
        return $return;
    }
}
