<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class base_component_session{

    function sess_id(){
        return session_id();
    }

    function start(){
        return session_start();
    }

    function close(){
        return session_write_close();
    }

}
