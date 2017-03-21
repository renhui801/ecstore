<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class desktop_router implements base_interface_router{

    function __construct($app){
        $this->app = $app;

        if(app::get('site')->getConf('desktop.whitelist.enabled')) {
            $remote_ip = base_request::get_remote_addr();
            $ips = app::get('site')->getConf('desktop.whitelist.ips');
            $error_code = app::get('site')->getConf('desktop.whitelist.error_code');
            $forbidden = true;
            foreach ($ips as $ip) {
		$ip = trim($ip);
		if(!$ip) {
			continue;
		}
                if ( base_request::ip_in_range($remote_ip, $ip)) {
                    $forbidden = false;
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
    }

    function gen_url($params=array(),$full=false){
        $params = utils::http_build_query($params);
        if($params){
            return $this->app->base_url($full).'index.php?'.$params;
        }else{
            return $this->app->base_url($full);
        }
    }

    function dispatch($query){
        // 目录遍历漏洞过滤
        $this->check_get($_GET);

        $_GET['ctl'] = $_GET['ctl']?$_GET['ctl']:'default';
        $_GET['act'] = $_GET['act']?$_GET['act']:'index';
        $_GET['app'] = $_GET['app']?$_GET['app']:'desktop';
        logger::debug(sprintf('Desktop access: "app:%s ctl:%s, act:%s"', $_GET['app'], $_GET['ctl'], $_GET['act']));
        $query_args = $_GET['p'];

        $controller = app::get($_GET['app'])->controller($_GET['ctl']);

        $server = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] :  $_SERVER['SERVER_NAME'];
        if(app::get('desktop')->getConf('use_ssl') && $_SERVER['SERVER_PORT'] == '80') {
            header("Location:https://" . $server . ':443'. $_SERVER['REQUEST_URI']);
            exit();
        } elseif (!app::get('desktop')->getConf('use_ssl') && $_SERVER['SERVER_PORT'] == '443') {
            header("Location:http://" . $server . $_SERVER['REQUEST_URI']);
            exit();
        }
        $arrMethods = get_class_methods($controller);
        if (in_array($_GET['act'], $arrMethods))
            call_user_func_array(array(&$controller,$_GET['act']),(array)$query_args);
        else
            call_user_func_array(array(&$controller,'index'),(array)$query_args);
    }

    /**
     * 检查目录遍历漏洞的特殊字符
     * @param  string $var get数据
     */
    function check_char($var){
        $char1 = strchr($var,"../");
        $char2 = strchr($var,"./");
        $char3 = strchr($var,"..");
        $char4 = strchr($var,".");
        $char5 = strchr($var,"/");
        if($char1 || $char2 || $char3 || $char4 || $char5){
            throw new exception('Url you visit a security risk！');
            exit;
        }
    }

    /**
     * 过滤get参数特殊字符
     * @param  array $data
     */
    function check_get($data){
        $arr_key = array('app','ctl');
        foreach($arr_key as $key){
            $this->check_char($data[$key]);
        }
    }
}
