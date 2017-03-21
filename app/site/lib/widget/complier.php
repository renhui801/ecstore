<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class site_widget_complier 
{

    function compile_widget($tag_args, &$smarty){
        return '$s=$this->_files[0];
        $i = intval($this->_wgbar[$s]++);
        echo \'<div class="shopWidgets_panel">\';
        kernel::single(\'site_widget_proinstance\')->admin_load('.$tag_args['wid'].');echo \'</div>\';';

    }
}//End Class
