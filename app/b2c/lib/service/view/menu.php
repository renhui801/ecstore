<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_service_view_menu{
    function function_menu(){
        $shop_base = app::get('site')->router()->gen_url(array('app'=>'site', 'ctl'=>'default'));
        $html[] = "<a href='$shop_base' target='_blank'>".app::get('b2c')->_('浏览商店')."</a>";
        $html[] = "<a href='http://www.shopex.cn/help/ecstore' target='_blank'>".app::get('b2c')->_('帮助')."</a>";
        return $html;
    
    }
}