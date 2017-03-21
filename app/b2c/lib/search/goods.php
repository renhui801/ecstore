<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_search_goods {

    /*
     *  是否开启search
     * */
    public function is_search_status(){
        //搜索逻辑处理app是否启用
        $searchruleActive = app::get('searchrule')->getConf('app_is_actived');
        if(is_null($searchruleActive)){
            $searchruleActive = app::get('searchrule')->is_actived();
            $searchruleActive = $searchruleActive ? 'true' : 'false';
            app::get('searchrule')->setConf('app_is_actived',$searchruleActive);
        }
        //搜索app是否启用
        $searchActive = app::get('search')->getConf('app_is_actived');
        if(is_null($searchActive)){
            $searchActive = app::get('search')->is_actived();
            $searchActive = $searchActive ? 'true' : 'false';
            app::get('search')->setConf('app_is_actived',$searchActive);
        }
        //搜索服务是否启用
        $search = app::get('search')->getConf('server.search_server');
        if($searchruleActive == 'true' && $searchActive =='true' && $search){
            return true;
        }else{
            return false;
        }
    }
}
?>
