<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 

 * 此类为每个app接管前台页面的显示内容
 * 一般分两个方法function_header，function_footer用于接管头部
 */
class stats_site_view_helper 
{
    /**
     * 接管头部的方法
     * @param array 头部接管时传递过来的参数
     * @param object 页面的render
     */
    public function function_header($params, &$smarty)
    {
        return '';
    }
    
    /**
     * 接管底部的方法
     * @param array 接管底部时传递过来的参数
     * @param object 页面的render
     */
    public function function_footer($params, &$smarty)
    {
        if ($smarty->is_splash === false)
        {
            $obj_stats_modifier = kernel::single('stats_modifiers');
            return $obj_stats_modifier->print_footer();
        }
        else
        {
            return '';
        }
    }
}