<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


interface site_interface_router{
    
	/**
	* route 规则是否启用
	* 
	* @return bool 
	*/
    public function enable();

	/**
	* 生成链接:通过参数生成相应的访问地址
	* 
	* @param array $params 控制器，方法，参数 等
	* @return string  返回访问地址
	*/
    public function gen_url($params = array());
    
	/**
	* 分发:修改路由访问的query_info
	* 
	* @param array $params 控制器，方法，参数 等
	* @return string  返回访问地址
	*/
    public function modify_query(&$query_info);
    
    
}
