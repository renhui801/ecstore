<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class dev_sandbox{

    function show($result){
        print_r($result->get_callback_params());
        print_r($result->get_status());
        print_r($result->get_data());
        print_r($result->get_result());
        print_r($result->get_pid());
    }

}
