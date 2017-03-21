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
class groupactivity_mdl_purchase extends dbeav_model
{
    
    
    
    /**
     * `前台访问团购页面修改状态
     **/
    function update_state( &$arr ) {
        $now = time();
        $update = false;
        if( !$this->validate( $arr ) ) return false;
        if( $arr['start_time']<=$now && $arr['end_time']>=$now ) { //当前时间在活动范围之内
            if( $arr['state']==1 ) { 
                $update = true;
                $arr['state'] = 2;
            }
        } else {
            if( $arr['state']==2 ) {
                if( $arr['end_time']<=$now ) {
                    $update = true;
                    $arr['state'] = ($arr['buy']>=$arr['min_buy']) ? 3 : 4;
                } elseif( $arr['start_time']>=$now ) {
                    $update = true;
                    $arr['state'] = 1;
                }
            }
        }
        if( $update ) {
            $this->save($arr);
            // 团购成功，转正式订单
            if($arr['state']==3) {
                $obj_order_act = $this->app->model('order_act');
                $obj_order = $this->app->model('orders');
                $arr_order_id = $obj_order_act->getList( 'order_id',array('act_id'=>$arr['act_id']) );
                if( $arr_order_id ) {
                    $arr_order_id = array_map('current',$arr_order_id);
                    $obj_order->update( array('order_refer'=>'local'),array('order_id'=>$arr_order_id) );
                }
            }
        }
    }
    
    function validate( $arr ) {
        return $arr['act_open']=='true' ? true : false;
    }
    /**
     *  从回收站里恢复时，检查该商品是否已存在团购。同一商品只能做唯一团购
     * @author afei
     * @since 2001-04-12
     * @return bool
     */
    function pre_restore(&$data,$restore_type='add'){
        $filter = array ('gid'=>( int ) $data ['gid'] );
        if ($this->count ( $filter ) > 0) {
            return false;
        } else {
            $data ['need_delete'] = true;
            return true;
        }
    }
}