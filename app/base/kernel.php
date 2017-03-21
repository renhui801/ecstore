<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

if(!defined('APP_DIR')){
    define('APP_DIR',ROOT_DIR.'/app');
}
if(!defined('PUBLIC_DIR')){
    define('PUBLIC_DIR',ROOT_DIR.'/public');
}

if(!defined('ECAE_MODE') && defined('ECAE_SITE_ID') && ECAE_SITE_ID > 0){
    @define('ECAE_MODE', true);
}else{
    @define('ECAE_MODE', false);
}

error_reporting(E_ALL ^ E_NOTICE);

// ego version
if(file_exists(ROOT_DIR.'/app/base/ego/ego.php')){
    require_once(ROOT_DIR.'/app/base/ego/ego.php');
}
define('LOG_SYS_EMERG', 0);
define('LOG_SYS_ALERT', 1);
define('LOG_SYS_CRIT', 2);
define('LOG_SYS_ERR', 3);
define('LOG_SYS_WARNING', 4);
define('LOG_SYS_NOTICE', 5);
define('LOG_SYS_INFO', 6);
define('LOG_SYS_DEBUG', 7);


class kernel{

    static $base_url = null;
    static $url_app_map = array();
    static $app_url_map = array();
    static $console_output = false;
    static private $__online = null;
    static private $__router = null;
    static private $__db_instance = null;
    static private $__singleton_instance = array();
    static private $__request_instance = null;
    static private $__single_apps = array();
    static private $__service_list = array();
    static private $__base_url = array();
    static private $__language = null;
    static private $__service = array();
    static private $__require_config = null;
    static private $__host_mirrors = null;
    static private $__host_mirrors_count = null;
    static function boot(){
        set_error_handler(array('kernel', 'exception_error_handler'));

        try{
            if(!self::register_autoload()){
                require(dirname(__FILE__) . '/autoload.php');
            }

            require(ROOT_DIR.'/config/mapper.php');
            if( self::is_online() ){
                require(ROOT_DIR.'/config/config.php');
            }
            @include(APP_DIR.'/base/defined.php');
            date_default_timezone_set(
                defined('DEFAULT_TIMEZONE') ? ('Etc/GMT'.(DEFAULT_TIMEZONE>=0?(DEFAULT_TIMEZONE*-1):'+'.(DEFAULT_TIMEZONE*-1))):'UTC'
            );

            self::$url_app_map = $urlmap;
            foreach(self::$url_app_map AS $flag=>$value){
                self::$app_url_map[$value['app']] = $flag;
            }

            if(get_magic_quotes_gpc()){
                self::strip_magic_quotes($_GET);
                self::strip_magic_quotes($_POST);
            }


            $pathinfo = self::request()->get_path_info();
            $jump = false;
            if(isset($pathinfo{1})){
                if($p = strpos($pathinfo,'/',2)){
                    $part = substr($pathinfo,0,$p);
                }else{
                    $part = $pathinfo;
                    $jump = true;
                }
            }else{
                $part = '/';
            }

            if($part=='/api'){
                return kernel::single('base_rpc_service')->process($pathinfo);
            }elseif($part=='/openapi'){
                return kernel::single('base_rpc_service')->process($pathinfo);
            }elseif($part=='/app-doc'){
                //cachemgr::init();
                return kernel::single('base_misc_doc')->display($pathinfo);
            }

            if(isset(self::$url_app_map[$part])){
                if($jump){
                    $request_uri = self::request()->get_request_uri();
                    $urlinfo = parse_url($request_uri);
                    $query = $urlinfo['query']?'?'.$urlinfo['query']:'';
                    header('Location: '.$urlinfo['path'].'/'.$query);
                    exit;
                }else{
                    $app = self::$url_app_map[$part]['app'];
                    $prefix_len = strlen($part)+1;
                    kernel::set_lang(self::$url_app_map[$part]['lang']);
                }
            }else{
                $app = self::$url_app_map['/']['app'];
                $prefix_len = 1;
                kernel::set_lang(self::$url_app_map['/']['lang']);
            }

            if(!$app){
                readfile(ROOT_DIR.'/app/base/readme.html');
                exit;
            }

            if(!self::is_online()){
                if(file_exists(APP_DIR.'/setup/app.xml')){
                    if($app!='setup'){
                        //todo:进入安装check
                        setcookie('LOCAL_SETUP_URL', app::get('setup')->base_url(1), 0, '/');
                        header('Location: '. kernel::base_url().'/app/setup/check.php');
                        exit;
                    }
                }else{
                    echo '<h1>System is Offline, install please.</h1>';
                    exit;
                }
            }

            // 检查是否手机端
            if(base_mobiledetect::is_mobile()){
                base_mobiledetect::select_terminator($part,$_GET['ignore_ua_check'],self::$url_app_map);
            }

            if(isset($pathinfo{$prefix_len})){
                $path = substr($pathinfo,$prefix_len);
            }else{
                $path = '';
            }

            //init cachemgr
            if($app=='setup'){
                cachemgr::init(false);
            }else{
                cachemgr::init();
                cacheobject::init();
            }

            //get app router
            self::$__router = app::get($app)->router();
            self::$__router->dispatch($path);

        }catch(Exception $e){
            base_errorpage::exception_handler($e);
        }
    }

    static function exception_error_handler($errno, $errstr, $errfile, $errline )
    {
        switch ($errno) {
            case E_ERROR:
            case E_USER_ERROR:
                logger::error(sprintf('error: %s, severity:%s, file:%s, line:%s', $errstr, $errno, $errfile, $errline));
                throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            break;
            case E_STRICT:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            default:
                //do nothing
            break;
        }
        return true;
    }//End Function

    static function router(){
        return self::$__router;
    }

	static function openapi_url($openapi_service_name,$method='access',$params=null){
        if(substr($openapi_service_name,0,8)!='openapi.'){
            trigger_error('$openapi_service_name must start with: openapi.');
            return false;
        }
        $arg = array();
        foreach((array)$params as $k=>$v){
            $arg[] = urlencode($k);
            $arg[] = urlencode(str_replace('/','%2F',$v));
        }
        return kernel::base_url(1).kernel::url_prefix().'/openapi/'.substr($openapi_service_name,8).'/'.$method.'/'.implode('/',$arg);
        }

    static function request(){
        if(!isset(self::$__request_instance)){
            self::$__request_instance = kernel::single('base_request',1);
        }
        return self::$__request_instance;
    }

    static function url_prefix(){
        return (defined('WITH_REWRITE') && WITH_REWRITE === true)?'':'/index.php';
    }

    static function this_url($full=false){
        return self::base_url($full).self::url_prefix().self::request()->get_path_info();
    }

    static private function get_host_mirror(){
        if(defined('HOST_MIRRORS')){
            if (!isset(self::$__host_mirrors)) {
                $host_mirrors = preg_split('/[,\s]+/',constant('HOST_MIRRORS'));
                if(is_array($host_mirrors) && isset($host_mirrors[0])){
					self::$__host_mirrors = &$host_mirrors;
					self::$__host_mirrors_count = count($host_mirrors)-1;
                }
            }
			return self::$__host_mirrors[rand(0, self::$__host_mirrors_count)];
		}
		return false;
    }

	static function get_resource_host_url($local_flag = false){
        if(defined('HOST_MIRRORS')&&(!$local_flag)){
            if (!isset(self::$__host_mirrors)) {
                $host_mirrors = preg_split('/[,\s]+/',constant('HOST_MIRRORS'));
                if(is_array($host_mirrors) && isset($host_mirrors[0])){
					self::$__host_mirrors = &$host_mirrors;
					self::$__host_mirrors_count = count($host_mirrors)-1;
                }
            }
			return self::$__host_mirrors[rand(0, self::$__host_mirrors_count)];
		}
		return kernel::base_url(1);
	}

	static function get_themes_host_url($local_flag = false){
        if (defined('ECAE_MODE')) {
            return kernel::get_resource_host_url($local_flag).'/themes';
        }else{
            return kernel::get_resource_host_url($local_flag).substr(THEME_DIR, strlen(ROOT_DIR));
        }
    }

	static function get_app_statics_host_url($local_flag = false){
        return kernel::get_resource_host_url($local_flag).substr(PUBLIC_DIR, strlen(ROOT_DIR)).'/app';
	}
	//APP_STATICS_HOST

    static function base_url($full=false){
        $c = ($full) ? 'true' : 'false';
        if(!isset(self::$__base_url[$c]) || defined('BASE_URL')){
            // var_dump(constant('BASE_URL'));
            if(defined('BASE_URL')){
                if($full){
                    self::$__base_url[$c] = constant('BASE_URL');
                }else{
                    $url = parse_url(constant('BASE_URL'));
                    if(isset($url['path'])){
                        self::$__base_url[$c] = $url['path'];
                    }else{
                        self::$__base_url[$c] = '';
                    }
                }
            }else{
                if(!isset(self::$base_url)){
                    self::$base_url = self::request()->get_base_url();
                }

                if(self::$base_url == '/'){
                    self::$base_url = '';
                }

                if($full){
                    self::$__base_url[$c] = strtolower(self::request()->get_schema()).'://'.self::request()->get_host().self::$base_url;
                }else{
                    self::$__base_url[$c] = self::$base_url;
                }
            }
        }
        return self::$__base_url[$c];
    }

    static function set_online($mode){
        self::$__online = $mode;
    }

    static function is_online(){
        if(self::$__online===null){
            if(ECAE_MODE){
                if(file_exists(ROOT_DIR.'/config/config.php') && $__require_config === null ){
                    require(ROOT_DIR.'/config/config.php');
                    $__require_config = true;
                }else{
                    self::$__online = false;
                    return self::$__online;
                }
                $ecos_install_lock = app::get('base')->getConf('ecos.install.lock');
                empty($ecos_install_lock)?self::$__online=false:self::$__online=true;
            }else{
                self::$__online = file_exists(ROOT_DIR.'/config/config.php' );
            }
        }
        return self::$__online;
    }

    static function single($class_name,$arg=null){
        if($arg===null){
            $p = strpos($class_name,'_');
            if($p){
                $app_id = substr($class_name,0,$p);
                if(!isset(self::$__single_apps[$app_id])){
                    self::$__single_apps[$app_id] = app::get($app_id);
                }
                $arg = self::$__single_apps[$app_id];
            }
        }
        if(is_object($arg)){
            $key = get_class($arg);
            if($key==='app'){
                $key .= '.' . $arg->app_id;
            }
            $key = '__class__' . $key;
        }else{
            $key = md5('__key__'.serialize($arg));
        }
        if(!isset(self::$__singleton_instance[$class_name][$key])){
            self::$__singleton_instance[$class_name][$key] = new $class_name($arg);
        }
        return self::$__singleton_instance[$class_name][$key];
    }

    static function database(){
        if(!isset(self::$__db_instance)){
            $classname = defined('DATABASE_OBJECT') ? constant('DATABASE_OBJECT') : 'base_db_connections';
            $obj = new $classname;
            if($obj instanceof base_interface_db){
                self::$__db_instance = $obj;
            }else{
                trigger_error(DATABASE_OBJECT.' must implements base_interface_db!', E_USER_ERROR);
                exit;
            }
        }
        return self::$__db_instance;
    }

    static function service($srv_name,$filter=null){
        return self::servicelist($srv_name,$filter)->current();
    }

    static function servicelist($srv_name,$filter=null){
	    if(self::is_online()){
            $service_define = syscache::instance('service')->get($srv_name);
            if (!is_null($service_define)) {
                return new service($service_define,$filter);
            }else{
                return new ArrayIterator(array());
            }
            /*
            if(!isset(self::$__service[$srv_name])){
				if(base_kvstore::instance('service')->fetch($srv_name,$service_define)){
					self::$__service[$srv_name] = new service($service_define,$filter);
					return self::$__service[$srv_name];
				}
			}else{
				return self::$__service[$srv_name];
			}
            */
		}
        return new ArrayIterator(array());
	}

    static function strip_magic_quotes(&$var){
        foreach($var as $k=>$v){
            if(is_array($v)){
                self::strip_magic_quotes($var[$k]);
            }else{
                $var[$k] = stripcslashes($v);
            }
        }
    }

    static function register_autoload($load=array('kernel', 'autoload'))
    {
        if(function_exists('spl_autoload_register')){
            return spl_autoload_register($load);
        }else{
            return false;
        }
    }

    static function unregister_autoload($load=array('kernel', 'autoload'))
    {
        if(function_exists('spl_autoload_register')){
            return spl_autoload_unregister($load);
        }else{
            return false;
        }
    }

    static function autoload($class_name)
    {
        $p = strpos($class_name,'_');

        if($p){
            $owner = substr($class_name,0,$p);
            $class_name = substr($class_name,$p+1);
            $tick = substr($class_name,0,4);
            switch($tick){
            case 'ctl_':
                if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/'.$owner.'/controller/'.str_replace('_','/',substr($class_name,4)).'.php')){
                    $path = CUSTOM_CORE_DIR.'/'.$owner.'/controller/'.str_replace('_','/',substr($class_name,4)).'.php';
                }else{
                    $path = APP_DIR.'/'.$owner.'/controller/'.str_replace('_','/',substr($class_name,4)).'.php';
                }
                if(file_exists($path)){
                    return require_once $path;
                }else{
                    throw new exception('Don\'t find controller file');
                    exit;
                }
            case 'mdl_':
                if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/'.$owner.'/model/'.str_replace('_','/',substr($class_name,4)).'.php')){
                    $path = CUSTOM_CORE_DIR.'/'.$owner.'/model/'.str_replace('_','/',substr($class_name,4)).'.php';
                }else{
                    $path = APP_DIR.'/'.$owner.'/model/'.str_replace('_','/',substr($class_name,4)).'.php';
                }
                if(file_exists($path)){
                    return require_once $path;
                }elseif(file_exists(APP_DIR.'/'.$owner.'/dbschema/'.substr($class_name,4).'.php') || file_exists(CUSTOM_CORE_DIR.'/'.$owner.'/dbschema/'.substr($class_name,4).'.php')){
                    $parent_model_class = app::get($owner)->get_parent_model_class();
                    eval ("class {$owner}_{$class_name} extends {$parent_model_class}{ }");
                    return true;
                }else{
                    throw new exception('Don\'t find model file "'.$class_name.'"');
                    exit;
                }
            default:
                if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/'.$owner.'/lib/'.str_replace('_','/',$class_name).'.php')){
                    $path = CUSTOM_CORE_DIR.'/'.$owner.'/lib/'.str_replace('_','/',$class_name).'.php';
                }else{
                    $path = APP_DIR.'/'.$owner.'/lib/'.str_replace('_','/',$class_name).'.php';
                }
                if(file_exists($path)){
                    return require_once $path;
                }else{
                    throw new exception('Don\'t find lib file "'.$owner.'_'.$class_name.'"');
                    return false;
                }
            }
        }elseif(file_exists($path = APP_DIR.'/base/lib/static/'.$class_name.'.php')){
            if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/base/lib/static/'.$class_name.'.php')){
                 $path = CUSTOM_CORE_DIR.'/base/lib/static/'.$class_name.'.php';
            }
            return require_once $path;
        }else{
            throw new exception('Don\'t find static file "'.$class_name.'"');
            return false;
            //exit;
        }
    }

    static public function set_lang($language)
    {
        self::$__language = trim($language);
    }//End Function

    static public function get_lang()
    {
        return  self::$__language ? self::$__language : ((defined('LANG')&&constant('LANG')) ? LANG : 'zh_CN');
    }//End Function

}

if (!function_exists('gettext')) {
    require_once(APP_DIR.'/base/lib/static/gettext.inc');
}

if (!function_exists('__') ) {
    function __($str){
        return $str;
    }
}
