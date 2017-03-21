<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class pointprofessional_member_menuextends
{
    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

	/**
	 * 生成自己app会员中心的菜单
	 * @param array - 会员中心的菜单数组，引用值
	 * @return boolean - 是否成功
	 */
	public function get_extends_menu(&$arr_menus)
	{
		$site_get_policy_method = app::get('b2c')->getConf('site.get_policy.method');
		if ($site_get_policy_method != '1')
		{
			$arr_menus[2]['items'][] = array(
				'label'=>app::get('pointprofessional')->_('我的积分'),
				'app'=>'pointprofessional',
				'ctl'=>'site_point',
				'link'=>'point_detail',
			);
		}

		return true;
	}
}
