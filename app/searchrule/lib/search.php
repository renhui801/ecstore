<?php

class searchrule_search
{
    private static $_instance = array();

    static public function instance($type)
    {
        if(!isset(self::$_instance[$type])){
            $service = kernel::service('server.search_type.'.$type);
            if($service){
                $search_server = search_core::instance();
                if($search_server){
                    $search_server->_index = $type;
                    $service->search_server = $search_server;
                }else{
                    return false;
                }
                self::$_instance[$type] = $service;
            }else{
                return false;
            }
        }else{
            self::$_instance[$type]->search_server->_index=$type;
        }
        return self::$_instance[$type];
    }//End Function

}//End Class
