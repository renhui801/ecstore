<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class logisticstrack_mdl_logistic_log extends dbeav_model {
    var $has_many = array(
    );

    public function store( $data, $id ) {
		return parent::replace($data, array('delivery_id'=>$id));
    }
    
    /**
     * 重写getlist方法，重写排序方式
     */
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null) {
        return parent::getList($cols, $filter, 0, 1);
    }
}
