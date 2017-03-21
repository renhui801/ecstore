<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * 礼包商品信息
 * @package default
 * @author kxgsy163@163.com
 */
class giftpackage_site_goods
{
    //保存商品信息
    private $goods_info;
    
    
    function __construct(&$app) {
        $this->o_goods = app::get('b2c')->model('goods');
    }
    
    /*
     * return array(goods info)
     */
    public function get_goods_info($arr_goods_id)
    {
        if( !$arr_goods_id || !is_array($arr_goods_id) ) return false;
        $arr = array();
        foreach ($arr_goods_id as $key => $id) {
            if( !$this->goods_info[$id] )
                $arr[] = $this->o_goods->dump( $id );
            else 
                $arr[] = $this->goods_info[$id];
        }
        return $arr;
    }
    #End Func
}