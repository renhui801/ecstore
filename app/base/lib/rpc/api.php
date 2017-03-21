<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class base_rpc_api{

    function __construct(){
    }

    private function break_client($message='Process is running'){
        header('Connection: close');
        header('Content-length: '.strlen($message));
        echo $message;
    }

}
