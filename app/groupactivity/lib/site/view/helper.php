<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class groupactivity_site_view_helper 
{

    function function_header($params, &$smarty)
    {
		if ($smarty->app->app_id != 'b2c' && $smarty->app->app_id != 'groupactivity') return;
		
		$app_dir = app::get('groupactivity')->app_dir;
		$smarty->pagedata['ec_res_url'] = app::get('groupactivity')->res_url;
		/** 不同的页面扩展不同的css **/
		$ext_filename = $smarty->_request->get_app_name() . '_' . $smarty->_request->get_ctl_name() . '.html';
		if (file_exists($app_dir.'/view/site/common/ext/'.$ext_filename))
			$smarty->pagedata['extends_header'] .= $smarty->fetch('site/common/ext/'.$ext_filename,'groupactivity');
		/** end **/
    }
    
    function getgroupdate(&$buttonhtml,&$goodsdata,$referurl,&$group_url){
        $group_url = kernel::base_url(1)."/index.php/group-checkout.html";
        if(strpos($referurl, 'group')){
           $buttonhtml = str_replace('加入购物车', '立即购买', $buttonhtml);
           $refer_arr = explode('group-', $referurl);
           $act_id = trim($refer_arr[1],'.html');
           $oGroup = app::get('groupactivity')->model('purchase');
           $group_arr = $oGroup->getList('price',array('act_id'=>$act_id));
           $goodsdata['current_price'] = $group_arr[0]['price'];
           $goodsdata['price'] = $group_arr[0]['price'];
           $spec_arr = json_decode($goodsdata['product2spec'],1);
           foreach ($goodsdata['product'] as $key => $value) {
               $goodsdata['product'][$key]['price']['price']['price'] = $group_arr[0]['price'];
               $goodsdata['product'][$key]['price']['price']['current_price'] = $group_arr[0]['price'];
               $spec_arr[$key]['price'] = $group_arr[0]['price'];
               foreach($spec_arr[$key]['mprice'] as $k=>$v){
                   $spec_arr[$key]['mprice'][$k] = $group_arr[0]['price'];
                   $goodsdata['product'][$key]['price']['member_lv_price'][$k]['price'] = $group_arr[0]['price'];
               }
           }
           $goodsdata['product2spec'] = json_encode($spec_arr);
        }
    }

    function getgroupprice($referurl){
        if(strpos($referurl, 'group')){
           $refer_arr = explode('group-', $referurl);
           $act_id = trim($refer_arr[1],'.html');
           $oGroup = app::get('groupactivity')->model('purchase');
           $group_arr = $oGroup->getList('price',array('act_id'=>$act_id));
           return $group_arr[0]['price'];
        }else{
           return false;
        }
    }

}//结束