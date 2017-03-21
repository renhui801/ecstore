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
class timedbuy_site_product_btn_timedbuy
{
    
    private $file = 'site/product/btn/timedbuy.html';
    private $order = 80;
    private $unique = false;
    
    
    
    
    
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
        $enable = app::get('site')->model('modules')->getList( 'enable',array('app'=>'timedbuy') );
        foreach($enable as $v){
            $able = $v['enable'];
        }//print_r($able);exit;
        $object->pagedata['enable'] = $able;
        if( !$gid ) return false;
        kernel::single('timedbuy_info')->_defalut_filter_time = false;
        $row = kernel::single('timedbuy_info')->get_sales_goods_info( $gid );
        
        if( !is_array($row) ) return false;
        $solution = @unserialize($row['action_solution']);
        $from_time = $row['from_time'];
        $to_time = $row['to_time'];
        
        $object->pagedata['request_time_now'] = app::get('site')->router()->gen_url( array('app'=>'timedbuy','ctl'=>'site_timedbuy','act'=>'request_time_now') );
        
        $object->pagedata['timedbuy_sales_rule'] = $row;
        
        $config = $solution['timedbuy_promotion_solution_timedbuy'];
        //未开始
        if( time()<$from_time ) {
            if( $config['forenotice']['status']==='1' ) {
                $forenotice_time = $config['forenotice']['timeh']*3600 + $config['forenotice']['timei']*60 + $config['forenotice']['times'];
                $cache_time = $from_time-$forenotice_time;
                if( time()<$cache_time ) {//当前时间小于预告时间
                    #$cache_time = $cache_time;
                    
                    //不再预告时时间之内。状态设置为0  不启动预告计时器
                    $this->_set_cache_time($cache_time);
                    return false;
                } else {
                    $cache_time = $from_time;
                }
                
                $this->unique = true;
            } else {
                if( time()<$from_time ) { //未开启即时  且  未到开始时间
                    $this->_set_cache_time( $from_time );
                    return false;
                } else {
                    $cache_time = $from_time;
                }
            }
            $config['_buy_status'] = 1;
        } elseif( time()<$to_time) { //已开始 未结束
            $p = isset($object->pagedata['goods']['store']) ? $object->pagedata['goods']['store'] : 99999999;
            $object->pagedata['goods']['_real_store'] = ($p>$config['limit'])?$config['limit']:$object->pagedata['goods']['store'];
            $this->unique = true;
            $cache_time = $to_time;
            $config['_buy_status'] = 2;
            //获取购买信息
            kernel::single('timedbuy_cart_object_goods')->_get_kvstore( $row,$gid,$member_num,$num,$tmp );
            if( $tmp['quantity'] && $tmp['quantity']<=$num ) {
                //抢购数量已满
                $config['_buy_status'] = 4;
            }
            
        } else {  //结束了
            $cache_time = false;
            $config['_buy_status'] = 3;
        }
        
        if( $cache_time )
            $this->_set_cache_time($cache_time);
        
        if(empty($config['price'])) {
            $config['price'] = $object->pagedata['goods']['current_price'];
        }
        $config['price'] = floatval($config['price']);
        $object->pagedata['timedbuy'] = $config;
    }
    
    public function _set_cache_time( $cache_time ) {
        cachemgr::set_expiration($cache_time);
    }
    
    
    //////////////////////////////////////////////////////////////////////////
    //页面唯一 
    ///////////////////////////////////////////////////////////////////////////
    public function unique() {
        return $this->unique;
    }
}