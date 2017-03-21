<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * 立即购买按钮
 * @package default
 * @author kxgsy163@163.com
 */
class b2c_site_product_btn_fastbuy
{
    
    private $file = 'site/product/btn/fastbuy.html';
    private $wap_file = 'wap/product/btn/fastbuy.html'; //触屏版
    private $order = 90;
    
    
    
    
    
    public function __get($var)
    {
        return $this->$var;
    }
    #End Func
    
    public function get_order() {
    	return $this->order;
    }
    
}