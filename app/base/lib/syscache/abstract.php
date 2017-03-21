<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class base_syscache_abstract{
    private $_last_modify = null;
    private function _get_prefix(){
        return 'syscache_last_modified.'.get_class($this);
        
    }
    public function set_last_modify(){
        $last_modify = time();
        if (base_kvstore::instance('system')->store($this->_get_prefix(), $last_modify)) {
            $this->_last_modify = $last_modify;
            return true;
        }
        return false;
    }

    public function get_last_modify(){
        if (!isset($this->_last_modify)) {
            if (base_kvstore::instance('system')->fetch($this->_get_prefix(), $last_modify)===true &&
                !is_null($last_modify)){
                $this->_last_modify = $last_modify;
            }else{
                $this->_last_modify = 123450001;
            }
        }
        return $this->_last_modify;
    }
}
