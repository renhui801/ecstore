<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class pointprofessional_point_info
{
    /**
     * 公开构造方法
     * @params app object
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
		$this->app_b2c = app::get('b2c');
    }


	/**
	 * 我的积分 -冻结积分
	 * @param array - 会员ID
	 * @return string html
	 */
	public function gen_extend_detail_point($member_id)
	{
		if (!$member_id)
			return '';

        $obj_member = $this->app->model('members');
		$render = $this->app->render();
		$render->pagedata['obtained_point'] = $obj_member->get_obtained($member_id);
        $render->pagedata['expense_point'] = $obj_member->get_freez_point($member_id);
		return $render->fetch('site/member/info_point.html');
	}

	/**
	 * 获得当前有效积分
	 * @param int member id
	 * @param int 有效积分
	 * @return boolean
	 */
	public function get_real_point($member_id, &$real_point=0)
	{
		if (!$member_id)
			return false;

		$obj_member_point = $this->app->model('member_point');
		$real_point = $obj_member_point->get_total_count($member_id);
		return true;
	}

	/**
	 * 得到可用的积分
	 * @param int member id
	 * @param int 可用积分
	 * @return boolean
	 */
	public function get_usage_point($member_id, &$usage_point=0)
	{
		if (!$member_id)
			return false;

		$obj_member = $this->app->model('members');
		$usage_point = $obj_member->get_real_point($member_id);
		$freez_point = $obj_member->get_freez_point($member_id);
		$usage_point = $usage_point - $freez_point;

		return true;
	}

	/**
	 * @param member id
	 * @return mixed 有效积分记录
	 */
	public function get_usage_point_history($member_id)
	{
		if (!$member_id)
			return false;

		$obj_member_point = $this->app->model('member_point');
		return $obj_member_point->get_usable_point($member_id);
	}
}
