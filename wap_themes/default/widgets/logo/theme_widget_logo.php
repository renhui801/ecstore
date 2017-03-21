<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2013 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_logo($setting,&$smarty){
    $logo_id = app::get('wap')->getConf('wap.logo');
    $result['logo_image'] = base_storager::image_path($logo_id);
    return $result;
}
?>
