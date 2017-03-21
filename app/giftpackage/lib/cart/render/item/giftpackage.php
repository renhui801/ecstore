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
class giftpackage_cart_render_item_giftpackage
{

    public $app = 'giftpackage';
    public $file = 'site/cart/item/index.html';
    public $index = 81; // 所处位置

    public function _get_minicart_view() {
        $arr = array(
            'file'=>'site/cart/mini/item/giftpackage.html',
            'index'=>81,
        );
        return $arr;
    }

}
