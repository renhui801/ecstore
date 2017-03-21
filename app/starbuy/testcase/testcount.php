<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class testcount extends PHPUnit_Framework_TestCase
{

    public function testTestcount(){
        $mdl = app::get('ectools')->model('payment_cfgs');
        $m = $mdl->getList('*');
        print_r($m);
        return;
        $starbuy_special_goods = new starbuy_special_count;
        $fmt_check_products = array(2=>3,533=>1,547=>1,523=>1);
        $data = $starbuy_special_goods->get_special_products($fmt_check_products);
//        $data = $starbuy_special_goods->cut_count(10,2,2);


        print_r($data);
    }
}
