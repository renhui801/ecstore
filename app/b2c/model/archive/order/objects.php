<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class b2c_mdl_archive_order_objects extends dbeav_model{

    var $has_many = array(
        'order_items'=>'archive_order_items:contrast:obj_id^obj_id',
    );
    
    
}
