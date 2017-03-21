<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class base_command_api extends base_shell_prototype{

    var $command_list = '列出所有ECstore提供的api列表';
    
    public function command_list(){
        base_kvstore::instance('apilist')->fetch('api_list_array', $apilist);
        foreach( (array)$apilist  as $app_id => $row){
            logger::info("提供API的APP：".$app_id);
            foreach( (array)$row as $key ){
                $module = app::get('base')->getConf($key);
                $key = substr($key,4);
                //logger::info("  -API类型为：".$module['apiType']);
                logger::info("  API：".$key.'  '. $module['title']);
            }
        }
    }
}
