<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
function theme_widget_logo($setting,&$smarty){
    $logo_id = app::get('b2c')->getConf('site.logo');
    $result['logo_image'] = base_storager::image_path($logo_id);
    return $result;
}
?>
