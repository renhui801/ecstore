<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
interface base_interface_model{
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null);
    public function count($filter=null);
    public function get_schema();
}
