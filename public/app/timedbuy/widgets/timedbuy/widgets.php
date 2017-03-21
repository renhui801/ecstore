<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

$setting['author']='abc';
$setting['vary']='group';

$setting['name']=app::get('groupactivity')->_('限时抢购');

$setting['version']='135711';

$setting['catalog']=app::get('groupactivity')->_('商品相关');

$setting['description']    = app::get('groupactivity')->_('拉动本版块（widget）就可以在网店前台显示限时抢购，本版块不需要任何参数的设置，添加本版块到模板页面相应的插槽上即可。').'<br><br>'.app::get('b2c')->_('如需查看该版块的使用说明，请').'<a href="http://www.shopex.cn/bbs/read.php?tid-64227.html" target="_blank">'.app::get('groupactivity')->_('点击这里').'</a>。';


$setting['usual']    = '0';

$setting['stime']='2008-8-8';

$setting['template'] = array(
                            'default.html'=>app::get('groupactivity')->_('默认')
                        );


$arr = app::get('b2c')->model('sales_rule_goods')->getList( '*',array('status'=>'true','to_time|than'=>time()) );
$arr_goods = $arr_rule_id = $arr_rules = $arr_goods_id = $arr_goods_to_rule = array();

if( is_array($arr) ) {
    foreach( $arr as $row ) {
        $solution = $row['action_solution'];
        
        if( !is_array($solution) ) continue;
        if( key($solution)!='timedbuy_promotion_solution_timedbuy' )continue;
        $arr_rule_id[] = $row['rule_id'];
        $arr_rules[$row['rule_id']] = $row['name'];
    }
    
    $order = kernel::single('b2c_cart_prefilter_promotion_goods')->order();
    if( $arr_rule_id )
        $arr_goods_id = app::get('b2c')->model('goods_promotion_ref')->getList( 'goods_id,rule_id',array('rule_id'=>$arr_rule_id),0,-1,$order );
 
    if( $arr_goods_id ) {
        foreach( $arr_goods_id as $k=>$row ) {
            $arr_goods_ids[] = $row['goods_id'];
            if (!isset($arr_goods_to_rule[$row['goods_id']]))
                $arr_goods_to_rule[$row['goods_id']] = $row['rule_id'];
            unset($arr_goods_id[$k]);
        }
        
        $arr_goods = app::get('b2c')->model('goods')->getList( 'goods_id,name',array('goods_id'=>$arr_goods_ids) );
        
        foreach( $arr_goods as &$info ) {
            $info['rule_id'] = $arr_goods_to_rule[$info['goods_id']];
            $info['rule_name'] = $arr_rules[$info['rule_id']];
        }
    }
}
$setting['goods_list'] = $arr_goods;
