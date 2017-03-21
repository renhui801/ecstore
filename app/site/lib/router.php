<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


/*
 * @package site
 * @copyright Copyright (c) 2010, shopex. inc
 * @author edwin.lzh@gmail.com
 * @license
 */
class site_router implements base_interface_router
{

    /*
	 * 系统模块对应数组, 通过路径标识取得对应的模块信息
	 *
	 * array('<路径标识>' => array('app'=>'<app_name>', 'ctl'=>'<controler_name>', title'='<title>', 'extension'=>'<url_extension_name>', 'use_ssl'=>'<ssl>')
     * @var array $_sitemap
     * @accessvar private
     */
	private $_sitemap = array();

    /*
     * sitemap对应表
     *
	 * array('<app_name>:<ctl_name>' => <路径标识>')
	 *
     * @var array $_urlmap
     * @access private
     */
	private $_urlmap = array();

    /*
     * @var array $_query_info
     * @access private
     */
	private $_query_info = null;

    /*
     * @var object $_request
     * @access private
     */
	private $_request = null;

    /*
     * @var object $_response
     * @access private
     */
	private $_response = null;

    /*
     * 保存当前进程的gen_url, 避免重复生成
     *
     * @var object $_response
     * @access private
     */
	private $__gen_url_array = array();

    /*
     * uri扩展名
     *
     * @var object $_response
     * @access private
     */
	private $__uri_expended_name = null;

    /*
     * 构造
     * @var object $app
     * @access public
     * @return void
     */
    function __construct($app){
        $this->app = $app;
        $this->_sitemap = app::get('site')->getConf('sitemaps');
        if(!is_array($this->_sitemap)){
            $sitemap_config = kernel::single('site_module_base')->assemble_config();
            if(is_array($sitemap_config)){
                $this->_sitemap = $sitemap_config;       //todo：兼容kvstroe出错的情况下
                if(!kernel::single('site_module_base')->write_config($sitemap_config)){
                    logger::info('Error: sitemap can\'t save to kvstore');      //todo：如果写入失败，记录于系统日志中，前台不报错，保证网站运行正常
                }
            }else{
                trigger_error('sitemap is lost!', E_USER_ERROR);       //todo：无sitemap时报错
            }
        }
        foreach($this->_sitemap as $part=>$controller){
            $urlmap[$controller['app'].':'.$controller['ctl']] = $part;
            if($controller['extension'])  $extmap[$part] = '.'.$controller['extension'];
        }

        $this->_urlmap = $urlmap;
        $this->_extmap = $extmap;
        $this->_request = kernel::single('base_component_request');
        $this->_response = kernel::single('base_component_response');

    }//End Function

    /*
     * 取得sitemap
     * @access public
     * @return array
     */
    public function get_sitemap($key = null)
    {
		if ($key === null) {
			return $this->_sitemap;
		}
		return $this->_sitemap[$key];

    }//End Function

	private function get_current_sitemap($key = null){
		if ($key === null) {
			return $this->_sitemap[$this->get_query_info('module')];
		}
		return $this->_sitemap[$this->get_query_info('module')][$key];
	}

    /*
     * 取得urlmap
     * @access public
     * @return array
     */
    public function get_urlmap($key = null)
    {
		if ($key === null) {
			return $this->_urlmap;
		}
		return $this->_urlmap[$key];
    }//End Function

    /*
     * 取得extmap
     * @access public
     * @return array
     */
    public function get_extmap()
    {
        return $this->_extmap;
    }//End Function


    /*
     * 返回分隔符
     * @access public
     * @return string
     */
    public function get_separator()
    {
        if(!isset($this->__separator)){
            $this->__separator = trim(app::get('site')->getConf('base.site_params_separator'));
        }
        return $this->__separator;
    }//End Function

    /*
     * 参数特殊编码
     * @var array $args
     * @access public
     * @return void
     */
    public function encode_args($args)
    {
        if(is_array($args)){
            foreach($args AS $key=>$val){
                $args[$key] = str_replace(array('-', '.', '/', '%2F'), array(';jh;', ';dian;', ';xie;', ';xie;'), $val);
            }
        }else{
            $args = str_replace(array('-', '.', '/', '%2F'), array(';jh;', ';dian;', ';xie;', ';xie;'), $args);
        }
        return $args;
    }//End Function

    /*
     * 参数特殊解码
     * @var array $args
     * @access public
     * @return void
     */
    public function decode_args($args)
    {
        if(is_array($args)){
            foreach($args AS $key=>$val){
                $args[$key] = str_replace(array(';jh;', ';dian;', ';xie;'), array('-', '.', '/'), $val);
            }
        }else{
            $args = str_replace(array(';jh;', ';dian;', ';xie;'), array('-', '.', '/'), $args);
        }
        return $args;
    }//End Function

    /*
     * 后缀名
     * @var void
     * @access public
     * @return string
     */
    public function get_uri_expended_name($part=null)
    {
        if(!isset($this->__uri_expended_name)){
            if(app::get('site')->getConf('base.enable_site_uri_expanded') == 'true'){
                $this->__uri_expended_name = '.' . app::get('site')->getConf('base.site_uri_expanded_name');
            }else{
                $this->__uri_expended_name = '';
            }
        }
        return (!is_null($part) && isset($this->_extmap[$part])) ? $this->_extmap[$part] : $this->__uri_expended_name;
    }//End Function

    /*
     * 产生链接
     * @var array $params
     * @access public
     * @return string
     */
    public function gen_url($params = array())
    {
        $app = $params['app'];
        if(empty($app)) return '/';

        if(!is_null($this->get_urlmap($params['app'].':'.$params['ctl']))){
            if(is_array($params['args']))  ksort($params['args']);
            ksort($params);
            $gen_key = md5(serialize($params));     //todo：缓存相同的url
            if(!isset($this->__gen_url_array[$gen_key])){
                foreach($params AS $k=>$v){
                    if($k!='args' && substr($k, 0, 3)=='arg'){
                        if(empty($v)){
                            unset($params['args'][substr($k, 3)]);
                        }else{
                            $params['args'][substr($k, 3)] = $v;
                        }
                    }
                }//fix smarty function
                $params['args'] = (is_array($params['args'])) ? $this->encode_args($params['args']) : array();
                if(!isset($this->__site_router_service[$app])){
                    $app_router_service = kernel::service('site_router.' . $app);
                    if(is_object($app_router_service) && $app_router_service->enable()){
                        $this->__site_router_service[$app] = $app_router_service;
                    }else{
                        $this->__site_router_service[$app] = false;
                    }
                }
                if($this->__site_router_service[$app]){
                    $this->__gen_url_array[$gen_key] = $this->__site_router_service[$app]->gen_url($params);
                }else{
                    $this->__gen_url_array[$gen_key] = $this->default_gen_url($params);
                }
                $this->__gen_url_array[$gen_key] = utils::_filter_crlf($this->__gen_url_array[$gen_key]);
                
            }
            return $this->__gen_url_array[$gen_key];
        }else{
            return '/';
        }
    }//End Function

    /*
     * 缺省方法
     * @var array $params
     * @access public
     * @return string
     */
    public function default_gen_url($params=array())
    {
        $full = ($params['full']) ? 'true' : 'false';
        $real = ($params['real']) ? 'true' : 'false';
        $params['act'] = ($params['act']) ? $params['act'] : 'index';
        $args_keys = array_keys($params['args']);
        $first_arg = $params['args'][$args_keys[0]];
        if($params['act']=='index' && (count($params['args'])==0 || is_numeric($first_arg))){
            //此情况可省略act 这个太恶心了 EDwin
        }else{
            array_unshift($params['args'], $params['act']);
        }
        $part = $this->get_urlmap($params['app'].':'.$params['ctl']);
        array_unshift($params['args'], $part);
        if(!isset($this->__base_url[$full])){
            $this->__base_url[$full] = app::get('site')->base_url((($full=='true')?true:false));
        }
        $url = implode($this->get_separator(), $params['args']) . $this->get_uri_expended_name($part);
        return $this->__base_url[$full] . (($params['real']=='true') ? $url : $this->parse_route_static_genurl($url));
    }//End Function

    /*
     * http状态
     * @var string $query
     * @access public
     * @return void
     */
    public function http_status($code)
    {
        $this->_response->set_http_response_code($code);
        $this->_response->send_headers();

        $c = 'errorpage.'.$code;
        $msg = kernel::single('site_errorpage_get')->getConf( $c );;
        if( $msg ) kernel::single('site_controller')->splash( 'failed',null,$msg);

        exit;
    }//End Function

    /*
     * @var void
     * @access public
     * @return float
     */
    public function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }//End Function


    /*
     * 检查是来访IP是否在黑名单
	 *
	 * dispatch()子调用, 进行黑名单判断, 如果在黑名单之列则根据配置规则抛出相应http状态码
     * @access private
     * @return void
     */
	private function check_blacklist(){
        if(app::get('site')->getConf('site.blacklist.enabled')) {
            $remote_ip = base_request::get_remote_addr();
            $ips = app::get('site')->getConf('site.blacklist.ips');
            $error_code = app::get('site')->getConf('site.blacklist.error_code');
            $forbidden = false;
            foreach ($ips as $ip) {
                if ( base_request::ip_in_range($remote_ip, $ip)) {
                    $forbidden = true;
                    break;
                }
            }
            if($forbidden) {
                switch ($error_code) {

                case '403':
                    header("HTTP/1.1 403 Forbidden");
                    break;
                case '404':
                    header("HTTP/1.1 404 Not Found");
                    break;
                default:
                    header("HTTP/1.1 403 Forbidden");
                }
                exit();
            }
        }
		return;
	}
	/*
	 * 检查是否使用部份ssl控制
	 *
	 * dispatch()子调用, 检测页面是否部分ssl控制
     *   1) 如果设置过部份SSL控制，则使用https的页面一定使用https,而非https的页面一定用http访问。
     *   2) 否则按默认服务器设置
	 * @access private
	 * @return void
	 */

	private function check_https(){
        /* $use_ssl_control = false; */
        /* foreach ($this->get_sitemap() as $part) { */
        /*     if(isset($part['use_ssl']) && $part['use_ssl'] === true) { */
        /*         $use_ssl_control = true; */
        /*         break; */
        /*     } */
        /* } */
        $use_ssl_control = true;

		$server = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] :  $_SERVER['SERVER_NAME'];

		if($this->get_current_sitemap('use_ssl') === true && $_SERVER['SERVER_PORT'] == '80') {
            header("Location:https://" . $server . ':443'. $_SERVER['REQUEST_URI']);
            exit();
        } else if ( $this->get_current_sitemap('use_ssl') === false && $_SERVER['SERVER_PORT'] == '443') {
            header("Location:http://" . $server . ':80'. $_SERVER['REQUEST_URI']);
            exit();
        }
		return;
	}

	private function check_expanded_name(){
        if(!array_key_exists($this->get_query_info('module'), $this->get_sitemap())){
            $this->http_status(404);   //404页面
        }
        if(app::get('site')->getConf('base.check_uri_expanded_name')=='true' && $this->get_uri_expended_name($this->get_query_info('module'))!=$this->get_query_info('extension')){

            $this->http_status(404);   //404页面
        }
	}

	private function check_router_cache(){
		$use_static_cache = false;
        $app_name = $this->_request->get_app_name();
        $ctl_name = $this->_request->get_ctl_name();
        $act_name = $this->_request->get_act_name();
        foreach( kernel::servicelist('site.router.cache') as $value) {
            if(!method_exists($value, 'get_cache_methods'))  continue;
            $methods = $value->get_cache_methods();
            foreach( (array) $methods as $method ) {
                if(isset($method['app']) && isset($method['ctl']) && isset($method['act'] )
                   && $method['app'] == $app_name && $method['ctl'] == $ctl_name && $method['act'] == $act_name) {
                    $use_static_cache = true;
                    $expires = 0;
                    if(($expires = app::get('site')->getConf($method['app'] . '_' . $method['ctl'] . '_' .$method['act'] . '.cache_expires'))!==null) {
                        $expires = (int) $expires;
                    } elseif(isset($method['expires']) && (int) $method['expires'] ) {
                        $expires = (int) $method['expires'];
                    }
                    if ((int)$expires===0) {
                        $use_static_cache = false;
                    }
                }
            }
        }
        if($use_static_cache){
            $skipvary = true;
            $cache_expires = time() + $expires;
        } else {
            $skipvary = false;
            $cache_expires = 0;
        }
		return array('skipvary' => $skipvary,
					 'expires' => $cache_expires);

	}

	protected $_is_cache = false;

	function is_cache(){
		return $this->_is_cache;
	}

    /*
     * 判断是否为预览
     * @access private
     * @return bool
     */

	public function is_preview() {
		if( ($_COOKIE['site']['preview']) == 'true'){
			return true;
		}
		return false;
	}

	private function is_need_cache() {
		if (count($this->_request->get_post())==0 &&
			app::get('site')->getConf('base.site_page_cache') ==='true' &&
			kernel::single('site_theme_base')->get_default() &&    //判断是否有模板，避免无模板情况cache问题
			!$this->is_preview()){
			return true;
		}
		return false;
	}

    /*
     * 执行
     * @var string $query
     * @access public
     * @return void
     */
    public function dispatch($query){
		$this->check_blacklist(); //黑名单检测
        $page_starttime = $this->microtime_float();

        /** 影响性能暂时去掉
    	foreach(kernel::servicelist('site.router.predispatch') as $obj)	{
			$query = $obj->router_predispatch($query);
        **/
		$this->init_query_info($query);
		$this->init_request_info();
		$this->check_https();
		$this->check_expanded_name();
		$router_cache_options = $this->check_router_cache();
        $page_key = 'SITE_PAGE_CACHE:' . $this->_request->get_request_uri();
        logger::debug('page: '.$this->_request->get_request_uri());
        if(!$this->is_need_cache() || ($this->is_need_cache() && !cachemgr::get($page_key, $page, $router_cache_options['skipvary']))){
            if(WITHOUT_CACHE!==true){
                logger::info('page cache miss:'.$this->_request->get_request_uri());
            }
            cachemgr::co_start();
            $this->default_dispatch();
            $page['html'] = join("\n", $this->_response->get_bodys());
            $page['date'] = date("Y-m-d H:i:s");

            $page['times'] = sprintf('%0.2f', ($this->microtime_float() - $page_starttime));
            if($this->is_need_cache() && $this->_response->get_http_response_code()==200 && $this->has_page_cache_control()===true ){
                $page_cache = true;
                $this->_response->set_header('X-Cache', 'HIT from ecos-pagecache ' . $page['date']);    //todo:记录x-cache
                $page['headers'] = $this->_response->get_headers();
                $page['raw_headers'] = $this->_response->get_raw_headers();
                $page['etag'] = md5($page['html']);
                $cache_options = cachemgr::co_end();

                $cache_options['expires'] = $router_cache_options['expires']; //todo: expires 应该设置为$cache_options['expires'] | $router_cache_options['expires'] 中比较小的那个
				//$cache_options['skip_vary'] = $cache_expires;
                cachemgr::set($page_key, $page, $cache_options);
            }else{
                $page_cache = false;
                cachemgr::co_end();
            }
        }else{
            $page_cache = true;
            $this->_response->clean_headers();
            if(isset($page['headers'])){
                foreach($page['headers'] AS $header){
                    $this->_response->set_header($header['name'], $header['value'], $header['replace']);
                }
            }
            if(isset($page['raw_headers'])){
                foreach($page['raw_headers'] AS $raw_header){
                    $this->_response->set_raw_headers($raw_header);
                }
            }
            if(WITHOUT_CACHE!==true){
                logger::info('page cache hit:'.$this->_request->get_request_uri());
            }
        }

        if($page_cache === true){
            $etag = ($page['etag']) ? $page['etag'] : md5($page['html']);   //todo: 兼容
            $this->_response->set_header('Etag', $etag);
            $matchs = explode(',', $_ENV['HTTP_IF_NONE_MATCH']);
            foreach($matchs AS $match){
                if(trim($match) == $etag){
                    $this->_response->clean_headers();
                    $this->_response->set_header('Content-length', '0');
                    $this->_response->set_http_response_code(304)->send_headers();
                    exit;
                }
            }
        }
        $this->set_vary_cookie();
        $this->_response->send_headers();
        echo $page['html'];
        logger::debug('This page created by ' . $page['date']);
        logger::debug('Mysql queries count:' . base_db_connections::$mysql_query_executions);
        logger::debug('Kvstore queries count:' . base_kvstore::$__fetch_count);
        logger::debug('Page Times: ' . $page['times']);
    }//End Function

    /*
     * 执行
     * @access private
     * @return void
     */
    private function set_vary_cookie()
    {
        $cookie_vary = $_COOKIE['vary'];
        $vary = cachemgr::get_cache_check_version() . md5(serialize(cachemgr::get_cache_global_varys()));
        if($cookie_vary !== $vary){
            setCookie('vary', $vary, time()+86400*30*12*10, '/');
        }
    }//End Function

    private function init_request_info(){
        $query_args = explode($this->get_query_info('separator'), $this->get_query_info('query'));

        $part = array_shift($query_args);
        if(count($query_args)){
            if(is_numeric($query_args[0])){
                $action = 'index';
            }else{
                $action = array_shift($query_args);
            }
        }else{
            $action = 'index';
        }

        $query_args = $this->decode_args($query_args);
        $this->_request->set_app_name($this->get_current_sitemap('app')); //设置app信息
        $this->_request->set_ctl_name($this->get_current_sitemap('ctl')); //设置ctl信息
        $this->_request->set_act_name($action);         //设置act信息
        $this->_request->set_params($query_args);       //设置参数信息
    }
    /*
     * 缺省执行
     * @var string $query
     * @var string $allow_name  //许可名
     * @var string $separator   //分隔符
     * @access public
     * @return void
     */
    public function default_dispatch()
    {
        $controller = app::get($this->_request->get_app_name())->controller($this->_request->get_ctl_name());
        $action = $this->_request->get_act_name();
        $query_args = $this->_request->get_params();
        
        if(method_exists($controller, $action)){
            try{
                call_user_func_array(array($controller, $action), (array)$query_args);

            }catch(Exception $e){
                if (defined('DEBUG_PHP') && constant('DEBUG_PHP')===true) {
                    throw $e;
                }else{
                    $this->http_status(500);   //405页面
                }
            }
        }else{
            $this->http_status(400);   //400页面
        }
    }//End Function

    /*
     * 检查是否存在cache_control的头并判断是否需要页面缓存
     * @access public
     * @return boolean
     */
    public function has_page_cache_control()
    {
        //response对像
        if($this->_response->get_header('cache-control', $header)){
            $caches = explode(',', $header['value']);
            foreach($caches AS $cache){
                if(in_array(strtolower(trim($cache)), array('no-cache', 'no-store'))){
                    return false;
                }
            }
        }
        //php header
        $code_headers = headers_list();
        foreach($code_headers AS $code_header){
            $tmp_header = explode(':', $code_header);
            if(strtolower(trim($tmp_header[0])) == 'cache-control'){
                $caches = explode(',', $tmp_header[1]);
                foreach($caches AS $cache){
                    if(in_array(strtolower(trim($cache)), array('no-cache', 'no-store'))){
                        return false;
                    }
                }
            }
        }
        return true;
    }//End Function

    /*
     * 得到router所允许的名称
     * @var string $query
     * @access public
     * @return array
     */
    private function parse_query($query)
    {
        $query = urldecode($query);
        $query = $this->parse_route_static_dispatch($query);
        $query = ($query=='index.php') ? '' : $query;
        $query = ($query) ? $query : 'index' . $this->get_uri_expended_name();
        $pos = strrpos($query, '.');
        $extended_name = null;
        if($pos > 0){
            $extended_name = substr($query, $pos, strlen($query)-$pos);
            $query = substr($query, 0, $pos);
        }
        //分融符只支持 '-', '/' 或 xxx.html 或 xxx
        preg_match_all('/^([^\/\-]+)([\/\-]{1}).*$/isU', $query, $matchs);
        if(count($matchs[0]))   return array('query'=>$query, 'module'=>$matchs[1][0], 'extension'=>$extended_name, 'separator'=>$matchs[2][0]);
        preg_match_all('/^([^.]+)$/isU', $query, $matchs);
        if(count($matchs[0]))   return array('query'=>$query, 'module'=>$matchs[1][0], 'extension'=>$extended_name, 'separator'=>$this->get_separator());
        return array('query'=>$query, 'module'=>$query, 'extension'=>$extended_name, 'separator'=>$this->get_separator());
    }//End Function


	public function init_query_info($query){
		$query_info = $this->parse_query($query);
		$app_name = $this->_sitemap[$query_info['module']]['app'];
		$service = kernel::service('site_router.' . $app_name);
		if(is_object($service) && $service->enable()){
			$this->_request->clear_params();
			$service->modify_query($query_info);
		}
		$this->_query_info = $query_info;
		return $this;
	}

	public function get_query_info($key = null){
		if ($key === null) {
			return $this->_query_info;
		}
		if (in_array($key, array('module', 'separator', 'query', 'extension'))) {
			return $this->_query_info[$key];
		}
		return false;
	}


    /*
     * 处理静态路由
     * @var string $query
     * @access public
     * @return string
     */
    public function parse_route_static_dispatch($query)
    {
        if($val = kernel::single('site_route_static')->get_dispatch($query)){
            if($val['enable'] == 'true'){
                return $val['url'];
            }
        }
        return $query;
    }//End Function

    /*
     * 处理静态链接
     * @var string $url
     * @access public
     * @return string
     */
    public function parse_route_static_genurl($url)
    {
        if($val = kernel::single('site_route_static')->get_genurl($url)){
            return $val;
        }
        return $url;
    }//End Function

}//End Class
