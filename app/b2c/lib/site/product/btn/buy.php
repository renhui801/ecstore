<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * 加入购物车按钮
 * @package default
 * @author kxgsy163@163.com
 */
class b2c_site_product_btn_buy
{
    
    private $file = 'site/product/btn/buy.html';
    private $wap_file = 'wap/product/btn/buy.html';//触屏版
    private $order = 80;
    
    
    
    
    
    public function __get($var)
    {
        return $this->$var;
    }
    #End Func
    
    public function get_order() {
    	return $this->order;
    }
    
}