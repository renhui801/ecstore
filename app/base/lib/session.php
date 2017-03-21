<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class base_session{
    
    private $_sess_id;
    private $_sess_key = 's';
    private $_session_started = false;
    private $_sess_expires = 60;
    private $_cookie_expires = 0;
    private $_session_destoryed = false;

    function __construct() 
    {
        if(defined('SESS_NAME') && constant('SESS_NAME'))    $this->_sess_key = constant('SESS_NAME');
        if(defined('SESS_CACHE_EXPIRE') && constant('SESS_CACHE_EXPIRE'))   $this->_sess_expires = constant('SESS_CACHE_EXPIRE');
    }//End Function

    public function sess_id(){
        return $this->_sess_id;
    }
    
    public function set_sess_id($sess_id){
        return $this->_sess_id=$sess_id;
    }

    public function set_sess_expires($minute) 
    {
        $this->_sess_expires = $minute;
    }//End Function

    private function _get_cache_key(){
        if (WITHOUT_CACHE===true) {
            return $this->sess_id();
        }else{
            return 'USER_SESSION:'.$this->sess_id();    
        }
    }
    
    private function _get_session(){
        if (WITHOUT_CACHE===true) {
            if (base_kvstore::instance('sessions')->fetch($this->_get_cache_key(), $return)){
                return $return;
            }else{
                return array();
            }
        }else{
            if (cacheobject::get($this->_get_cache_key(), $return)) {
                return $return;
            }else{
                return array();
            }
        }
    }

    private function _set_session($value, $ttl){
        if (WITHOUT_CACHE===true) {
            return base_kvstore::instance('sessions')->store($this->_get_cache_key(), $value, $ttl );
        }else{
            return cacheobject::set($this->_get_cache_key(), $value, $ttl+time());
        }
    }
    
    public function set_cookie_expires($minute) 
    {
        $this->_cookie_expires = ($minute > 0) ? $minute : 0;
        if(isset($this->_sess_id)){
            $cookie_path = kernel::base_url();
            $cookie_path = $cookie_path ? $cookie_path : "/";
            header(sprintf('Set-Cookie: %s=%s; path=%s; expires=%s; httpOnly;', $this->_sess_key, $this->_sess_id, $cookie_path, gmdate('D, d M Y H:i:s T', time()+$minute*60)), true);
        }
    }//End Function

    public function start(){
        if($this->_session_started !== true){
            $cookie_path = kernel::base_url();
            $cookie_path = $cookie_path ? $cookie_path : "/";
            if($this->_cookie_expires > 0){
                $cookie_expires = sprintf("expires=%s;",  gmdate('D, d M Y H:i:s T', time()+$this->_cookie_expires*60));
            }else{
                $cookie_expires = '';
            }
            if(isset($_GET['sess_id'])){
                $this->_sess_id = $_GET['sess_id'];
                if($_COOKIE[$this->_sess_key] != $_GET['sess_id'])
                    header(sprintf('Set-Cookie: %s=%s; path=%s; %s httpOnly;', $this->_sess_key, $this->_sess_id, $cookie_path, $cookie_expires), true);
                $_SESSION = $this->_get_session();
            }elseif($_COOKIE[$this->_sess_key]){
                $this->_sess_id = $_COOKIE[$this->_sess_key];
                $_SESSION = $this->_get_session();
            }elseif(!$this->_sess_id){
                $this->_sess_id = $this->gen_session_id();
                $_SESSION = array();
                header(sprintf('Set-Cookie: %s=%s; path=%s; %s httpOnly;', $this->_sess_key, $this->_sess_id, $cookie_path, $cookie_expires), true);
            }
            $this->_session_started = true;
            register_shutdown_function(array(&$this,'close'));
        }
        return true;
    }

    public function close($writeBack = true){
        if(strlen($this->_sess_id) != 32){ 
            return false; 
        } 
        if(!$this->_session_started){
            return false;
        }
        $this->_session_started = false;
        if(!$writeBack){
            return false;
        }
        if($this->_session_destoryed){
            return true;
        }else{
            try {
                return $this->_set_session($_SESSION, $this->_sess_expires * 60);
            }catch(Exception $e){
                
            }
        }
    }

    protected function gen_session_id() {
        return md5(uniqid('', true).base_request::get_remote_addr().microtime(true).mt_rand(0,9999));
    }
    
    public function destory(){
        if(!$this->_session_started){
            return false;
        }
        $this->_session_started = false;
        $res = $this->_set_session(array(), 1);
        if($res){
            $_SESSION = array();
            $this->_session_destoryed = true;
            $cookie_path = kernel::base_url();
            $cookie_path = $cookie_path ? $cookie_path : "/";
            header(sprintf('Set-Cookie: %s=%s; path=%s; httpOnly;', $this->_sess_key, $this->_sess_id, $cookie_path), true);
            unset($this->_sess_id);
            return true;
        }else{
            return false;
        }
    }

}
