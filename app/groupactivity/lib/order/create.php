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
class groupactivity_order_create
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
        if($sdf['order_refer'] == 'local_group'){
            $aRs = $this->app->model('purchase')->getList('buy',array('act_id'=>$sdf['groupactivity_act_id']));
            $tmp_buy_times = intval($aRs[0]['buy'])+$sdf['order_objects'][0]['quantity'];
            return $this->app->model('purchase')->update(array('buy'=>$tmp_buy_times),array('act_id'=>$sdf['groupactivity_act_id']));
        }else{
            return false;
        }
    }
    #End Func
}
