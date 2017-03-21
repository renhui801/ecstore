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
class timedbuy_cart_object_goods
{
    
    
    function __construct(&$app) {
        $this->app = &$app;
        $this->arr_member_info = kernel::single('b2c_cart_objects')->get_current_member();
        $this->member_ident = kernel::single("base_session")->sess_id();
    }

    //检查限时抢购商品是否超出限购范围
    function check($gid,$pid,$quantity=0,&$msg) {
        if( !$gid ) {
            $msg = '商品ID丢失！';
            return false;
        }

        $arr_sales_info = kernel::single('timedbuy_info')->get_sales_goods_info( $gid );

        $flag = $this->_get_kvstore( $arr_sales_info,$gid,$member_num,$num,$config );

        if( $arr_sales_info['from_time']>time() ) {
            $msg = '抢购活动还未开始！';
            return false;
        }
        
        if( $arr_sales_info['to_time']<time() ) {
            $msg = '活动已经结束！';
            return true;
        }
        

        if( !$arr_sales_info || !is_array($arr_sales_info) ) return true;
        
        if( !$this->arr_member_info || !$this->arr_member_info['member_id'] ) {
            $msg = '只限会员抢购！！！';
            #return false;
            $jump_to_url = app::get('site')->router()->gen_url( array('app'=>'b2c','ctl'=>'site_cart','act'=>'loginBuy','arg0'=>1) );
            if($_POST['mini_cart']){
                echo json_encode( array('url'=>$jump_to_url) );exit;
            } else {
                header('Location:'.$jump_to_url);exit;
               // kernel::single('base_controller')->splash( 'success',$jump_to_url );exit;
            }
        }
        
        if( $arr_sales_info['member_lv_ids'] ) {
            if( !in_array($this->arr_member_info['member_lv'],(array)explode(',',$arr_sales_info['member_lv_ids'])) ) {
                $msg = '您所属的会员等级不符！';
                return true;//如果不符合等级，则为普通商品购买，返回true
            }
        }
        
        //针对 货品情况 判断整个购物车
        $filter = array('member_id'=>$this->arr_member_info['member_id'],'member_ident'=>$this->member_ident);
        $arr_cart_objects = app::get('b2c')->model('cart_objects')->getList( '*',$filter );
        foreach( (array)$arr_cart_objects as $cart_objects ) {
            if( $cart_objects['params']['goods_id']==$gid && $cart_objects['params']['product_id']!=$pid ) {
                $quantity += $cart_objects['quantity'];
            } 
        }
        
        
        // $flag = $this->_get_kvstore( $arr_sales_info,$gid,$member_num,$num,$config );
        
        #if( !$flag ) return true;
        
        //限购数量留空时不限制  下同
        #echo $config['limit'],'---',$member_num,'---',$quantity,"<HR>";#exit;
        if( $config['limit'] && $config['limit']<$member_num+$quantity ) {
            $msg = "累计购买数量超出每人限购数 ".$config['limit']." 件";
            return false;
        }
        
        #echo $config['quantity'],'---',$num,'---',$quantity;exit;
        if( $config['quantity'] && $config['quantity']<$num+$quantity ) {
            $msg = '已超出限购库存！';
            return false;
        }
        /*
        $data = $this->kv_data[$rule_id] = array(
                            'from_time' => $arr_sales_info['from_time'],
                            'to_time' => $arr_sales_info['to_time'],
                            $this->arr_member_info['member_id'] => array('num'=>$member_num,'time'=>time()),
                            'num' => $num,
                        );
        
        $this->_obj_kvstore->store($gid,$data);
        */
        return true;
    }
    
    
    /* 
     * 系统存在问题，当促销id为1的促销做过限抢后再次开启限抢功能，计算问题。
     * ：把last_modify 改为下单时间。根据下单时间和促销时间为依据做判断
     * 针对以上。追加了ctime字段
     */
    public function _get_kvstore( $arr_sales_info,$gid,&$member_num,&$num,&$config ) {
        //开始时间
        $from_time = $arr_sales_info['from_time'];
        
        //结束时间
        $to_time = $arr_sales_info['to_time'];
        
        //会员id
        $member_id = $this->_member_id = $this->arr_member_info['member_id'];
        
        //促销id
        $rule_id = $arr_sales_info['rule_id'];
        
        
        $solution = @unserialize($arr_sales_info['action_solution']);
        
        $config = $solution['timedbuy_promotion_solution_timedbuy'];//配置
        
        $filter= array();
        $filter['goods_id'] = $gid;
        $filter['sales_rule_id'] = $arr_sales_info['rule_id'];
        
        $stock_freez_time = app::get('b2c')->getConf('system.goods.freez.time');
        $data = $this->app->model('objitems')->getList('*',$filter);
        if( !$data || !is_array($data) ) return true;
        foreach( $data as $row ) {
            if( !$row['ctime'] ) $row['ctime'] = $row['last_modify'];
            if( $row['ctime']<$from_time || $row['ctime']>=$to_time ) continue;
            #if( $row['order_pay_status']!='0' ) {
                if($stock_freez_time == 1 || $row['order_pay_status'] == '1') {
                    $num += $row['quantity'];
                }
                else {
                    if($row['member_id'] == $member_id) {
                        $num += $row['quantity'];
                    }
                }
                if( $row['member_id']==$member_id )
                    $member_num += $row['quantity'];
            #} else if( $stock_freez_time=='1' ) {
            #    $num += $row['quantity'];
            #    if( $row['member_id']==$member_id )
            #        $member_num += $row['quantity'];
            #}
        }
        return $flag;
        
        //////////////////////////////////////////////////////////////////////////
        //以下废弃
        ///////////////////////////////////////////////////////////////////////////
        $this->_obj_kvstore = kernel::single("base_kvstore")->instance('timedbuy');
        $this->_obj_kvstore->fetch($gid,$data);
        $this->kv_data = &$data;
        
        $member_num = $num = 0;
        
        if( $data ) {
            if( $data[$rule_id] ) {
                if( $data[$rule_id]['from_time']>=time() && $data[$rule_id]['to_time']<=time() ) {
                    if( isset($data[$rule_id][$member_id]) ) {
                        //会员购买量
                        $member_num = $data[$rule_id][$member_id]['num'];
                        
                        //总购买量
                        $num = $data[$rule_id]['num'];
                        //查询开始时间
                        $search_time = $data[$rule_id][$member_id]['time'];
                    }
                }
            }
        } 
        if( !$search_time ) {
            //查询开始时间
            $search_time = $from_time;
        }

        
        $filter = array('last_modified|bthan'=>$search_time,'last_modified|sthan'=>$to_time);
        $filter['createtime|bthan']=$from_time;
        $filter['createtime|sthan']=$to_time;
        #$filter['member_id'] = $member_id;
        
        //1：下单 2：支付
        $stock_freez_time = app::get('b2c')->getConf('system.goods.freez.time');
        
        if ($stock_freez_time == '2') 
        {
            $filter['pay_status|noequal'] = '0';
        }
        
        $arr = app::get('b2c')->model('orders')->getList( 'order_id,member_id',$filter );
        if( !$arr ) return false;
        foreach( $arr as $row ) {
            $arr_order_id[$row['order_id']] = $row['member_id'];
        }
        $filter = array();
        $filter['order_id'] = array_keys($arr_order_id);
        $filter['goods_id'] = $gid;
        
        
        $arr = app::get('b2c')->model('order_items')->getList( 'item_id,order_id,nums',$filter );
        if( !$arr ) return false;
        
        foreach( $arr as $row ) {
            if( $arr_order_id[$row['order_id']]==$this->_member_id )
                $member_num += $row['nums'];
            $num += $row['nums'];
        }
        return true;
    }
    
    private function get_error_msg( $msg ) {
        return array('status'=>'false','msg'=>$msg);
    }
    
}