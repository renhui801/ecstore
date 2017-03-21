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
class groupactivity_purchase
{
    private $__dump_data = array();
    
    
    
    function __construct($app) {
        $this->app = $app;  
    }
    
    
    
    /*
     * 修改名称回收站显示
     */
    public function edit_name() {
        $o_purchase = $this->app->model('purchase');
        $arr = $o_purchase->getList( 'gid,act_id',array() );
        if( !$arr || !is_array($arr) ) return true;
        $o = app::get('b2c')->model('goods');
        foreach( $arr as $row ) {
            $tmp = $o->getList( 'name',array('goods_id'=>$row['gid']) );
            reset( $tmp );
            $tmp = current( $tmp );
            $tmp['act_id'] = $row['act_id'];
            if( $tmp )
                $o_purchase->save( $tmp );
        }
    }
    
    /*
     * 验证团购状态 成功 失败 ==    
     */
    public function get_stauts( $order_id )
    {
        $model = $this->app->model('order_act');
        $arr = $model->getList( '*',array('order_id'=>$order_id,'extension_code'=>'succ') );
        if( $arr && is_array($arr) ) {
            reset($arr);
            $arr = current($arr);
            $quantity = $arr['quantity'];
            $act_id = $arr['act_id'];
            if( !$act_id ) return false;
            $purchase = $this->app->model('purchase');
            $arr = $purchase->getList('*',array('act_id'=>$act_id));
            $arr = $arr[0];
            $arr['buy'] += $quantity;
            if( $arr['max_buy'] && ($arr['buy'] >= $arr['max_buy']) ) {
                $arr['state'] = 3;
            }
            
            $purchase->save($arr);
            
            switch($arr['state']) {
                case 2:  //进行中
                    if( $arr['buy']>=$arr['min_buy'] )
                        return true;
                    break;
                case 3: //已结束 成功
                    return true;
                    break;
                #case 4: //已结束 待处理
                #    return false;
                #case 5: //已结束 失败
                #    return false;
                default:
                    return false;
            }
        }
        return false;
    }
    #End Func
    
    /*
     * 返回 purchase dump 信息
     * 用于商品详细页面
     */
    public function _get_dump_data($gid=0)
    {
        if( !$gid ) return false;
        $now = time();
        if( !isset($this->__dump_data[$gid]) ) {
            $arr = $this->app->model('purchase')->getList( '*',array('gid'=>$gid,'end_time|than'=>$now) );
            if( is_array( $arr ) ) {
                reset( $arr );
                $arr = current( $arr );
                if( !$arr ) {
                    $arr = 'false';
                } else {//开始时范围内 处理状态
                    $arr['state'] = 2;
                    $this->app->model('purchase')->update( array('state'=>'2'),array('act_id'=>$arr['act_id']) );
                }
            }
            else 
                $arr = 'false';
            $this->_set_dump_data( $gid,$arr );
        }
        if( $this->__dump_data[$gid]==='false' ) return false;
        return $this->__dump_data[$gid];
    }
    #End Func
    
    
    /*
     * 修改团购 活动 的状态
     */
    public function edit_state()
    {
        $this->kv('fetch',$a);
        
        $now = time();
        if( is_array($a) ) 
            list($time,$act_id) = $a;

        $model = $this->app->model('purchase');
        if( !$time || $time<=$now ) {
            if( $time && $time<=$now ) {//小于当前时间 修改状态
                $arr = $model->getList( '*',array('end_time|sthan'=>$now,'act_open'=>'true','state'=>'2') );
                $obj_order_act = $this->app->model('order_act');
                $obj_order = $this->app->model('orders');
                if( is_array($arr) ) {
                    foreach( $arr as $row ) {
                        if( $row['buy']>=$row['min_buy'] ) {
                            $row['state'] = '3';
                            //修改订单来源 转入正常订单管理
                            $arr_order_id = $obj_order_act->getList( 'order_id',array('act_id'=>$row['act_id']) );
                            if( $arr_order_id ) {
                                $arr_order_id = array_map('current',$arr_order_id);
                                $obj_order->update( array('order_refer'=>'local'),array('order_id'=>$arr_order_id) );
                            }
                        } else $row['state'] = '4';
                        $model->save($row);
                    }
                }
            }
            $arr = $model->getList( '*',array('end_time|bthan'=>$now,'act_open'=>'true','state'=>'2'),0,1,'end_time ASC' );
            if( $arr ) {
                reset( $arr );
                $arr =current( $arr );
                $time = $arr['end_time'];
                $act_id = $arr['act_id'];
                $data = array($time,$act_id);
                $this->kv('store',$data);
            } else {
                $this->kv('delete',$tmp);
            }
        }elseif( $time > $now ){
			$arr = $model->getList( '*',array('end_time|bthan'=>$now,'act_open'=>'true','state'=>'2') );
			//$start_time = array();
			foreach($arr as $v){
				if( $v['start_time'] > $now ){
					$start_time = $v['start_time'];
					$sql = "SELECT * FROM `sdb_groupactivity_purchase` WHERE `start_time`=".$start_time;
					$act = $model->db->selectrow($sql);
					$update_act = array(
						'act_open' => 'false',
						'state' => '1'
					);
					$model->update($update_act,array('act_id'=>$act['act_id']));
				}
			}
		}
    }
    #End Func
    
    /*
     * 设置修改状态时间
     */
    public function set_edit_time( $array )
    {
        $this->kv('fetch',$kvdata);
        if( $kvdata && is_array($kvdata) ) {
            list($time,$act_id) = $kvdata;
            if( $time<=$array['time'] )
                return false;
        }
        $array = array($array['time'],$array['act_id']);
        $this->kv('store',$array);
    }
    #End Func
    
    
    /*
     * kvstore
     */
    private function kv( $func,&$data )
    {
        if( !$this->_obj_kvstore )
            $this->_obj_kvstore = kernel::single("base_kvstore")->instance('groupactivity');
        if( strtolower($func)=='delete' ) 
            $this->_obj_kvstore->delete('edit_state_time');
        else
            $this->_obj_kvstore->$func('edit_state_time',$data);
        
    }
    #End Func
    
    /*
     * 设置 purchase dump 信息
     */
    private function _set_dump_data($gid,$data)
    {
        $this->__dump_data[$gid] = $data;
    }
    #End Func
}