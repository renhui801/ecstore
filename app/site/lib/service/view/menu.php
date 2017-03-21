<?php


class site_service_view_menu {
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

    function function_menu(){
        $html = array();
        //$shop_base = app::get('site')->router()->gen_url(array('app'=>'site', 'ctl'=>'sitemaps', 'act'=>'catalog'));
        //$shop_base = substr( $shop_base, 0, -(strpos(strrev($shop_base), '.')) ) . 'xml';
        //$html[] = "<a href='$shop_base' target='_blank'>sitemaps</a>";
        return $html;
    
    }
}