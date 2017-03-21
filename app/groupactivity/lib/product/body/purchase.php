<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * 商品详细页 加入团购信息
 * @package default
 * @author kxgsy163@163.com
 */
class groupactivity_product_body_purchase
{
    
    private $file = 'site/product/body/purchase.html';
    private $order = 99;
    
    
    function __construct($app) {
        $this->app = $app;
    }
    
    
    public function __get($var)
    {
        return $this->$var;
    }
    #End Func
    
    public function get_order() {
        return $this->order;
    }
    
    
    public function set_page_data( $gid,$object )
    {
        $object->pagedata['groupactivity'] = kernel::single("groupactivity_purchase")->_get_dump_data($gid);
    }
    
}