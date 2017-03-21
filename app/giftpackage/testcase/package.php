<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * 测试用例
 * @package default
 * @author kxgsy163@163.com
 */
class package extends PHPUnit_Framework_TestCase
{
    
    function setUp() {
        // 调用model
        $this->app = app::get('giftpackage');
        $this->oCartObject = app::get('b2c')->model('cart_objects');
        $this->member_ident = 'd4b7d1af8275849d18c4d64c1bf75f8d';
    }
    
    
    /*
     * test print
     */
    public function test_add_giftpackage()
    {
        #$this->markTestSkipped("加入购物车商品数据");
        // 加入一些数据
        // 商品加入购物车
        $aTest = array(
                    'id'   => 1,
                    'num' => 3,
                    'products' => array(
                                        array(
                                                'goods_id' => 78,
                                                'product_id' => 526,
                                        ),
                                        array(
                                                'goods_id' => 79,
                                                'product_id' => 527,
                                        ),
                                        array(
                                                'goods_id' => 81,
                                                'product_id' => 533,
                                        ),
                                        array(
                                                'goods_id' => 87,
                                                'product_id' => 539,
                                        ),
                                ),
                 );
        $o = kernel::single('giftpackage_cart_object_giftpackage');
        $o->member_ident = $this->member_ident;
        $o->add($aTest);
        #$this->assertTrue(($aTest['goods_id'] == 1),"商品数据2加入购物车失败");
    }
    #End Func
    
    /*
     * giftpackage getall
     */
    public function test_get_all()
    {
        $o = kernel::single('giftpackage_cart_object_giftpackage');
        $o->member_ident = $this->member_ident;
        $arr = $o->getAll(true);
        print_r($arr);
    }
    #End Func
}