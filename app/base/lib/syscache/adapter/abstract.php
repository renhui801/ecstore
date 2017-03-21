<?php
/**
 * syscache适配器抽象类
 *
 * @link http://www.shopex.cn
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @package base
 * @author bryant.yan@gmail.com
 */

abstract class base_syscache_adapter_abstract{
    
    /*
     * 生成经过处理的唯一key
     * @var string $key
     * @access public
     * @return string
     */
    protected function get_key(){
        $key = 'ecstore-'.substr(get_class($this), strrpos(get_class($this), '_')+1).'-'.get_class($this->_handler).'-cache-'.md5(ROOT_DIR).'-'.$this->_handler->get_last_modify();
        return $key;
    }

    public function init($handler){
        $this->_handler = $handler;
        return $this->init_data();
    }
}