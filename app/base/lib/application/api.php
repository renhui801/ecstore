<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class base_application_api extends base_application_prototype_xml{

    var $xml='api.xml';
    var $xsd='base_api';
    var $path = 'module';

    function current(){
        $this->current = $this->iterator()->current();
        $this->key = $this->current['id'];
        return $this;
    }

    function install(){
        $row = $this->row();
        $app_id = $row['app_id'];
        base_kvstore::instance('apilist')->fetch('api_list_array', $apilist);
        foreach( (array)$this->current['api'] as $k=>$row ){
            $api_module['class'] = $this->current['class'];
            $api_module['apiType'] = $this->current['type'];
            $api_module['title'] = $row['value'];
            $api_module['function'] = $row['function'];

            $key = 'api.'.$this->key.'.'.$row['function'];
            $apilist[$app_id][$key] = $key;
            app::get('base')->setConf($key,$api_module);
            logger::info("Installing api => ".$this->key.'.'.$row['function'].'  '.$row['value']);

            base_kvstore::instance('apilist')->store('api_list_array', $apilist);
        }
    }

    function clear_by_app($app_id){
        if(!$app_id){
            return false;
        }

        base_kvstore::instance('apilist')->fetch('api_list_array', $apilist);
        foreach( (array)$apilist[$app_id] as $key ){
            app::get('base')->setConf($key,'');
        }
        $apilist[$app_id] = array();
        base_kvstore::instance('apilist')->store('api_list_array', $apilist);
    }
}
