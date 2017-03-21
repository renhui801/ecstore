<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class timedbuy_info{
    private $_kv_data;
    private $_kv_sales_ref_data;
    private $_kv_obj;
    
    public $_defalut_filter_time = true;
    
    
    public function __construct() {
        $this->_kv_obj = kernel::single("base_kvstore")->instance('timedbuy');
        $this->_kv_obj->fetch('_rule_goods',$this->_kv_data);
        $this->_kv_obj->fetch('_rule_goods_ref',$this->_kv_sales_ref_data);
    }
    
    
    public function get_sales_goods_info( $gid ) {
        $order = kernel::single('b2c_cart_prefilter_promotion_goods')->order();
        $time = time();
        $filter = array('goods_id'=>$gid,'status'=>'true','to_time|bthan'=>$time);
        if( $this->filter_time() ) {
            $filter['from_time|sthan'] = $time;
        }
        $arr = app::get('b2c')->model('goods_promotion_ref')->getList( '*',$filter,0,-1,$order );
        if( !$arr ) return false;
        foreach( $arr as $row ) {
            $solution = @unserialize($row['action_solution']);
            if( !is_array($solution) ) continue;
            if( !isset($solution['timedbuy_promotion_solution_timedbuy']) ) continue;
            
            /*
             *
             * 抢购商品详细页显示做了个限制。。
             * 两个抢购都应用在某一个商品上的时候。
             * 会显示在时间范围内的抢购信息。
             * 如果两个都不在时间范围内。就显示最后应用的。
             * 预告功能 对显示哪个抢购信息不会有任何干涉
             */
            if( !$return ) $return = $row;
            if( $row['from_time']<time() && $row['to_time']>time() ) {
                $return = $row;
                break;
            }
        }
        return $return;
    }
    
    public function filter_time() {
        return $this->_defalut_filter_time;
    }
    
    public function get( $key ) {
        if( $this->$key ) return $this->$key;
        $this->_kv_obj->fetch($key,$arr);
        $this->$key = $arr;
        return $arr;
    }
    
    public function set( $key,$value ) {
        $this->_kv_obj->store( $key,$value );
    }
}
