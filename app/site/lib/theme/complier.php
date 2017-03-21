<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class site_theme_complier 
{

    function compile_main($tag_args, &$smarty){
        return '?><div class="system-widgets-box">'.app::get('site')->_('[系统功能区块，无法可视化编辑]').'</div><?php';
    }

    function compile_widgets($tag_args, &$smarty){
        if($tag_args['id']){
            $id = ','.$tag_args['id'];
        }
		
		if ($tag_args['id'])
			return '$s=$this->_files[0];
			$i = intval($this->_wgbar[$s]++);
			echo \'<div class="shopWidgets_panel" base_file="\'.$s.\'" base_slot="\'.$i.\'" base_id='.$tag_args['id'].' widgets_theme="">\';
			kernel::single(\'site_theme_widget\')->admin_load($s,$i'.$id.');echo \'</div>\';';
		else
			return '$s=$this->_files[0];
			$i = intval($this->_wgbar[$s]++);
			echo \'<div class="shopWidgets_panel" base_file="\'.$s.\'" base_slot="\'.$i.\'" base_id="" widgets_theme="">\';
			kernel::single(\'site_theme_widget\')->admin_load($s,$i'.$id.');echo \'</div>\';';

    }
}//End Class
