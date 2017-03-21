<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

interface pointprofessional_point_task_interface
{
	/**
	 * 得到添加积分的类型
	 * @param null
	 * @return string 返回类型
	 */
	public function get_point_task_type();
	
	/**
	 * 生成临时表结构
	 * @param array - 所有所需的参数
	 * @param array - 引用数据值，需要的结果
	 * @return null
	 */
	public function generate_data($arr_data=array(), &$arr_point_task);
}