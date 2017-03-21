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

class giftpackage_mdl_giftpackage extends dbeav_model
{
    
    
    
    /*
     * 冻结礼包库存
     */
    public function freez( $id,$quantity )
    {
        if( !$id || !$quantity ) return false;
        $arr = $this->dump( $id );
        $freez = $arr['freez'];
        $arr = array('freez'=>$freez+$quantity,'id'=>$id);
        return $this->save( $arr );
    }
    #End Func
    
    
    
    /*
     * 解冻礼包库存
     */
    public function unfreez( $id,$quantity )
    {
        if( !$id || !$quantity ) return false;
        $arr = $this->dump( $id );
        $freez = $arr['freez'];
        $arr = array('freez'=>max(($freez-$quantity),0),'id'=>$id);
        return $this->save( $arr );
    }
    #End Func
	
	/*
     * 检查能否冻结礼包库存
     */
    public function check_freez( $id,$quantity )
    {
        if( !$id || !$quantity ) return false;
        $arr = $this->dump( $id );
        $freez = $arr['freez'];
		$store = $arr['store'];
		if ($freez + $quantity > $store)
			return false;
        
		return true;
    }
    #End Func
}