<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class recommended_mdl_goods_period extends dbeav_model {
	var $has_tag   = true;
	var $defaultOrder = array( 'last_modified', 'DESC' );
	var $has_one = array(
	    
	);
}