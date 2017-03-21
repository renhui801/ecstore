<?php
class system_request{

    function register($url,$params){
        $method ='matrix.node.reg';
        //base_shopnode::token('b2c'), 
        $token = $params['token'];
        $headers = array(
            'Connection'=>'Close',
        );
        $query_params=array(
            'method'=>$method,
            'format'=>'json',
            'from_node_id'=>base_shopnode::node_id('b2c'),
            'v'=>'v2_0',
            'timestamp'=>date('Y-m-d H:m:s',time()),
            'token'=> $token,
        );

        $query_params = array_merge((array)$params,$query_params);
        $query_params['sign'] = $this->gen_sign($query_params,$token);
        $core_http = kernel::single('base_httpclient');

        $response = $core_http->set_timeout(6)->post($url,$query_params,$headers);

        $result = json_decode($response,true);
        if($result['rsp'] == 'succ'){
            return true;
        }else{
            return false;
        }
    }


    static function gen_sign($params,$token){
        $str = self::assemble($params);
        logger::info('siyou_matrix：'.$str."====".$token);
        return strtoupper(md5(strtoupper(md5($str)).$token));
    }

    static function assemble($params)
    {
        if(!is_array($params))  return null;
        ksort($params, SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            if(is_null($val))   continue;
            if(is_bool($val))   $val = ($val) ? 1 : 0;
            $sign .= $key . (is_array($val) ? self::assemble($val) : $val);
        }
        return $sign;
    }//End Function

}
