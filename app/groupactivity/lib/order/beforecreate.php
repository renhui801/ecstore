<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * 修改订单信息
 * @package default
 * @author kxgsy163@163.com
 */
class groupactivity_order_beforecreate
{
    
    function __construct($app)
    {
        $this->app = $app;
    }
    
    /*
     * 修改订单信息
     */
    public function generate( &$sdf )
    {
        if( !is_array($sdf['order_objects']) || count($sdf['order_objects'])>1 ) return false;
        if( !kernel::single("groupactivity_cart_object_purchase")->get_session() ) return false;
        foreach( $sdf['order_objects'] as $key => $row ) {
            if( !is_array($row) ) continue;
            
            $gid = $row['goods_id'];
            if( !$gid ) continue;
            
            $sdf['order_objects'][$key]['obj_alias'] = '团购区块';
            $sdf['order_refer'] = 'local_group';
            
            $arr = $this->app->model('purchase')->getList( '*',array('gid'=>$gid,'end_time|than'=>time()) );
            if( !$arr || !is_array($arr) ) continue;
            reset( $arr );
            $arr = current($arr);
            $sdf['groupactivity_act_id'] = $arr['act_id'];
        }
        
        $arr = array(
                    'order_id' => $sdf['order_id'],
                    'group_total_amount' => $sdf['total_amount'],
                    'last_change_time' => time(),
                    'act_id' => $sdf['groupactivity_act_id'],
                    'quantity' => $sdf['order_objects'][0]['quantity'],
                    'member_id' => $sdf['member_id'],
                    'createtime'=>time()
                );
        $model = $this->app->model('order_act');
        $model->save($arr);
    }
    #End Func
}