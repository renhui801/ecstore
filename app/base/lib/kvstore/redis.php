<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

/*
 * @package base
 * @copyright Copyright (c) 2010, shopex. inc
 * @author edwin.lzh@gmail.com
 * @license 
 */
class base_kvstore_redis extends base_kvstore_abstract implements base_interface_kvstore_base,base_interface_kvstore_extension
{
    static private $_cacheObj;

    function __construct($prefix)
    {
        $this->connect();
        $this->prefix = $prefix;
    }//End Function

    public function connect() 
    {
        if(!isset(self::$_cacheObj))
        {
            if(defined('KVSTORE_REDIS_CONFIG') && constant('KVSTORE_REDIS_CONFIG'))
            {
                self::$_cacheObj = new Redis();
                $config = explode(':', KVSTORE_REDIS_CONFIG);
                self::$_cacheObj->connect($config[0], $config[1]);
            } else {
                trigger_error('Can\'t load KVSTORE_REDIS_CONFIG, please check it', E_USER_ERROR);
            }
        }
    }//End Function

    public function fetch($key, &$value, $timeout_version=null)
    {
        $store = self::$_cacheObj->get($this->create_key($key));
        $store = json_decode($store,true);
        if($store !== false)
        {
            if($timeout_version < $store['dateline'])
            {
                if($store['ttl'] > 0 && ($store['dateline']+$store['ttl']) < time()){
                    return false;
                }
                $value = $store['value'];
                return true;
            }
        }
        return false;
    }//End Function

    public function store($key, $value, $ttl=0)
    {
        $store['value'] = $value;
        $store['dateline'] = time();
        $store['ttl'] = $ttl;
        return self::$_cacheObj->set($this->create_key($key), json_encode($store));
    }//End Function

    public function delete($key)
    {
        return self::$_cacheObj->delete($this->create_key($key));
    }//End Function

    public function recovery($record)
    {
        $key = $record['key'];
        $store['value'] = $record['value'];
        $store['dateline'] = $record['dateline'];
        $store['ttl'] = $record['ttl'];
        return self::$_cacheObj->set($this->create_key($key), json_encode($store));
    }//End Function

    public function increment($key, $offset=1)
    {
        $real_key = $this->create_key($key);
        return self::$_cacheObj->incr($real_key, $offset);
    }//End Function

    public function decrement($key, $offset=1)
    {
        $real_key = $this->create_key($key);
        return self::$_cacheObj->decr($real_key, $offset);
    }//End Function
}//End Class

