<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class ectools_view_input{

    function input_region($params){
        if($params['required'] == 'true'){
            $params['vtype'] = 'area';
        }
        $package = kernel::service('ectools_regions.ectools_mdl_regions');
        $params['package'] = $package->key;

        if(!$params['callback']) {
            unset($params['callback']);
        }

        $render = app::get('ectools')->render();
        $render->pagedata['params'] = $params;
        $area_depth = app::get('ectools')->getConf('system.area_depth');
        $aDepth = array();
        for($i=0;$i<$area_depth;$i++) {
            $aDepth[] = $i;
        }
        $render->pagedata['area_depth'] = $aDepth;
        if(ECAE_MODE){
            $render->pagedata['region_data']=app::get('ectools')->getConf('system.region_data');
        }
        if($params['platform']=='iswap'){
            $views = 'wap/common/region.html';
        }else{
            $views = 'common/region.html';
        }
        return $render->fetch($views);
    }
}
