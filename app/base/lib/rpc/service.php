<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class base_rpc_service{

    private $start_time;
    private $path = array();
    private $finish = false;
    static $node_id;
    static $api_info;
    static public $is_start = false;

    function __construct(){
        if(!kernel::is_online()){
            die('error');
        }else{
            @include(APP_DIR.'/base/defined.php');
        }
        
        cachemgr::init();
        cacheobject::init();
        self::$is_start = true;
    }

    public function process($path){

        if($path=='/api'){
            $this->process_rpc();
        }else{
			if(strpos($path, '/openapi') !== false){
				$args = explode('/',substr($path,9));
				$service_name = 'openapi.'.array_shift($args);
				$method = array_shift($args);
				foreach($args as $i=>$v){
					if($i%2){
						$params[$k] = str_replace('%2F','/',$v);
					}else{
						$k = $v;
					}
				}
				kernel::service($service_name)->$method($params);
			}
		}
    }

    private function begin()
    {
        register_shutdown_function(array(&$this, 'shutdown'));
        array_push($this->path,$key);
        @ob_start();
    }//End Function

    private function end($shutdown=false){
        if($this->path){
            $this->finish = true;
            $content = ob_get_contents();
            ob_end_clean();
            $name = array_pop($this->path);
            if(defined('SHOP_DEVELOPER')){
                error_log("\n\n".str_pad(@date(DATE_RFC822).' ',60,'-')."\n".$content
                    ,3,ROOT_DIR.'/data/logs/trace.'.$name.'.log');
            }
            if($shutdown){
                echo json_encode(array(
                    'rsp'=>'fail',
                    'res'=>$content,
                    'data'=>null,
                ));
                exit;
            }
            return $content;
        }
    }

    public function shutdown(){
        $this->end(true);
    }


    //app_id     String     Y     分配的APP_KEY
    //method     String     Y     api接口名称
    //date     string     Y     时间戳，为datetime格式
    //format     string     Y     响应格式，xml[暂无],json
    //certi_id     int     Y     分配证书ID
    //v     string     Y     API接口版本号
    //sign     string     Y     签名，见生成sign
    private function parse_rpc_request($request){

        $sign = $request['sign'];
        unset($request['sign']);

		$app_id = $request['app_id'];
		if ($app_id)
			$app_id = substr($app_id, strpos($app_id, '.')+1,strlen($app_id));
        if( $request["direct"] == 'true' ) {
            $sign_check = base_certificate::gen_sign($request);
        }
        else {
            if (!base_shopnode::token($app_id))
                $sign_check = base_certificate::gen_sign($request);
            else
                $sign_check = base_shopnode::gen_sign($request,$app_id);
        }

        if('private' == app::get('system')->getConf('system.matrix.set')){
            $sign_check = kernel::single('system_shopmatrix')->get_sign($request);
        }

        if($sign != $sign_check){
            $this->send_user_error('4003', 'sign error');
            return false;
        }

        $system_params = array('app_id','method','date','format','certi_id','v','sign','node_id');
        foreach($system_params as $name){
            $call[$name] = $request[$name];
            unset($request[$name]);
        }


        //api version control 20120627 mabaineng
        $system_params_addon = array('from_node_id', 'from_api_v', 'to_node_id', 'to_api_v');
        foreach($system_params_addon as $name){
          if( $request[$name] ) {
            self::$api_info[$name] = $request[$name];
            unset($request[$name]);
          }
        }


        //if method request = 'aaa.bbb.ccc.ddd'
        //then: object_service = api.aaa.bbb.ccc, method=ddd
        if(isset($call['method']{2})){
            if($p = strrpos($call['method'],'.')){
                $service = substr($call['method'],0,$p);
                self::$api_info['api_name'] = $service;
                $service = 'api.'.$service;
                $method = substr($call['method'],$p+1);
            }
        }else{
            $this->send_user_error('4001', 'error method');
            return false;
        }

        if($call['node_id']){
            self::$node_id = $call['node_id'];
        }

        return array($service,$method,$request);
    }

    private function gen_uniq_process_id(){
        return uniqid();
    }

    private function process_rpc(){

        ignore_user_abort();
        set_time_limit(0);
        $this->process_id = $this->gen_uniq_process_id();
        header('Process-id: '.$this->process_id);
        header('Connection: close');
        flush();

        if(get_magic_quotes_gpc()){
            kernel::strip_magic_quotes($_REQUEST);
        }

        if(strtolower($_SERVER['HTTP_CONTENT_ENCODING']) == 'gzip'){
            $_input = fopen('php://input','rb');
            while(!feof($_input)){
                $_post .= fgets($_input);
            }
            fclose($_input);
            $_post = utils::gzdecode($_post);
            parse_str($_post, $post);
            if($post){
                if(get_magic_quotes_gpc()){
                    kernel::strip_magic_quotes($_GET);
                }
                $_REQUEST = array_merge($_GET, $post);
            }
        }//todo: uncompress post data


        $this->begin(__FUNCTION__);
        set_error_handler(array(&$this,'error_handle'),E_ERROR);
        set_error_handler(array(&$this,'user_error_handle'),E_USER_ERROR);

        $this->start_time = $_SERVER['REQUEST_TIME']?$_SERVER['REQUEST_TIME']:time();
        list($service,$method,$params) = $this->parse_rpc_request($_REQUEST);
        $data = array(
            'apilog'=>$_REQUEST['task'],
            'calltime'=>$this->start_time,
            'params'=>$params,
            'api_type'=>'response',
            'msg_id'=>$this->process_id,
            'method'=>$service,
            'worker'=>$service.":".$method,
        );
        $obj_rpc_poll = app::get('apiactionlog')->model('apilog');

        // 防止多次重刷.

        if (!$obj_rpc_poll->db->select('SELECT apilog_id FROM ' . $obj_rpc_poll->table_name(1) . ' WHERE apilog=\''.$_REQUEST['task'].'\' AND api_type=\'response\' LIMIT 0,30 LOCK IN SHARE MODE')) {
            //记录apilog
            $apilog_services = kernel::single('apiactionlog_router_logging');
            $apilog_services->save_log($service,$method,$data);
            $api_module = app::get('base')->getConf($service.'.'.$method);
            if( isset($api_module['function'])  ){
                $object = kernel::single($api_module['class']);
                $result = $object->$method($params,$this);
                $output = $this->end();
            }else{
                $output = $this->end();
                $msg = 'ECstore中不存在'.$_REQUEST['method'].'接口';
                $output = app::get('base')->_($msg);
                $status = 'fail';
            }
        }else {
            $output = $this->end();
            $output = app::get('base')->_('该请求已经处理，不能在处理了！');
        }

        $result_json = array(
            'rsp'=> $status ? $status : 'succ',
            'data'=>$result,
            'res'=>strip_tags($output)
        );

        $this->rpc_response_end($result, $this->process_id, $result_json);
        echo json_encode($result_json);
    }

    private function rpc_response_end($result, $process_id, $result_json)
    {
        if (isset($process_id) && $process_id)
        {
            $connection_aborted = $this->connection_aborted();
            $obj_rpc_poll = app::get('apiactionlog')->model('apilog');
            switch($result_json['rsp']){
            case 'succ':
                $status="success";
                break;
            case 'fail':
                $status="fail";
                break;
            }
            $data=array(
                'status'=>$status,
                'msg'=>$result_json['res'],
                'msg_id'=>$process_id,
                'api_type'=>'response',
                );
            $obj_rpc_poll->save_data($data);

            if($connection_aborted){
                if($_SERVER['HTTP_CALLBACK']){
                    $return = kernel::single('base_httpclient')->get($_SERVER['HTTP_CALLBACK'].'?'.json_encode($result_json));
                    $return = json_decode($return);
                }
            }
        }
    }

    private function connection_aborted(){
        $return = connection_aborted();
        if(!$return){
            if(is_numeric($_SERVER['HTTP_CONNECTION']) && $_SERVER['HTTP_CONNECTION']>0){
                if(time()-$this->start_time>=$_SERVER['HTTP_CONNECTION']){
                    $return = true;
                }
            }
        }
        return $return;
    }

    public function async_result_handler($params){

        $this->begin(__FUNCTION__);

        $apilog_mdl = app::get('apiactionlog')->model('apilog');
        $api_log = kernel::single('apiactionlog_router_logging');
        $result = new base_rpc_result($_POST,$params['app_id']);
        $arr_rpc_id = explode('-', $params['id']);
        $rpc_id = $arr_rpc_id[0];
        $rpc_calltime = $arr_rpc_id[1];
        $status = $result->get_status();
        $msg_id = $result->get_pid();
        $data = $result->get_data();
        if($result->get_result()){
            $msg = $result->get_result().",".$_POST['err_msg'];
        }

        switch($status){
            case 'succ':
                $log_status = 'success';
                break;
            case 'fail':
                $log_status = 'fail';
                break;
        }

        $log_data = array(
            'status'=>$log_status,
            'msg'=>$msg,
            'msg_id'=>$msg_id,
        );
        $return = $api_log->update($log_data,$rpc_id,$rpc_calltime);

        if(!$return){
            $return = array(
                "rsp"=>"fail",
                "res"=>"",
                "msg_id"=>"",
            );
        }else{
            $return = array(
                'rsp' => $status,
                'res' => $msg,
                'data' => $data,
            );
        }

        $this->end();

        header('Content-type: text/plain');
        echo json_encode($return);
    }

    function error_handle($error_code, $error_msg){
        $this->send_user_error('4007', $error_msg);
    }

    function user_error_handle($error_code, $error_msg){
        $this->send_user_error('4007', $error_msg);
    }

    public function send_user_error($code, $data)
    {
        $this->end();
        $res = array(
            'rsp'   =>  'fail',
            'res'   =>  $code,
            'data'  =>  $data,
        );
        $this->rpc_response_end($data,$this->process_id, $res);
        echo json_encode($res);
        exit;
    }//End Function

}
