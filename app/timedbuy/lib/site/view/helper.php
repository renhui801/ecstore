<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class timedbuy_site_view_helper 
{

    function function_header($params, &$smarty)
    {
		if ($smarty->app->app_id != 'b2c' && $smarty->app->app_id != 'timedbuy') return;
		
		$app_dir = app::get('timedbuy')->app_dir;
		$smarty->pagedata['ec_res_url'] = app::get('timedbuy')->res_url;
		/** 不同的页面扩展不同的css **/
		$ext_filename = $smarty->_request->get_app_name() . '_' . $smarty->_request->get_ctl_name() . '.html';
		if (file_exists($app_dir.'/view/site/common/ext/'.$ext_filename))
			$smarty->pagedata['extends_header'] .= $smarty->fetch('site/common/ext/'.$ext_filename,'timedbuy');
		/** end **/
    }

}//结束