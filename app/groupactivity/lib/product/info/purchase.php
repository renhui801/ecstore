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
class groupactivity_product_info_purchase
{
    
    private $file = 'site/group_basic.html';
    private $order = 80;
    
    
    
    
    
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
        $enable = app::get('site')->model('modules')->getList( 'enable',array('app'=>'groupactivity') );
        foreach($enable as $v){
            $able = $v['enable'];
        }
        $object->pagedata['enable'] = $able;
        $object->pagedata['purchase'] = $arr = kernel::single("groupactivity_purchase")->_get_dump_data($gid);

        if( $arr ) {
            if( $arr['act_open']=='false' || ($arr['max_buy']<=$arr['buy'] && $arr['max_buy']!=0) ) $object->pagedata['purchase'] = null;
            $object->pagedata['group_url'] = app::get('site')->router()->gen_url( array('app'=>'groupactivity','ctl'=>'site_cart','act'=>'index','arg0'=>$arr['act_id']) );
            cachemgr::set_expiration($arr['end_time']);
        }
        #$this->_response->set_header('Cache-Control', 'no-store');
    }
    
    public function get_groupbuy_info($act_id){
        $oPurchase = app::get('groupactivity')->model('purchase'); 
        $purchase_arr = $oPurchase->getList('start_value,max_buy,buy',array('act_id'=>$act_id));
        return $purchase_arr[0]['max_buy']-$purchase_arr[0]['buy'];
    }
    
}