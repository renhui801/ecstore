<?php

class search_core
{
    private static $_instance = array();

    private static $_segment = array();

    static public function instance()
    {
        $service = app::get('search')->getConf('server.search_server');
        if(!isset(self::$_instance[$service]) && !empty($service)){
            $server = kernel::single($service);
            if($service && $server instanceof search_interface_search){
                self::$_instance[$service] = $server;
            }else{
                return false;
            }
        }
        return self::$_instance[$service];
    }//End Function

    static public function segment()
    {
        $service = app::get('search')->getConf('server.search_segment');
        $type = 'scws';
        if(!isset(self::$_instance[$type])){
            $service_name = 'search_segment_scws';
            $service = kernel::single($service_name);
            self::$_segment[$type] = $service;
        }
        return self::$_segment[$type];
    }//End Function

}//End Class
