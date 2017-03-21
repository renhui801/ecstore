<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class pointprofessional_extension_settings
{
	/**
     * 公开构造方法
     * @params app object
     * @return null
     */
    public function __construct($app)
    {        
        $this->app = $app;
    }
	
	/**
	 * 扩展setting的方法
	 * @param array 引用的数组
	 * @return null
	 */
	public function settings(&$arr_settings=array())
	{
		$arr_ext_settings = array(
			'site.point_expired',
			'site.point_expried_method',
			'site.point_expired_value',
			'site.point_max_deductible_method',
			'site.point_max_deductible_value',
			'site.point_deductible_value',
			'site.get_point_interval_time',
			'site.get_policy.stage',
			'site.consume_point.stage',
			'site.point_usage',
		);
		
		$arr_settings[app::get('b2c')->_('积分设置')] = array_merge($arr_settings[app::get('b2c')->_('积分设置')], $arr_ext_settings);
	}
}