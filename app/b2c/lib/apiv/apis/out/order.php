<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_apiv_apis_out_order implements b2c_apiv_interface_requestout
{
  public function init($sdf)
  {
      $url = 'http://www.baidu.com';
      $core_http = kernel::single('base_httpclient');
      $response = $core_http->set_timeout(10)->post($url,$sdf,array(
                                                        'Content-Encoding' => 'gzip',
                                                        ));

      if($response===HTTP_TIME_OUT){
          $headers = $core_http->responseHeader;
          logger::info('Request timeout, process-id is '.$headers['process-id']);
          return false;
      }else{
          
      }
  }
}