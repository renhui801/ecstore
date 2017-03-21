<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class base_rpc_service_basic{

    function ping($params, &$service){
        return func_get_args();
    }

    function time(){
        trigger_error('asdfasfsf',E_USER_ERROR);
        return date(DATE_RFC822);
    }

}
