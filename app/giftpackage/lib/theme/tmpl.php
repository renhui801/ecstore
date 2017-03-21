<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * @package default
 * @author kxgsy163@163.com
 */
class giftpackage_theme_tmpl
{
    
    /*
     * return tmpl
     */
    public function __get_tmpl_list()
    {
        $ctl = array(
            'giftpackage' => '礼包页',
        );
        return $ctl;
    }
    #End Func
}