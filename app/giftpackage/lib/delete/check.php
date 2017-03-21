<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 */
class giftpackage_delete_check
{
    
    
    function __construct( &$app ) {
        $this->app = $app;
    }
    
    
    /**
     * 
     * @params $gid 商品id
     * @params $pid 货品id
     * @return bool
     **/
    public function is_delete( $gid,$pid=null ) {
        $filter = array();
        $o = $this->app->model('giftpackage');
        
        
        $arr = $o->getList( 'goods' );
        if( !$arr || !is_array($arr) ) return true;
        foreach( $arr as $row ) {
            $goods = $row['goods'];
            $arr_goods_id = array();
            if( is_array($goods) ) {
                foreach( $goods as $val ) {
                    $tmp = explode(',',$val);
                    $arr_goods_id = array_merge($arr_goods_id,(array)$tmp);
                }
            } else {
                $arr_goods_id = explode(',',$goods);
            }
            if( in_array($gid,$arr_goods_id) ) {
                $this->error_msg = '该商品在礼包中存在！无法删除！';
                return false;
            }
        }
        
        return true;
    }
}