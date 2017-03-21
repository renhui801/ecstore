<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 */
class groupactivity_delete_check
{
    
    
    function __construct( &$app ) {
        $this->app = $app;
        $this->o_purchase = $this->app->model('purchase');
    }
    
    
    /**
     * 
     * @params $gid 商品id
     * @params $pid 货品id
     * @return bool
     **/
    public function is_delete( $gid,$pid=null ) {
        $filter = array();
        
        #if( $pid )
        #    $filter['product_id'] = $pid;
        
        if( $gid ) 
            $filter['gid'] = $gid;
        if( !$filter ) return true;
        $count = $this->o_purchase->count( $filter );
        if( $count ) {
            $this->error_msg = '该商品在团购中存在！无法删除！';
            return false;
        }
        return true;
    }
}