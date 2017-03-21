<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class base_application_cache_expires extends base_application_prototype_filepath  
{
    var $path = 'dbschema';

    public function install() 
    {
        $dbschema = $this->getPathname();
        if(is_file($dbschema)){
            require($dbschema);
            foreach($db AS $key=>$val){
                if($val['ignore_cache'] !== true){
                    $data['type'] = 'DB';
                    $data['app'] = $this->target_app->app_id;
                    $data['name'] = strtoupper($this->target_app->app_id . "_" . $key);
                    $data['expire'] = time();
                    logger::info('Installing Cache_Expires DB:'. $data['name']);
                    app::get('base')->model('cache_expires')->replace($data,
                        array('type'=>$data['type'],'app'=>$data['app'],'name'=>$data['name'])
                        );
                }
                break;
            }
            logger::info('UPDATE CACHE EXPIRES KV DATA');
            cachemgr::store_vary_list(cachemgr::fetch_vary_list(true)); //更新kv
        }
    }//End Function

    function clear_by_app($app_id){
        if(!$app_id){
            return false;
        }
        app::get('base')->model('cache_expires')->delete(array(
            'app'=>$app_id));
    }

}//End Class
