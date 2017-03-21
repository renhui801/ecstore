<?php
class base_prism extends base_client{

    var $app_key;
    var $app_secret;
    var $base_url;
    var $access_token;
    var $https_mode;

    private $_oauth_url;
    private $_oauth_token;
    private $_oauth_nonce;
    private $_oauth_view;
    public $strip_code_on_login = true;
    private $site_login_url = '';
    private $redirect_url = '';

    function __construct($base_url, $app_key, $app_secret){
        parent::__construct();
        $this->base_url = rtrim($base_url, '/');
        $this->app_key = $app_key;
        $this->app_secret = $app_secret;
    }

    public function action($method, $path, $headers=null, $data=null){
        $url = $this->base_url .'/'. ltrim($path, '/');
        $query = array();
        $url_info = parse_url($url);
        if(isset($url_info['query'])){
            parse_str($url_info['query'], $query);
        }

        if($method=='GET'){
            if (is_array($data)) {
                $query = array_merge($query, $data);
            }
            $data = null;
            $request = &$query;
        }else{
            $request = &$data;
        }

        if($this->access_token){
            $headers["Authorization"] = "Bearer ".$this->access_token;
        }

        $request['client_id'] = $this->app_key;

        if($this->https_mode){
            $request['client_secret'] = $this->app_secret;
        }else{
            $request['sign_time'] = time();
            $request['sign_method'] = 'md5';
            $request['sign'] = $this->sign($this->app_secret, $method, $url_info['path'], $headers, $query, $data);
        }

        $url_info['query'] = http_build_query($query);
        $url = $this->build_url($url_info);


        $this->log("url: ". $url);

        $result = parent::action($method, $url, $headers, $data);
        /*$data = json_decode($result, true);
echo "<br>====================================================<br>";
var_dump($result);
echo "<br>";
var_dump($data);
echo "<br>====================================================<br>";
return $data?$data:$result;*/
        return $result;
    }

    public function get($path, $data=null, $headers=null){
        return $this->action('GET', $path, $headers, $data);
    }

    public function post($path, $data=null, $headers=null){
        return $this->action('POST', $path, $headers, $data);
    }

    public function put($path, $data=null, $headers=null){
        return $this->action('PUT', $path, $headers, $data);
    }

    public function delete($path, $data=null, $headers=null){
        return $this->action('DELETE', $path, $headers, $data);
    }

    private function sign($secret, $method, $path, $headers, &$query, &$post){
        $sign = array(
                    $secret,
                    $method,
                    rawurlencode($path),
                    rawurlencode($this->sign_headers($headers)),
                    rawurlencode($this->sign_params($query)),
                    rawurlencode($this->sign_params($post)),
                    $secret
            );

        $sign = implode('&', $sign);
        $this->log("signstr: ". $sign);
        return strtoupper(md5($sign));
    }

    private function sign_headers($headers){
        if(is_array($headers)){
            ksort($headers);
            $ret = array();
            foreach($headers as $k=>$v){
                if ( ($k == 'Authorization') || (substr($k, 0, 6)=='X-Api-') ) {
                    $ret[] = $k.'='.$v;
                }
            }
            return implode('&', $ret);
        }
    }

    private function sign_params(&$params){
        if(is_array($params)){
            ksort($params);
            $ret = array();
            foreach($params as $k=>&$v){
                if (null == $v) {
                   $v = '';
                }
                $ret[] = $k.'='.$v;
            }
            return implode('&', $ret);
        }
    }

    public function notify(){
        include_once(dirname(__FILE__).'/notify.php');
        return new prism_notify($this);
    }

    public function go_authorize($state = ''){
        $callback = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $params = array(
                'response_type' => 'code',
                'client_id' => $this->app_key,
                'redirect_uri' => $this->redirect_uri ? $this->redirect_uri : $callback,
            );
        if($state){
            $params['state'] = $params;
        }
        //header("Location: ". $this->authorize_url().'?'.http_build_query($params));
        if($this->site_login_url){
           header("Location: ". $this->site_login_url);
        }else{
            header("Location: ". $this->authorize_url().'?'.http_build_query($params));
        }
        exit();
    }

    public function get_oauth_url(){
        if(!$this->_oauth_url){
            $url = parse_url($this->base_url);
            $url['path'] = $this->realpath($url['path'].'/../oauth');
            $this->_oauth_url = $this->build_url($url);
        }
        return $this->_oauth_url;
    }

    public function get_authorize_url(){
        //$callback = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $params = array(
            'response_type' => 'code',
            'client_id' => $this->app_key,
            'redirect_uri' => $this->redirect_url,
        );
        if ($this->_oauth_view) {
            $params['view'] = $this->_oauth_view;
        }
        if($state){
            $params['state'] = $params;
        }
        return $this->authorize_url().'?'.http_build_query($params);
    }

    private function realpath($path) {
        $out=array();
        foreach(explode('/', $path) as $i=>$fold){
            if ($fold=='' || $fold=='.') continue;
            if ($fold=='..' && $i>0 && end($out)!='..') array_pop($out);
            else $out[]= $fold;
        }
        return ($path{0}=='/'?'/':'').join('/', $out);
    }

    private function set_oauth_url($url){
        $this->_oauth_url = $url;
    }

    public function set_oauth_view($view = ''){
        $this->_oauth_view = $view;
    }

    private function authorize_url(){
        return $this->get_oauth_url().'/authorize';
    }

    private function token_url(){
        return $this->get_oauth_url().'/token';
    }

    private function logout_url(){
        return $this->get_oauth_url().'/logout';
    }

    public function refresh_token($token = "") {
        if($token==""){
            $token = $this->access_token;
        }
        return $this->post_oauth('/token', array(
                'access_token'=>$token,
                'grant_type' => 'refresh_token'
            ));
    }

    private function post_oauth($path, $params){
        $this->https_mode = true;

        $api_base_url = $this->base_url;
        $this->base_url = $this->get_oauth_url();
        $result = $this->post($path, $params);
        $this->base_url = $api_base_url;
        return $result;
    }

    public function process_oauth_callback() {
        if($_GET['code']) {
            $result = $this->post_oauth('/token', array(
                    'code'=>$_GET['code'],
                    'grant_type' => 'authorization_code'
                ));
            $result = json_decode($result);
            if(isset($result->access_token)) {
                $result->expires_time = time() + $result->expires_in;
                if($result->access_token) {
                    $this->access_token = $result->access_token;
                }
                return $result;
            }else{
                trigger_error($result->message, E_USER_ERROR);
            }
        }
    }

    public function logout(&$var){
        $var = null;
        $callback = 'http://'.$_SERVER['HTTP_HOST'];
        $params = array(
            'redirect_uri' => $callback,
        );

        header("Location: ". $this->logout_url().'?'.http_build_query($params));
        exit();
    }

    public function check_session(&$data) {
        if($data){
            $result = $this->post('/api/platform/oauth/session_check',
                array('session_id'=>$data->session_id));
            $result = json_decode($result);
            if( $result->error != null ){
                $data = null;
            }

            $this->access_token = $data->access_token;
            $this->oauth_data = $data;
            $this->oauth_data->expires_in = $this->oauth_data->expires_time - time();
        }else{
            $rst = $this->process_oauth_callback();
            if($rst && $this->access_token){
                $this->oauth_data = $rst;
            }
        }
        return $this->oauth_data;
    }
    
    public function require_oauth(&$data, $on_login_callback = null) {
        if($data){
            $result = $this->post('/api/platform/oauth/session_check',
                array('session_id'=>$data->session_id));
            $result = json_decode($result);
            if( $result->error != null ){
                $data = null;
                $this->go_authorize();
            }

            $this->access_token = $data->access_token;
            $this->oauth_data = $data;
            $this->oauth_data->expires_in = $this->oauth_data->expires_time - time();

            /*
if(is_callable($on_login_callback)){
$on_login_callback($data);
}
*/

        }else{
            $rst = $this->process_oauth_callback();
            if($rst && $this->access_token){
                $data = $rst;
                $this->oauth_data = $data;
                
                if(is_callable($on_login_callback)){
                    $on_login_callback($data);
                }

                if($this->strip_code_on_login){
                    $this->_strip_code_redirect();
                }
            }else{
                $this->go_authorize();
            }
        }
        return $this->oauth_data;
    }

    private function _strip_code_redirect(){
        if(isset($_GET['code'])){
            $params = $_GET;
            if (isset($_SERVER['PATH_INFO'])) {
                $redirect = $_SERVER['PATH_INFO'];
            }
            else {
                $redirect = $_SERVER['DOCUMENT_URI'];
            }
            unset($params['code']);
            if($params){
               $redirect .= '?'.http_build_query($params);
            }
            header("Location: ".$redirect);
            exit;
        }
    }

    public function set_site_login_url($login_url){
        $this->site_login_url = $login_url;
    }
     
    public function set_redirect_url($url=''){
        if($url){
            $this->redirect_url = $url;
        }else{
            $callback = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $this->redirect_url = $callback;
        }
    }
}