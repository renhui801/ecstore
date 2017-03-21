<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class recommended_task 
{
    public function post_install()
    {
        logger::info('Initial recommended');
        kernel::single('base_initial', 'recommended')->init();
    }
	
	public function post_update($dbinfo)
	{
		$dbver = $dbinfo['dbver'];
		$app_xml = kernel::single('base_xml')->xml2array(file_get_contents(app::get('recommended')->app_dir.'/app.xml'),'base_app');
		if ($app_xml['version'] == '0.2' && $app_xml['version'] > $dbver){
			$goods = app::get('recommended')->model('goods');
			$filter = array('secondary_goods_id|has'=>',');
			$arr = $goods->getList('*',$filter);
			if ($arr){
				foreach ($arr as $_arr_goods){
					$temp = explode(',',$_arr_goods['secondary_goods_id']);
					if ($temp&&is_array($temp)){
						foreach ($temp as $_arr){
							$item = array(
								'primary_goods_id'=>$_arr_goods['primary_goods_id'],
								'secondary_goods_id'=>$_arr,
								'last_modified'=>$_arr_goods['last_modified'],
							);
							$goods->replace($item, array('primary_goods_id'=>$item['primary_goods_id'],'secondary_goods_id'=>$item['secondary_goods_id']));
						}
					}
					$goods->delete(array('primary_goods_id'=>$_arr_goods['primary_goods_id'],'secondary_goods_id'=>$_arr_goods['secondary_goods_id']));
				}
			}
			$goods_period = app::get('recommended')->model('goods_period');
			$arr = $goods_period->getList('*',$filter);
			if ($arr){
				foreach ($arr as $_arr_goods){
					$temp = explode(',',$_arr_goods['secondary_goods_id']);
					if ($temp&&is_array($temp)){
						foreach ($temp as $_arr){
							$item = array(
								'primary_goods_id'=>$_arr_goods['primary_goods_id'],
								'secondary_goods_id'=>$_arr,
								'last_modified'=>$_arr_goods['last_modified'],
							);
							$goods_period->replace($item, array('primary_goods_id'=>$item['primary_goods_id'],'secondary_goods_id'=>$item['secondary_goods_id']));
						}
					}
					$goods_period->delete(array('primary_goods_id'=>$_arr_goods['primary_goods_id'],'secondary_goods_id'=>$_arr_goods['secondary_goods_id']));
				}
			}
		}
	}
}
