<?php
/**
 * ShopEx licence
 *
 * @category ecos
 * @package image.lib
 * @author shopex ecstore dev dev@shopex.cn
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @version 0.1
 */
 
/** 
 * 定义图片库的设置 
 * 
 * @category   ecos 
 * @package    image.lib
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license    http://ecos.shopex.cn/ ShopEx License
 */
interface image_interface_set{
	/**
	 * 设置配置结构
	 * @param array 数据数组
	 * @return boolean true or false
	 */
    public function setconfig($data);
}

?>