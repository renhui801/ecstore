<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_orderlist(&$setting,&$smarty){

  $order = app::get('b2c')->model('orders');
  $number=intval($setting['rowNum'])?intval($setting['rowNum']):5;

  //$result=$order->getList('*',$where,0,$number);

  $setting['smallPic'] and $setting['smallPic'] = app::get('b2c')->res_url . '/icons/' . $setting['smallPic'];

  $result=$order->getLastestOrder($number);
  if ($result)
    foreach($result as $key=>$val){
      $aTmp[$key]['order_id'] = $val['order_id'];
      $aTmp[$key]['ship_name'] = $val['ship_name'];
      $aTmp[$key]['ship_status'] = $val['ship_status'];
      $aTmp[$key]['sex'] = $val['sex'];
      $aTmp[$key]['date'] = date("Y-m-d",$val['createtime']);
      $aTmp[$key]['total_amount'] = $val['total_amount'];
      $aTmp[$key]['currency'] = $val['currency'];
    }
    // echo "<pre>";print_r($aTmp);
  $aTmp['setting'] = $setting;
  return $aTmp;
}
?>
