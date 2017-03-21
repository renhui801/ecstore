<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class api extends PHPUnit_Framework_TestCase
{
    /*
     * author guzhengxiao
     */
    public function setUp()
    {
        $this->apiname = $_SERVER['argv'][0];
        include('api/basic.php');
        //$api_params; 应用级参数

        $task = uniqid();
        $basic_params = array(
            'to_api_v' => '2.0',
            'direct' => 'true',
            'task' => $task,
            'method' => $this->apiname,
        );
        $this->params = array_merge($api_params,$basic_params);
        $sign = base_certificate::gen_sign($this->params);
        $this->params['sign'] = $sign;
    }

    public function testApi(){
        $headers = array(
            /*'Connection'=>$this->timeout,*/
            'Connection'=>'Close',
        );
        if($gzip){
            $headers['Content-Encoding'] = 'gzip';
        }

        $url = kernel::base_url(1).'/index.php/api';//'http://192.168.51.51/ecstore2.0/index.php/api';
        $core_http = kernel::single('base_httpclient');
        $response = $core_http->set_timeout(10)->post($url,$this->params,$headers);
        $data = json_decode($response,true);
        print_r($data);
    }
}
