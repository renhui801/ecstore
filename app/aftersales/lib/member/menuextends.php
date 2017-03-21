<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 这个类实现前台会员中心按钮的的扩展
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package aftersales.lib
 */
class aftersales_member_menuextends
{
    /**
     * 构造方法
     * @param object app
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

	/**
	 * 生成自己app会员中心的菜单
	 * @param array - 会员中心的菜单数组，引用值
	 * @param array - url 参数
	 * @return boolean - 是否成功
	 */
	public function get_extends_menu(&$arr_menus, $args=array())
	{
        $arr_menus[0]['items'][]= array(
                'label' => $this->app->_('申请退换货'),
                'app'=>'aftersales',
                'ctl'=>'site_member',
                'link'=>'afterlist',
        );
		return true;
	}
}
