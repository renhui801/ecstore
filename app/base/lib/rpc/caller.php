<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class base_rpc_caller{

    var $timeout = 10;

    function __construct(&$app,$node_id,$version){
        $this->network_id = $node_id;
        $this->app = $app;
		$this->api_request_version = $version;
    }

    
    private function get_url($node){
        $row = app::get('base')->model('network')->getlist('node_url,node_api', array('node_id'=>$this->network_id));
        if($row){
            if(substr($row[0]['node_url'],-1,1)!='/'){
                $row[0]['node_url'] = $row[0]['node_url'].'/';
            }
            if($row[0]['node_api']{0}=='/'){
                $row[0]['node_api'] = substr($row[0]['node_api'],1);
            }
            $url = $row[0]['node_url'].$row[0]['node_api'];
        }

        return $url;
    }

    public function call($method,$params,$rpc_id=null,$gzip=false){

        $api_log = kernel::single('apiactionlog_router_logging');
        $rpc_id = $api_log->request_log($method,$params,$rpc_id);
        if(!$rpc_id){
            $microtime = utils::microtime();
            $rpc_id = str_replace('.','',strval($microtime));
            $randval = uniqid('', true);
            $rpc_id .= strval($randval);
            $rpc_id = md5($rpc_id);
        }
        $headers = array(
            /*'Connection'=>$this->timeout,*/
            'Connection'=>'Close',
        );
        if($gzip){
            $headers['Content-Encoding'] = 'gzip';
        }

        $query_params = array(
            'app_id'=>'ecos.'.$this->app->app_id,
            'method'=>$method,
            'date'=>date('Y-m-d H:i:s'),
            'callback_url'=>kernel::openapi_url('openapi.rpc_callback','async_result_handler',array('id'=>$rpc_id,'app_id'=>$this->app->app_id)),
            'format'=>'json',
            'certi_id'=>base_certificate::certi_id(),
            'v'=>$this->api_version($method),
            'from_node_id' => base_shopnode::node_id($this->app->app_id),
        );
        $query_params = array_merge((array)$params,$query_params);

        // rpc_id 分id 和 calltime
        $arr_rpc_key = explode('-', $rpc_id);
        $rpc_id = $arr_rpc_key[0];
        $rpc_calltime = $arr_rpc_key[1];
		$query_params['task'] = $rpc_id;
		if (!base_shopnode::token($this->app->app_id))
			$query_params['sign'] = base_certificate::gen_sign($query_params);
		else
			$query_params['sign'] = base_shopnode::gen_sign($query_params,$this->app->app_id);

        $url = $this->get_url($this->network_id);

        //私有矩阵apiurl
        if("private" == app::get('system')->getConf('system.matrix.set')){
            unset($query_params['sign']);
            $query_params['v'] = 'v2_0';
            if($this->network_id == 2){
                $query_params['callback_url'] = "";
            }
            $query_params['sign'] = kernel::single('system_shopmatrix')->get_sign($query_params,base_shopnode::node_id($this->app->app_id));
            $url = kernel::single('system_shopmatrix')->get_api_url(base_shopnode::node_id($this->app->app_id));
        }
        
        $core_http = kernel::single('base_httpclient');
        $response = $core_http->set_timeout($this->timeout)->post($url,$query_params,$headers);
        
        logger::info('Response: '.$response);
        
        if($response===HTTP_TIME_OUT){
            $headers = $core_http->responseHeader;
            logger::info('Request timeout, process-id is '.$headers['process-id']);
            $api_log->update(array('msg_id'=>$headers['process-id'],'status'=>'fail','msg'=>'请求超时'),$rpc_id,$rpc_calltime);
            $this->status = RPC_RST_RUNNING;
            return false;
        }else{
            $result = json_decode($response);
            if($result){
                $this->error = $response->error;
                switch($result->rsp){
                case 'running':
                    $this->status = RPC_RST_RUNNING;
                    $api_log->update(array('msg_id'=>$result->msg_id,'status'=>'running'),$rpc_id,$rpc_calltime);
                    // 存入中心给的process-id也就是msg-id
                    return true;

                case 'succ':
                    $result = json_decode($response,true);
                    $api_log->update(array('msg_id'=>$result['msg_id'],'status'=>'success','calltime'=>time()),$rpc_id,$rpc_calltime);
                    $this->status = RPC_RST_FINISH;
                    $this->rpc_response = $response;
                    return $result['data'];

                case 'fail':
                    $this->error = 'Bad response';
                    $this->status = RPC_RST_ERROR;
                    $api_log->update(array('msg_id'=>$result->msg_id,'status'=>'fail','msg'=>$result->res),$rpc_id,$rpc_calltime);
                    $this->rpc_response = $response;
                    return false;
                }

            }else{
                //error 解码失败
            }
        }
    }

    public function set_callback($callback_class,$callback_method,$callback_params=null){
        $this->callback_class = $callback_class;
        $this->callback_method = $callback_method;
        $this->callback_params = $callback_params;
        return $this;
    }

    public function set_timeout($timeout){
        $this->timeout = $timeout;
        return $this;
    }
	
	public function set_api_version($version){
		$this->api_request_version = $version;
	}

    private function api_version($method){ return $this->api_request_version; }

}
