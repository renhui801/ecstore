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
class groupactivity_product_btn_purchase
{
    
    private $file = 'site/product/btn/purchase.html';
    private $order = 70;
    
    
    
    
    
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