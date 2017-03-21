<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_sales_basic_input_selectmulti
{
    public $type = 'selectmulti';
    public function create($aData, $table_info=array()) {
        $aData['multi'] = true;
        return kernel::single('b2c_sales_basic_input_selectmulti')->create($aData);
    }
}
?>
