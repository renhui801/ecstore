<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_friend_links(&$setting,&$render){
    $link_info = app::get('site')->model('link')->getList('*','',0,$setting['limit'],'orderlist ASC');
    return $link_info;
}
?>
