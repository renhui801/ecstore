<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_apiv_apis_20_ome_orderupdate extends b2c_apiv_extends_request
{
  var $method = 'store.trade.update';
  var $callback = array();
  var $title = '订单变更';
  var $timeout = 1;
  var $async = true;

  public function get_params($sdf)
  {
    $order_id = $sdf['order_id'];
    $order_detail = kernel::single('b2c_order_full')->get($order_id);
    return $order_detail;
  }
}