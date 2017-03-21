<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class base_syscache_adapter_chdb extends base_syscache_adapter_abstract implements base_interface_syscache_adapter{

    private $_controller = null;

    protected $_handler = null;

    private function _get_pathname() {
        return TMP_DIR.'/'.$this->get_key();
    }

    public function init_data(){
        try {
            $this->_controller = new chdb($this->_get_pathname());
        } catch (Exception $e){
            return false;
        }
        return true;
    }
    

    public function create($data){
        foreach( (array)$data as $k => $v ){
            $data[$k] = serialize($v);
        }
        chdb_create($this->_get_pathname(), $data);
        return true;
    }

    public function get($key){
        $value = $this->_controller->get($key);
        if (is_null($value)) {
            return null;
        }
        return unserialize($value)?unserialize($value):null;
    }
}
